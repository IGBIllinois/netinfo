<?php
set_include_path(get_include_path() . ':' . __DIR__ . '/../../libs');

require_once __DIR__ . '/../../conf/app.inc.php';

if (file_exists(__DIR__ . '/../../conf/settings.inc.php')) {
	require_once __DIR__ . '/../../conf/settings.inc.php';
}
else {
	echo "<br>/conf/settings.inc.php does not exist";
}

if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
	require_once __DIR__ . '/../../vendor/autoload.php';
}
else {
	echo "<br>/vendor/autoload.php does not exist.  Please run 'composer install' to created vendor folder";
}

function my_autoloader($class_name) {
        if(file_exists(__DIR__ . "/../../libs/" . $class_name . ".class.inc.php")) {
                require_once $class_name . '.class.inc.php';
        }
}

spl_autoload_register('my_autoloader');

date_default_timezone_set(settings::get_timezone());

$db = new \IGBIllinois\db(settings::get_mysql_host(),
			settings::get_mysql_database(),
			settings::get_mysql_user(),
			settings::get_mysql_password(),
			settings::get_mysql_ssl(),
			settings::get_mysql_port()
			);
$log = new \IGBIllinois\log(settings::get_log_enabled(),settings::get_logfile());

?>
