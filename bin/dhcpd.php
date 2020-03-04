<?php

chdir(dirname(__FILE__));

set_include_path(get_include_path() . ':../libs');
require_once '../conf/settings.inc.php';
date_default_timezone_set(__TIMEZONE__);
function my_autoloader($class_name) {
        if(file_exists("../libs/" . $class_name . ".class.inc.php")) {
                require_once $class_name . '.class.inc.php';
        }
}
spl_autoload_register('my_autoloader');

//Command parameters
$output_command = "Usage: php dhcpd.php -n NETWORK NAME -d OUTPUT DIRECTORY -f\n";
$output_command .= "   -n Name of the network.  Using 'ALL' will generate all network dhcp configs\n";
$output_command .= "   -d Output directory (ie -d /etc/dhcp)\n";
$output_command .= "   -c check dhcpd config syntax. Versions of dhcpd older than 4.0 will give errors with having seperated dhcpd configs for each network\n";

//Parameters
$shortopts = "";
$shortopts .= "n:"; //-n required
$shortopts .= "d:"; //-d required
$shortopts .= "c::"; //-c is optional

//If run from command line
if (php_sapi_name() != 'cli') {
        exit("Error: This script can only be run from the command line.\n");
}

$error = false;
$network_name = "";
$directory = "";
$message = "";
$options = getopt($shortopts);

//network parameter	
if (!isset($options['n']) || ($options['n'] == "")) {
	$error = true;
	$message .= "Error: Please specifiy a network name with the -n parameter\n";
}
else {
	$network_name = $options['n'];
}

//directory parameter
if (!isset($options['d']) || ($options['d'] == "")) {
	$error = true;
	$message .= "Error: Please specify a directory with the -d parameter\n";
}
else {
	$directory = $options['d'];
	//remove trailing slash
	if (substr($directory,-1) == "/") {
		$directory = substr($directory,0,-1);
	}
	//check directory exists
	if (!file_exists($directory)) {
		$error = true;
		$message = "Error: " . $directory . " does not exist\n";
	}


}

//verify config parameter
$verify_config = false;
if (isset($options['c'])) {
	$verify_config = true;
}
	
if (!$error) {

	$db = new db(__MYSQL_HOST__,__MYSQL_DATABASE__,__MYSQL_USER__,__MYSQL_PASSWORD__);
	if ($network_name == 'ALL') {
		$dhcp_enabled = 1;
		$all_networks = functions::get_networks($db,$dhcp_enabled);
		foreach ($all_networks as $network) {
			$network = new network($db,$network['name']);
	                $result = $network->update_dhcpd($directory,$verify_config);
			$message .= $result['MESSAGE'];
		}
	}
	else {
		$network = new network($db,$network_name);
                $result = $network->update_dhcpd($directory,$verify_config);
		$message .= $result['MESSAGE'];
	}
}
else {
	$message .= $output_command;
}

echo $message;

?>
