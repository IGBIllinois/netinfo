<?php

class network_switch {


	private $db;
	private $id;
	private $hostname;
	private $enabled;
	private $ignore_ports = array();
	private $type;
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

	public function get_type() {
		return $this->type;
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
			$this->type=$result[0]['type'];

		}
	}

	private function load_ignore_ports() {
		$sql = "SELECT * FROM ignored_ports WHERE switch_hostname=:hostname";
		$params = array(':hostname'=>$this->get_hostname());
		$result = $this->db->query($sql,$params);
		if (count($result)) {
			$ignore_ports = array();
			foreach ($result as $data) {
				$ignore_ports[] = $data['portname'];	


			}
	
			$this->ignore_ports = $ignore_ports;
		}

	}

}

?>
