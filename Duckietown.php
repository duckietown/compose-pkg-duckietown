<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


namespace system\packages\duckietown;

use \system\classes\Core;
use \system\classes\Configuration;
use \system\classes\Utils;
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
    
    const STORAGE_BUCKETS = [
        'duckietown-public-storage',
        'duckietown-private-storage'
    ];
    
    const STORAGE_ACTIONS = [
// NOTE: Only some actions are enabled
//        "abort_multipart_upload",
//        "complete_multipart_upload",
//        "copy_object",
//        "create_bucket",
//        "create_multipart_upload",
//        "delete_bucket",
//        "delete_bucket_analytics_configuration",
//        "delete_bucket_cors",
//        "delete_bucket_encryption",
//        "delete_bucket_inventory_configuration",
//        "delete_bucket_lifecycle",
//        "delete_bucket_metrics_configuration",
//        "delete_bucket_policy",
//        "delete_bucket_replication",
//        "delete_bucket_tagging",
//        "delete_bucket_website",
        "delete_object",
//        "delete_object_tagging",
//        "delete_objects",
//        "delete_public_access_block",
//        "get_bucket_accelerate_configuration",
//        "get_bucket_acl",
//        "get_bucket_analytics_configuration",
//        "get_bucket_cors",
//        "get_bucket_encryption",
//        "get_bucket_inventory_configuration",
//        "get_bucket_lifecycle",
//        "get_bucket_lifecycle_configuration",
//        "get_bucket_location",
//        "get_bucket_logging",
//        "get_bucket_metrics_configuration",
//        "get_bucket_notification",
//        "get_bucket_notification_configuration",
//        "get_bucket_policy",
//        "get_bucket_policy_status",
//        "get_bucket_replication",
//        "get_bucket_request_payment",
//        "get_bucket_tagging",
//        "get_bucket_versioning",
//        "get_bucket_website",
        "get_object",
//        "get_object_acl",
//        "get_object_legal_hold",
//        "get_object_lock_configuration",
//        "get_object_retention",
//        "get_object_tagging",
//        "get_object_torrent",
//        "get_public_access_block",
//        "head_bucket",
        "head_object",
//        "list_bucket_analytics_configurations",
//        "list_bucket_inventory_configurations",
//        "list_bucket_metrics_configurations",
//        "list_buckets",
//        "list_multipart_uploads",
//        "list_object_versions",
//        "list_objects",
        "list_objects_v2",
//        "list_parts",
//        "put_bucket_accelerate_configuration",
//        "put_bucket_acl",
//        "put_bucket_analytics_configuration",
//        "put_bucket_cors",
//        "put_bucket_encryption",
//        "put_bucket_inventory_configuration",
//        "put_bucket_lifecycle",
//        "put_bucket_lifecycle_configuration",
//        "put_bucket_logging",
//        "put_bucket_metrics_configuration",
//        "put_bucket_notification",
//        "put_bucket_notification_configuration",
//        "put_bucket_policy",
//        "put_bucket_replication",
//        "put_bucket_request_payment",
//        "put_bucket_tagging",
//        "put_bucket_versioning",
//        "put_bucket_website",
        "put_object",
//        "put_object_acl",
//        "put_object_legal_hold",
//        "put_object_lock_configuration",
//        "put_object_retention",
//        "put_object_tagging",
//        "put_public_access_block",
//        "restore_object",
//        "select_object_content",
//        "upload_part",
//        "upload_part_copy"
    ];
    
    
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
                if (Core::getUserRole('duckietown') == 'candidate' && Configuration::$PAGE != self::$CANDIDATE_PAGE) {
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
        // create user descriptor
        $user_info = [
            "username" => $userid,
            "name" => sprintf('Duckietown User #%s', $duckietown_user_id),
            "email" => '',
            "picture" => 'images/default_user.png',
            "role" => "user",
            "active" => true,
            "pkg_role" => []
        ];
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
                    'Please, contact the administrator'
            ];
        }
        // update duckietown token for the current user
        self::setUserToken($userid, $duckietown_user_id, $duckietoken);
        // (try to) set login system
        try {
            Core::setLoginSystem('DUCKIETOWN_TOKEN');
        } catch (\Exception $e) {
        }
        // set login variables
        $_SESSION['USER_LOGGED'] = true;
        $_SESSION['USER_RECORD'] = $user_info;
        // ---
        Core::regenerateSessionID();
        return ['success' => true, 'data' => $user_info];
    }//logInUserWithDuckietoken
    
    
    public static function getStorageSpacePermissionsForUser($uid=null, $bucket=null, $object=null, $action=null) {
        return self::getStorageSpacePermissions('user_storage_permission', $uid, $bucket, $object, $action);
    }//getStorageSpacePermissionsForUser
    
    
    public static function getStorageSpacePermissionsForGroup($gid=null, $bucket=null, $object=null, $action=null) {
        return self::getStorageSpacePermissions('group_storage_permission', $gid, $bucket, $object, $action);
    }//getStorageSpacePermissionsForGroup
    
    
    public static function checkStorageSpacePermissions($uid, $bucket, $object, $action) {
        // get user identities associated to the given UID
        $identities = [
            'user_storage_permission' => [],
            'group_storage_permission' => []
        ];
        $db = new Database('duckietown', 'identity');
        if ($db->key_exists($uid)) {
            $res = $db->read($uid);
            if (!$res['success']) {
                return $res;
            }
            // read identities
            $identities['user_storage_permission'] = $res['data']['identities'];
        }
        // fetch group identities
        foreach ($identities['user_storage_permission'] as $identity) {
            $res = Core::getUserGroups($identity);
            if (!$res['success']) {
                return $res;
            }
            $identities['group_storage_permission'] = array_merge(
                $identities['group_storage_permission'], $res['data']
            );
        }
        // get storage permissions for given bucket on all identities
        foreach ($identities as $db_name => $db_identities) {
            $db = new Database('duckietown', $db_name);
            foreach ($db_identities as $identity) {
                $key = sprintf('%s__%s__%s', $identity, $bucket, $action);
                if ($db->key_exists($key)) {
                    // the user has an identity that has the wanted permission on the bucket
                    $res = $db->read($key);
                    if (!$res['success']) {
                        return $res;
                    }
                    // get object patterns associated to this permission
                    $object_patterns = $res['data']['objects'];
                    // match given object against patterns
                    foreach ($object_patterns as $pattern) {
                        if (fnmatch($pattern, $object)) {
                            return ['success' => true, 'data' => true];
                        }
                    }
                }
            }
        }
        // no permissions found
        return ['success' => true, 'data' => false];
    }//checkStorageSpacePermissions
    
    
    public static function grantStorageSpacePermissionToUser($uid, $bucket, $object, $action){
        // make sure that the user exists
        if (!Core::userExists($uid)) {
            return ['success' => false, 'data' => "User $uid not found!"];
        }
        // grant permissions
        return self::grantStorageSpacePermission('user', $uid, $bucket, $object, $action);
    }//grantStorageSpacePermissionToUser
    
    
    public static function grantStorageSpacePermissionToGroup($gid, $bucket, $object, $action){
        // make sure that the group exists
        if (!Core::groupExists($gid)) {
            return ['success' => false, 'data' => "Group $gid not found!"];
        }
        // grant permissions
        return self::grantStorageSpacePermission('group', $gid, $bucket, $object, $action);
    }//grantStorageSpacePermissionToGroup
    
    
    public static function revokeStorageSpacePermissionToUser($uid, $bucket, $object, $action){
        // make sure that the user exists
        if (!Core::userExists($uid)) {
            return ['success' => false, 'data' => "User $uid not found!"];
        }
        // revoke permissions
        return self::revokeStorageSpacePermission(
            'user_storage_permission', $uid, $bucket, $object, $action
        );
    }//revokeStorageSpacePermissionToUser
    
    
    public static function revokeStorageSpacePermissionToGroup($gid, $bucket, $object, $action){
        // make sure that the group exists
        if (!Core::groupExists($gid)) {
            return ['success' => false, 'data' => "Group $gid not found!"];
        }
        // revoke permissions
        return self::revokeStorageSpacePermission(
            'group_storage_permission', $gid, $bucket, $object, $action
        );
    }//revokeStorageSpacePermissionToUser
    
    
    // ============================================================================================
    // Private functions
    
    
    private static function getStorageSpacePermissions($db_name, $id=null, $bucket=null, $object=null, $action=null) {
        // create filtering key
        $k_id = is_null($id)? '[A-Za-z0-9_]+' : Utils::string_to_valid_filename($id);
        $k_bucket = is_null($bucket)? '[A-Za-z0-9_]+' : Utils::string_to_valid_filename($bucket);
        $k_action = is_null($action)? '[A-Za-z0-9_]+' : Utils::string_to_valid_filename($action);
        // compile key pattern
        $key_pattern = "/^($k_id)__($k_bucket)__($k_action)$/";
        // get storage permissions for given bucket on all identities
        $db = new Database('duckietown', $db_name, $key_pattern);
        $keys = $db->list_keys();
        // read everything
        $objects = [];
        foreach ($keys as $key) {
            $res = $db->read($key);
            if (!$res['success']) {
                return $res;
            }
            $bkt = $res['data']['bucket'];
            $act = $res['data']['action'];
            if (!array_key_exists($bkt, $objects)) {
                $objects[$bkt] = [];
            }
            foreach ($res['data']['objects'] as $pattern) {
                if (!is_null($object) && !fnmatch($pattern, $object)) {
                    continue;
                }
                if (!array_key_exists($pattern, $objects[$bkt])) {
                    $objects[$bkt][$pattern] = [];
                }
                if (!array_key_exists($act, $objects[$bkt][$pattern])) {
                    $objects[$bkt][$pattern][$act] = [];
                }
                array_push(
                    $objects[$bkt][$pattern][$act],
                    array_filter(
                        $res['data'],
                        function ($k) {return !in_array($k, ['objects']);},
                        ARRAY_FILTER_USE_KEY
                    )
                );
            }
        }
        // ---
        return $objects;
    }//getStorageSpacePermissions
    
    
    private static function grantStorageSpacePermission($entity, $id, $bucket, $object, $action){
        $db = new Database('duckietown', "{$entity}_storage_permission");
        $key = sprintf('%s__%s__%s', $id, $bucket, $action);
        // create default entry payload (will be overwritten if the record already exists)
        $data = [
            'bucket' => $bucket,
            'action' => $action,
            'grantee-type' => $entity,
            'grantee-id' => $id,
            'created-by' => Core::getUserLogged('username'),
            'creation-time' => time(),
            'objects' => []
        ];
        // append to existing record
        if ($db->key_exists($key)) {
            $res = $db->read($key);
            if (!$res['success']) {
                return $res;
            }
            $data = $res['data'];
        }
        // add new permission to entry
        array_push($data['objects'], $object);
        // remove duplicates
        $data['objects'] = array_values(array_unique($data['objects']));
        // commit changes
        return $db->write($key, $data);
    }//grantStorageSpacePermissionToUser
    
    
    private static function revokeStorageSpacePermission($db_name, $id, $bucket, $object, $action){
        $db = new Database('duckietown', $db_name);
        $key = sprintf('%s__%s__%s', $id, $bucket, $action);
        // nothing to do if the permission is not granted
        if (!$db->key_exists($key)) {
            return ['success' => true, 'data' => null];
        }
        // get entry
        $res = $db->read($key);
        if (!$res['success']) {
            return $res;
        }
        $data = $res['data'];
        // remove permission from entry (if it is there)
        $loc = array_search($object, $data['objects']);
        if ($loc === FALSE) {
            return ['success' => true, 'data' => null];
        }
        unset($data['objects'][$loc]);
        $data['objects'] = array_values(array_unique($data['objects']));
        // remove entry if the list of objects is now empty
        if (count($data['objects']) == 0) {
            return $db->delete($key);
        }
        // commit changes
        return $db->write($key, $data);
    }//revokeStorageSpacePermissionToUser
    
}//Duckietown

?>
