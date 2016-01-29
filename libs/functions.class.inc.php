<?php


class functions {


	public static function get_domains($db,$bind_enabled = false) {
		$sql = "SELECT id,name,alt_names ";
                $sql .= "FROM domains ";
		if ($bind_enabled) {
                	$sql .= "WHERE enabled=1 ";
		}
		$sql .= "ORDER BY name ASC";
                return $db->query($sql);

	}

	public static function get_networks($db,$dhcp_enabled = false) {
		$sql = "SELECT networks.*,domains.name as domain_name FROM networks ";
		$sql .= "LEFT JOIN domains ON networks.domain_id=domains.id ";
		if ($dhcp_enabled) {
			$sql .= "WHERE networks.enabled='1' ";
		}
		return $db->query($sql);
		
	}

	public static function write_file($data,$filename) {
		$handle = fopen($filename,"w");
		$bytes = fwrite($handle,$data);
		fclose($handle);
		if ($bytes) {
			return true;
		}
		return false;

	}

	public static function mask2cidr($mask){
 		$long = ip2long($mask);
		$base = ip2long('255.255.255.255');
		return 32-log(($long ^ $base)+1,2);

	}


}







?>
