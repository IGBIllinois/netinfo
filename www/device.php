<?php
include_once 'includes/main.inc.php';
include_once 'includes/header.inc.php';
include_once 'functions.inc.php';

if (isset($_POST['ipnumber'])) {
        foreach ($_POST as $var) {
                $var = trim(rtrim($var));
        }

	$ipnumber = $_POST['ipnumber'];
	$device = new device($db,$ipnumber);
        $aname = $_POST['aname'];
        $hardware = $_POST['hardware'];
        $user = $_POST['user'];
        $email = $_POST['email'];
        $room = $_POST['room'];
        $description = $_POST['description'];
        $retropass = $_POST['retrospect'];
        $property_tag = $_POST['property_tag'];
        $device_os = $_POST['os'];
	$domain = $_POST['domain'];
}

if (isset($_POST['delete'])) {
        $result = $device->delete($_SESSION['username']);
	if ($result['RESULT']) {
		unset($_POST);
	}
}
elseif (isset($_POST['cancel'])) {
        unset($_POST);
        $result['MESSAGE'] = "<div class='alert'>Device update was canceled.</div>";
}
elseif (isset($_POST['add_alias'])) {
        $result = $device->add_alias($_POST['new_alias'],$_SESSION['username']);
        if ($result['RESULT']) {
                unset($_POST);
        }
}
elseif (isset($_POST['delete_alias'])) {
        $result = $device->delete_alias($_POST['alias'],$_SESSION['username']);
        if ($result['RESULT']) {
                unset($_POST);
        }

}
elseif (isset($_POST['update'])) {
        foreach ($_POST as $var) {
                $var = trim(rtrim($var));
        }
        $result = $device->update($aname,$hardware,$user,
                        $email,$room,$description,
                        $retropass,$property_tag,$device_os,$_SESSION['username']);
	if ($result['RESULT']) {
	        unset($_POST);
	}
}

if (isset($_GET['ipnumber']) && !isset($_POST['ipnumber'])) {
	$ipnumber = $_GET['ipnumber'];
	$device = new device($db,$ipnumber);
	$aname = $device->get_aname();
        $hardware = $device->get_hardware();
        $user = $device->get_user();
        $email = $device->get_email();
        $room = $device->get_room();
        $description = $device->get_description();
        $retropass = $device->get_retrospect();
        $property_tag = $device->get_property_tag();
        $domain = $device->get_domain();
        $device_os = $device->get_os();

}
else {
	end;
}



	
$locations = $device->get_locations();
$locations_html = "";
foreach ($locations as $location) {
	$locations_html .= "<tr>";
	$locations_html .= "<td>" . $location['date'] . "</td>";
	$locations_html .= "<td>" . $location['switch'] . "</td>";
	$locations_html .= "<td>" . $location['port'] . "</td>";
	$locations_html .= "</tr>";
}

$aliases = $device->get_alias();
$alias_html = "";
if (count($aliases)) {
	foreach ($aliases as $alias) {
		$alias_html .= "<tr>";
		$alias_html .= "<td>" . $alias . "</td>";
		$alias_html .= "<td>";
		$alias_html .= "<form method='post' action='" . $_SERVER['PHP_SELF'] . "?ipnumber=" . $device->get_ipnumber() . "'>";
		$alias_html .= "<input type='hidden' name='alias' value='" . $alias . "'>";		
		$alias_html .= "<button class='btn btn-danger btn-mini' name='delete_alias' ";
		$alias_html .= "onClick='return confirm_remove_alias()'><i class='icon-remove'></i></button>";
		$alias_html .= "</form></td>";
		$alias_html .= "</tr>";
	
	}
}
else {
	$alias_html = "<tr><td colspan='2'>None</td></tr>";
}
$os = get_operating_systems($db);
$os_html = "<select name='os'>";
$os_exist = false;
foreach ($os as $var) {
	if ($device_os == $var['os']) {
		$os_html .= "<option selected='selected' value='" . $var['os'] . "'>" . $var['os'] . "</option>";
		$os_exist = true;
	}
	else {
		$os_html .= "<option value='" . $var['os'] . "'>" . $var['os'] . "</option>";
	}

}
if (($os_exist == false) && ($device->get_os() != "")) {
	$os_html .= "<option selected='selected' value='" . $device->get_os() . "'>" . $device->get_os() . "</option>";
}
$os_html .= "</select>";
?>
<form method='post' action='<?php echo $_SERVER['PHP_SELF'] . "?ipnumber=" . $device->get_ipnumber(); ?>'>
<input type='hidden' name='ipnumber' value='<?php echo $device->get_ipnumber(); ?>'>
<div class='span5'>
<h4>Device Information</h4>
<table class='table table-condensed table-striped table-bordered'>
<tr><td>IP Address</td><td><?php echo $device->get_ipnumber(); ?></td></tr>
<tr><td>Name (ANAME)</td><td><input class='input' type='text' name='aname' maxlength='20' value='<?php echo $aname; ?>'></td></tr>
<tr><td>Domain</td><td><input class='input' type='text' readonly='readonly' name='domain' value='<?php echo $domain; ?>'></td></tr>
<tr><td>Hardware (MAC) Address</td><td><input type='text' name='hardware' maxlength='12' value='<?php echo $hardware; ?>'></td></tr>
<tr><td>User</td><td><input type='text' name='user' value='<?php echo $user; ?>'></td></tr>
<tr><td>Email</td><td><input type='text' name='email' value='<?php echo $email; ?>'></td></tr>
<tr><td>Room</td><td><input type='text' name='room' value='<?php echo $room; ?>'></td></tr>
<tr><td>Device Type/OS</td><td><?php echo $os_html; ?></td></tr>
<tr><td>Description</td><td><input type='text' name='description' value='<?php echo $description; ?>'></td></tr>
<tr><td>Retrospect Password</td><td><input type='text' name='retropass' value='<?php echo $retropass; ?>'></td></tr>
<tr><td>Property Tag</td><td><input type='text' name='property_tag' value='<?php echo $property_tag; ?>'></td></tr>
<tr><td>Last Modified By</td><td><?php echo $device->get_modifiedby(); ?></td></tr>
<tr><td>Last Modified</td><td><?php echo $device->get_modified(); ?></td></tr>
<tr><td>Network Card Vendor</td><td><?php echo $device->get_vendor(); ?></td></tr>
</table>
</div>
<div class='span6'>
<h4>Location</h4>
<table class='table table-condensed table-striped table-bordered'>
	<thead>
		<th>Last Seen</th>
		<th>Switch</th>
		<th>Port</th>
	</thead>
	<?php echo $locations_html; ?>
</table>
</div>
<div class='span6'>
<h4>Aliases</h4>
<table class='table table-condensed table-striped table-bordered'>
	<thead>
		<th colspan='2'>Alias (CNAME)</th>
	</thead>
<?php 
	echo $alias_html;
	if ($device->get_aname() != "spare") { 
		echo "<tr><td><input class='input' type='text' name='new_alias' value='";
		if (isset($_POST['new_alias'])) { echo $_POST['new_alias']; }
		echo "'></td>";
		echo "<td><input class='btn btn-primary' type='submit' name='add_alias' value='Add' onClick='return confirm_alias()'></td></tr>";
	}
	?>
</table>

</div>
<div class='span8'>
<input class='btn btn-primary' type='submit' value='Update' name='update' onClick='return confirm_update()'>
<input class='btn btn-warning' type='submit' value='Cancel' name='cancel'>
<?php if ($device->get_aname() != 'spare') {
echo "<input class='btn btn-danger' type='submit' value='Delete' name='delete' onClick='return confirm_delete()'>";
}
?>
</div>
</form>
<br>
<div class='span10'>
<?php
if (isset($result['MESSAGE'])) {
	echo $result['MESSAGE'];
}
?>
</div>
<?php

include_once 'includes/footer.inc.php';
?>
