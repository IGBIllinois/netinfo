<?php
set_include_path(get_include_path() . ':../libs');

require_once '../conf/app.inc.php';

if (file_exists('../conf/settings.inc.php')) {
	require_once '../conf/settings.inc.php';
}
else {
	echo "<br>/conf/settings.inc.php does not exist";
}

if (file_exists('../vendor/autoload.php')) {
	require_once '../vendor/autoload.php';
}
else {
	echo "<br>/vendor/autoload.php does not exist.  Please run 'composer install' to created vendor folder";
}

function my_autoloader($class_name) {
        if(file_exists("../libs/" . $class_name . ".class.inc.php")) {
                require_once $class_name . '.class.inc.php';
        }
}

spl_autoload_register('my_autoloader');

if (settings::get_debug()) {
	ini_set("log_errors", 1);
	ini_set('display_errors', 1); 
	ini_set('display_startup_errors', 1); 
	error_reporting(E_ALL);

}

date_default_timezone_set(settings::get_timezone());
$db = new db(__MYSQL_HOST__,__MYSQL_DATABASE__,__MYSQL_USER__,__MYSQL_PASSWORD__);
?>
