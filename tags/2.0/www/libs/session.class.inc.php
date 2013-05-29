<?php
class session {

	////////////////Private Variables//////////
	private $db; //mysql database object
	private $user; //user object
	private $ldap;
	private $password;
	private $username;
	private $group;

	////////////////Public Functions///////////

	public function __construct($db,$ldap,$username,$password,$group) {
		$this->db = $db;
		$this->ldap = $ldap;
		$this->password = $password;
		$this->username = $username;
		$this->group = $group;

	}
	public function __destruct() {
	}

	public function get_username() { return $this->username; }


	public function authenticate() {
		if (!($this->ldap->bind($this->get_username(),$this->get_password()))) {
			return false;
		}
		if (!($this->ldap->is_group_member($this->get_username(),$this->group))) {
			return false;
		}	
		return true;
	}


	//////////////Private Functions/////////////

	private function get_password() {
		return $this->password;
	}


}

?>
