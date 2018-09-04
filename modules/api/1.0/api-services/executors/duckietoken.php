<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Monday, January 8th 2018
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Sunday, January 14th 2018



require_once $GLOBALS['__PACKAGES__DIR__'].'/duckietown/Duckietown.php';
use \system\packages\duckietown\Duckietown;
use \system\classes\Core;
use \system\classes\Database;

require_once $GLOBALS['__SYSTEM__DIR__'].'/api/1.0/utils/utils.php';

function execute( &$service, &$actionName, &$arguments ){
	$action = $service['actions'][$actionName];
	//
	switch( $actionName ){
		case 'set':
			// get username of the current user
			$user_id = Core::getUserLogged('username');
			// get arguments
			$token = $arguments['duckietoken'];
			// verify token
			$url = sprintf(
				'%s://%s/%s/%s',
				'https',
				'challenges.duckietown.org',
				'v2',
				'info'
			);
			// configure a CURL object
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
			curl_setopt($curl, CURLOPT_HTTPHEADER, [sprintf('X-Messaging-Token: %s', $token)]);
			// call CURL
			$curl_response = curl_exec($curl);
			$curl_res = curl_getinfo($curl);
			curl_close($curl);
			// handle errors
			if( $curl_response === false || !in_array($curl_res['http_code'], [200, 401]) ){
				return response500InternalServerError(
					sprintf(
						'An error occurred while talking to the challenges API. The server returned the code <strong>%d</strong>.',
						$curl_res['http_code']
					)
				);
			}
			if( $curl_res['http_code'] == 401 ){
				return response400BadRequest('The token is not valid');
			}
			// success, store token
			$db = new Database('duckietown', 'token');
			$res = $db->write( $user_id, ['value' => $token] );
			if( !$res['success'] ) return response500InternalServerError($res['data']);
			//
			return response200OK();
			break;
		//
		case 'unlink':
			// get username of the current user
			$user_id = Core::getUserLogged('username');
			// unlink token
			$db = new Database('duckietown', 'token');
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
