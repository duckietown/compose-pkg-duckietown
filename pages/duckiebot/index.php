<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Tuesday, January 9th 2018
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Wednesday, January 10th 2018

use \system\classes\Core as Core;
use \system\classes\Configuration as Configuration;
use \system\packages\duckietown\Duckietown as Duckietown;

$duckiebotName = null;

if( Core::getUserRole() == 'user' ){
	$user = Core::getUserLogged('username');
	$res = Duckietown::getDuckiebotLinkedToUser( $user );
	if( !$res['success'] ){
		Core::throwError(
			sprintf('Error: "%s"', $res['data'])
		);
	}
	if( is_null($res['data']) ){
		Core::throwError('Your account is not linked to any Duckiebot');
	}
	$duckiebotName = $res['data'];
}else if(
	in_array(Core::getUserRole(), ['administrator', 'supervisor']) &&
	isset( $_GET['veh'] ) && !empty( $_GET['veh'] )
	){
		$duckiebotName = strtolower($_GET['veh']);
		if( strlen($duckiebotName) < 1 ){
			Core::redirectTo("");
	}
}else{
	Core::redirectTo("");
}

if( !Duckietown::duckiebotExists($duckiebotName) ){
	Core::throwError(
		sprintf('The Duckiebot `%s` does not exist.', $duckiebotName)
	);
}

$GLOBALS['_duckietown_duckiebot_veh'] = $duckiebotName;

$action = empty(Configuration::$ACTION)? 'home' : strtolower(Configuration::$ACTION);

if( in_array($action, ['home', 'mission-control']) ){
	require_once __DIR__.'/actions/'.$action.'.php';
}

?>


<script type="text/javascript" src="<?php echo Core::getJSscriptURL('roslibjs.min.js', 'ros'); ?>"></script>
