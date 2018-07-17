<?php
set_include_path(get_include_path() . ':../libs');

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

date_default_timezone_set(__TIMEZONE__);

function my_autoloader($class_name) {
	if(file_exists("../libs/" . $class_name . ".class.inc.php")) {
		require_once $class_name . '.class.inc.php';
	}
}

spl_autoload_register('my_autoloader');

$db = new db(__MYSQL_HOST__,__MYSQL_DATABASE__,__MYSQL_USER__,__MYSQL_PASSWORD__);
$networks = functions::get_networks($db);

$nav_html = "";

foreach ($networks as $network) {
	$cidr = functions::mask2cidr($network['netmask']);
	$nav_html .= "<li class='nav-header'>" . $network['name'] . " - " . $network['network'] . "/" . $cidr . "</li>";
	$nav_html .= "<li><a href='index.php?network=" . $network['network'] . "/" . $cidr . "'>All Devices</a></li>";
	$nav_html .= "<li><a href='index.php?network=" . $network['network'] . "/" . $cidr . "&search=spare&exact=1'>Spares</a></li>";
	$nav_html .= "<li><a href='index.php?network=" . $network['network'] . "/" . $cidr . "&start_date=" . date('Y:m:d',strtotime("-6 month",time())) . "&end_date=0'>Older Than 6 Months Devices</a></li>";
	$nav_html .= "<li><a href='index.php?network=" . $network['network'] . "/" . $cidr . "&start_date=0&end_date=0'>Never Seen Devices</a></li>";
}
?>
