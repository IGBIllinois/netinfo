<?php
	include_once("../html/includes/main.inc.php");
	
	$stacks = $db->query("select `hostname` from switches where type='building'",array());
	$insertquery = $db->get_link()->prepare("insert into portconfig (switchstack, descriptor, lastUpdateTime, mode, vlan, printerfirewall, allowedvlan) values (:switch,:descriptor,NOW(),:mode,:vlan,:printerfirewall,:allowedvlan) on duplicate key update mode=:mode, vlan=:vlan, printerfirewall=:printerfirewall, allowedvlan=:allowedvlan, lastUpdateTime=NOW()");
	
	// Download configs
	$hostnames = array();
	for($i=0; $i<count($stacks); $i++){
	    $hostnames[$i] = substr($stacks[$i]['hostname'], 0, strpos($stacks[$i]['hostname'], '.'));
		$configs[] = "/data/switch_configs/".$hostnames[$i]."/".$hostnames[$i].".cfg";
	}
	echo "Downloading configs...\n";
	exec('scp -T switch_backup@phalanx.igb.illinois.edu:"'.implode(" ", $configs).'" ../switch_configs/');
	// Process configs
	for($i=0; $i<count($stacks); $i++){
		echo "Switch ".$stacks[$i]['hostname']."\n";
		if(file_exists("../switch_configs/".$hostnames[$i].".cfg")){
			echo "Found ".$hostnames[$i].".cfg\n";
			$handle = fopen("../switch_configs/".$hostnames[$i].".cfg","r");
			
			$interface = null;
			$mode = "access";
			$accessvlan = null;
			$nativevlan = null;
			$allowedvlans = null;
			$printerfirewall = false;
			if($handle){
				while(($line = fgets($handle)) !== false){
					$line = trim($line);
					if(preg_match('/interface (GigabitEthernet[0-9]+\/[0-9]+\/[0-9]+)$/uUm', $line, $matches)){
						echo "\t".$matches[1]."\n";
						$interface = $matches[1];
					}
					if($line == "switchport mode access"){
						$mode = "access";
					}
					if($line == "switchport mode trunk"){
						$mode = "trunk";
					}
					if($line == "ip access-group 110 out"){
						$printerfirewall = true;
					}
					if(preg_match('/switchport access vlan ([0-9]+)$/uUm', $line, $matches)){
						$accessvlan = $matches[1];
					}
					if(preg_match('/switchport trunk native vlan ([0-9]+)$/uUm', $line, $matches)){
						$nativevlan = $matches[1];
					}
					if(preg_match('/switchport trunk allowed vlan ([0-9,]+)$/uUm', $line, $matches)){
						$allowedvlans = $matches[1];
					}
					if($line == "!" && $interface != null){
						$vlan = $accessvlan;
						if($mode == "trunk"){
							echo "\t\tTrunk port\n";
							echo "\t\tNative VLAN $nativevlan\n";
							echo "\t\tAllowed VLANs: $allowedvlans\n";
							$vlan = $nativevlan;
						} else {
							echo "\t\tAccess VLAN $accessvlan\n";
						}
						if($printerfirewall) echo "\t\tPrinter firewall on\n";
						$insertquery->execute(array(":switch"=>$stacks[$i]['hostname'],":descriptor"=>$interface,":mode"=>$mode,":vlan"=>$vlan,":printerfirewall"=>($printerfirewall?1:0),":allowedvlan"=>$allowedvlans ));
						$interface = null;
						$mode = "access";
						$accessvlan = null;
						$nativevlan = null;
						$allowedvlans = null;
						$printerfirewall = false;
					}
				}
			}
		}
	}