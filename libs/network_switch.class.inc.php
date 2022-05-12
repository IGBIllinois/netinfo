<?php

class network_switch {


	private $db;
	private $id;
	private $hostname;
	private $enabled;
	private $ignore_ports = array();
	/////////////Public Functions//////////////
        public function __construct($db,$hostname) {
                $this->db = $db;
		$this->load_switch($hostname);
		$this->load_ignore_ports();

        }
        public function __destruct() {

        }


	public function get_ignore_ports() {
		return $this->ignore_ports;
	}

	public function get_id() {
		return $this->id;
	}
	public function get_hostname() {
		return $this->hostname;
	}
	public function get_enabled() {
		return $this->enabled;
	}

	/////////////////Private Functions/////////////


	private function load_switch($hostname) {
		$sql = "SELECT * FROM switches WHERE hostname=:hostname LIMIT 1";
		$params = array(':hostname'=>$hostname);
		$result = $this->db->query($sql,$params);
		if (count($result)) {
			$this->id=$result[0]['switch_id'];
			$this->hostname=$hostname;
			$this->enabled=$result[0]['enabled'];

		}
	}

	private function load_ignore_ports() {
		$sql = "SELECT * FROM ignored_ports WHERE switch_id=:switch_id";
		$params = array(':switch_id'=>$this->get_id());
		$result = $this->db->query($sql,$params);
		if (count($result)) {
			$ignore_ports = array();
			foreach ($result as $data) {
				$ignore_ports[] = $data['port'];	


			}
	
			$this->ignore_ports = $ignore_ports;
		}

	}

	//////////////////////Public Static Functions///////////////

	public static function get_switches($db) {
                $sql = "select * from switches WHERE enabled='1'";
                return $db->query($sql);


        }

}

?>
