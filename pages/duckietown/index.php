<?php
# @Author: Andrea F. Daniele <afdaniele>
# @Date:   Thursday, October 12th 2017
# @Email:  afdaniele@ttic.edu
# @Last modified by:   afdaniele
# @Last modified time: Tuesday, February 13th 2018

use \system\classes\Configuration as Configuration;
use \system\classes\Core as Core;

if( Configuration::$ACTION == NULL ){
	// redirect to /show
    Core::redirectTo('duckietown/show');
}elseif( Configuration::$ACTION == 'show' ){
	// show the town editor
	require_once __DIR__.'/actions/town-show.php';
}elseif( Configuration::$ACTION == 'new' ){
	// show the town editor
	require_once __DIR__.'/actions/town-editor.php';
}elseif( Configuration::$ACTION == 'review' ){
	// show the town editor
	require_once __DIR__.'/actions/town-review.php';
}

?>
