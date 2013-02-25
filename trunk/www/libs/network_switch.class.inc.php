<?php

class network_switch {

////////////////Private Variables//////////

        private $db; //mysql database object
	private $id;
	private $name;
	private $time_created;

        ////////////////Public Functions///////////

        public function __construct($db,$id = 0) {
                $this->db = $db;
		if ($id !== 0) {
			$this->get_switch($id);
		}
        }
        public function __destruct() {
        }

	public function create($name) {
		$name = strtolower($name);
		$error = 0;
		$message = "";
		if (!$this->verify_hostname($name)) {
			$error = 1;
			$message .= "<div class='alert alert-error'>Invalid hostname</div>";

		}
		if (!$this->unique_hostname($name)) {
			$error = 1;
			$message .= "<div class='alert alert-error'>Switch name already exists</div>";

		}

		if ($error == 0) {
			$sql = "INSERT INTO switches(name) ";
			$sql .= "VALUES ('" . $name . "')";
			$result = $this->db->insert_query($sql);
			$message = "<div class='alert alert-success'>Switch successfully added</div>";
			return array('RESULT'=>$result,'MESSAGE'=>$message);
		}
		else {
			return array('RESULT'=>false,'MESSAGE'=>$message);
		}

	}
	
	public function get_id() { return $this->id; }
	public function get_name() { return $this->name; }
	public function get_time_created() { return $this->time_created; }

	/////////////////Private Functions//////////

	private function get_switch($id) {
		$sql = "SELECT * FROM switches ";
		$sql .= "WHERE id='" . $id . "' LIMIT 1";
		$result = $this->db->query($sql);
		if (count($result)) {
			$this->id = $result[0]['id'];
			$this->name = $result[0]['name'];
			$this->time_created = $result[0]['time_created'];
		}

	}

	private function verify_hostname($hostname) {
		$hostname = strtolower($hostname);
                $valid = 1;
                if (!preg_match('/^[a-z0-9-]+$/',$hostname)) {
                        $valid = 0;
                }
                return $valid;
	}

	private function unique_hostname($hostname) {
		$sql = "SELECT count(1) as count ";
		$sql .= "FROM switches ";
		$sql .= "WHERE name='" . $hostname. "'";
		$result = $this->db->query($sql);
		if ($result[0]['count']) {
			return false;
		}
		return true;

	}
}

?>
