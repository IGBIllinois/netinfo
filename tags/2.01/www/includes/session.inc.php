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

session_start();

set_include_path('libs');
include_once 'settings.inc.php';
include_once 'db.class.inc.php';
include_once 'session.class.inc.php';
$db = new db(__MYSQL_HOST__,__MYSQL_DATABASE__,__MYSQL_USER__,__MYSQL_PASSWORD__);
$session = new session($db,$username,$password);

if (($session->authenticate(__LDAP_HOST__,__LDAP_SSL__,__LDAP_PORT__,__LDAP_PEOPLE_OU__)) {

	define(login_user,$_SESSION['username']);
	define(login_pass,$_SESSION['password']);
}
else {
	session_start();
	$_SESSION['webpage'] = $_SERVER['REQUEST_URI'];
	header('Location: login.php');
	exit;

}

?>
