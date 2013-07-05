<?php
/////////////////////////////////////////
//
//	session.inc.php
//
//	Used to verify the user is
//	logged in before proceeding
//
//	David Slater
//	May 2009
//
//////////////////////////////////////////

include_once 'includes/main.inc.php';
include_once 'authenticate.inc.php';

$session = new session(__SESSION_NAME__);
if (time() > $session->get_var('timeout') + __SESSION_TIMEOUT__) {
	unset($_POST);
	header('Location: logout.php');

}
elseif (!$session->get_var('login')) {
	unset($_POST);
	header('Location:logout.php');	

}

else {
	//Reset timeout
	$session_vars = array('timeout'=>time());
	$session->set_session($session_vars);	
}





?>
