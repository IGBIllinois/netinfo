<?php

class network {

	////////////////Private Variables//////////

        private $db;
	private $id;
	private $domain_id;
	private $domain_name;
	private $name;
	private $network;
	private $netmask;
	private $vlan;
	private $options;
	private $enabled;
	private $last_updated;

        ////////////////Public Functions///////////

        public function __construct($db,$name = "") {
                $this->db = $db;
                if ($name != "") {
                        $this->get_network($name);
                }
        }
        public function __destruct() {}

	public function get_id() { return $this->id; }
	public function get_domain_id() { return $this->domain_id; }
	public function get_domain_name() { return $this->domain_name; }
	public function get_name() { return $this->name; }
	public function get_network_number() { return $this->network; }
	public function get_netmask() { return $this->netmask; }
	public function get_vlan() { return $this->vlan; }
	public function get_options() { return $this->options; }
	public function is_enabled() { return $this->enabled; }
	public function get_last_updated() { return $this->last_updated; }


	public function update_dhcpd($directory) {
                $valid = false;
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


                if (!$error) {
                        $valid = true;
                        $dhcpd_conf = $this->build_dhcpd_conf();
                        $filename = $directory . "/" . $this->get_name() . ".conf";
                        functions::write_file($dhcpd_conf,$filename);
			$valid = $this->verify_dhcpd_conf($filename);
			if ($valid) {
                        	$message = "DHCP successfully updated for " . $this->get_name() . " to " . $filename . "\n";
			}
			else {
				$message = "Failed updating DHCP for " . $this->get_name() . "\n";
			}
                }

                return array('RESULT'=>$valid,'MESSAGE'=>$message);



        }

	////////////////////Private Functions///////////////////
	private function get_network($name) {
		$sql = "SELECT networks.*,domains.name as domain_name FROM networks ";
		$sql .= "LEFT JOIN domains ON networks.domain_id=domains.id ";
		$sql .= "WHERE networks.name='" . $name . "' LIMIT 1";
		$result = $this->db->query($sql);
		if (count($result)) {
			$this->id = $result[0]['id'];
			$this->name = $result[0]['name'];
			$this->network = $result[0]['network'];
			$this->netmask = $result[0]['netmask'];
			$this->vlan = $result[0]['vlan'];
			$this->options = $result[0]['options'];
			$this->enabled = $result[0]['enabled'];
			$this->last_updated = $result[0]['last_updated'];
			$this->domain_id = $result[0]['domain_id'];
			$this->domain_name = $result[0]['domain_name'];
		}


	}



	private function build_dhcpd_conf() {

                $dhcpd_conf = "subnet " . $this->get_network_number() . " netmask " . $this->get_netmask() . " {\n";
		if ($this->get_options() != "") {
                	$dhcpd_conf .= "\n\t" . $this->get_options();
		}
                $dhcpd_conf .= "\n\n";
                $dhcpd_conf .= $this->get_reservations();
                $dhcpd_conf .= "\n}\n";
                return $dhcpd_conf;



        }

        private function get_reservations() {
                $valid = false;
		$sql = "SELECT aname,ipnumber,hardware FROM namespace ";
                $sql .= "WHERE network_id='" . $this->get_id() . "' ";
                $sql .= "AND aname<>'spare' AND hardware<>'000000000000'";
		$reservations = $this->db->query($sql);
                
                $reservation_txt = "";
                if (count($reservations)) {
                        foreach ($reservations as $reservation) {
                                $reservation_txt .= "\thost " . $reservation['aname'] . " {\n";
                                $reservation_txt .= "\t\thardware ethernet " . $this->format_hardware_address($reservation['hardware']) . ";\n";
                                $reservation_txt .= "\t\tfixed-address " . $reservation['ipnumber'] . ";\n";
                                $reservation_txt .= "\t}\n";


                        }
                        $valid = $reservation_txt;
                }
                return $valid;

        }



        private static function format_hardware_address($hardware) {
		$hardware = strtolower($hardware);
                $chunks = str_split($hardware,2);
                return implode(':',$chunks);

        }


	private static function verify_dhcpd_conf($filename) {

		$valid = false;
                $exec = "dhcpd -t -cf " . $filename . " > /dev/null 2>&1";
                $exit_status = 1;
                $output_array = array();
                $output = exec($exec,$output_array,$exit_status);
                if ($exit_status == "0") {
                        $valid = true;
                }

                return $valid;


	}




}
?>
