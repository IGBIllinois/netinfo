<?php

class location {

////////////////Private Variables//////////

        private $db; //mysql database object
	private $id;
	private $switch_id;
	private $switch_name;
	private $port;
	private $jack;
	private $building;
	private $room;

        ////////////////Public Functions///////////

        public function __construct($db,$id=0) {
                $this->db = $db;
		if ($id !== 0) {
			$this->get_location($id);
		}
        }
        public function __destruct() {
        }


	public function create($switch_id,$port,$jack,$room,$building) {
		$error = 0;
		$message = "";
		if ($switch_id == "") {
			$error = 1;
			$message .= "<div class='alert alert-error'>Please select a switch</div>";
		}
		if (!$this->verify_port($port)) {
			$error = 1;
			$message .= "<div class='alert alert-error'>Port name " . $port . " is invalid. ie (Ga3/05)</div>";

		}
		if (!$this->unique_port($port,$switch_id)) {
			$error = 1;
			$message .= "<div class='alert alert-error'>Port " . $port . " already exists</div>";
		}
		if (!$this->verify_jack($jack)) {
			$error = 1;
			$message .= "<div class='alert alert-error'>Jack number is invalid. ie (HA-P4-06)</div>";
		}
		if (!$this->unique_jack($jack)) {
			$error = 1;
			$message .= "<div class='alert alert-error'>Jack " . $jack . " already exists</div>";
		}
		if (!$this->verify_room($room)) {
			$error = 1;
			$message .= "<div class='alert alert-error'>Room Number invalid</div>";
		}
		if ($building == "") {
			$error = 1;
			$message .= "<div class='alert alert-error'>Please select a building</div>";
		}

		if ($error === 0) {
			$build_insert = array('switch_id'=>$switch_id,
					'port'=>$port,
					'jack'=>$jack,
					'room'=>$room,
					'building'=>$building);
			$result = $this->db->build_insert('locations',$build_insert);
			$message = "<div class='alert alert-success'>Location successfully added</div>";
			return array('RESULT'=>$result,'MESSAGE'=>$message);
		}
		else {
			return array('RESULT'=>false,'MESSAGE'=>$message);
		}
	}
	public function get_id() { return $this->id; }
	public function get_switch() { return $this->switch_name; }
	public function get_port() { return $this->port; }
	public function get_jack() { return $this->jack; }
	public function get_room() { return $this->room; }
	public function get_building() { return $this->building; }
	

	////////////////Private Functions/////////////

	private function get_location($id) {
		$sql = "SELECT locations.id, locations.port, locations.jack, locations.room, ";
		$sql .= "locations.building, switches.name as switch_name,switches.id as switch_id ";
		$sql .= "FROM locations ";
		$sql .= "LEFT JOIN switches ON switches.id=locations.switch_id ";
		$sql .= "WHERE locations.id='" . $id . "' LIMIT 1";
		$result = $this->db->query($sql);
		if (count($result)) {
			$this->id = $result[0]['id'];
			$this->switch_id = $result[0]['switch_id'];
			$this->switch_name = $result[0]['switch_name'];
			$this->jack = $result[0]['jack'];
			$this->room = $result[0]['room'];
			$this->building = $result[0]['building'];
			$this->port = $result[0]['port'];
		
		}
	}

	private function verify_room($room) {
		$valid = 1;
		if (strlen($room) >= 10) {
			$valid = 0;
		}
		elseif (!preg_match('/^[A-Z0-9]+$/',$room)) {
                        $valid = 0;
                }
		return $valid;
	}
	
	private function verify_jack($jack) {
		$valid = 1;
		if (strlen($jack) > 10) {
			$valid = 0;
		}
		elseif (!preg_match('/[A-Z]{2}-[A-Z0-9]{2}-[0-9]{2}/',$jack)) {
                        $valid = 0;
                }

		return $valid;

	}
	private function unique_jack($jack) {
		$valid = 1;
		$sql = "SELECT count(1) as count ";
		$sql .= "FROM locations ";
		$sql .= "WHERE jack='" . $jack . "'";
		$result = $this->db->query($sql);
		if ($result['count']) {
			$valid = 0;
		}
		return $valid;

	}

	private function verify_port($port) {
		$valid = 1;
		if (strlen($port) > 10) {
			$valid = 0;
		}
		elseif (!preg_match('/^[a-zA-Z0-9\/]+$/',$port)) {
                        $valid = 0;
                }

		return $valid;
	
	}
	private function unique_port($port,$switch_id) {
		$valid = 1;
		$sql = "SELECT count(1) as count ";
		$sql .= "FROM locations ";
		$sql .= "WHERE switch_id='" . $switch_id . "' ";
		$sql .= "AND port='" . $port . "'";
		$result = $this->db->query($sql);
		if ($result['count']) {
			$valid = 0;
		}
		return $valid;
	}
}

?>
