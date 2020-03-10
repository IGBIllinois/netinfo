<?php
	include_once("../html/includes/main.inc.php");
	
	$stacks = $db->query("select hostname from switches where type='building'");
	for($i=0; $i<count($stacks); $i++){
		echo "Switch ".$stacks[$i]['hostname']."\n";
		$switch = new PortStatSwitch($db, $stacks[$i]['hostname'], 'morpheus');
		echo "\tWalking Interfaces...\n";
		$switch->pollInterfaces();
		echo "\tWalking Admin Status...\n";
		$switch->pollAdminStatus();
		echo "\tWalking Operation Status...\n";
		$switch->pollOperStatus();
	}