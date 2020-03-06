<?php

$dryrun = false;
if($argc > 1 && $argv[1] == "dryrun"){
	echo "Dryrun Mode\n";
	$dryrun = true;
}

if (file_exists('../conf/settings.inc.php')) {
	require_once '../conf/settings.inc.php';
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

//If run from command line
if (php_sapi_name() != 'cli') {
        exit("Error: This script can only be run from the command line.\n");
}
$db = new db(__MYSQL_HOST__,__MYSQL_DATABASE__,__MYSQL_USER__,__MYSQL_PASSWORD__);

// Array of VLANS to watch
$vlans = macwatch::get_vlans($db);
print_r($vlans);
echo "Community: " . settings::get_snmp_community() . "\n";
// Array of switches to poll
$switches = macwatch::get_switches($db);

// Foreach switch
foreach ($switches as $switch) {
	echo "Switch: ".$switch['hostname']."\n";
	$portvlans = array();
	//  Foreach VLAN
	foreach ($vlans as $vlan) {
		echo "\tVLAN: ".$vlan['vlan']."\n";
		//   Get MACs, "bridges", interface numbers, and interface names
		
		$snmp = new SNMP(SNMP::VERSION_2C, $switch['hostname'], '" . settings::get_snmp_community() . "@'.$vlan['vlan'],1000000,5);
		$snmp->valueretrieval = SNMP_VALUE_PLAIN;

		//$macs = $snmp->walk(macwatch::get_mac_oid());
		$macs = $snmp->walk(".");
		if($macs === false){
			continue; // If there was an SNMP error on the first walk, skip the rest.
		}
		$bridges = $snmp->walk(macwatch::get_bridge_oid());
		$ifnums = $snmp->walk(macwatch::get_interface_oid());
		$ifnames = $snmp->walk(macwatch::get_interface_name_oid());
		// Get ports to ignore
		$ignoredports = $db->query("select * from macwatch_ignored_ports where switch_hostname=:switch",array(':switch'=>$switch['hostname']));
		// Reverse the array to make it easier to search
		$ignore = array();
		foreach ($ignoredports as $port) {
			$ignore[$port['portname']] = 1;
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
			$vendor = "";
			$existingEntry = $db->query("select * from macwatch where switch=:switch and port=:port and mac=:mac order by date desc limit 1", array(':switch'=>$switch['hostname'],':port'=>$ifname,':mac'=>$mac));
			if($existingEntry === FALSE || count($existingEntry) == 0 || $existingEntry[0]['vendor'] == "" || $existingEntry[0]['vendor'] == null){
				$vendorjson = file_get_contents("http://macvendors.co/api/".$mac."/json");
				if($vendorjson !== false){
					$vendor = json_decode($vendorjson);
					if(!isset($vendor->result) || !isset($vendor->result->company)){
						$vendor = "";
					} else {
						$vendor = $vendor->result->company;
					}
				}
			} else {
				$vendor = $existingEntry[0]['vendor'];
			}

			// Update the VLANs field
			$portvlans[$ifname][$mac][] = $vlan['vlan'];
			
			echo "\t\t$mac: $ifname ($vendor)\n"; // For now, we just print
			if(!$dryrun){
				//$db->insert_query('insert into macwatch(switch,port,mac,vendor,vlans) values (:switch,:port,:mac,:vendor,:vlans) on duplicate key update date=NOW(), vendor=:vendor, vlans=:vlans',array(':switch'=>$switch['hostname'],':port'=>$ifname,':mac'=>$mac,':vendor'=>$vendor,':vlans'=>implode(',', $portvlans[$ifname][$mac])));
			}
		}
	}
}
