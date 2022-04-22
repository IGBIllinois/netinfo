#!/usr/bin/env php
<?php

if (file_exists('../conf/settings.inc.php')) {
	require_once '../conf/settings.inc.php';
}

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
        require_once __DIR__ . '/../vendor/autoload.php';
}
else {
        echo "<br>/vendor/autoload.php does not exist.  Please run 'composer install' to created vendor folder";
}

function my_autoloader($class_name) {
	if(file_exists("../libs/" . $class_name . ".class.inc.php")) {
		require_once "../libs/" . $class_name . '.class.inc.php';
	}
}

spl_autoload_register('my_autoloader');

if (settings::get_debug()) {
        ini_set("log_errors", 1);
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

}

//Command parameters
$output_command = "Usage: php macwatch.php \n";
$output_command .= "   --dry-run Do dry run only\n";
$output_command .= "   -h, --help Show help menu \n";

//Parameters
$shortopts = "h";

$longopts = array(
	'help',
	'dry-run'
);

//If run from command line
if (php_sapi_name() != 'cli') {
        exit("Error: This script can only be run from the command line.\n");
}

$options = getopt($shortopts,$longopts);

$dryrun = false;
if (isset($options['h']) || isset($options['help'])) {
		echo $output_command;
		exit;
}
elseif (isset($options['dry-run'])) {
	$dryrun = true;
	echo "Dry Run enabled\n";
}

$db = new \IGBIllinois\db(MYSQL_HOST,MYSQL_DATABASE,MYSQL_USER,MYSQL_PASSWORD);

// Array of VLANS to watch
$vlans = macwatch::get_vlans($db);
echo "Community: " . settings::get_snmp_community() . "\n";
// Array of switches to poll
$switches = macwatch::get_switches($db);

// Foreach switch
foreach ($switches as $switch) {
	echo "Switch: ".$switch['hostname']."\n";
	$switch_obj = new network_switch($db,$switch['hostname']);
	$ignoredports = $switch_obj->get_ignore_ports();

	$portvlans = array();
	//  Foreach VLAN
	foreach ($vlans as $vlan) {
		echo "\tVLAN: ".$vlan['vlan']."\n";
		//   Get MACs, "bridges", interface numbers, and interface names
				
		$snmp = new SNMP(SNMP::VERSION_2C, $switch['hostname'],settings::get_snmp_community() . "@" . $vlan['vlan'],1000000,5);
		$snmp->valueretrieval = SNMP_VALUE_PLAIN;

		$macs = @$snmp->walk(macwatch::SNMP_MAC_OID);
		if($macs === false){
			continue; // If there was an SNMP error on the first walk, skip the rest.
		}
		$bridges = @$snmp->walk(macwatch::SNMP_BRIDGE_OID);
		$ifnums = @$snmp->walk(macwatch::SNMP_INTERFACE_OID);
		$ifnames = @$snmp->walk(macwatch::SNMP_INTERFACE_NAME_OID);
		// Reverse the array to make it easier to search
		$ignore = array();
		foreach ($ignoredports as $port) {
			$ignore[$port] = 1;
		}

		// Parse the SNMP data
		foreach ($macs as $mkey => $mac) {
			$mac = bin2hex($mac); // Hex gets returned as binary data, convert it to hex string

			// The bridge number oid has the same suffix as the mac oid, use this to fetch the bridge
			$bkey = str_replace("4.3.1.1","4.3.1.2",$mkey); 
			if(!isset($bridges[$bkey])){
				continue;
			}
			$bridge = $bridges[$bkey];

			// The interface number oid is suffixed by the bridge number, use this to fetch the interface number
			$ikey = "SNMPv2-SMI::mib-2.17.1.4.1.2.".$bridge;
			if(!isset($ifnums[$ikey])){
				continue;
			}
			$ifnum = $ifnums[$ikey];

			// The interface name oid is suffixed by the interface number, use this to fetch the interface name
			$nkey = "IF-MIB::ifName.".$ifnum;
			if(!isset($ifnames[$nkey])){
				continue;
			}
			$ifname = $ifnames[$nkey];

			// Check to make sure this isn't an ignored port
			if(isset($ignore[$ifname])){
				continue;
			}
			
			// Get MAC vendor ONLY if it's not already in the database, to save time
			$vendor = macwatch::get_vendor($db,$switch['hostname'],$ifname,$mac);
			if(!$vendor){
				$vendorjson = file_get_contents("http://macvendors.co/api/".$mac."/json");
				if($vendorjson !== false){
					$vendor = json_decode($vendorjson);
					if(!isset($vendor->result) || !isset($vendor->result->company)){
						$vendor = "";
					} else {
						$vendor = $vendor->result->company;
					}
				}
			}

			// Update the VLANs field
			$portvlans[$ifname][$mac][] = $vlan['vlan'];
			echo "\t\t$mac: $ifname ($vendor)\n"; // For now, we just print
			if(!$dryrun){
				macwatch::add($db,$switch['hostname'],$ifname,$mac,$vendor,$portvlans[$ifname][$mac]);
			}
		}
	}
}
