<?php

class macwatch {

	private CONST MAC_OID='.1.3.6.1.2.1.17.4.3.1.1';
	private CONST BRIDGE_OID='.1.3.6.1.2.1.17.4.3.1.2';
	private CONST INTERFACE_OID='.1.3.6.1.2.1.17.1.4.1.2';
	private CONST INTERFACE_NAME_OID='.1.3.6.1.2.1.31.1.1.1.1';

	public static function get_mac_oid() {
		return self::MAC_OID;
	}
	public static function get_bridge_oid() {
		return self::BRIDGE_OID;

	}
	public static function get_interface_oid() {
		return self::INTERFACE_OID;
	}
	public static function get_interface_name_oid() {
		return self::INTERFACE_NAME_OID;

	}
	public static function get_vlans($db) {
		$sql = "select vlan,name from vlans";
		return $db->query($sql);


	}

	public static function get_switches($db) {
		$sql = "select * from switches WHERE enabled='1'";
		return $db->query($sql);


	}

	public static function add($db,$hostname,$ifname,$mac,$vendor,$vlans) {
		$sql = "INSERT INTO macwatch(switch,port,mac,vendor,vlans) ";
		$sql .= "VALUES(:switch,:port,:mac,:vendor,:vlans) ";
		$sql .= "ON DUPLICATE KEY UPDATE date=NOW(), vendor=:vendor, vlans=:vlans";
		$params = array(':switch'=>$hostname,
				':port'=>$ifname,
				':mac'=>$mac,
				':vendor'=>$vendor,
				':vlans'=>implode(',', $vlans)
			);
		return $db->insert_query($sql,$params);


	}
}

?>
