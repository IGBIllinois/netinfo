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

include 'includes/main.inc.php';
session_destroy();
header("Location: login.php")

?>
