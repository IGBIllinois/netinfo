<?php


function authenticate($ldap,$username,$password,$group) {
	$result = false;
	$rdn = get_user_rdn($ldap,$username);
	if (($ldap->bind($rdn,$password)) && ($ldap->is_group_member($username,$group))) {
		$result = true;
	}
	return $result;
}

function get_user_rdn($ldap,$username) {
                $filter = "(uid=" . $username . ")";
                $attributes = array('dn');
                $result = $ldap->search($filter,'',$attributes);
                if (isset($result[0]['dn'])) {
                        return $result[0]['dn'];
                }
                else {
                        return false;
                }
        }




?>
