<?php

class ldap {

	///////////////Private Variables//////////
	private $ldap_resource = false;
	private $ldap_host;
	private $ldap_base_dn;
	private $ldap_people_ou;
	private $ldap_group_ou;
	private $ldap_bind_user;
	private $ldap_bind_pass;
	private $ldap_ssl = false;
	private $ldap_port = 389;
	private $ldap_protocol = 3;
	////////////////Public Functions///////////

	public function __construct($ldap_host,$ldap_ssl,$ldap_port,$ldap_base_dn) {
		$this->set_ldap_host($ldap_host);
		$this->set_ldap_ssl($ldap_ssl);
		$this->set_ldap_port($ldap_port);
		$this->set_ldap_base_dn($ldap_base_dn);
		$this->set_ldap_people_ou($ldap_base_dn);
		$this->set_ldap_group_ou($ldap_base_dn);
		$this->connect();
	}


	public function __destruct() {
	}

	//get ldap functions
	public function get_ldap_host() {
		return $this->ldap_host;
	}
	public function get_ldap_base_dn() {
		return $this->ldap_base_dn;
	}
	public function get_ldap_people_ou() {
		return $this->ldap_people_ou;
	}
	public function get_ldap_group_ou() {
		return $this->ldap_group_ou;
	}
	public function get_ldap_bind_user() {
		return $this->ldap_bind_user;
	}
	public function get_ldap_ssl() {
		return $this->ldap_ssl;
	}
	public function get_ldap_port() {
		return $this->ldap_port;
	}
	public function get_ldap_protocol() {
		return $this->ldap_protocol;
	}
	public function get_ldap_resource() {
		return $this->ldap_resource;
	}

	//set ldap functions
	public function set_ldap_host($ldap_host) {
		$this->ldap_host = $ldap_host;
	}
	public function set_ldap_base_dn($ldap_base_dn) {
		$this->ldap_base_dn = $ldap_base_dn;
	}
	public function set_ldap_people_ou($ldap_people_ou) {
		$this->ldap_people_ou= $ldap_people_ou;
	}
	public function set_ldap_group_ou($ldap_group_ou) {
		$this->ldap_group_ou= $ldap_group_ou;
	}
	public function set_ldap_bind_user($ldap_bind_user) {
		$this->ldap_bind_user = $ldap_bind_user;
	}
	public function set_ldap_bind_pass($ldap_bind_pass) {
		$this->ldap_bind_pass = $ldap_bind_pass;
	}
	public function set_ldap_ssl($ldap_ssl) {
		$this->ldap_ssl = $ldap_ssl;
	}
	public function set_ldap_port($ldap_port) {
		$this->ldap_port = $ldap_port;
	}
	public function set_ldap_protocol($ldap_protocol) {
		$this->ldap_protocol = $ldap_protocol;
		ldap_set_option($this->get_ldap_resource(),LDAP_OPT_PROTOCOL_VERSION,$ldap_protocol);
	}

	public function connect() {

		$prefix;
		if ($this->get_ldap_ssl() == true) {
			$prefix = "ldaps://";
		}
		elseif ($this->get_ldap_ssl() == false) {
			$prefix = "ldap://";
		}

		$this->ldap_resource = ldap_connect($prefix . $this->get_ldap_host(),$this->get_ldap_port());
		ldap_set_option($this->get_ldap_resource(),LDAP_OPT_REFERRALS,0);
		if ($this->get_ldap_resource() != false) {
			return true;
		}
		else { return false;
		}

	}

	public function bind($username,$password) {
		$bind_dn = "uid=" . $username . "," . $this->get_ldap_people_ou();
		if ($this->get_ldap_resource()) {
			$result = @ldap_bind($this->get_ldap_resource(), $bind_dn, $password);
			return $result;
		}
		else {
			return false;
		}

	}

	public function get_ldap_full_name($username) {
		if ($this->get_ldap_resource()) {
			$username = trim(rtrim($username));
			$filter = "(uid=" . $username . ")";
			$attributes = array("cn");
			$result = $this->search($this->get_ldap_people_ou(),$filter,$attributes);
			return $result[0]['cn'][0];
		}
		else { return false;
		}
	}

	public function is_ldap_user($username) {
		$username = trim(rtrim($username));
		$filter = "(uid=" . $username . ")";
		$attributes = array('');
		$result = $this->search($this->get_ldap_people_ou(),$filter,$attributes);
		if ($result['count']) {
			return true;
		}
		else {
			return false;
		}


	}

	public function get_group_members($group) {
		if ($this->get_ldap_resource()) {
			$group = trim(rtrim($group));
			$filter = "(cn=" . $group . ")";
			$attributes = array('memberUid');
			$result = $this->search($this->get_ldap_group_ou(),$filter,$attributes);
			unset($result[0]['memberuid']['count']);
			$members = array();
			foreach ($result[0]['memberuid'] as $row) {
				array_push($members,$row);
			}
			return $members;
		}
	}
	
	public function is_group_member($username,$group) {
		$group_members = $this->get_group_members($group);
		return in_array($username,$group_members);
	
	}


	/*****************Private Functions*********************/
	private function search($ou,$filter,$attributes) {
		$ldap_result = ldap_search($this->get_ldap_resource(),$ou,$filter,$attributes);

		$result = ldap_get_entries($this->get_ldap_resource(),$ldap_result);
		return $result;

	}

}

?>
