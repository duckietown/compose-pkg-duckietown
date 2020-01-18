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











  // TODO: everything after this line is from Duckietown 2017 and will likely need to be changed

  public static function getDuckiebotsCurrentBranch(){
    $DUCKIEFLEET_PATH = Core::getSetting('duckiefleet_root', 'duckietown');
    $DUCKIEFLEET_BRANCH = Core::getSetting('duckiefleet_branch', 'duckietown');
    exec( "ls -l '".$DUCKIEFLEET_PATH.'/robots/'.$DUCKIEFLEET_BRANCH."' | awk '{print $9}' | grep -E '[a-zA-Z0-9]*.robot.yaml' | sed -e 's/\.robot.yaml$//'", $duckiebots, $exit_code );
    //
    return $duckiebots;
  }//getDuckiebotsCurrentBranch


  public static function getDuckiebotOwner( $bot_name ){
    if( !self::duckiebotExists($bot_name) ){
      return array('success' => false, 'data' => 'Duckiebot not found');
    }
    //
    $DUCKIEFLEET_PATH = Core::getSetting('duckiefleet_root', 'duckietown');
    $DUCKIEFLEET_BRANCH = Core::getSetting('duckiefleet_branch', 'duckietown');
    $yaml_file = $DUCKIEFLEET_PATH.'/robots/'.$DUCKIEFLEET_BRANCH.'/'.$bot_name.'.robot.yaml';
    $yaml_file = str_replace('//', '/', $yaml_file);
    if( !file_exists($yaml_file) ){
      return null;
    }
    $yaml_content = spyc_load_file( $yaml_file );
    if( !isset($yaml_content['owner']) ){
      return null;
    }
    //
    return strtolower( preg_replace('/ /', '', $yaml_content['owner']) );
  }//getDuckiebotOwner


  private static function execCommandOnDuckiebot( $bot_name, $command, $ssh=null ){
    $res = array(
      'success' => false,
      'data' => null,
      'exit_code' => null,
      'connection' => null
    );
    // Set PHP timeout to 5 seconds
    set_time_limit(5);
    // open SSH connection (if needed)
    if( is_null($ssh) ){
      $host = sprintf('%s.local', $bot_name);
      $ssh = ssh2_connect($host);
      if ( $ssh === false ) {
        $res['data'] = 'Host unreachable';
        return $res;
      }
      // authenticate SSH session
      $DUCKIEBOT_DEFAULT_USERNAME = Core::getSetting('duckiebot_username', 'duckietown');
      $DUCKIEBOT_DEFAULT_PASSWORD = Core::getSetting('duckiebot_password', 'duckietown');
      $auth = @ssh2_auth_password($ssh, $DUCKIEBOT_DEFAULT_USERNAME, $DUCKIEBOT_DEFAULT_PASSWORD);
      if ( $auth === false ) {
        $res['data'] = 'Authentication failed';
        return $res;
      }
    }
    $res['connection'] = $ssh;
    // exec command
    $command .= '; echo -e "\n__EXIT_CODE_$?"';
    $return_stream = @ssh2_exec( $ssh, $command );
    stream_set_blocking( $return_stream, true );
    if( strcmp(get_resource_type($return_stream), "stream") !== 0 ){
      $res['data'] = 'Command failed';
      return $res;
    }
    // get stream content
    $stream_content = stream_get_contents( $return_stream );
    // get exit code
    $exit_code = Utils::regex_extract_group($stream_content, "/.*__EXIT_CODE_([0-9]+).*/", 1);
    $stream_content = trim( preg_replace( "/.*__EXIT_CODE_([0-9]+).*/", "", $stream_content, 1 ) );
    // create response object
    $res['success'] = true;
    $res['exit_code'] = $exit_code;
    $res['data'] = $stream_content;
    return $res;
  }


  private static function getROScommand( $command ){
    $DUCKIEBOT_ROS_PATH = Core::getSetting('duckiebot_ros_path', 'duckietown');
    return sprintf('source %s/setup.bash; %s', $DUCKIEBOT_ROS_PATH, $command);
  }//getROScommand


  public static function authenticateOnDuckiebot( $bot_name, $username, $password, $protectDefaultUser=true ){
    // prepare result object
    $res = array(
      'success' => false,
      'data' => null,
      'connection' => null
    );
    // check whether the Duckiebot exists
    if( !self::duckiebotExists($bot_name) ){
      $res['data'] = 'Duckiebot not found';
      return $res;
    }
    // check whether the username provided matches the default backdoor username used by the platform
    if( $protectDefaultUser ){
      $DUCKIEBOT_DEFAULT_USERNAME = Core::getSetting('duckiebot_username', 'duckietown');
      if( strcasecmp( trim($username), trim($DUCKIEBOT_DEFAULT_USERNAME) ) == 0 ){
        $res['data'] = sprintf('The user `%s` is protected. Create your own user to continue.', $DUCKIEBOT_DEFAULT_USERNAME);
        return $res;
      }
    }
    // Set PHP timeout to 5 seconds and open SSH connection
    set_time_limit(5);
    $host = sprintf('%s.local', $bot_name);
    $ssh = ssh2_connect($host);
    if( $ssh === false ){
      $res['data'] = 'SSH connection timed out';
      return $res;
    }
    // authenticate SSH session
    $auth = @ssh2_auth_password($ssh, $username, $password);
    if( $auth === false ) {
      $res['data'] = 'Authentication failed';
      return $res;
    }
    $res['success'] = true;
    $res['data'] = 'OK';
    $res['connection'] = $ssh;
    //
    return $res;
  }//authenticateOnDuckiebot


  public static function getDuckiebotNetworkConfig( $bot_name ){
    if( !self::duckiebotExists($bot_name) ){
      return array('success' => false, 'data' => 'Duckiebot not found');
    }
    //
    $command = "ifconfig -a | sed -e 's/^$/=====/'";
    $res = self::execCommandOnDuckiebot( $bot_name, $command );
    if( $res['success'] ){
      $data = $res['data'];
      $interfaces_strs = explode("=====", $data);
      $interfaces = array();
      // iterate over the interfaces
      foreach ($interfaces_strs as $interface_str) {
        if( strlen($interface_str) <= 10 ) continue;
        $interface_str = trim( $interface_str );
        // get interface name
        $interface_name = trim( Utils::regex_extract_group($interface_str, "/(.+) Link encap:.*/", 1) );
        // get interface MAC address
        $interface_mac = Utils::regex_extract_group($interface_str, "/.*HWaddr ([a-z0-9:]{17}).*/", 1);
        if( $interface_mac == null ){
          $interface_mac = 'ND';
        }
        // get status and IP address
        $interface_connected = true;
        $interface_IP = Utils::regex_extract_group($interface_str, "/.*inet addr:([0-9\.]+).*/", 1);
        $interface_mask = Utils::regex_extract_group($interface_str, "/.*Mask:([0-9\.]+).*/", 1);
        if( $interface_IP == null ){
          $interface_IP = 'ND';
          $interface_mask = 'ND';
          $interface_connected = false;
        }
        array_push( $interfaces, array(
          'name' => $interface_name,
          'connected' => $interface_connected,
          'mac' => $interface_mac,
          'ip' => $interface_IP,
          'mask' => $interface_mask
        ) );
      }
      return $interfaces;
    }
    return $res;
  }//getDuckiebotNetworkConfig


  public static function getDuckiebotDiskStatus( $bot_name ){
    if( !self::duckiebotExists($bot_name) ){
      return array('success' => false, 'data' => 'Duckiebot not found');
    }
    //
    // get list of mounted devices and their mount points
    $command = "mount | grep '^/dev' | awk '{print $3\",\"$1}'";
    $res = self::execCommandOnDuckiebot( $bot_name, $command );
    $devices = array();
    if( $res['success'] ){
      foreach( explode("\n", $res['data']) as $dev ){
        $dev = explode(",", $dev);
        $dev_mountpoint = $dev[0];
        $dev_name = $dev[1];
        $devices[ $dev_mountpoint ] = array(
          'mountpoint' => $dev_mountpoint,
          'device' => $dev_name,
          'size' => 0.0,
          'used' => 1.0,
          'free' => 0.0
        );
      }
    }else{
      return $res;
    }
    // get list of mountpoints and their status
    $command = "df -h | sed -n '1!p' | sed 's/%//g' | awk '{print $6\",\"$2\",\"$5/100}'";
    $res = self::execCommandOnDuckiebot( $bot_name, $command, $res['connection'] );
    if( $res['success'] ){
      foreach( explode("\n", $res['data']) as $dev ){
        $dev = explode(",", $dev);
        $dev_mountpoint = $dev[0];
        $dev_size = $dev[1];
        $dev_usage = round( $dev[2], 2 );
        if( isset($devices[ $dev_mountpoint ]) ){
          $devices[ $dev_mountpoint ]['size'] = $dev_size;
          $devices[ $dev_mountpoint ]['used'] = $dev_usage;
          $devices[ $dev_mountpoint ]['free'] = 1.0-$dev_usage;
        }
      }
    }else{
      return $res;
    }
    // convert the dictionary into a list of devices
    $devices_list = array_values($devices);
    //
    return $devices_list;
  }//getDuckiebotDiskStatus


  public static function getDuckiebotConfiguration( $bot_name ){
    if( !self::duckiebotExists($bot_name) ){
      return array('success' => false, 'data' => 'Duckiebot not found');
    }
    //
    $configuration = array(
      'w' => false,
      'j' => false,
      'd' => false
    );
    // get the list of USB devices
    $command = "lsusb";
    $res = self::execCommandOnDuckiebot( $bot_name, $command );
    if( $res['success'] ){
      // search for the Edimax (w configuration)
      $device_ids = implode( "|", Duckietown::$DUCKIEBOT_W_CONFIG_DEVICE_VID_PID_LIST );
      $regex = sprintf("/.* ID (%s) .*/", $device_ids);
      $wireless_device_id = Utils::regex_extract_group($res['data'], $regex, 1);
      if( !is_null($wireless_device_id) ){
        $configuration['w'] = true;
      }
      // search for the USB Drive (d configuration)
      $device_ids = implode( "|", Duckietown::$DUCKIEBOT_D_CONFIG_DEVICE_VID_PID_LIST );
      $regex = sprintf("/.* ID (%s) .*/", $device_ids);
      $storage_device_id = Utils::regex_extract_group($res['data'], $regex, 1);
      if( !is_null($storage_device_id) ){
        $configuration['d'] = true;
      }
    }else{
      return $res;
    }
    // search Joystick (j configuration)
    $command = "test -e /dev/input/js0";
    $res = self::execCommandOnDuckiebot( $bot_name, $command, $res['connection'] );
    if( $res['success'] ){
      if( $res['exit_code'] == 0 ){
        $configuration['j'] = true;
      }
    }else{
      return $res;
    }
    //
    return $configuration;
  }//getDuckiebotConfiguration


  public static function getDuckiebotROScoreStatus( $bot_name, $ssh=null ){
    if( !self::duckiebotExists($bot_name) ){
      return array('success' => false, 'data' => 'Duckiebot not found');
    }
    //
    // look for a running roscore process in the Duckiebot
    $command = "pgrep rosmaster";
    $res = self::execCommandOnDuckiebot( $bot_name, $command, $ssh );
    if( $res['success'] ){
      if( $res['exit_code'] == 0 ){
        $res['data'] = array('is_running' => true, 'pid' => trim($res['data']) );
      }else{
        $res['data'] = array('is_running' => false, 'pid' => null );
      }
    }
    return $res;
  }//getDuckiebotROScoreStatus


  public static function getDuckiebotROSnodes( $bot_name, $ssh=null ){
    if( !self::duckiebotExists($bot_name) ){
      return array('success' => false, 'data' => 'Duckiebot not found');
    }
    //
    // get the list of ROS nodes running on the Duckiebot
    $command = self::getROScommand( "rosnode list" );
    $res = self::execCommandOnDuckiebot( $bot_name, $command, $ssh );
    if( $res['success'] ){
      if( $res['exit_code'] == 0 ){
        $res['data'] = explode("\n", $res['data']);
      }else{
        $res['success'] = false;
      }
    }
    return $res;
  }//getDuckiebotROSnodes


  public static function getDuckiebotROStopics( $bot_name, $ssh=null ){
    if( !self::duckiebotExists($bot_name) ){
      return array('success' => false, 'data' => 'Duckiebot not found');
    }
    //
    // get the list of ROS topics being published on the Duckiebot
    $command = self::getROScommand( "rostopic list" );
    $res = self::execCommandOnDuckiebot( $bot_name, $command, $ssh );
    if( $res['success'] ){
      if( $res['exit_code'] == 0 ){
        $res['data'] = explode("\n", $res['data']);
      }else{
        $res['success'] = false;
      }
    }
    return $res;
  }//getDuckiebotROStopics


  public static function getDuckiebotROS( $bot_name ){
    if( !self::duckiebotExists($bot_name) ){
      return array('success' => false, 'data' => 'Duckiebot not found');
    }
    //
    $out = array(
      'core' => array(
        'is_running' => false,
        'pid' => null
      ),
      'nodes' => array(),
      'topics' => array()
    );
    $res = self::getDuckiebotROScoreStatus( $bot_name );
    if( $res['success'] ){
      $out['core'] = $res['data'];
      if( !$res['data']['is_running'] ){ return $out; }
      // get nodes
      $res = self::getDuckiebotROSnodes( $bot_name, $res['connection'] );
      if( !$res['success'] ){ return $res; }
      $out['nodes'] = $res['data'];
      // get topics
      $res = self::getDuckiebotROStopics( $bot_name, $res['connection'] );
      if( !$res['success'] ){ return $res; }
      $out['topics'] = $res['data'];
    }else{
      return $res;
    }
    return $out;
  }//getDuckiebotROS


  public static function getDuckiebotLatestWhatTheDuck( $bot_name ){
    if( !self::duckiebotExists($bot_name) ){
      return array('success' => false, 'data' => 'Duckiebot not found');
    }
    //
    $what_the_duck = array(
      'duckiebot' => null,
      'laptops' => array() //TODO: support for multiple laptops
    );
    // check if we have the WTD for the Duckiebot results locally
    $duckiebot_wtd_test_filepath = sprintf( '%s/%s.wtd.json', Duckietown::$WHAT_THE_DUCK_TESTS_DATA_PATH, $bot_name );
    if( file_exists($duckiebot_wtd_test_filepath) ){
      $wtd_str = file_get_contents($duckiebot_wtd_test_filepath);
      $wtd = json_decode($wtd_str, true);
      $what_the_duck['duckiebot'] = $wtd['what-the-duck'];
    }
    //
    return $what_the_duck;
  }//getDuckiebotLatestWhatTheDuck


  public static function getDuckiebotLinkedToUser( $username ){
    // TODO: this association dir is not used anymore. use Database instead.
    $associations_dir = __DIR__."/data/private/duckiebot.owner/";
    // create command
    $cmd = sprintf("ls -l %s | awk '{print $9}' | grep -E '^%s.[a-z]+$' | cat", $associations_dir, $username);
    // execute the command and parse the output
    $output = [];
    $retval = -100;
    exec( $cmd, $output, $retval );
    if( $retval != 0 ){
      return array('success' => false, 'data' => 'An error occurred while processing your request.');
    }
    // get the list of associations (hopefully no more than one element is present)
    if( count($output) == 0 ){
      return array('success' => true, 'data' => null);
    }else{
      //TODO: check for multiple associations indicating an inconsistent DB
      $association = $output[0];
      $parts = explode('.', $association);
      $bot_name = $parts[1];
      return array('success' => true, 'data' => $bot_name);
    }
  }//getDuckiebotLinkedTo

  public static function getUserLinkedToDuckiebot( $bot_name ){
    $associations_dir = __DIR__."/data/private/duckiebot.owner/";
    // create command
    $cmd = sprintf("ls -l %s | awk '{print $9}' | grep -E '^[0-9]+.%s$' | cat", $associations_dir, $bot_name);
    // execute the command and parse the output
    $output = [];
    $retval = -100;
    exec( $cmd, $output, $retval );
    if( $retval != 0 ){
      return array('success' => false, 'data' => 'An error occurred while processing your request.');
    }
    // get the list of associations (hopefully no more than one element is present)
    if( count($output) == 0 ){
      return array('success' => true, 'data' => null);
    }else{
      //TODO: check for multiple associations indicating an inconsistent DB
      $association = $output[0];
      $parts = explode('.', $association);
      $username = $parts[0];
      return array('success' => true, 'data' => $username);
    }
  }//getUserLinkedToDuckiebot


  public static function isDuckiebotOnline( $bot_name ){
    if( !self::duckiebotExists($bot_name) ){
      return array('success' => false, 'data' => 'Duckiebot not found');
    }
    //
    exec( "ping -c 1 ".$bot_name.".local", $_, $exit_code );
    $is_online = booleanval( $exit_code == 0 );
    //
    return $is_online;
  }//isDuckiebotOnline


  public static function duckiebotExists( $bot_name ){
    $duckiebots = self::getDuckiebotsCurrentBranch();
    //
    return in_array($bot_name, $duckiebots);
  }//isDuckiebotOnline


  public static function linkDuckiebotToUserAccount( $bot_name ){
    // prepare result object
    $res = array(
      'success' => false,
      'data' => null
    );
    // check whether there is a user logged in
    if( !Core::isUserLoggedIn() ){
      $res['data'] = 'You must be logged in to link a Duckiebot to your account';
      return $res;
    }
    // get the username of the current user
    $username = Core::getUserLogged('username');
    // check whether the Duckiebot exists
    if( !self::duckiebotExists($bot_name) ){
      $res['data'] = sprintf('Duckiebot `%s` not found', $bot_name);
      return $res;
    }
    // check whether the user is already linked to a Duckiebot
    $res2 = self::getDuckiebotLinkedToUser($username);
    if( !$res2['success'] ){
      return $res2;
    }
    if( !is_null($res2['data']) ){
      $res['data'] = sprintf('The user account `%s` is already linked to a Duckiebot. Release it first.', $username);
      return $res;
    }
    // check whether the Duckiebot is already linked to another account
    $res2 = self::getUserLinkedToDuckiebot($bot_name);
    if( !$res2['success'] ){
      return $res2;
    }
    if( !is_null($res2['data']) ){
      $res['data'] = sprintf('The Duckiebot `%s` is already linked to a user account.', $bot_name);
      return $res;
    }
    // check whether the user exists, if it does not, return an error
    $user_exists = Core::userExists($username);
    if( !$user_exists ){
      $res['data'] = sprintf("The user `%s` was not found", $username);
    }
    // link Duckiebot to user account
    $associations_dir = __DIR__."/data/private/duckiebot.owner/";
    // create command
    $cmd = sprintf("touch %s/%s.%s", $associations_dir, $username, $bot_name);
    // execute the command and parse the output
    $output = [];
    $retval = -100;
    exec( $cmd, $output, $retval );
    if( $retval != 0 ){
      $res['data'] = array_pop($output);
      return $res;
    }
    // update the info about the user within the system
    $_SESSION['USER_DUCKIEBOT'] = $bot_name;
    //
    $res['success'] = true;
    return $res;
  }//linkDuckiebotToUserAccount


  public static function unlinkDuckiebotFromUserAccount( $bot_name ){
    // get the user account this duckiebot is linked to
    $res = self::getUserLinkedToDuckiebot($bot_name);
    if( !$res['success'] ){
      return $res;
    }
    $username = $res['data'];
    // remove the association flag
    $associations_dir = __DIR__."/data/private/duckiebot.owner/";
    // create command
    $cmd = sprintf("rm -f %s/%s.%s", $associations_dir, $username, $bot_name);
    // execute the command and parse the output
    $output = [];
    $retval = -100;
    exec( $cmd, $output, $retval );
    if( $retval != 0 ){
      return array('success' => false, 'data' => array_pop($output));
    }
    // update the info about the user within the system
    unset( $_SESSION['USER_DUCKIEBOT'] );
    //
    return array('success' => true, 'data' => null);
  }//unlinkDuckiebotFromUserAccount

}//Duckietown

?>
