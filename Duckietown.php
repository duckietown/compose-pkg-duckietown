<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


namespace system\packages\duckietown;

use Exception;
use \system\classes\Core;
use \system\classes\Configuration;
use \system\classes\Database;
use \system\classes\enum\StringType;


/**
 *   Module for managing entities in Duckietown (e.g., Duckietown, Duckiebots)
 */
class Duckietown {
    
    private static $initialized = false;
    
    // user-specific data
    private static $user_id = null;
    private static $user_token = null;
    
    private static $CANDIDATE_PAGE = 'onboarding';
    
    private static $WP_API_HOSTNAME = 'www.duckietown.org';
    private static $WP_API_URL = 'https://%s/wp-json/wp/v2/%s';

    
    // disable the constructor
    private function __construct() {
    }
    
    /** Initializes the module.
     *
     * @retval array
     *        a status array of the form
     *    <pre><code class="php">[
     *        "success" => boolean,    // whether the function succeded
     *        "data" => mixed        // error message or NULL
     *    ]</code></pre>
     *        where, the `success` field indicates whether the function succeded.
     *        The `data` field contains errors when `success` is `FALSE`.
     */
    public static function init() {
        if (!self::$initialized) {
            // register new user types
            Core::registerNewUserRole('duckietown', 'candidate', self::$CANDIDATE_PAGE);
            Core::registerNewUserRole('duckietown', 'guest');
            Core::registerNewUserRole('duckietown', 'user');
            // update the role of the current user
            if (!Core::isUserLoggedIn()) {
                // set the user role to be a `duckietown:guest`
                Core::setUserRole('guest', 'duckietown');
            } else {
                // set the user role to be a `duckietown:candidate` (by default)
                Core::setUserRole('candidate', 'duckietown');
                $user_role = Core::getUserRole();
                if (in_array($user_role, ['user', 'supervisor', 'administrator'])) {
                    $username = Core::getUserLogged('username');
                    // open tokens database
                    $db = new Database('duckietown', 'authentication');
                    // if the duckietoken entry exists, the user is at least a `duckietown:user`
                    if ($db->key_exists($username)) {
                        $res = $db->read($username);
                        if (!$res['success']) return $res;
                        // read ID and token
                        self::$user_id = $res['data']['uid'];
                        self::$user_token = $res['data']['token'];
                        Core::setUserRole('user', 'duckietown');
                    }
                }
                // redirect to the welcome page (token setup)
                if (Core::getUserRole('duckietown') == 'candidate' &&
                    Configuration::$PAGE != self::$CANDIDATE_PAGE) {
                    Core::redirectTo(self::$CANDIDATE_PAGE);
                }
            }
            //
            return array('success' => true, 'data' => null);
        } else {
            return array('success' => true, 'data' => "Module already initialized!");
        }
    }//init
    
    
    /** Safely terminates the module.
     *
     * @retval array
     *        a status array of the form
     *    <pre><code class="php">[
     *        "success" => boolean,    // whether the function succeded
     *        "data" => mixed        // error message or NULL
     *    ]</code></pre>
     *        where, the `success` field indicates whether the function succeded.
     *        The `data` field contains errors when `success` is `FALSE`.
     */
    public static function close() {
        // do stuff
        return array('success' => true, 'data' => null);
    }//close
    
    
    // ============================================================================================
    // Public functions
    
    public static function getUserToken($user_id = null) {
        if (is_null($user_id))
            return self::$user_token;
        // load token for the given user
        $db = new Database('duckietown', 'authentication');
        if ($db->key_exists($user_id)) {
            $res = $db->read($user_id);
            if (!$res['success']) return $res;
            //
            return $res['data']['token'];
        }
        return null;
    }//getUserToken
    
    
    public static function setUserToken($compose_user_id, $duckietown_user_id, $duckietoken) {
        $res = self::verifyDuckietoken($duckietoken);
        if (!$res['success']) {
            return $res;
        }
        // ---
        // store token
        $db = new Database('duckietown', 'authentication');
        $res = $db->write(
            $compose_user_id,
            [
                'uid' => $duckietown_user_id,
                'token' => $duckietoken
            ]
        );
        if (!$res['success']) {
            return $res;
        }
        // update identities
        $db = new Database('duckietown', 'identity');
        $identities = [];
        if ($db->key_exists($duckietown_user_id)) {
            $res = $db->read($duckietown_user_id);
            if (!$res['success']) {
                return $res;
            }
            $identities = $res['data']['identities'];
        }
        // merge identities
        array_push($identities, $compose_user_id);
        $identities = array_unique($identities);
        // write back to disk
        $res = $db->write(
            $duckietown_user_id,
            [
                'identities' => $identities
            ]
        );
        if (!$res['success']) {
            return $res;
        }
        // ---
        return ['success' => True, 'data' => null];
    }//setUserToken
    
    
    public static function getDuckietownUserId($user_id = null) {
        if (is_null($user_id))
            return self::$user_id;
        // load token for the given user
        $db = new Database('duckietown', 'authentication');
        if ($db->key_exists($user_id)) {
            $res = $db->read($user_id);
            if (!$res['success']) return $res;
            //
            return $res['data']['uid'];
        }
        return null;
    }//getDuckietownUserId
    
    
    public static function verifyDuckietoken($duckietoken) {
        $duckietoken = trim($duckietoken);
        // validate given token
        $parts = explode('-', $duckietoken);
        if (count($parts) != 3) {
            return ['success' => False, 'data' => '[Error DT-20]: Duckietown Token not valid'];
        }
        $expected_length = [3, -1, -1];
        for ($i = 0; $i < 3; $i++) {
            if ($expected_length[$i] > 0 && strlen($parts[$i]) != $expected_length[$i]) {
                return ['success' => False, 'data' => '[Error DT-21]: Duckietown Token not valid'];
            }
            if (!StringType::isAlphaNumeric($parts[$i])) {
                return ['success' => False, 'data' => '[Error DT-22]: Duckietown Token not valid'];
            }
        }
        // ---
        // it is now safe to append the token to the python call
        $token_verifier_py = sprintf(
            '%smodules/login/python/duckietoken.py',
            Core::getPackageDetails('duckietown', 'root')
        );
        $cmd = sprintf('python3 %s "%s"', $token_verifier_py, $duckietoken);
        $output = "";
        $exit_code = 0;
        exec($cmd, $output, $exit_code);
        $success = boolval($exit_code == 0);
        // ---
        $res = json_decode($output[0], True);
        if (!$success) {
            return [
                'success' => False,
                'data' => sprintf('[Error DT-PY-%s]: %s', $res['exit_code'], $res['message'])
            ];
        }
        return ['success' => True, 'data' => $res['data']];
    }//verifyDuckietoken
    
    
    /** Logs in a user using the personal Duckietown Token.
     *
     * @param string $duckietoken
     *        Personal Duckietown token, it can be retrieved
     *        from https://www.duckietown.org/site/your-token;
     *
     * @return array
     * @retval array
     *        a status array of the form
     *    <pre><code class="php">[
     *        "success" => boolean,    // whether the call succeded
     *        "data" => mixed        // error message or NULL
     *    ]</code></pre>
     *        where, the `success` field indicates whether the call succeded.
     *        The `data` field contains an error string when `success` is `FALSE`.
     */
    public static function logInUserWithDuckietoken($duckietoken) {
        if ($_SESSION['USER_LOGGED']) {
            return ['success' => false, 'data' => 'You are already logged in!'];
        }
        // verify duckietoken
        $res = self::verifyDuckietoken($duckietoken);
        if (!$res['success'])
            return $res;
        // ---
        $duckietown_user_id = $res['data']['uid'];
        $userid = sprintf('duckietown_user_%s', $duckietown_user_id);
        // look for a pre-existing user profile
        $user_exists = Core::userExists($userid);
        if ($user_exists) {
            // there exists a user profile, load info
            $res = Core::openUserInfo($userid);
            if (!$res['success']) {
                return $res;
            }
            $user_info = $res['data']->asArray();
        } else {
            // create default user descriptor
            $user_info = [
                "username" => $userid,
                "name" => sprintf('Duckietown User #%s', $duckietown_user_id),
                "email" => '',
                "picture" => 'images/default_user.png',
                "role" => "user",
                "active" => true,
                "pkg_role" => []
            ];
            // (try to) fetch user info
            $res = self::fetchUserInfo($duckietown_user_id);
            // update with remote info (if available)
            if ($res['success']) {
                $user_info = array_merge($user_info, $res['data']);
            }
            // create new user account
            $res = Core::createNewUserAccount($userid, $user_info);
            if (!$res['success']) {
                return $res;
            }
        }
        // make sure that the user is active
        if (!boolval($user_info['active'])) {
            return [
                'success' => false,
                'data' => 'The user profile you are trying to login with is not active. ' .
                    'Please, contact the administrator.'
            ];
        }
        // update duckietown token for the current user
        self::setUserToken($userid, $duckietown_user_id, $duckietoken);
        // (try to) set login system
        try {
            Core::setLoginSystem('DUCKIETOWN_TOKEN');
        } catch (Exception $e) {
        }
        // set login variables
        $_SESSION['USER_LOGGED'] = true;
        $_SESSION['USER_RECORD'] = $user_info;
        // ---
        Core::regenerateSessionID();
        return ['success' => true, 'data' => $user_info];
    }//logInUserWithDuckietoken
    
    
    // ============================================================================================
    // Private functions
    
    
    private static function fetchUserInfo($dt_user_id) {
        $wp_user_url = sprintf(
            self::$WP_API_URL, self::$WP_API_HOSTNAME, sprintf('users/%s', $dt_user_id)
        );
        // get autentication token
        $auth_token = Core::getSetting('wordpress_api/token', 'duckietown', null);
        if (is_null($auth_token) || strlen(trim($auth_token)) <= 0) {
            return ['success' => false, 'data' => 'WP API Token is not set'];
        }
        // setup a cURL session
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_URL, $wp_user_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            sprintf('Authorization: Basic %s', $auth_token)
        ]);
        // perform cURL
        $res = curl_exec($ch);
        curl_close($ch);
        // check if we got nothing
        if ($res === false) {
            $ch_info = curl_getinfo($ch);
            return [
                'success' => false,
                'data' => sprintf('The WP server returned the code %s', $ch_info['http_code'])
            ];
        }
        // it looks like we got something, let's see if it makes any sense
        $wp_info = json_decode($res, true);
        if ($wp_info === false) {
            return [
                'success' => false,
                'data' => sprintf('An error occurred while fetching the user data from ' .
                    'the WP API on %s. Response cannot be parsed.', self::$WP_API_HOSTNAME)
            ];
        }
        // make sure the response contains what we need
        if (!array_key_exists('name', $wp_info) or !array_key_exists('avatar_urls', $wp_info)) {
            return ['success' => false, 'data' => 'Response from WP API cannot be parsed.'];
        }
        // create user descriptor
        return [
            'success' => true,
            'data' => [
                "name" => $wp_info['name'],
                "picture" => $wp_info['avatar_urls']['96']
            ]
        ];
    }//fetchUserInfo
    
    
}//Duckietown

?>
