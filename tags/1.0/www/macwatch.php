
<?php
  $mysqluser='root';
  $mysqlpass='igb123';
  $database='macwatch';
  $iptable='namespace';
  $ldaphost='permauth.igb.uiuc.edu';
  $ldaprdn='ou=people, dc=igb, dc=uiuc, dc=edu';
  $loginpage='login.php';
  $ippage='iplookup.php';


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

  $link=mysql_connect('localhost',$mysqluser,$mysqlpass);
 
  if(!$link){
    die('Could not connect: '.mysql_error());
   }

  $db_selected = mysql_select_db($database, $link);
  if (!$db_selected) {
    die ('Can\'t use $database : ' . mysql_error());
  }
?>

<?php if(isset($_POST['submit'])):?>

<?php

?>

<?php
  if(isset($_POST['mac']) and $_POST['mac']!=''){
    $query.="mac like '".$_POST['mac']."'";
  }
  if(isset($_POST['switch']) and $_POST['switch']!=''){
    if(isset($query)){
      $query.=" and switch like '".$_POST['switch']."'";
    }else{
      $query.="switch like '".$_POST['switch']."'";
    }
  }
  if(isset($_POST['port']) and $_POST['port']!=''){
    if(isset($query)){
      $query.=" and port like '".$_POST['port']."'";
    }else{
      $query.="port='".$_POST['port']."'";
    }
  }
  if(isset($_POST['date']) and $_POST['date']!=''){
    if(isset($query)){
      $query.=" and date".$_POST['date'];
    }else{
      $query.="date".$_POST['date'];
    }
  }
  $query="select * from $database where ".$query." order by ".$_POST['sortby'];
  print $query."<br>\n";
  $result = mysql_query($query);
  if (!$result) {
    die('Invalid query: ' . mysql_error());
  }

  if (mysql_num_rows($result) == 0) {
    echo "No match to you query";
    exit;
  }
  print "<table border=1>\n<form action=$ippage method=post>\n";
  while ($row = mysql_fetch_assoc($result)) {
    $query="select ipnumber from $iptable where hardware like '".$row['hardware']."'";
    $subresult=mysql_query($query);
    if(mysql_numrows($subresult)){
      print "<tr><td>".$row['switch']."</td><td><input type=submit name=search value=".$row['mac']."></td><td>".$row['port']."</td><td>".$row['date']."</td></tr>\n";
    }else{
      print "<tr><td>".$row['switch']."</td><td>".$row['mac']."</td><td>".$row['port']."</td><td>".$row['date']."</td></tr>\n";
    }
  }
  print "</form>\n</table>\n";
?>

<?php else:?>

<form action=<?php echo $_SERVER['SCRIPT_NAME']?> method=post>
<table border=1>
<tr>
  <td align=center colspan=2>Enter Search Criteria</td>
</tr>
<tr>
  <td>Mac Address</td>
  <td><input type=text name=mac maxlength=12></td>
</tr>
<tr>
  <td>Switch</td>
  <td><input type=text name=switch></td>
</tr>
<tr>
  <td>Port</td>
  <td><input type=text name=port></td>
</tr>
<tr>
  <td>Date</td>
  <td><input type=text name=date></td>
</tr>
<tr>
  <td>Sort By</td>
  <td><select name=sortby>
     <option value="switch desc, port desc">Switch-Port</option>
     <option value="date asc, mac desc">Date-Mac</option>
  </select></td>
</tr>
<tr>
  <td align=center colspan=2><input type=submit name=submit value=Search></td>
</tr>
</table>
</form>
<?php endif?>