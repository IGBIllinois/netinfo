<?php

class location {

	////////////Class Variables/////////////////
	private $db;
	private $id;
	private $switch_id;
	private $switch;
	private $port;
	private $jack_number;
	private $room;
	private $building;
	private $date;

	private const IRIS_SWITCH_COL = 0;
	private const IRIS_PORT_COL = 1;
	private const IRIS_JACK_COL = 7;
	private const IRIS_BUILDING_COL = 8;
	private const IRIS_ROOM_COL = 9;
	private const IRIS_FILETYPE = 'csv';
	private const IRIS_DELIMITER = ",";
	private const IRIS_ENCLOSURE = '"';

	/////////////Public Functions//////////////
	public function __construct($db,$id) {
        	$this->db = $db;

	}
        public function __destruct() {

        }

	public function get_id() {
		return $this->id;
	}
	public function get_switch_id() {
		return $this->switch_id;
	}
	public function get_switch() {
		return $this->switch;
	}
	public function get_port() {
		return $this->port;
	}
	public function get_jack_number() {
		return $this->jack_number;
	}
	public function get_building() {
		return $this->building;
	}
	public function get_room() {
		return $this->room;
	}
	public function get_date_added() {
		return $this->date;
	}


	public static function add_location($db,$switch,$port,$jack_number,$room,$building) {
		if (empty($jack_number) || empty($room) || empty($building)) {
			return false;
		}
		else {

			$sql = "INSERT INTO locations(switch_id,port,jack_number,room,building) ";
			$sql .= "VALUES((SELECT switch_id FROM switches WHERE hostname=:switch),:port,:jack_number,:room,:building)";
			$params = array('switch'=>$switch,
					'port'=>$port,
					'jack_number'=>$jack_number,
					'room'=>$room,
					'building'=>$building
				);
			$result = $db->insert_query($sql,$params);
			if ($result) {
				return true;
			}	

		}
		return false;	

	}

	public static function import_iris($db,$csv) {
		$result = true;
		$i = 0;
		$count = 0;
		if (($file_handle = fopen($csv,'r')) !== FALSE) {
			while (($line = fgetcsv($file_handle,0,self::IRIS_DELIMITER,self::IRIS_ENCLOSURE)) !== FALSE) {
				if ($i) {
					$switch = $line[self::IRIS_SWITCH_COL];
					$port = $line[self::IRIS_PORT_COL];
					$jack_number = $line[self::IRIS_JACK_COL];
					$room = $line[self::IRIS_ROOM_COL];
					$building = $line[self::IRIS_BUILDING_COL];
					$count += self::add_location($db,$switch,$port,$jack_number,$room,$building);
				}
				$i++;
			}
		}
		fclose($file_handle);
		return $count;
	}
	
	public static function get_iris_filetype() {
		return self::IRIS_FILETYPE;
	}
	///////////////Private Functions////////////////
	private function get_location($id) {
		$sql = "SELECT locations.*,switches.hostname as switch FROM locations ";
		$sql .= "LEFT JOIN switches ON switches.switch_id=locations.switch_id ";
		$sql .= "WHERE id='" . $id . "' LIMIT 1";
		$result = $this->db->query($sql);
		if (count($result)) {
			$this->switch_id = $result[0]['switch_id'];
			$this->switch = $result[0]['switch'];
			$this->id = $result[0]['id'];
			$this->port = $result[0]['port'];
			$this->jack_number = $result[0]['jack_number'];
			$this->room = $result[0]['room'];
			$this->building = $result[0]['building'];
			$this->date = $result[0]['date'];


		}		



	}











}

?>
