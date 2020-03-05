<?php
//////////////////////////////////////////
//					
//	logout.php			
//
//	Logs user out
//
//	By: David Slater
//	Date: May 2009
//
//////////////////////////////////////////

require_once 'includes/main.inc.php';

$session = new session(__SESSION_NAME__);
log::send_log("User " . $session->get_var('username') . " logged out");

//destroy session, this logs you out on our side.
$session->destroy_session();

//unset $_POST, just in case something is set in there
unset($_POST);

//Redirect to login
header('Location: login.php');
?>


