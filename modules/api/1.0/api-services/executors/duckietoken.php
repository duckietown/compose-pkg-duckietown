<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele


use \system\packages\duckietown\Duckietown;
use \system\classes\Core;
use \system\classes\Database;


function execute( &$service, &$actionName, &$arguments ){
  $action = $service['actions'][$actionName];
  Core::startSession();
  //
  switch( $actionName ){
    case 'login_with_duckietoken':
    if( Core::isUserLoggedIn() ){
      // error
      return response412PreconditionFailed('You are already logged in');
    }
    //
    $duckietoken = $arguments['duckietoken'];
    $res = Duckietown::logInUserWithDuckietoken( $duckietoken );
    if( !$res['success'] ){
      return response400BadRequest($res['data']);
    }
    // success
    return response200OK($res);
    break;
    //
    case 'set':
    // get username of the current user
    $user_id = Core::getUserLogged('username');
    // get arguments
    $token = $arguments['duckietoken'];
    // verify token
    $res = Duckietown::verifyDuckietoken($token);
    if(!$res['success'])
    return response400BadRequest($res['data']);
    // get duckietown id
    $duckietown_user_id = $res['data']['uid'];
    // store auth info
    $res = Duckietown::setUserToken($user_id, $duckietown_user_id, $token);
    if( !$res['success'] )
    return response400BadRequest($res['data']);
    //
    return response200OK();
    break;
    //
    case 'unlink':
    // get username of the current user
    $user_id = Core::getUserLogged('username');
    // unlink auth info
    $db = new Database('duckietown', 'authentication');
    $res = $db->delete( $user_id );
    if( !$res['success'] ) return response500InternalServerError($res['data']);
    //
    return response200OK();
    break;
    //
    default:
    return response404NotFound( sprintf("The command '%s' was not found", $actionName) );
    break;
  }
}//execute

?>
