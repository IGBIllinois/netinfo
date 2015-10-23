<?php

chdir(dirname(__FILE__));

set_include_path(get_include_path() . ':../libs');
require_once '../conf/settings.inc.php';

function my_autoloader($class_name) {
        if(file_exists("../libs/" . $class_name . ".class.inc.php")) {
                require_once $class_name . '.class.inc.php';
        }
}
spl_autoload_register('my_autoloader');

$sapi_type = php_sapi_name();
//If run from command line
if ($sapi_type != 'cli') {
        echo "Error: This script can only be run from the command line.\n";
}
else {

	$error = false;
	$network_name = "";
	$directory = "";
	$message = "";
	if (isset($argv[1])) {
		$network_name = $argv[1];
	}
	if (isset($argv[2])) {
		$directory = $argv[2];
	}

	if ($network_name == "") {
		$error = true;
		$message = "Please enter a network name\n";
	}	
	elseif (!file_exists($directory) || $directory == "") {
		$error = true;
		$message = $directory . " does not exist\n";
	}	

	if (!$error) {
		$db = new db(__MYSQL_HOST__,__MYSQL_DATABASE__,__MYSQL_USER__,__MYSQL_PASSWORD__);
		if ($network_name == 'ALL') {
			$dhcp_enabled = 1;
			$all_networks = functions::get_networks($db,$dhcp_enabled);
			foreach ($all_networks as $network) {
				$result = functions::create_dhcp_conf($db,$network['name'],$directory);
				$message .= $result['MESSAGE'];
			}

		}
		else {
			$result = functions::create_dhcp_conf($db,$network_name,$directory);


		}
	}
	echo $message;
}
?>
