<?php
  $ldaphost='permauth.igb.uiuc.edu';
  $ldaprdn='ou=people, dc=igb, dc=uiuc, dc=edu';
  $redirect="iplookup.php";
?>
<?php if(isset($_POST['submit'])):?>
<?php
  if(!isset($_POST['username']) or !isset($_POST['password'])){
    die("Please enter both the username and password");
  }
  $ldapconn=ldap_connect($ldaphost) or die("Couldnt connect to $ldaphost");
  if($ldapconn){
    $ldapbind=ldap_bind($ldapconn,"uid=".$_POST['username'].", ".$ldaprdn,$_POST['password']);
    if(!$ldapbind){
      die("Incorrect Username or password, please try again");
    }else{
      session_start();
      $_SESSION['username']=$_POST['username'];
      $_SESSION['password']=$_POST['password'];
      print "<head><meta http-equiv='refresh' content='0;URL=$redirect'></head>";
    }
  }
?>
<?php else:?>
<form id='login' name='login' action=<?php echo $_SERVER['SCRIPT_NAME']?> method=post>
<table border=1>
  <tr><td>Username</td><td><input type=text name='username' tabindex='1'></td></tr>
  <tr><td>Password</td><td><input type=password name='password' tabidnex='2'></td></tr>
  <tr><td colspan=2 align=center><input type=submit name=submit value="Log In"></td></tr>
</table>
</form>
<?php endif;?>
