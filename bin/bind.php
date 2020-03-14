<?php
echo afddf
chdir(dirname(__FILE__));

set_include_path(get_include_path() . ':../libs');
require_once '../conf/settings.inc.php';
function my_autoloader($class_name) {
        if(file_exists("../libs/" . $class_name . ".class.inc.php")) {
                require_once $class_name . '.class.inc.php';
        }
}
spl_autoload_register('my_autoloader');
date_default_timezone_set(settings::get_timezone());

//Command parameters
$output_command = "Usage: php bind.php -n DOMIAN NAME -d OUTPUT DIRECTORY -f\n";
$output_command .= "   -n Domain name of the network.  Using 'ALL' will generate all domain bind configs\n";
$output_command .= "   -d Output directory (ie -d /var/named/chroot/var/named)\n";
$output_command .= "   -f force creation of bind config files. This will increment the domain serial number\n";


//Parameters
$shortopts = "";
$shortopts .= "n:"; //-n required
$shortopts .= "d:"; //-d required
$shortopts .= "f::"; //-f is optional



//If run from command line
if (php_sapi_name() != 'cli') {
        exit("Error: This script can only be run from the command line.\n");
}

$domain_name = "";
$directory = "";
$error = false;
$message = "";

$options = getopt($shortopts);

//domain name parameter
if (!isset($options['n']) || ($options['n'] == "")) {
	$error = true;
	$message .= "Error: Please specify a domain name with the -n parameter\n";
}
else {
	$domain_name = $options['n'];

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
		$message .= "Error: " . $directory . " does not exist\n";
	}


}

$force_create = false;
if (isset($options['f'])) {
	$force_create = true;
}

if (!$error) {
	$db = new db(MYSQL_HOST,MYSQL_DATABASE,MYSQL_USER,MYSQL_PASSWORD);
	if ($domain_name == 'ALL') {
		$bind_enabled = 1;
		$all_domains = functions::get_domains($db,$bind_enabled);
		foreach ($all_domains as $domain) {
			$domain = new domain($db,$domain['name']);
	                $result = $domain->update_bind($directory,$force_create);
			$message .= $result['MESSAGE'];
		}

	}
	else {
		$domain = new domain($db,$domain_name);
		$result = $domain->update_bind($directory,$force_create);
		$message = $result['MESSAGE'];

	}
}
else {
	$message .= $output_command;
}

echo $message;
?>
