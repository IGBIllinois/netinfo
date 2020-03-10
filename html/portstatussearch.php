<?php
	require_once('includes/main.inc.php');
	$results = [];

	PortStatMacwatchHelper::$db = $db;
    $switchResult = $db->query("select * from switches where type='building'");
    /** @var PortStatSwitch[] $switches */
    $switches = [];
    foreach ($switchResult as $row){
        $switch = new PortStatSwitch($db, $row['hostname']);
        $switch->loadInterfaces();
        $switches[] = $switch;
    }
	
	function filterByLocation($filter,$interface){
		return $interface->location == $filter;
	}
	function filterByLocationRegex($filter,$interface){
		return preg_match('/^'.$filter.'$/m', $interface->location);
	}
	function filterByVLAN($filter,$interface){
		$allowedvlans = explode(',', $interface->allowedvlan);
		return ($interface->mode == "access" && $interface->vlan == $filter) || ($interface->mode=='trunk' && in_array($filter, $allowedvlans)) || ($interface->mode=='trunk' && $interface->allowedvlan==null);
	}
	function filterByMode($filter,$interface){
		return $interface->mode == $filter;
	}
	function filterByANAME($filter,$interface){
		$found = false;
		foreach($interface->macwatch as $macwatch){
			$found = $found || (strpos($macwatch['aname'],$filter) !== false);
		}
		return $found;
	}
	function filterByMAC($filter,$interface){
		$found = false;
		foreach($interface->macwatch as $macwatch){
			$found = $found || (strpos($macwatch['mac'],$filter) !== false);
		}
		return $found;
	}
	function filterByVendor($filter,$interface){
		$found = false;
		foreach($interface->macwatch as $macwatch){
			$found = $found || (strpos($macwatch['vendor'],$filter) !== false);
		}
		return $found;
	}
	
	function filterByFunction($filter,$switches,$filterFunc){
		$ifaces = array();
		for($i=0; $i<count($switches); $i++){
			$switch = $switches[$i];
			for($j=1; $j<count($switch->interfaces)+1; $j++){ // loops through switches in stack
				for($k=1; $k<count($switch->interfaces[$j][0])+1; $k++){ // loops through interfaces in switch
					$interface = $switch->interfaces[$j][0][$k];
					if( $filterFunc($filter,$interface) ){
						$ifaces[] = sprintf("%s|%s", $switch->getHostname(), $interface->descriptor);
					}
				}
			}
		}
		return $ifaces;
	}
	
	if(isset($_GET['location']) && $_GET['location'] != ''){
		$stars = 0;
		$qmarks = 0;
		$_GET['location'] = str_replace('*', '.', $_GET['location'], $stars);
		$_GET['location'] = str_replace('?', '.?', $_GET['location'], $qmarks);
		
		if($stars > 0 || $qmarks > 0){
			$results[] = filterByFunction($_GET['location'],$switches,'filterByLocationRegex');
		} else {
			$results[] = filterByFunction($_GET['location'],$switches,'filterByLocation');
		}
	}
	
	if(isset($_GET['vlan']) && $_GET['vlan'] != ''){
		$results[] = filterByFunction($_GET['vlan'],$switches,'filterByVLAN');
	}
	
	if(isset($_GET['mode']) && $_GET['mode'] != ''){
		$results[] = filterByFunction($_GET['mode'],$switches,'filterByMode');
	}
	
	if(isset($_GET['aname']) && $_GET['aname'] != ''){
		$results[] = filterByFunction($_GET['aname'],$switches,'filterByANAME');
	}
	
	if(isset($_GET['mac']) && $_GET['mac'] != ''){
		$results[] = filterByFunction($_GET['mac'],$switches,'filterByMAC');
	}

	if(isset($_GET['vendor']) && $_GET['vendor'] != ''){
		$results[] = filterByFunction($_GET['vendor'],$switches,'filterByVendor');
	}
	
	if(count($results) == 0){
		echo '[]';
	} else {
		$finalresults = $results[0];
		for($i=1; $i<count($results); $i++){
			$finalresults = array_intersect($finalresults, $results[$i]);
		}
		echo json_encode(array_values($finalresults));
	}
	
	
