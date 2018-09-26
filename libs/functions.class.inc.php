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
		$sql .= "ORDER BY vlan ASC ";
		if ($dhcp_enabled) {
			$sql .= "WHERE networks.enabled='1' ";
		}
		return $db->query($sql);
		
	}

	public static function write_file($data,$filename) {
		$valid = true;
		if ((file_exists($filename)) && (!is_writable($filename))) {
			$valid = false;
		}
		elseif (!is_writeable(dirname($filename))) {
			$valid = false;
		}

		if ($valid) {
			$handle = fopen($filename,"w");
			$bytes = fwrite($handle,$data);
			fclose($handle);
			if ($bytes) {
				return true;
			}
		}
		return false;

	}

	public static function mask2cidr($mask){
 		$long = ip2long($mask);
		$base = ip2long('255.255.255.255');
		return 32-log(($long ^ $base)+1,2);

	}


	public static function get_devices($db,$network = "",$search = "",$exact = 0,$start_date = "",$end_date = "") {
		$search = strtolower(trim(rtrim($search)));
		$where_sql = array();
		$sql = "SELECT namespace.aname, namespace.ipnumber, ";
		$sql .= "LOWER(namespace.hardware) as hardware, namespace.name as user, ";
		$sql .= "namespace.email, namespace.room, namespace.os, ";
		$sql .= "namespace.description, namespace.serial_number, namespace.alias, ";
		$sql .= "namespace.modifiedby, namespace.modified, namespace.property_tag, ";
		$sql .= "a.switch, a.port, a.last_seen ";
		$sql .= "FROM namespace ";
		$sql .= "LEFT JOIN ( ";
		$sql .= "SELECT MAX(macwatch.date) as last_seen, macwatch.switch as switch, ";
		$sql .= "macwatch.port as port,LOWER(macwatch.mac) as mac FROM macwatch GROUP BY mac) as a ";
		$sql .= "ON a.mac=LOWER(namespace.hardware) ";
		if ($network != "") {
			if (strpos($network,"/")) {  //Use network/subnet format, 128.174.124.0/22
				list($low,$high) = self::get_ip_range($network);
			}
			elseif (strpos($network,"-")) { //Use startip-endip format, 128.174.124.1-128.174.124.100
				list($low,$high) = explode("-",$network);
				$low = ip2long($low);
				$high = ip2long($high);
			}
			$network_sql = "((INET_ATON(ipnumber) >='" . $low . "') AND (INET_ATON(ipnumber) <='" . $high . "')) ";
			array_push($where_sql,$network_sql);
		}
		if (($start_date != "") && ($end_date != "")) {
			if (($start_date == 0) && ($end_date == 0)) {
				$last_seen_sql = "(a.last_seen IS NULL) AND namespace.aname !='spare' ";
			}
			elseif ($end_date == 0) {
				$last_seen_sql = "(DATE(a.last_seen) <= DATE('" . $start_date . "')) ";
			}
			else {
				$last_seen_sql = "((DATE(a.last_seen) < DATE('" . $start_date . "')) AND (DATE(a.last_seen) > DATE('" . $end_date . "'))) ";
			}
			array_push($where_sql,$last_seen_sql);

		}
		if (($search != "" )  && ($exact == 0)){
			$terms = explode(" ",$search);
			foreach ($terms as $term) {
				$search_sql = "(LOWER(namespace.aname) LIKE '%" . $term . "%' OR ";
				$search_sql .= "namespace.ipnumber LIKE '%" . $term . "%' OR ";
				$search_sql .= "LOWER(namespace.hardware) LIKE '%" . $term . "%' OR ";
				$search_sql .= "LOWER(namespace.name) LIKE '%" . $term . "%' OR ";
				$search_sql .= "LOWER(namespace.email) LIKE '%" . $term . "%' OR ";
				$search_sql .= "LOWER(namespace.room) LIKE '%" . $term . "%' OR ";
				$search_sql .= "LOWER(namespace.os) LIKE '%" . $term . "%' OR ";
				$search_sql .= "LOWER(namespace.description) LIKE '%" . $term . "%' OR ";
				$search_sql .= "LOWER(namespace.property_tag) LIKE '%" . $term . "%' OR ";
				$search_sql .= "LOWER(namespace.serial_number) LIKE '%" . $term . "%' OR ";
				$search_sql .= "LOWER(namespace.alias) LIKE '%" . $term . "%') ";
				array_push($where_sql,$search_sql);
			}
	
		}
		elseif (($search != "") && ($exact == 1)) {
			$terms = explode(" ",$search);
        	        foreach ($terms as $term) {
                	        $search_sql = "(LOWER(namespace.aname)='" . $term . "' OR ";
                        	$search_sql .= "namespace.ipnumber='" . $term . "' OR ";
	                        $search_sql .= "LOWER(namespace.hardware)='" . $term . "' OR ";
        	                $search_sql .= "LOWER(namespace.name)='" . $term . "' OR ";
                	        $search_sql .= "LOWER(namespace.email)='" . $term . "' OR ";
                        	$search_sql .= "LOWER(namespace.room)='" . $term . "' OR ";
	                        $search_sql .= "LOWER(namespace.os)='" . $term . "' OR ";
        	                $search_sql .= "LOWER(namespace.description)='" . $term . "' OR ";
                	        $search_sql .= "LOWER(namespace.property_tag)='" . $term . "' OR ";
				$search_sql .= "LOWER(namespace.serial_number) LIKE '%" . $term . "%' OR ";
	                        $search_sql .= "LOWER(namespace.alias)='" . $term . "') ";
        	                array_push($where_sql,$search_sql);
                	}

		}


		$num_where = count($where_sql);
		if ($num_where) {
			$sql .= "WHERE ";
			$i = 0;
			foreach ($where_sql as $where) {
				$sql .= $where;
				if ($i<$num_where-1) {
					$sql .= "AND ";
				}
				$i++;	
			}

		}
		$sql .= "ORDER BY INET_ATON(ipnumber) ASC ";
		$result = $db->query($sql);
		return $result;

	}


	//get_operating_systems()
	//$db - database object
	//returns list of operating systems in database table operating_systems
	public static function get_operating_systems($db) {
		$sql = "SELECT * FROM operating_systems ";
		$sql .= "ORDER BY os ASC";
		return $db->query($sql);

	}

	//get_last_seen()
	//$last_seen - date/time in human readable format ie (YYYY-MM-DD HH-MM-SS)
	//returns integer
	//1 if seen in last day
	//2 if seen in last month, greater than 1 day
	//3 if seen in last 6 months, greater than 1 month
	//4 if seen greater than 6 months
	//5 if never seen
	public static function get_last_seen($last_seen) {

		$one_day = strtotime('-1 day',time());
		$one_month = strtotime('-1 month',time());
		$six_months = strtotime('-6 month',time());
		$last_seen = strtotime($last_seen);


		if ($last_seen >= $one_day) {
			return 1;
		}
		elseif (($last_seen < $one_day) && ($last_seen >= $one_month)) {
			return 2;
		}
		elseif (($last_seen < $one_month) && ($last_seen >= $six_months)) {
			return 3;
		}
		elseif (($last_seen > 0) && ($last_seen < $six_months)) {
			return 4;
		}
		else { //Never seen
			return 5;
		}

	}

	//get_pages_html()
	//$url - url of page
	//$num_records - number of items
	//$start - start index of items
	//$count - number of items per page
	//returns pagenation to navigate between pages of devices
	public static function get_pages_html($url,$num_records,$start,$count) {
	
		$num_pages = ceil($num_records/$count);
		$current_page = $start / $count + 1;
		if (strpos($url,"?")) {
			$url .= "&start=";
		}
		else {
			$url .= "?start=";
		
		}
	
		$pages_html = "<nav><ul class='pagination justify-content-center flex-wrap'>";
		if ($current_page > 1) {
			$start_record = $start - $count;
			$pages_html .= "<li class='page-item'><a class='page-link' href='" . $url . $start_record . "'>&laquo;</a></li> ";
		}
		else {
			$pages_html .= "<li class='page-item disabled'><a class='page-link' href='#'>&laquo;</a></li>";
		}
		
		for ($i=0; $i<$num_pages; $i++) {
			$start_record = $count * $i;
			if ($i == $current_page - 1) {
				$pages_html .= "<li class='page-item disabled'>";
			}
			else {
				$pages_html .= "<li class='page-item'>";
			}
			$page_number = $i + 1;
			$pages_html .= "<a class='page-link' href='" . $url . $start_record . "'>" . $page_number . "</a></li>";
		}

		if ($current_page < $num_pages) {
			$start_record = $start + $count;
			$pages_html .= "<li class='page-item'><a class='page-link' href='" . $url . $start_record . "'>&raquo;</a></li> ";
		}
		else {
			$pages_html .= "<li class='page-item disabled'><a class='page-link' href='#'>&raquo;</a></li>";
		}
		$pages_html .= "</ul></nav>";
		return $pages_html;

	}

	//get_ip_range()
	//$cidr - network in format network/subnet, ie 128.174.124.0/22
	//returns array size 2, first index is start ip, second index is end ip
	//calcuates the start and end range of ips from given cidr
	public static function get_ip_range($cidr) {

		list($ip, $mask) = explode('/', $cidr);
 
		$maskBinStr =str_repeat("1", $mask ) . str_repeat("0", 32-$mask );      //net mask binary string
		$inverseMaskBinStr = str_repeat("0", $mask ) . str_repeat("1",  32-$mask ); //inverse mask
  	
		$ipLong = ip2long( $ip );
		$ipMaskLong = bindec( $maskBinStr );
		$inverseIpMaskLong = bindec( $inverseMaskBinStr );
		$netWork = $ipLong & $ipMaskLong; 
		$start = $netWork+1;//ignore network ID(eg: 192.168.1.0)
 	
		$end = ($netWork | $inverseIpMaskLong) - 1; //ignore brocast IP(eg: 192.168.1.255)
		return array($start,$end );


	}

	//get_switches()
	//$db - database object
	//returns array of switches in database
	public static function get_switches($db) {
		$sql = "SELECT * FROM switches";
		return $db->query($sql);
	}


	//get_locations()
	//$db - database object
	//$search - search term - optional
	//$start - start index - optional
	//$count - amount to return - optional
	//returns array of locations
	public static function get_locations($db,$search= "",$start = 0,$count = 0) {
        	$search = strtolower(trim(rtrim($search)));
	        $where_sql = array();
		$sql = "SELECT locations.*, ports.port as port, ";
		$sql .= "switches.name as switch_name ";
		$sql .= "FROM locations ";
		$sql .= "LEFT JOIN ports ON locations.id=ports.location_id ";	
		$sql .= "LEFT JOIN switches ON switches.id=ports.switch_id ";

		if ($search != "" ) {
                	$terms = explode(" ",$search);
        	        foreach ($terms as $term) {
                        	$search_sql = "(LOWER(ports.port) LIKE '%" . $term . "%' OR ";
	                        $search_sql .= "LOWER(locations.jack) LIKE '%" . $term . "%' OR ";
	                        $search_sql .= "LOWER(locations.room) LIKE '%" . $term . "%' OR ";
        	                $search_sql .= "LOWER(locations.building) LIKE '%" . $term . "%' OR ";
                	        $search_sql .= "LOWER(switches.name) LIKE '%" . $term . "%') ";
	                        array_push($where_sql,$search_sql);
        	        }

	        }
        	$num_where = count($where_sql);
	        if ($num_where) {
        	        $sql .= "WHERE ";
                	$i = 0;
	                foreach ($where_sql as $where) {
        	                $sql .= $where;
                	        if ($i<$num_where-1) {
                        	        $sql .= "AND ";
	                        }
        	                $i++;
                	}

	        }
		$sql .= "ORDER BY locations.room ASC ";	
		if ($count != 0) {
        	        $sql .= "LIMIT " . $start . "," . $count;
	        }
		$result = $db->query($sql);
		return $result;
	}

	public static function get_num_locations($db,$search = "") {
		return count(self::get_locations($db,$search));

	}

	//get_ports()
	//$db - database object
	//$switch_id - database id of switch
	//returns array of ports on the given switch
	public static function get_ports($db,$switch_id) {
		$sql = "SELECT ports.port as port, ports.id as id ";
		$sql .= "FROM ports ";
		$sql .= "WHERE ports.switch_id='" . $switch_id . "'";
		return $db->query($sql);
	}

	//get_unused_ports()
	//$db - database object
	//$switch_id - database id of switch
	//returns array of ports that are not connected to a jack
	public static function get_unused_ports($db,$switch_id) {
        	$sql = "SELECT ports.id as id, ports.port as port ";
	        $sql .= "FROM ports ";
        	$sql .= "WHERE ports.switch_id='" . $switch_id . "' ";
		$sql .= "AND ports.location_id=0 ";
        	return $db->query($sql);

	}

	public static function get_hardware_addresses($db,$search = "",$start = 0,$count = 0) {
		$search = strtolower(trim(rtrim($search)));
		$where_sql = array();
		$sql = "SELECT macwatch.switch as switch, macwatch.port as port, ";
		$sql .= "macwatch.mac as mac, macwatch.vendor as vendor, ";
		$sql .= "macwatch.date as last_seen ";
		$sql .= "FROM macwatch ";
		//$sql .= "LEFT JOIN namespace ON namespace.hardware=macwatch.mac ";
	
		array_push($where_sql,"macwatch.port IS NOT NULL ");

        	if ($search != "" ){
			$terms = explode(" ",$search);
	                foreach ($terms as $term) {
        	                $search_sql = "(LOWER(macwatch.switch) LIKE '%" . $term . "%' OR ";
                	        $search_sql .= "LOWER(macwatch.port) LIKE '%" . $term . "%' OR ";
                        	$search_sql .= "LOWER(macwatch.mac) LIKE '%" . $term . "%' OR ";
	                        $search_sql .= "LOWER(macwatch.vendor) LIKE '%" . $term . "%') ";
        	                array_push($where_sql,$search_sql);
                	}
		}
		$num_where = count($where_sql);
	        if ($num_where) {
        	        $sql .= "WHERE ";
                	$i = 0;
	                foreach ($where_sql as $where) {
        	                $sql .= $where;
                	        if ($i<$num_where-1) {
                        	        $sql .= "AND ";
	                        }
        	                $i++;   
                	}

	        }
		$sql .= "GROUP BY macwatch.mac ";
		$sql .= "HAVING MAX(macwatch.date) ";
	        $sql .= "ORDER BY last_seen DESC ";
	
		if ($count !== 0) {
			$sql .= "LIMIT " . $start . "," . $count;
		}
        	$result = $db->query($sql);
	        return $result;

	}

	public static function get_num_hardware_addresses($db,$search) {
		return count(self::get_hardware_addresses($db,$search));
	}	

        public static function alert($message, $success = 1) {
                $alert = "";
                if ($success) {
                        $alert = "<div class='alert alert-success' role='alert'>" . $message . "</div>&nbsp;";

                }
                else {
                        $alert = "<div class='alert alert-danger' role='alert'>" . $message . "</div>&nbsp;";
                }
                return $alert;

        }

	public static function authenticate($ldap,$username,$password,$group) {
	        $result = false;
        	$rdn = self::get_user_rdn($ldap,$username);
	        if (($ldap->bind($rdn,$password)) && ($ldap->is_group_member($username,$group))) {
        	        $result = true;
	        }
        	return $result;
	}

	private static function get_user_rdn($ldap,$username) {
                $filter = "(uid=" . $username . ")";
                $attributes = array('dn');
                $result = $ldap->search($filter,'',$attributes);
                if (isset($result[0]['dn'])) {
                        return $result[0]['dn'];
                }
                else {
                        return false;
                }
        }

}
?>
