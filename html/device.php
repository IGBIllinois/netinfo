<?php
require_once 'includes/main.inc.php';
require_once 'includes/session.inc.php';
require_once 'includes/header.inc.php';

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
        $serial_number = $_POST['serial_number'];
        $property_tag = $_POST['property_tag'];
        $device_os = $_POST['os'];
}

if (isset($_POST['delete'])) {
        $result = $device->delete($session->get_var('username'));
	if ($result['RESULT']) {
		$result['MESSAGE'] = "<div class='alert alert-danger'>Device Reservation was deleted.</div>";
		unset($_POST);
	}
}
elseif (isset($_POST['cancel'])) {
        unset($_POST);
        $result['MESSAGE'] = "<div class='alert alert-warning'>Device update was canceled.</div>";
}
elseif (isset($_POST['add_alias'])) {
        $result = $device->add_alias($_POST['new_alias'],$session->get_var('username'));
        if ($result['RESULT']) {
                unset($_POST);
        }
}
elseif (isset($_POST['delete_alias'])) {
        $result = $device->delete_alias($_POST['alias'],$session->get_var('username'));
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
                        $serial_number,$property_tag,$device_os,$session->get_var('username'));
	if ($result['RESULT']) {
	        unset($_POST);
	}
}

if (isset($_GET['ipnumber']) && !(isset($_POST['ipnumber']))) {
	$ipnumber = $_GET['ipnumber'];
	$device = new device($db,$ipnumber);
	$aname = $device->get_aname();
        $hardware = $device->get_hardware();
        $user = $device->get_user();
        $email = $device->get_email();
        $room = $device->get_room();
        $description = $device->get_description();
        $serial_number = $device->get_serial_number();
        $property_tag = $device->get_property_tag();
        $domain = $device->get_domain();
        $device_os = $device->get_os();

}


$locations = $device->get_locations();
$locations_html = "";
foreach ($locations as $location) {
	$locations_html .= "<tr>";
	$locations_html .= "<td>" . $location['date'] . "</td>";
	$locations_html .= "<td>" . $location['switch'] . "</td>";
	$locations_html .= "<td>" . $location['port'] . "</td>";
	$locations_html .= "<td>" . $location['jack_number'] . "</td>";
	$locations_html .= "<td>" . $location['room'] . "</td>";
	$locations_html .= "<td>" . $location['vlans'] . "</td>";
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
		$alias_html .= "<input type='hidden' name='ipnumber' value='" . $device->get_ipnumber() . "'>";
		$alias_html .= "<button class='btn btn-danger btn-mini' name='delete_alias' ";
		$alias_html .= "onClick='return confirm_remove_alias()'><i class='fas fa-trash'></i></button>";
		$alias_html .= "</form></td>";
		$alias_html .= "</tr>";
	
	}
}
else {
	$alias_html = "<tr><td colspan='2'>None</td></tr>";
}
$os = functions::get_operating_systems($db);
$os_html = "<select class='form-control custom-select' name='os'>";
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
<div class='row'>
<div class='col-md-6 col-lg-6 col-xl-6'>
	<h4>Device Information</h4>
	<table class='table table-bordered table-sm table-striped '>
	<tr><td>IP Address</td><td><?php echo $device->get_ipnumber(); ?></td></tr>
	<tr><td>Name (ANAME)</td><td><input class='form-control' type='text' name='aname' maxlength='20' value='<?php echo $aname; ?>'></td></tr>
	<tr><td>Domain</td><td><?php echo $device->get_domain(); ?></td></tr>
	<tr><td>Hardware (MAC) Address</td><td><input class='form-control' type='text' name='hardware' maxlength='12' value='<?php echo $hardware; ?>'></td></tr>
	<tr><td>User</td><td><input class='form-control' type='text' name='user' value='<?php echo $user; ?>'></td></tr>
	<tr><td>Email</td><td><input class='form-control' type='text' name='email' value='<?php echo $email; ?>'></td></tr>
	<tr><td>Room</td><td><input class='form-control' type='text' name='room' value='<?php echo $room; ?>'></td></tr>
	<tr><td>Device Type/OS</td><td><?php echo $os_html; ?></td></tr>
	<tr><td>Description</td><td><input class='form-control' type='text' name='description' value='<?php echo $description; ?>'></td></tr>
	<tr><td>Serial Number</td><td><input class='form-control' type='text' name='serial_number' value='<?php echo $serial_number; ?>'></td></tr>
	<tr><td>Property Tag</td><td><input class='form-control' type='text' name='property_tag' value='<?php echo $property_tag; ?>'></td></tr>
	<tr><td>Last Modified By</td><td><?php echo $device->get_modifiedby(); ?></td></tr>
	<tr><td>Last Modified</td><td><?php echo $device->get_modified(); ?></td></tr>
	<tr><td>Network Card Vendor</td><td><?php echo $device->get_vendor(); ?></td></tr>
	</table>
</div>

<div class='col-md-6 col-lg-6 col-xl-6'>
	<h4>Location</h4>
	<table class='table table-bordered table-sm table-striped '>
		<thead>
			<th>Last Seen</th>
			<th>Switch</th>
			<th>Port</th>
			<th>Jack</th>
			<th>Room</th>
			<th>VLANS</th>
		</thead>
		<?php echo $locations_html; ?>
	</table>
</div>

<div class='col-md-6 col-lg-6 col-xl-6'>
	<h4>Hardware (MAC) Address Formats</h4>
	<table class='table table-bordered table-sm'>
		<tr>
			<td><?php echo $device->get_hardware(); ?>&nbsp;</td>
			<td><?php echo $device->get_hardware(true); ?>&nbsp;</td>
		</tr>
		<tr>
			<td><?php echo $device->get_hardware_cisco(); ?>&nbsp;</td>
			<td><?php echo $device->get_hardware_cisco(true); ?>&nbsp;</td>
		</tr>
		<tr>
			<td><?php echo $device->get_hardware_dashes(); ?>&nbsp;</td>
			<td><?php echo $device->get_hardware_dashes(true); ?>&nbsp;</td>
		</tr>
		<tr>
			<td><?php echo $device->get_hardware_colon(); ?>&nbsp;</td>
			<td><?php echo $device->get_hardware_colon(true); ?>&nbsp;</td>
		</tr>
	</table>

</div>
<div class='col-md-6 col-lg-6 col-xl-6'>
	<h4>Aliases</h4>
	<table class='table table-bordered table-sm table-striped '>
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
</div>
<div class='col-md-12 col-lg-12 col-xl-12'>
<input class='btn btn-primary' type='submit' value='Update' name='update' onClick='return confirm_update()'>
<input class='btn btn-warning' type='submit' value='Cancel' name='cancel'>
<?php if ($device->get_aname() != 'spare') {
echo "<input class='btn btn-danger' type='submit' value='Delete' name='delete' onClick='return confirm_delete()'>";
}
?>
</div>
</form>
<div class='container col-md-12 col-lg-12 col-xl-12'>
<p>
<?php
if (isset($result['MESSAGE'])) {
	echo $result['MESSAGE'];
}
?>
</p>
</div>
<?php

require_once 'includes/footer.inc.php';
?>
