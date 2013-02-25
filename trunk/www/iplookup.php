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

  if(isset($_GET['submit'])){
    $_POST['submit']=$_GET['submit'];
    $_POST['aname']=$_GET['aname'];
    $_POST['ip']=$_GET['ip'];
  }

  

  $link=mysql_connect('localhost',$mysqluser,$mysqlpass);
 
  if(!$link){
    die('Could not connect: '.mysql_error());
   }

  $db_selected = mysql_select_db($database, $link);
  if (!$db_selected) {
    die ('Can\'t use $database : ' . mysql_error());
  }

  if(isset($_POST['search'])){
    $_POST['submit']=1;
    $_POST['hardware']=$_POST['search'];
  }
?>
<?php if(isset($_POST['change'])):?>
<?php
  $query="select modified from $table where ipnumber like '".$_POST['ipnumber']."'";
  $result=mysql_query($query) or die("cant do query $query");
  if (mysql_num_rows($result) == 0) {
    echo "No match to you query";
    exit;
  }
  $row=mysql_fetch_assoc($result);
  if($_POST['modified']!=$row['modified']){
    print $_POST['modified'].'-'.$row['modified']."<br>\n";
    die("record appears to have changed while you were editing, please reload record and try again");
  }
  $query="update $table set aname='".$_POST['aname']."',hardware='".$_POST['hardware']."',name='".$_POST['name']."',email='".$_POST['email']."',room='".$_POST['room']."',os='".$_POST['os']."',description='".$_POST['description']."',backpass='".$_POST['backpass']."',alias='".$_POST['alias']."',property_tag='" . $_POST['property_tag'] . "',modifiedby='".$_SESSION['username']."'  where ipnumber like '".$_POST['ipnumber']."'";
  
  $result=mysql_query($query) or die("cant do query $query");
  if (mysql_affected_rows() == 0) {
    echo "Error: record did not edit or no changes were made";
    exit;
  }
  $query="select * from $table where ipnumber like '".$_POST['ipnumber']."'";
  $result=mysql_query($query) or die("cant do query $query");
  if (mysql_num_rows($result) == 0) {
    echo "No match to you query";
    exit;
  }
  $row=mysql_fetch_assoc($result);
  print "<table border=1>\n\t
	<tr><td>Aname<td>".$row['aname']."</td></tr>\n\t
	<tr><td>IP</td><td>".$row['ipnumber']."</td></tr>\n\t
	<tr><td>Hardware</td><td>".$row['hardware']."</td></tr>\n\t
	<tr><td>Name</td><td>".$row['name']."</td></tr>\n\t
	<tr><td>E-Mail</td><td>".$row['email']."</td></tr>\n\t
	<tr><td>Room</td><td>".$row['room']."</td></tr>\n\t
	<tr><td>OS</td><td>".$row['os']."</td></tr>\n\t
	<tr><td>Description</td><td>".$row['description']."</td></tr>\n\t
	<tr><td>Backup Password</td><td>".$row['backpass']."</td></tr>\n\t
	<tr><td>Aliases</td><td>".$row['alias']."</td></tr>\n
	<tr><td>Property Tag</td><td>" . $row['property_tag'] . "</td></tr>\n\t
	</table><br>\n";
?>
<?php elseif(isset($_POST['modify'])):?>
<?php
  $query="select * from $table where ipnumber like '".$_POST['ipnumber']."'";
  $result=mysql_query($query) or die("cant do query $query");
  $row=mysql_fetch_assoc($result);
  print "<form action='".$_SERVER['SCRIPT_NAME']."' method='post'>
	<input type=hidden name=modified value='".$row['modified']."'>
	<table border=1>\n\t
	<tr><td>Aname<td><input type='text' name='aname' value='".$row['aname']."'></td></tr>\n\t
	<tr><td>IP</td><td><input type='hidden' name='ipnumber' value='".$row['ipnumber']."'>".$row['ipnumber']."</td></tr>\n\t
	<tr><td>Hardware</td><td><input type='text' name='hardware' value='".$row['hardware']."'></td></tr>\n\t
	<tr><td>Name</td><td><input type='text' name='name' value='".$row['name']."'></td></tr>\n\t
	<tr><td>E-Mail</td><td><input type='text' name='email' value='".$row['email']."'></td></tr>\n\t
	<tr><td>Room</td><td><input type='text' name='room' value='".$row['room']."'></td></tr>\n\t
	<tr><td>OS</td><td>".optionselect($row['os'])."</td></tr>\n\t
	<tr><td>Description</td><td><input type='text' name='description' value='".$row['description']."'></td></tr>\n\t
	<tr><td>Backup Password</td><td><input type='text' name='backpass' value='".$row['backpass']."'></td></tr>\n\t
	<tr><td>Aliases</td><td><input type='text' name='alias' value='".$row['alias']."'></td></tr>\n\t
	<tr><td>Property Tag</td><td><input type='text' name='property_tag' value='" . $row['property_tag'] . "'></td></tr>\n\t
	<tr><td align='center' colspan='2'><input type='submit' name='change' value='Commit Changes'></td></tr>\n
	</table>
	</form><br>\n";
?>
<?php elseif(isset($_POST['blank'])):?>
<?php
  $query="update $table set aname='spare',hardware='',name='',email='',room='',os='',description='',backpass='',alias='',property_tag='',modifiedby='".$_SESSION['username']."'  where ipnumber like '".$_POST['ipnumber']."'";
  $result=mysql_query($query) or die("cant do query $query");
  if (mysql_affected_rows() == 0) {
    echo "Error: record did not blank or was already blank";
    exit;
  }else{
    print "Record successfully erased<br>\n";
    $query="select * from $table where ipnumber like '".$_POST['ipnumber']."'";
    $result=mysql_query($query) or die("cant do query $query");
    $row=mysql_fetch_assoc($result);
    print "<table border=1>\n\t
	<tr><td>Aname<td>".$row['aname']."</td></tr>\n\t
	<tr><td>IP</td><td>".$row['ipnumber']."</td></tr>\n\t
	<tr><td>Hardware</td><td>".$row['hardware']."</td></tr>\n\t
	<tr><td>Name</td><td>".$row['name']."</td></tr>\n\t
	<tr><td>E-Mail</td><td>".$row['email']."</td></tr>\n\t
	<tr><td>Room</td><td>".$row['room']."</td></tr>\n\t
	<tr><td>OS</td><td>".$row['os']."</td></tr>\n\t
	<tr><td>Description</td><td>".$row['description']."</td></tr>\n\t
	<tr><td>Backup Password</td><td>".$row['backpass']."</td></tr>\n\t
	<tr><td>Aliases</td><td>".$row['alias']."</td></tr>\n\t
	<tr><td>Property Tag</td><td>" . $row['property_tag'] . "</td></tr>\n\t
	</table>
	</form><br>\n";
  }
?>
<?php elseif(isset($_POST['lastseen'])):?>
<?php
  $query="select * from $mactable where mac like '".$_POST['hardware']."' order by date";
  $result = mysql_query($query);
  if (!$result) {
    die('Invalid query: ' . mysql_error());
  }

  if (mysql_num_rows($result) == 0) {
    echo "No match to you query";
    exit;
  }
  print "<table border=1>\n";
  print "\t<tr><td colspan=4 align=center>Known Locations for ".$_POST['hardware']."</td></tr>\n";
  print "\t<tr><td>Switch</td><td>Port</td><td>Date</td><td>Vendor</td></tr>\n";
  while ($row = mysql_fetch_assoc($result)) {
    print "<tr><td>".$row['switch']."</td><td>".$row['port']."</td><td>".$row['date']."</td><td>".$row['vendor']."</td></tr>\n";
  }
  print "</table>\n";
?>
<?php elseif(isset($_POST['submit'])):?>
<?php
#print "searching<br>\n";
  if(isset($_POST['aname']) and $_POST['aname']!=''){
    $query.="aname like '".$_POST['aname']."'";
  }
  if(isset($_POST['ip']) and $_POST['ip']!=''){
    if(isset($query)){
      $query.=" and ipnumber like '".$_POST['ip']."'";
    }else{
      $query.="ipnumber like '".$_POST['ip']."'";
    }
  }
  if(isset($_POST['hardware']) and $_POST['hardware']!=''){
    if(isset($query)){
      $query.=" and hardware like '".$_POST['hardware']."'";
    }else{
      $query.="hardware like '".$_POST['hardware']."'";
    }
  }
  if(isset($_POST['name']) and $_POST['name']!=''){
    if(isset($query)){
      $query.=" and name like '".$_POST['name']."'";
    }else{
      $query.="name like '".$_POST['name']."'";
    }
  }
  if(isset($_POST['email']) and $_POST['email']!=''){
    if(isset($query)){
      $query.=" and email like '".$_POST['email']."'";
    }else{
      $query.="email like '".$_POST['email']."'";
    }
  }
  if(isset($_POST['room']) and $_POST['room']!=''){
    if(isset($query)){
      $query.=" and room like '".$_POST['room']."'";
    }else{
      $query.="room like '".$_POST['room']."'";
    }
  }
  if(isset($_POST['os']) and $_POST['os']!='Ignore'){
    if(isset($query)){
      $query.=" and os like '".$_POST['os']."'";
    }else{
      $query.="os like '".$_POST['os']."'";
    }
  }
  if(isset($_POST['description']) and $_POST['description']!=''){
    if(isset($query)){
      $query.=" and description like '".$_POST['description']."'";
    }else{
      $query.="description like '".$_POST['description']."'";
    }
  }
  if(isset($_POST['property_tag']) and $_POST['property_tag']!=''){
    if(isset($query)){
      $query.=" and property_tag like '".$_POST['property_tag']."'";
    }else{
      $query.="property_tag like '".$_POST['property_tag']."'";
    }
  }

  $query="select * from $table where $query order by ipnumber desc";
#print "$query<br>\n";
  $result = mysql_query($query);
  if (!$result) {
    die('Invalid query: ' . mysql_error());
  }

  if (mysql_num_rows($result) == 0) {
    echo "No match to you query";
    exit;
  }
  while ($row = mysql_fetch_assoc($result)) {
    if(isset($row['hardware']) and $row['hardware']!=''){
      $query="select * from $mactable where mac like '".$row['hardware']."'order by date desc limit 1";
      #print "$query<br>\n";
      $subresult = mysql_query($query);
      if (!$subresult) {
        die('Invalid query in secondary table: ' . mysql_error());
      }

      if (mysql_num_rows($subresult) == 0) {
        $lastseen="Never";
      }else{
        $lastseen = mysql_fetch_assoc($subresult);
        $lastseen="<input type=submit name=lastseen value='".$lastseen['date']."'><br>".$lastseen['date'];;
      }
    }else{
      $lastseen="NA";
    }
    print "<form action=".$_SERVER['SCRIPT_NAME']." method=post>
	<table border=1>\n\t
	<tr><td>Aname<td>".$row['aname']."</td></tr>\n\t
	<tr><td>IP</td><td>".$row['ipnumber']."</td></tr>\n\t
	<tr><td>Hardware</td><td>".$row['hardware']."</td></tr>\n\t
	<tr><td>Name</td><td>".$row['name']."</td></tr>\n\t
	<tr><td>E-Mail</td><td>".$row['email']."</td></tr>\n\t
	<tr><td>Room</td><td>".$row['room']."</td></tr>\n\t
	<tr><td>OS</td><td>".$row['os']."</td></tr>\n\t
	<tr><td>Description</td><td>".$row['description']."</td></tr>\n\t
	<tr><td>Backup Password</td><td>".$row['backpass']."</td></tr>\n\t
	<tr><td>Aliases</td><td>".$row['alias']."</td></tr>\n\t
	<tr><td>Property Tag</td><td>" . $row['property_tag'] . "</td></tr>\n\t
	<tr><td>Last Seen</td><td><input type=hidden name=hardware value='".$row['hardware']."'>$lastseen</td></tr>\n\t
	<tr><td><input type=hidden name=ipnumber value='".$row['ipnumber']."'>
	<input type=submit name=blank value='Blank Record'></td><td><input type=submit name=modify value='Modify Record'></td></tr>\n
	</table></form><br>\n";
  }
?>
<?php else:?>
<form action=<?php echo $_SERVER['SCRIPT_NAME']?> method=post>
<table border=1>
  <tr>
    <td>AName</td>
    <td><input type=text name=aname></td>
  </tr>
  <tr>
    <td>IP</td>
    <td><input type=text name=ip></td>
  </tr>
  <tr>
    <td>Hardware</td>
    <td><input type=text name=hardware></td>
  </tr>
  <tr>
    <td>Users Name</td>
    <td><input type=text name=name></td>
  </tr>
  <tr>
    <td>E-Mail</td>
    <td><input type=text name=email></td>
  </tr>
  <tr>
    <td>Room</td>
    <td><input type=text name=room></td>
  </tr>
  <tr>
    <td>Operating System</td>
    <td><?php echo optionselect('Ignore')?></td>
  </tr>
  <tr>
    <td>Description</td>
    <td><input type=text name=description></td>
  </tr>
  <tr>
	<td>Property Tag</td>
	<td><input type='text' name='property_tag'></td>
  </tr>
  <tr>
    <td colspan=2 align=center><input type=submit name=submit value="Search for IPs"></td>
  </tr>
</table>
<?php endif?>

<?php
function optionselect($default){
  $query="select os from operating_systems ORDER BY os ASC";
  $result = mysql_query($query);
  if (!$result) {
    die('Invalid query: ' . mysql_error());
  }

  if (mysql_num_rows($result) == 0) {
    echo "No match to you query";
    exit;
  }
  $os=array();
  while($row=mysql_fetch_assoc($result)){
    array_push($os, $row['os']);
  }
  $returnval="<select name=os>\n";
  if(!in_array($default, $os)){
    $returnval.="\t<option selected>$default</option>\n";
  }
  foreach($os as $item){
    if($item == $default){
      $returnval.="\t<option selected>$item</option>\n";
    }else{
      $returnval.="\t<option>$item</option>\n";
    }
  }
  $returnval.="</select>";
  return $returnval;
}
?>
