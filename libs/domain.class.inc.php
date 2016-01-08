<?php

class domain {

	////////////////Private Variables//////////

        private $db;
	private $id;
	private $serial;
	private $enabled;
	private $header;
	private $options;
	private $name;
	private $last_updated;
	private $alt_names = array();
	private $serial_variable = "[SERIAL]";
	private $domain_variable = "[DOMAIN]";
        ////////////////Public Functions///////////

        public function __construct($db,$name = "") {
                $this->db = $db;
                if ($name != "") {
                        $this->get_domain($name);
                }
        }
        public function __destruct() {}

	public function get_id() { return $this->id; }
	public function get_name() { return $this->name; }
	public function get_serial() { return $this->serial; }
	public function get_header() { return $this->header; }
	public function get_options() { return $this->options; }
	public function get_alt_names() { return $this->alt_names; } 
	public function is_enabled() { return $this->enabled; }
	public function get_last_updated() { return $this->last_updated; }


	public function build_bind_conf($domain_name) {

			$bind_conf = $this->build_bind_header(); 
			$bind_conf .= ";\n";
			$bind_conf .= $this->build_bind_options($domain_name);
                        $bind_conf .= ";Addresses for the cname\n";
                        $bind_conf .= ";\n";

                        $bind_conf .= $this->get_cnames($domain_name);
                        $bind_conf .= ";\n";
                        $bind_conf .= ";Addresses for the aname\n";
                        $bind_conf .= ";\n";
                        $bind_conf .= $this->get_anames();

                        return $bind_conf;
        }

	//build_bind_header()
	//builds the header file that goes at the top of a zone file.
	public function build_bind_header() {
		$bind_conf = $this->get_header();
                //Replace variables for [SERIAL]
                $search = array($this->serial_variable);
                $replace = array($this->get_serial());
		$bind_conf = str_replace($search,$replace,$bind_conf);
		$bind_conf .= "\n";
		return $bind_conf;
	}

	//build_bind_options()
	//builds the text for additional DNS records that are not CNAMES or ANAMES
        public function build_bind_options($domain_name) {
                $bind_conf = $this->get_options();
                //Replace variables for [SERIAL]
                $search = array($this->domain_variable);
                $replace = array($domain_name);
                $bind_conf = str_replace($search,$replace,$bind_conf);
                $bind_conf .= "\n";
                return $bind_conf;
        }


        public function update_bind($directory) {
                $valid = true;
                $error = false;
                $message = "";
                if (!is_dir($directory) || !file_exists($directory)) {
                        $error = true;
                        $message = $directory . " does not exist\n";
                }
                elseif (!is_writable($directory)) {
                        $error = true;
                        $message = $directory . " is not writable\n";
                }
                elseif (strtotime($this->get_last_updated()) > strtotime($this->get_last_modified())) {
                        $error = true;
                        $message = "No DNS updates needed for " . $this->get_name() . "\n";
                }


                if (!$error) {
                        $this->update_serial();
			
			//Create conf for main domain name
			$domain_name = $this->get_name();	
                        $bind_conf = $this->build_bind_conf($domain_name);
                        $filename = $directory . "/db." . $domain_name;
                        functions::write_file($bind_conf,$filename);
			
			$verify = $this->verify_zone_file($domain_name,$filename);
			if (!$verify) {
				$valid = false;
                                $message .= "Invalid zone file: " . $filename . "\n";
			}

			//Create conf for alternate domain names
			if (count($this->get_alt_names())) {
				foreach ($this->get_alt_names() as $domain_name) {
					$bind_conf = $this->build_bind_conf($domain_name);
					$filename = $directory . "/db." . $domain_name;
		                        functions::write_file($bind_conf,$filename);
					$verify = $this->verify_zone_file($domain_name,$filename);
        	                        if (!$verify) {
                	                        $valid = false;
                        	                $message .= "Invalid zone file: " . $filename . "\n";

                                	}
	
				}

			}
			//Create conf for reverse zone files	
			foreach ($this->get_reverse_zones() as $network=>$reverse_txt) {
				$parts = explode('.',$network);
				array_pop($parts);
                                $reverse_network = implode('.', array_reverse($parts));
				$reverse_txt = $this->build_bind_header($network) . "\n\n" . $reverse_txt;	
				$filename = $directory . "/db." . $reverse_network;
				functions::write_file($reverse_txt,$filename);
				$verify = $this->verify_zone_file($network,$filename);
				if (!$verify) {
					$valid = false;
					$message .= "Invalid zone file: " . $filename . "\n";

				}

			}
			if ($valid) {
                        	$message = "DNS successfully updated for " . $this->get_name() . "\n";
			}
			else {
				$message .= "DNS failed updating for " . $this->get_name() . "\n";
			}
                }

                return array('RESULT'=>$valid,'MESSAGE'=>$message);


        }
	////////////////////Private Functions///////////////////
	private function get_domain($name) {
		$sql = "SELECT * FROM domains WHERE name='" . $name . "' LIMIT 1";
		$result = $this->db->query($sql);
		if (count($result)) {
			$this->id = $result[0]['id'];
			$this->name = $result[0]['name'];
			$this->serial = $result[0]['serial'];
			$this->header = $result[0]['header'];
			$this->options = $result[0]['options'];
			$this->enabled = $result[0]['enabled'];
			$this->last_updated = $result[0]['last_updated'];
			if (strlen($result[0]['alt_names']) > 0) {
                                $this->alt_names = explode(",",$result[0]['alt_names']);
                        }
                        else {
                                $this->alt_names = array();
                        }

		}


	}



	public function get_cnames($domain_name) {

		$sql = "SELECT aname,alias FROM namespace ";
		$sql .= "LEFT JOIN networks ON namespace.network_id=networks.id ";
		$sql .= "WHERE alias <> '' AND ";
		$sql .= "domain_id='" . $this->get_id() . "' ";
		$sql .= "AND aname<>'spare' ORDER BY aname ASC ";
		$result = $this->db->query($sql);
		$cname_txt = "";
                if (count($result)) {
                        foreach ($result as $record) {
                                $aname_fqdn = $record['aname'] . "." . $domain_name . ".";
                                $aliases = explode(',',$record['alias']);
                                foreach ($aliases as $alias) {
                                        $alias_fqdn = $alias . "." . $domain_name . ".";

                                        $cname_txt .= $alias_fqdn . "\t\tIN CNAME\t" . $aname_fqdn . "\n";
                                }
                        }
                }

                return $cname_txt;


	}

	public function get_anames() {
		$sql = "SELECT aname,ipnumber,networks.id FROM namespace ";
                $sql .= "LEFT JOIN networks ON namespace.network_id=networks.id ";
		$sql .= "WHERE domain_id='" . $this->get_id() . "' ";
		$sql .= "AND aname<>'spare' ORDER BY INET_ATON(ipnumber) ASC ";
		$result = $this->db->query($sql);
		 $aname_txt = "";
                if (count($result)) {
                        foreach ($result as $record) {
                                $aname_txt .= $record['aname'] . "\t\tIN A\t" . $record['ipnumber'] . "\n";
                        }
                }

                return $aname_txt;

	}

        private function get_reverse_zones() {
                $sql = "SELECT aname,ipnumber,network,netmask FROM namespace ";
                $sql .= "LEFT JOIN networks ON namespace.network_id=networks.id ";
                $sql .= "WHERE domain_id='" . $this->get_id() . "' ";
                $sql .= "AND aname<>'spare' ORDER BY INET_ATON(ipnumber) ASC";
                $result = $this->db->query($sql);
                $reverse_txt = "";
		$networks = array();
                if (count($result)) {
                        foreach ($result as $record) {
				$class_c_network = substr($record['ipnumber'],0,strrpos($record['ipnumber'],".")) . ".0";
				if (!array_key_exists($class_c_network,$networks)) {
					$networks[$class_c_network] = "";
				}
				//chops up ip address into array
				$parts = explode('.',$record['ipnumber']);
				//reverses the ip address
				$reverse_ip = implode('.', array_reverse($parts));
				//gets full qualified domain name
                                $aname_fqdn =$record['aname'] . "." . $this->get_name() . ".";
                                $networks[$class_c_network] .= array_pop($parts) . "\t\tIN PTR\t" . $aname_fqdn . "\n";
                        }
                }

                return $networks;



        }

	private function get_last_modified() {
		$sql = "SELECT modified FROM namespace ";
                $sql .= "LEFT JOIN networks ON namespace.network_id=networks.id ";
		$sql .= "WHERE domain_id='" . $this->get_id() . "' ";
		$sql .= "ORDER BY modified DESC LIMIT 1 ";
		$result = $this->db->query($sql);
		if (count($result)) {
			return $result[0]['modified'];
		}
		return false;


	}

	
	private function update_serial() {
		$sql = "UPDATE domains SET serial=serial+1 ";
		$sql .= "WHERE id='" . $this->get_id() . "' LIMIT 1";
		$this->serial = $this->serial + 1;
		return $this->db->non_select_query($sql);

	}

	//verify_zone_file()
	//$domain - string - domain name
	//$zone_file - string - full path to zone file
	//Verifies zone file using named-checkzone command
	//returns True on success, false otherwise
	private function verify_zone_file($domain,$zone_file) {
		$valid = false;
		$exec = "named-checkzone " . $domain . " " . $zone_file;
		$exit_status = 1;
		$output_array = array();
        	$output = exec($exec,$output_array,$exit_status);
		if (array_key_exists(1,$output_array) && ($output_array[1] == 'OK')) {
			$valid = true;
		}

		return $valid;

	}


}
?>
