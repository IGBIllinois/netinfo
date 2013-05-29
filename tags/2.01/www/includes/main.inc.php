<?php
set_include_path(get_include_path() . ':./libs');
include_once 'settings.inc.php';
include_once 'functions.inc.php';
function __autoload($class_name) {
	if(file_exists("libs/" . $class_name . ".class.inc.php")) {
		require_once $class_name . '.class.inc.php';
	}
}
session_start();

$db = new db(__MYSQL_HOST__,__MYSQL_DATABASE__,__MYSQL_USER__,__MYSQL_PASSWORD__);
if (isset($_SESSION['login']) && ($_SESSION['login'] == true)) {
	$ldap = new ldap(__LDAP_HOST__,__LDAP_SSL__,__LDAP_PORT__,__LDAP_BASE_DN__);
	$ldap->set_ldap_people_ou(__LDAP_PEOPLE_OU__);
	$ldap->set_ldap_group_ou(__LDAP_GROUP_OU__);
	$session = new session($db,$ldap,$_SESSION['username'],$_SESSION['password'],__LDAP_GROUP__);
	if ($session->authenticate()) {
		define("login",$_SESSION['login']);
		define("login_user",$_SESSION['username']);
		define("login_pass",$_SESSION['password']);
	}
	else {
		session_start();
		$_SESSION['webpage'] = $_SERVER['REQUEST_URI'];
		header('Location: login.php');

	}
}
else {
	$_SESSION['webpage'] = $_SERVER['REQUEST_URI'];
	header('Location: login.php');




}
?>
