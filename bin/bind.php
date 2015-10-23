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
	$domain_name = "";
	$directory = "";
	$error = false;
	$message = "";
	if (isset($argv[1])) {
		$domain_name = $argv[1];
	}
	if (isset($argv[2])) {
		$directory = $argv[2];
	}

	if ($domain_name == "") {
                $error = true;
                $message .= "Please enter a domain name\n";
        }
	if ($directory == "") {
		$message .= "Please enter a directory\n";
	}
        elseif (!file_exists($directory)) {
                $error = true;
                $message = $directory . " does not exist\n";
        }

        if (!$error) {
		$db = new db(__MYSQL_HOST__,__MYSQL_DATABASE__,__MYSQL_USER__,__MYSQL_PASSWORD__);
                if ($domain_name == 'ALL') {
                        $bind_enabled = 1;
                        $all_domains = functions::get_domains($db,$bind_enabled);
                        foreach ($all_domains as $domain) {
                                $result = functions::create_bind_conf($db,$domain['name'],$directory);
                                $message .= $result['MESSAGE'];
                        }

                }
                else {
                        $result = functions::create_bind_conf($db,$domain_name,$directory);
			$message = $result['MESSAGE'];

                }
        }
        echo $message;
	
}
?>
