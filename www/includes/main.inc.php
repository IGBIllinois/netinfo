<?php
set_include_path(get_include_path() . ':./libs');
include_once 'settings.inc.php';
include_once 'functions.inc.php';
function __autoload($class_name) {
	if(file_exists("libs/" . $class_name . ".class.inc.php")) {
		require_once $class_name . '.class.inc.php';
	}
}

$db = new db(__MYSQL_HOST__,__MYSQL_DATABASE__,__MYSQL_USER__,__MYSQL_PASSWORD__);


?>
