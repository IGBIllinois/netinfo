<?php
  $mysqluser='root';
  $mysqlpass='igb123';
  $database='macwatch';
  $table='namespace';
  $mactable='macwatch';
  $ldaphost='permauth.igb.uiuc.edu';
  $ldaprdn='ou=people, dc=igb, dc=uiuc, dc=edu';
  $loginpage='login.php';


  session_start();
  if(!isset($_SESSION['username']) or !isset($_SESSION['password'])){
      print "<head><meta http-equiv='refresh' content='0;URL=$loginpage'></head>";
      exit;
  }

  $ldapconn=ldap_connect($ldaphost) or die("Couldnt connect to $ldaphost");
  if($ldapconn){
    $ldapbind=ldap_bind($ldapconn,"uid=".$_SESSION['username'].", ".$ldaprdn,$_SESSION['password']);
    if(!$ldapbind){
      print "<head><meta http-equiv='refresh' content='0;URL=$loginpage'></head>";
      exit;
    }
  }
if(isset($_POST['newrecord'])) {
	echo "check";
	echo $_POST['newrecord'];
}

if(isset($_POST['searchbackup'])) {
	$username=$_POST['username'];
	$ipaddr=$_POST['ipaddress'];
	$retropass=$_POST['backuppass'];
	
	
	

}

print "<font color=\"red\">*Under Construction</font>";
print "<FORM action=".$_SERVER['SCRIPT_NAME']." method=post>";
print "<table border=1>\n\t<tr><td>Users Name</td><td><input type=\"text\" name=\"username\"></td></tr>";
print "<tr><td>IP Address</td><td><input type=\"text\" name=\"ipaddress\"></td></tr>";
print "<tr><td>Retrospect Pass.</td><td><input type=\"text\" name=\"backuppass\"></td></tr>"; 
print "<tr><td><input type=\"submit\" name=\"newrecord\" value=\"Create New\"></td><td><input type=\"submit\" name=\"searchbackup\" value=\"Search Records\"><td></td></tr></table></FORM>";


?>

