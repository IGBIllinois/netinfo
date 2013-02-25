<?php

function get_devices($db,$network = "",$search = "",$start = 0 ,$count = 0) {
	$search = strtolower(trim(rtrim($search)));
	$where_sql = array();
	$sql = "SELECT namespace.aname, namespace.ipnumber, ";
	$sql .= "LOWER(namespace.hardware) as hardware, namespace.name as user, ";
	$sql .= "namespace.email, namespace.room, namespace.os, ";
	$sql .= "namespace.description, namespace.backpass, namespace.alias, ";
	$sql .= "namespace.modifiedby, namespace.modified, namespace.property_tag, ";
	$sql .= "a.switch, a.port, a.last_seen ";
	$sql .= "FROM namespace ";
	$sql .= "LEFT JOIN ( ";
	$sql .= "SELECT MAX(macwatch.date) as last_seen, macwatch.switch as switch, ";
	$sql .= "macwatch.port as port,LOWER(macwatch.mac) as mac FROM macwatch GROUP BY mac) as a ";
	$sql .= "ON a.mac=LOWER(namespace.hardware) ";
	if ($network != "") {
		list($low,$high) = get_ip_range($network);
		$network_sql = "((INET_ATON(ipnumber) >='" . $low . "') AND (INET_ATON(ipnumber) <='" . $high . "')) ";
		array_push($where_sql,$network_sql);
	}
	if ($search != "" ) {
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
			$search_sql .= "LOWER(namespace.alias) LIKE '%" . $term . "%') ";
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
	if ($count != 0) {
                $sql .= "LIMIT " . $start . "," . $count;
        }
	$result = $db->query($sql);
	return $result;



}

function get_num_devices($db,$network="", $search = "") {
	$devices = get_devices($db,$network,$search);
	return count($devices);

}

function get_operating_systems($db) {
	$sql = "SELECT * FROM operating_systems ";
	$sql .= "ORDER BY os ASC";
	return $db->query($sql);


}
function get_last_seen($last_seen) {

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
	else {
		return 5;
	}

}


function get_pages_html($url,$num_records,$start,$count) {
	
	$num_pages = ceil($num_records/$count);
	$current_page = $start / $count + 1;
	if (strpos($url,"?")) {
		$url .= "&start=";
	}
	else {
		$url .= "?start=";
		
	}
	
	$pages_html = "<div class='pagination pagination-centered'><ul>";

	if ($current_page > 1) {
		$start_record = $start - $count;
		$pages_html .= "<li><a href='" . $url . $start_record . "'>&laquo;</a></li> ";
	}
	else {
		$pages_html .= "<li class='disabled'><a href='#'>&laquo;</a></li>";
	}
		
	for ($i=1; $i<$num_pages; $i++) {
		$start_record = $count * $i;
		if ($i == $current_page - 1) {
			$pages_html .= "<li class='disabled'>";
		}
		else {
			$pages_html .= "<li>";
		}
		$pages_html .= "<a href='" . $url . $start_record . "'>$i</a></li>";
	}

	if ($current_page < $num_pages) {
		$start_record = $start + $count;
		$pages_html .= "<li><a href='" . $url . $start_record . "'>&raquo;</a></li> ";
	}
	else {
		$pages_html .= "<li class='disabled'><a href='#'>&raquo;</a></li>";
	}
	$pages_html .= "</ul></div>";
	return $pages_html;

}

function get_ip_range($cidr) {

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

function get_switches($db) {
	$sql = "SELECT * FROM switches";
	return $db->query($sql);
}

function get_buildings() {
	return explode(",",__BUILDINGS__);

}

function get_locations($db,$search= "",$start = 0,$count = 0) {
        $search = strtolower(trim(rtrim($search)));
        $where_sql = array();
	$sql = "SELECT locations.*, ";
	$sql .= "switches.name as switch_name ";
	$sql .= "FROM locations ";
	$sql .= "LEFT JOIN switches ON switches.id=locations.switch_id ";

	if ($search != "" ) {
                $terms = explode(" ",$search);
                foreach ($terms as $term) {
                        $search_sql = "(LOWER(locations.port) LIKE '%" . $term . "%' OR ";
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
	
	if ($count != 0) {
                $sql .= "LIMIT " . $start . "," . $count;
        }
	$result = $db->query($sql);
	return $result;
}

function get_num_locations($db,$search = "") {
	return count(get_locations($db,$search));

}

?>
