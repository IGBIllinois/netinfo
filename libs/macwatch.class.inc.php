<?php

class macwatch {

	public CONST SNMP_MAC_OID='.1.3.6.1.2.1.17.4.3.1.1';
	public CONST SNMP_BRIDGE_OID='.1.3.6.1.2.1.17.4.3.1.2';
	public CONST SNMP_INTERFACE_OID='.1.3.6.1.2.1.17.1.4.1.2';
	public CONST SNMP_INTERFACE_NAME_OID='.1.3.6.1.2.1.31.1.1.1.1';

	public static function get_vlans($db) {
		$sql = "select * from vlans";
		return $db->query($sql);


	}

	public static function add($db,$switch_id,$port,$mac,$vendor,$vlans) {
		sort($vlans,SORT_NUMERIC);
		$sql = "INSERT INTO macwatch(switch_id,port,mac,vendor,vlans) ";
		$sql .= "VALUES(:switch_id,:port,:mac,:vendor,:vlans) ";
		$sql .= "ON DUPLICATE KEY UPDATE date=NOW(), vendor=:vendor, vlans=:vlans";
		$params = array(':switch_id'=>$switch_id,
				':port'=>$port,
				':mac'=>$mac,
				':vendor'=>$vendor,
				':vlans'=>implode(',', $vlans)
			);
		return $db->insert_query($sql,$params);


	}

	public static function get_vendor($db,$switch_id,$port,$mac) {
		$sql = "SELECT vendor FROM macwatch where switch_id=:switch_id and port=:port and mac=:mac ";
		$sql .= "ORDER BY date DESC LIMIT 1";
		$parameters = array(':switch_id'=>$switch_id,
				':port'=>$port,
				':mac'=>$mac);
		$result = $db->query($sql,$parameters);
		if (count($result) && $result[0]['vendor'] != '') {
			return $result[0]['vendor'];
		}
		return false;


	}
}

?>
