<?php

class macwatch {

	private CONST MAC_OID='.1.3.6.1.2.1.17.4.3.1.1';
	private CONST BRIDGE_OID='.1.3.6.1.2.1.17.4.3.1.2';
	private CONST INTERFACE_OID='.1.3.6.1.2.1.17.1.4.1.2';
	private CONST INTERFACE_NAME_OID='.1.3.6.1.2.1.31.1.1.1.1';

	public static function get_vlans($db) {
		$sql = "select * from vlans";
		return $db->query($sql);


	}

	public static function get_switches($db) {
		$sql = "select * from switches";
		return $db->query($sql);


	}


}
?>
