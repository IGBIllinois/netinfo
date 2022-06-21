<?php

class device {

////////////////Private Variables//////////

	private const DATE_FORMAT = "%Y-%m-%d %l:%i:%s %p";
	private const DESCRIPTION_LENGTH = 64;
	private const ROOM_LENGTH = 5;
	private const HOSTNAME_LENGTH = 64;
	private const PROPERTYTAG_LENGTH = 10;
	private const SERIALNUMBER_LENGTH = 50;

        private $db; //mysql database object
	private $log; //log object
	private $aname;
	private $ipnumber;
	private $hardware;
	private $user;
	private $email;
	private $room;
	private $os;
	private $description;
	private $serial_number;
	private $alias = array();
	private $modifiedby;
	private $modified;
	private $property_tag;
	private $vendor;
	private $switch;
	private $port;
	private $domain;
	private $network;
	private $advanced = null;

        ////////////////Public Functions///////////

        public function __construct($db,$log,$ipnumber = 0) {
                $this->db = $db;
		$this->log = $log;
        	if ($ipnumber != 0) {
			$this->get_device($ipnumber);
		}
	}
        public function __destruct() {
	}

	public function get_aname() { return $this->aname; }
	public function get_ipnumber() { return $this->ipnumber; }
	public function get_hardware($uppercase = 0) { 
		if ($uppercase) {
			return strtoupper($this->hardware);
		}
		return $this->hardware; 
	}

	public function get_hardware_cisco($uppercase = 0) {
		$hardware_cisco = "";
		if ($this->hardware != "") {
                        $chunks = str_split($this->hardware,4);
                        $hardware_cisco = implode('.',$chunks);
			if ($uppercase) {
				$hardware_cisco = strtoupper($hardware_cisco);
			}
		}
		return $hardware_cisco;
	}
	public function get_hardware_dashes($uppercase = 0) {
		$hardware_dashes = "";
		if ($this->hardware != "") {
			$chunks = str_split($this->hardware,2);
	                $hardware_dashes = implode('-',$chunks);
			if ($uppercase) {
				$hardware_dashes = strtoupper($hardware_dashes);
			}
		}
		return $hardware_dashes;
	}
	public function get_hardware_colon($uppercase = 0) {
		$hardware_colon = "";
		if ($this->hardware != "") {
                        $chunks = str_split($this->hardware,2);
                        $hardware_colon = implode(':',$chunks);
			if ($uppercase) {
				$hardware_colon = strtoupper($hardware_colon);
			}
		}
		return $hardware_colon;
	}
	public function get_user() { return $this->user; }
	public function get_email() { return $this->email; }
	public function get_room() { return $this->room; }
	public function get_os() { return $this->os; }
	public function get_description() { return $this->description; }
	public function get_serial_number() { return $this->serial_number; }
	public function get_alias() { return $this->alias; }
	public function get_modifiedby() { return $this->modifiedby; }
	public function get_modified() { return $this->modified; }
	public function get_property_tag() { return $this->property_tag; }
	public function get_vendor() { return $this->vendor; }
	public function get_switch() { return $this->switch; }
	public function get_port() { return $this->port; }
	public function get_domain() { return $this->domain; }
	public function get_network() { return $this->network; }

	public function get_url() { 
		if (isset($this->advanced['url'])) {
			return $this->advanced['url'];
		}
		return false;
	
	}
	public function get_username() {
		if (isset($this->advanced['username'])) {
			return $this->advanced['username'];
		}
		return false;
	}
	public function get_password() {
		if (isset($this->advanced['password'])) {
			return $this->advanced['password'];
		}
		return false;
	}
	public function delete($modified_by) {
		$sql = "UPDATE namespace ";
		$sql .= "SET aname='spare',";
		$sql .= "hardware='',";
		$sql .= "name='',";
		$sql .= "email='',";
		$sql .= "room='',";
		$sql .= "os='',";
		$sql .= "description='',";
		$sql .= "serial_number='',";
		$sql .= "alias='',";
		$sql .= "property_tag='', ";
		$sql .= "advanced='{}', ";
		$sql .= "modifiedby='" . $modified_by . "' ";
		$sql .= "WHERE ipnumber='" . $this->get_ipnumber() . "' ";
		$sql .= "LIMIT 1";
		$this->db->non_select_query($sql);
		$this->log->send_log("User " . $modified_by . ": Deleted device " . $this->get_ipnumber() . " - " . $this->get_aname());
		$this->get_device($this->get_ipnumber());
		return array('RESULT'=>true);
	}

	public function get_locations() {
		$sql = "SELECT DATE_FORMAT(macwatch.date,'" . self::DATE_FORMAT . "') as last_seen,macwatch.mac, ";
		$sql .= "SUBSTRING_INDEX(switches.hostname,'.',1) AS switch, ";
		$sql .= "macwatch.port, macwatch.vlans, ";
		$sql .= "a.jack_number AS jack_number, ";
                $sql .= "a.room AS room, ";
                $sql .= "a.building AS building ";
		$sql .= "FROM macwatch ";
		$sql .= "LEFT JOIN switches ON switches.switch_id=macwatch.switch_id ";
		$sql .= "LEFT JOIN (SELECT locations.port,locations.jack_number,locations.room,locations.building,switches.hostname,switches.switch_id FROM locations ";
		$sql .= "LEFT JOIN switches ON switches.switch_id=locations.switch_id) AS a ";
		$sql .= "ON (a.port=macwatch.port AND a.switch_id=macwatch.switch_id) ";
		$sql .= "WHERE LOWER(macwatch.mac)='" . $this->get_hardware() . "' ";
		$sql .= "ORDER BY macwatch.date DESC LIMIT 10";
		return $this->db->query($sql);


	}
	
	public function update($aname,$hardware,$user,$email,$room,$description,$serial_number,$property_tag,$os,$modified_by,$advanced) {
		$message = "";
		$error = 0;
		if (($aname == $this->get_aname()) && ($hardware == $this->get_hardware()) && ($user == $this->get_user()) &&
			($email == $this->get_email()) && ($room == $this->get_room()) && ($description == $this->get_description()) &&
			($serial_number == $this->get_serial_number()) && ($property_tag == $this->get_property_tag()) &&
			($os == $this->get_os()) && ($advanced == $this->advanced)) 
		{
			$error = 1;
			$message .= "<div class='alert alert-primary' role='alert'>No changes were made</div>";	

		}
		else {
			if (!$this->verify_hostname($aname)) {
				$message .= "<div class='alert alert-danger' role='alert'>Invalid hostname. ";
				$message .= "Hostname can contain only lowercase letters, numbers, and hyphens. ";
				$message .= "Maximum length is " . self::HOSTNAME_LENGTH . " characters. It can not contain the word 'spare'.</div>";
				$error = 1;
			}
			elseif (!$this->unique_aname($aname)) {
				$message .= "<div class='alert alert-danger' role='alert'>Hostname " . $aname . " already exists on domain <strong>" . $this->get_domain() . "</strong></div>";
				$error = 1;
		
			}
			if (!$this->verify_hardware($hardware)) {
				$message .= "<div class='alert alert-danger' role='alert'>Invalid Hardware Address. ";
				$message .= "Hardware address can contain only numbers and lowercase letters from a-f. ";
				$message .= "Must contain 12 characters.</div>";	
				$error = 1;
			}
			elseif (!$this->unique_hardware($hardware)) {
				$message .= "<div class='alert alert-danger' role='alert'>Hardware Address ";
				$message .= $hardware . " already exists on network <strong>" . $this->get_network() . "</strong></div";
				$error = 1;
			}
			if (!$this->verify_user($user)) {
				$message .= "<div class='alert alert-danger' role='alert'>Please enter the user's full name</div>";
				$error = 1;
			}
			if (!$this->verify_email($email)) {
				$message .= "<div class='alert alert-danger' role='alert'>Please enter the user's email address</div>";
				$error = 1;
			}
			if (!$this->verify_description($description)) {
				$message .= "<div class='alert alert-danger' role='alert'>Please enter a description.  Maximum length is " . self::DESCRIPTION_LENGTH . " characters</div>";
				$error = 1;
			}
			if (!$this->verify_room($room)) {
				$message .= "<div class='alert alert-danger' role='alert'>Please enter a room number.  Maximum of " . self::ROOM_LENGTH . " characters (ie 2414a)</div>";
				$error = 1;
			}
			if (!$this->verify_propertytag($property_tag)) {
				$message .= "<div class='alert alert-danger' role='alert'>Please enter a valid property tag.  Maxiumum length is " . self::PROPERTYTAG_LENGTH . " characters</div>";
				$error = 1;
			}
			if (!$this->verify_serialnumber($serial_number)) {
				$message .= "<div class='alert alert-danger' role='alert'>Please enter a valid serial number.  Maximum length is " . self::SERIALNUMBER_LENGTH . " charachters</div>";
				$error = 1;

			}
			if (!$this->verify_advanced($advanced)) {
				$message .= "<div class='alert alert-danger' role='alert'>Please enter valid URL</div>";
				$error = 1;
			}
		}
		if ($error == 0) {
			
			$sql = "UPDATE namespace SET ";
			$sql .= "aname=:aname, hardware=:hardware,name=:user,email=:email,";
		        $sql .= "room=:room,os=:os,description=:description,serial_number=:serial_number,";
	                $sql .= "property_tag=:property_tag,modifiedby=:modified_by,advanced=:advanced ";
	        	$sql .= "WHERE ipnumber=:ipnumber LIMIT 1";
			$parameters = array(':aname'=>$aname,
					':hardware'=>$hardware,
					':user'=>$user,
					':email'=>$email,
					':room'=>$room,
					':os'=>$os,
					':description'=>$description,
					':serial_number'=>strtoupper($serial_number),
					':property_tag'=>strtoupper($property_tag),
					':modified_by'=>$modified_by,
					':advanced'=>$advanced,
					':ipnumber'=>$this->get_ipnumber()
					);

			$result = $this->db->non_select_query($sql,$parameters);
			$this->get_device($this->get_ipnumber());
			$message = "<div class='alert alert-success' role='alert'>Device Successfully Updated</div>";
			$this->log->send_log("User " . $modified_by . ": Updated device " . $this->get_ipnumber() . " - " . $aname);
			return array('RESULT'=>$result,'MESSAGE'=>$message);
		}
		else {
			return array('RESULT'=>false,'MESSAGE'=>$message);
		}


	}
	public function add_alias($alias,$modified_by) {
		$message = "";
		$result = true;
		$number_periods = substr_count($alias,".");
		if ($number_periods > 1) {
                        $message = "<div class='alert alert-danger' role='alert'>Invalid Alias Name. ";
                        $message .= "Alias can contain only one subdomain.</div>";
                        $result = false;

		}
		elseif ($number_periods == 1) {
			$hostnames = explode(".",$alias);
			foreach ($hostnames as $host) {
				if (!$this->verify_hostname($host)) {
		                        $message = "<div class='alert alert-danger' role='alert'>Invalid Alias Name. ";
                		        $message .= "Alias can contain only lowercase letters, numbers, and hyphens. here</div>";
		                        $result = false;

				}

			}

		}
		elseif ((!$number_periods) && (!$this->verify_hostname($alias))) {
			$message = "<div class='alert alert-danger' role='alert'>Invalid Alias Name. ";
			$message .= "Alias can contain only lowercase letters, numbers, and hyphens.</div>";
			$result = false;
		}
		
		if (!$this->unique_alias($alias)) {
			$message = "<div class='alert alert-danger' role='alert'>Hostname " . $alias . " already exists on domain <strong>" . $this->get_domain() . "</strong></div>";
			$result = false;

		}
		if ($result) {
			$alias_array = $this->get_alias();
			array_push($alias_array,$alias);
			$alias_string = implode(",",$alias_array);
			$sql = "UPDATE namespace SET alias='" . $alias_string . "', ";
			$sql .= "modifiedby='" . $modified_by . "' ";
			$sql .= "WHERE ipnumber='" . $this->get_ipnumber() . "' LIMIT 1";
			$result = $this->db->non_select_query($sql);
			if ($result) {
				$message = "<div class='alert alert-success' role='alert'>Alias " . $alias . " successfully added</div>";
				 $this->log->send_log("User " . $modified_by . ": Updated Alias for device " . $this->get_ipnumber() . " - Alias " . $alias_string);
				$this->get_device($this->get_ipnumber());
			}
			else {
				$message = "<div class='alert alert-danger' role='alert'>Error adding alias</div>";
			}
			
		}
		return array('RESULT'=>$result,'MESSAGE'=>$message);
	}

	public function delete_alias($alias,$modified_by) {
		$message = "";
		$alias_array = $this->get_alias();
		$alias_index = array_search($alias,$alias_array);
		unset($alias_array[$alias_index]);	
		$alias_string = implode(",",$alias_array);
		$sql = "UPDATE namespace SET alias='" . $alias_string . "', ";
		$sql .= "modifiedby='" . $modified_by . "' ";
		$sql .= "WHERE ipnumber='" . $this->get_ipnumber() . "' LIMIT 1";
		$result = $this->db->non_select_query($sql);
		if ($result) {
			$message = "<div class='alert alert-success' role='alert'>Alias " . $alias . " successfully deleted</div>";
			$this->log->send_log("User " . $modified_by . ": Deleted Alias for device " . $this->get_ipnumber() . " - Alias " . $alias_string);
			$this->get_device($this->get_ipnumber());
		}
		else {
			$message = "<div class='alert alert-danger' role='alert'>Error removing alias</div>";
		}
		return array('RESULT'=>$result,'MESSAGE'=>$message);

	}
	////////////////Private Functions//////////

	private function get_device($ipnumber) {
		$sql = "SELECT namespace.aname, namespace.ipnumber, ";
		$sql .= "LOWER(namespace.hardware) as hardware , namespace.name, ";
		$sql .= "namespace.email, namespace.room, namespace.os, namespace.description, ";
		$sql .= "namespace.serial_number, namespace.alias, namespace.modifiedby, DATE_FORMAT(namespace.modified,'" . self::DATE_FORMAT . "') as modified, ";
		$sql .= "namespace.property_tag,networks.name as network,domains.name as domain, namespace.advanced as advanced, ";
		$sql .= "DATE_FORMAT(a.last_seen,'" . self::DATE_FORMAT . "') as last_seen,a.switch, a.port, a.vendor ";
		$sql .= "FROM namespace ";
		$sql .= "LEFT JOIN networks ON namespace.network_id=networks.id ";
		$sql .= "LEFT JOIN domains ON networks.domain_id=domains.id ";
		$sql .= "LEFT JOIN ( ";
		$sql .= "SELECT macwatch_latest.date as last_seen, macwatch_latest.switch as switch, macwatch_latest.vendor as vendor, ";
		$sql .= "macwatch_latest.port as port,LOWER(macwatch_latest.mac) as mac FROM macwatch_latest) as a ";
		$sql .= "ON a.mac=LOWER(namespace.hardware) ";
		$sql .= "WHERE ipnumber='" . $ipnumber . "' LIMIT 1";
		$result = $this->db->query($sql);
		if (count($result)) {
			$this->aname = $result[0]['aname'];
			$this->ipnumber = $result[0]['ipnumber'];
			$this->hardware = strtolower($result[0]['hardware']);
			$this->user = $result[0]['name'];
			$this->email = $result[0]['email'];
			$this->room = $result[0]['room'];
			$this->os = $result[0]['os'];
			$this->description = $result[0]['description'];
			$this->serial_number = $result[0]['serial_number'];
			if (strlen($result[0]['alias']) > 0) {
				$this->alias = explode(",",$result[0]['alias']);
			}
			else {
				$this->alias = array();
			}
			$this->modifiedby = $result[0]['modifiedby'];
			$this->modified = $result[0]['modified'];
			$this->property_tag = $result[0]['property_tag'];
			$this->vendor = $result[0]['vendor'];
			$this->switch = $result[0]['switch'];
			$this->port = $result[0]['port'];
			$this->domain = $result[0]['domain'];
			$this->network = $result[0]['network'];
			$this->advanced = json_decode($result[0]['advanced'],true);

		}
	}

	private function verify_hostname($hostname) {
		$valid = 1;
		if (!preg_match('/^[a-z0-9-]+$/',$hostname)) {
			$valid = 0;
		}
		elseif (preg_match('/spare/',$hostname)) {
			$valid = 0;
		}
		elseif (strlen($hostname) > $this::HOSTNAME_LENGTH) {
			$valid = 0;
		}
		elseif (strpos($hostname,"spare")) {
			$valid = 0;
		}
		return $valid;
	}
	private function verify_hardware($hardware) {
		$valid = 1;
		if (!preg_match('/^[a-f0-9]{12}$/',$hardware)) {
			$valid = 0;
		}
		return $valid;
	}

	private function verify_email($email) {
		$email = strtolower($email);
		$valid = 1;
		if (strpos($email,"@")) {
			list($prefix,$hostname) = explode("@",$email);
			if (!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/",
				$email)) {
                        	$valid = 0;     
                	}
                	if (($hostname != "") && (!checkdnsrr($hostname,"MX"))) {
                        	$valid = 0;
                	}
		}
		else {
			$valid = 0;
		}
		return $valid;

	}

	private function unique_aname($aname) {
		$sql = "SELECT count(1) as count FROM namespace ";
		$sql .= "LEFT JOIN networks ON namespace.network_id=networks.id ";
                $sql .= "LEFT JOIN domains ON networks.domain_id=domains.id ";	
		$sql .= "WHERE (aname='" . $aname . "' AND ";
		$sql .= "domains.name='" . $this->get_domain() . "' AND ";
		$sql .= "namespace.ipnumber<>'" . $this->get_ipnumber() . "') ";
		$sql .= "OR (FIND_IN_SET('" . $aname . "',namespace.alias) AND ";
		$sql .= "domains.name='" . $this->get_domain() . "') ";
		$result = $this->db->query($sql);
		if ($result[0]['count']) {
			return false;
		}
		return true;
	}

	private function unique_alias($alias) {
                $alias = strtolower($alias);
                $sql = "SELECT count(1) as count FROM namespace ";
		$sql .= "LEFT JOIN networks ON namespace.network_id=networks.id ";
		$sql .= "LEFT JOIN domains ON networks.domain_id=domains.id ";
                $sql .= "WHERE (aname='" . $alias . "' AND ";
		$sql .= "domains.name='" . $this->get_domain() . "') ";
                $sql .= "OR (FIND_IN_SET('" . $alias . "',alias) AND ";
		$sql .= "domains.name='" . $this->get_domain() . "')";
                $result = $this->db->query($sql);
                if ($result[0]['count']) {
                        return false;
                }
                return true;


	}

	private function unique_hardware($hardware) {
		$hardware = strtolower($hardware);
		if ($hardware == "000000000000") {
			return true;
		}
		else {
			$sql = "SELECT count(1) as count FROM namespace ";
			$sql .= "LEFT JOIN networks ON namespace.network_id=networks.id ";
			$sql .= "WHERE hardware='" . $hardware . "' AND ";
			$sql .= "ipnumber<>'" . $this->get_ipnumber() . "' AND ";
			$sql .= "networks.name='" . $this->get_network() . "'";
			$result = $this->db->query($sql);
			if ($result[0]['count']) {
				return false;
			}
			else {
				return true;
			}
		}
	}

	private function verify_user($user) {
		$user = trim(rtrim($user));
		$valid = 1;	
		if ($user == "") {
			$valid = 0;
		}
		return $valid;
	}
	
	private function verify_description($description) {
		$description = trim(rtrim($description));
		$valid = 1;
		if ($description == "") {
			$valid = 0;
		}
		elseif (strlen($description) > self::DESCRIPTION_LENGTH) {
			$valid = 0;
		}	
		return $valid;
	}

	private function verify_room($room) {
		$room = trim(rtrim($room));
		$valid = 1;
		if ($room == "") {
			$valid = 0;
		}
		elseif (strlen($room) > self::ROOM_LENGTH) {
			$valid = 0;
		}
		return $valid;

	}

	private function verify_propertytag($tag) {
		$tag = trim($tag);
		$valid = 1;
		if (($tag != "") && (strlen($tag) > self::PROPERTYTAG_LENGTH)) {
			$valid = 0;
		}
		return $valid;

	}

	private function verify_serialnumber($serial_number) {
		$serial_number = trim($serial_number);
		$valid = 1;
		if (($serial_number != "") && (strlen($serial_number) > self::SERIALNUMBER_LENGTH)) {
			$valid = 0;
		}
		return $valid;


	}

	private function verify_advanced($advanced) {
		$valid = 1;
		$json = json_decode($advanced,true);
		if (json_last_error() != 'JSON_ERROR_NONE') {
			$valid = 0;
		}
		if (($json['url'] != "") && filter_var($json['url'],FILTER_VALIDATE_URL) === FALSE) {
			$valid = 0;
		}
		return $valid;	
	}
}


?>
