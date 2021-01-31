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

require_once 'includes/main.inc.php';

$session = new \IGBIllinois\session(SESSION_NAME);
if (time() > $session->get_var('timeout') + SESSION_TIMEOUT) {
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
