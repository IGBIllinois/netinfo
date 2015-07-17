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

include_once 'includes/main.inc.php';

$session = new session(__SESSION_NAME__);

//destroy session, this logs you out on our side.
$session->destroy_session();

//unset $_POST, just in case something is set in there
unset($_POST);

//Redirect to login
header('Location: login.php');
?>


