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
class Duckietown{

  private static $initialized = false;

  // user-specific data
  private static $user_id = null;
  private static $user_token = null;
  private static $user_duckiebot = null;

  private static $DUCKIEBOT_W_CONFIG_DEVICE_VID_PID_LIST = [
    '7392:b822'
  ];
  private static $DUCKIEBOT_D_CONFIG_DEVICE_VID_PID_LIST = [
    '0781:5583',
    '090c:1000'
  ];
  private static $WHAT_THE_DUCK_TESTS_DATA_PATH = "/tmp";


  private static $CANDIDATE_PAGE = 'setup-token';


  // disable the constructor
  private function __construct() {}

  /** Initializes the module.
  *
  *	@retval array
  *		a status array of the form
  *	<pre><code class="php">[
  *		"success" => boolean, 	// whether the function succeded
  *		"data" => mixed 		// error message or NULL
  *	]</code></pre>
  *		where, the `success` field indicates whether the function succeded.
  *		The `data` field contains errors when `success` is `FALSE`.
  */
  public static function init(){
    if( !self::$initialized ){
      // register new user types
      Core::registerNewUserRole('duckietown', 'candidate', 'setup-token');
      Core::registerNewUserRole('duckietown', 'guest');
      Core::registerNewUserRole('duckietown', 'user');
      Core::registerNewUserRole('duckietown', 'engineer');
      // update the role of the current user
      if( !Core::isUserLoggedIn() ){
        // set the user role to be a `duckietown:guest`
        Core::setUserRole('guest', 'duckietown');
      }else{
        // set the user role to be a `duckietown:candidate` (by default)
        Core::setUserRole('candidate', 'duckietown');
        $user_role = Core::getUserRole();
        if( in_array($user_role, ['user', 'supervisor', 'administrator']) ){
          $username = Core::getUserLogged('username');
          // open tokens database
          $db = new Database('duckietown', 'authentication');
          // if the duckietoken entry exists, the user is at least a `duckietown:user`
          if( $db->key_exists($username) ){
            $res = $db->read($username);
            if( !$res['success'] ) return $res;
            // read ID and token
            self::$user_id = $res['data']['uid'];
            self::$user_token = $res['data']['token'];
            Core::setUserRole('user', 'duckietown');
          }
          // open user->duckiebots database
          $db = new Database('duckietown', 'user_to_vehicle');
          // if the duckiebot entry exists, the user is a `duckietown:engineer`
          if( $db->key_exists($username) ){
            $res = $db->read($username);
            if( !$res['success'] ) return $res;
            // read token
            self::$user_duckiebot = $res['data']['vehicle_name'];
            Core::setUserRole('engineer', 'duckietown');
          }
        }
        // redirect to the welcome page (token setup)
        if (Core::getUserRole('duckietown') == 'candidate' && Configuration::$PAGE != self::$CANDIDATE_PAGE) {
          Core::redirectTo(self::$CANDIDATE_PAGE);
        }
      }
      //
      return array( 'success' => true, 'data' => null );
    }else{
      return array( 'success' => true, 'data' => "Module already initialized!" );
    }
  }//init


  /** Safely terminates the module.
  *
  *	@retval array
  *		a status array of the form
  *	<pre><code class="php">[
  *		"success" => boolean, 	// whether the function succeded
  *		"data" => mixed 		// error message or NULL
  *	]</code></pre>
  *		where, the `success` field indicates whether the function succeded.
  *		The `data` field contains errors when `success` is `FALSE`.
  */
  public static function close(){
    // do stuff
    return array( 'success' => true, 'data' => null );
  }//close



  // =======================================================================================================
  // Public functions

  public static function getUserToken( $user_id=null ){
    if( is_null($user_id) )
    return self::$user_token;
    // load token for the given user
    $db = new Database('duckietown', 'authentication');
    if( $db->key_exists($user_id) ){
      $res = $db->read($user_id);
      if( !$res['success'] ) return $res;
      //
      return $res['data']['token'];
    }
    return null;
  }//getUserToken


  public static function setUserToken($compose_user_id, $duckietown_user_id, $duckietoken){
    $res = self::verifyDuckietoken($duckietoken);
    if(!$res['success'])
    return $res;
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
    if(!$res['success'])
    return $res;
    // ---
    return ['success' => True, 'data' => null];
  }//setUserToken


  public static function getDuckietownUserId( $user_id=null ){
    if( is_null($user_id) )
    return self::$user_id;
    // load token for the given user
    $db = new Database('duckietown', 'authentication');
    if( $db->key_exists($user_id) ){
      $res = $db->read($user_id);
      if( !$res['success'] ) return $res;
      //
      return $res['data']['uid'];
    }
    return null;
  }//getDuckietownUserId


  public static function getUserDuckiebot(){
    return self::$user_duckiebot;
  }//getUserDuckiebot


  public static function verifyDuckietoken($duckietoken){
    $duckietoken = trim($duckietoken);
    // validate given token
    $parts = explode('-', $duckietoken);
    if (count($parts) != 3){
      return ['success' => False, 'data' => '[Error DT-20]: Duckietown Token not valid'];
    }
    $expected_length = [3, -1, -1];
    for ($i=0; $i < 3; $i++) {
      if ($expected_length[$i] > 0 && strlen($parts[$i]) != $expected_length[$i]){
        return ['success' => False, 'data' => '[Error DT-21]: Duckietown Token not valid'];
      }
      if (!StringType::isAlphaNumeric($parts[$i])){
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
    if (!$success){
      return [
        'success' => False,
        'data' => sprintf('[Error DT-PY-%s]: %s', $res['exit_code'], $res['message'])
      ];
    }
    return ['success' => True, 'data' => $res['data']];
  }//verifyDuckietoken



  /** Logs in a user using the personal Duckietown Token.
  *
  *	@param string $duckietoken
  *		Personal Duckietown token, it can be retrieved from https://www.duckietown.org/site/your-token;
  *
  *	@retval array
  *		a status array of the form
  *	<pre><code class="php">[
  *		"success" => boolean, 	// whether the call succeded
  *		"data" => mixed 		// error message or NULL
  *	]</code></pre>
  *		where, the `success` field indicates whether the call succeded.
  *		The `data` field contains an error string when `success` is `FALSE`.
  */
  public static function logInUserWithDuckietoken( $duckietoken ){
    if( $_SESSION['USER_LOGGED'] ){
      return ['success' => false, 'data' => 'You are already logged in!'];
    }
    // verify duckietoken
    $res = self::verifyDuckietoken($duckietoken);
    if(!$res['success'])
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
    if( $user_exists ){
      // there exists a user profile, load info
      $res = Core::openUserInfo($userid);
      if( !$res['success'] ){
        return $res;
      }
      $user_info = $res['data']->asArray();
    }else{
      $res = Core::createNewUserAccount($userid, $user_info);
      if( !$res['success'] ){
        return $res;
      }
    }
    // make sure that the user is active
    if( !boolval($user_info['active']) ){
      return [
        'success' => false,
        'data' => 'The user profile you are trying to login with is not active. Please, contact the administrator'
      ];
    }
    // update duckietown token for the current user
    self::setUserToken($userid, $duckietown_user_id, $duckietoken);
    // (try to) set login system
    try {
      Core::setLoginSystem('DUCKIETOWN_TOKEN');
    } catch (\Exception $e) {}
    // set login variables
    $_SESSION['USER_LOGGED'] = true;
    $_SESSION['USER_RECORD'] = $user_info;
    // ---
    Core::regenerateSessionID();
    return ['success' => true, 'data' => $user_info];
  }//logInUserWithDuckietoken

}//Duckietown

?>
