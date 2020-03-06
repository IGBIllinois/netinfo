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
		$sql .= "ORDER BY vlan ASC ";
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


	public static function get_devices($db,$network = "",$search = "",$exact = 0,$start_date = "",$end_date = "",$include_spares = 1) {
		$search = strtolower(trim(rtrim($search)));
		$where_sql = array();
		$sql = "SELECT namespace.aname, namespace.ipnumber, ";
		$sql .= "LOWER(namespace.hardware) as hardware, namespace.name as user, ";
		$sql .= "namespace.email, namespace.room, namespace.os, ";
		$sql .= "namespace.description, namespace.serial_number, ";
		$sql .= "namespace.modifiedby, namespace.modified, namespace.property_tag, ";
		$sql .= "macwatch_latest.switch, macwatch_latest.port, macwatch_latest.date as last_seen, ";
		$sql .= "domains.name as domain_name, namespace.alias ";
		$sql .= "FROM namespace ";
		$sql .= "LEFT JOIN macwatch_latest ON macwatch_latest.mac=LOWER(namespace.hardware) ";
		$sql .= "LEFT JOIN networks ON networks.id=namespace.network_id ";
		$sql .= "LEFT JOIN domains ON domains.id=networks.domain_id ";
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
				$search_sql .= "LOWER(namespace.alias) LIKE '%" . $term . "%' OR ";
				$search_sql .= "LOWER(a.port) LIKE '%" . $term . "%' OR ";
				$search_sql .= "LOWER(a.switch) LIKE '%" . $term . "%') ";
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
	                        $search_sql .= "LOWER(namespace.alias)='" . $term . "' OR ";
				$search_sql .= "LOWER(a.port) LIKE '%" . $term . "%' OR ";
				$search_sql .= "LOWER(a.switch) LIKE '%" . $term . "%') ";
        	                array_push($where_sql,$search_sql);
                	}

		}

		if (!$include_spares) {
			$search_sql = "ANAME<>'spare'";
			array_push($where_sql,$search_sql);

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
		$sql = "SELECT macwatch_latest.mac as mac, macwatch_latest.vendor as vendor, ";
		$sql .= "macwatch_latest.port as port, macwatch_latest.switch as switch, ";
		$sql .= "macwatch_latest.date as last_seen ";
		$sql .= "FROM macwatch_latest ";
		//$sql .= "LEFT JOIN namespace ON namespace.hardware=macwatch_latest.mac ";
	
		array_push($where_sql,"macwatch_latest.port IS NOT NULL ");

        	if ($search != "" ){
			$terms = explode(" ",$search);
	                foreach ($terms as $term) {
        	                $search_sql = "(LOWER(macwatch_latest.switch) LIKE '%" . $term . "%' OR ";
                	        $search_sql .= "LOWER(macwatch_latest.port) LIKE '%" . $term . "%' OR ";
                        	$search_sql .= "LOWER(macwatch_latest.mac) LIKE '%" . $term . "%' OR ";
	                        $search_sql .= "LOWER(macwatch_latest.vendor) LIKE '%" . $term . "%') ";
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


	public static function authenticate($ldap,$username,$password,$group) {
	        $result = false;
        	$rdn = self::get_user_rdn($ldap,$username);
	        if (($ldap->bind($rdn,$password)) && ($ldap->is_group_member($username,$group))) {
        	        $result = true;
			log::send_log("User " . $username . " successfully logged in");
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

        //create_host_file()
        //$data - double array of data
        //$filename - name of file to create
        //creates a linux formatted /etc/hosts file.
        public static function create_host_file($data,$filename) {
		ob_clean();
                $delimiter = "\t";
                $file_handle = fopen('php://output','w');
                ob_start();
                foreach ($data as $row) {
			$formatted_row_array = array($row['ipnumber'],
						$row['aname'],
						$row['aname'] . "." . $row['domain_name']
						);
			
			fputcsv($file_handle,$formatted_row_array,$delimiter);				
                }
                fclose($file_handle);
                $result = ob_get_clean();
                //Sets headers then downloads the csv report file.
                header('Pragma: public');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Content-Type: text/plain');
                header("Content-Disposition:attachment; filename=" . $filename);
                echo $result;


        }

	public static function get_dhcpd_version() {
		$exec = "/usr/sbin/dhcpd --version 2>&1";
		$exit_status = 1;
		$output_array = array();
		$output = exec($exec,$output_array,$exit_status);
		if (!$exit_status) {
			return $output_array[0];
		}	
		return "";


	}

        public static function get_bind_version() {
                $exec = "/usr/sbin/named -V";
                $exit_status = 1;
                $output_array = array();
                $output = exec($exec,$output_array,$exit_status);
		if (!$exit_status) {
			return $output_array[0];
		}
                return "";
        }

	public static function get_php_extensions() {
		$chunk_size = 10;
		$extensions = get_loaded_extensions(); 
		natcasesort($extensions);
		$extensions_chunk = array_chunk($extensions,$chunk_size);	
			
		return $extensions_chunk;

	}
	
	public static function get_webserver_version() {
		if (isset($_SERVER['SERVER_SOFTWARE'])) {
			return $_SERVER['SERVER_SOFTWARE'];
		}
		return "";

	}
	
}
?>
