<?php
require_once 'includes/main.inc.php';
require_once 'includes/session.inc.php';
require_once 'includes/header.inc.php';

if (isset($_POST['ipnumber'])) {
        foreach ($_POST as $var) {
                $var = trim(rtrim($var));
        }

	$ipnumber = $_POST['ipnumber'];
	$device = new device($db,$log,$ipnumber);
        $aname = $_POST['aname'];
        $hardware = $_POST['hardware'];
        $user = $_POST['user'];
        $email = $_POST['email'];
        $room = $_POST['room'];
        $description = $_POST['description'];
        $serial_number = $_POST['serial_number'];
        $property_tag = $_POST['property_tag'];
	$device_os = $_POST['os'];
	$url = $_POST['url'];
	$username = $_POST['username'];
	$password = $_POST['password'];
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
	$advanced = array('url'=>$url,'username'=>$username,'password'=>$password);
	$advanced_json = json_encode($advanced);
        $result = $device->update($aname,$hardware,$user,
                        $email,$room,$description,
                        $serial_number,$property_tag,$device_os,$session->get_var('username'),$advanced_json);
	if ($result['RESULT']) {
	        unset($_POST);
	}
}

if (isset($_GET['ipnumber']) && !(isset($_POST['ipnumber']))) {
	$ipnumber = $_GET['ipnumber'];
	$device = new device($db,$log,$ipnumber);
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
	$url = $device->get_url();
	$username = $device->get_username();
	$password = $device->get_password();

}


$locations = $device->get_locations();
$locations_html = "";
if (count($locations)) {
	foreach ($locations as $location) {
		$locations_html .= "<tr>";
		$locations_html .= "<td>" . $location['last_seen'] . "</td>";
		$locations_html .= "<td>" . $location['switch'] . "</td>";
		$locations_html .= "<td>" . $location['port'] . "</td>";
		$locations_html .= "<td>" . $location['jack_number'] . "</td>";
		$locations_html .= "<td>" . $location['room'] . "</td>";
		$locations_html .= "<td>" . $location['vlans'] . "</td>";
		$locations_html .= "</tr>";
	}
}
else {
	$locations_html = "<tr><td colspan='6'>Not Seen</td></tr>";
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
		$alias_html .= "<button class='btn btn-danger btn-sm' name='delete_alias' ";
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
	<tr><td>Name (ANAME)</td><td><input class='form-control' type='text' name='aname' maxlength='64' value='<?php echo $aname; ?>'></td></tr>
	<tr><td>Domain</td><td><?php echo $device->get_domain(); ?></td></tr>
	<tr><td>Hardware (MAC) Address</td><td><input class='form-control' type='text' name='hardware' maxlength='12' value='<?php echo $hardware; ?>'></td></tr>
	<tr><td>User</td><td><input class='form-control' type='text' name='user' value='<?php echo $user; ?>'></td></tr>
	<tr><td>Email</td><td><input class='form-control' type='text' name='email' value='<?php echo $email; ?>'></td></tr>
	<tr><td>Room</td><td><input class='form-control' type='text' name='room' value='<?php echo $room; ?>'></td></tr>
	<tr><td>Device Type/OS</td><td><?php echo $os_html; ?></td></tr>
	<tr><td>Description</td><td><textarea class='form-control' name='description'><?php echo $description; ?></textarea></td></tr>
	<tr><td>Serial Number</td><td><input class='form-control' type='text' name='serial_number' value='<?php echo $serial_number; ?>'></td></tr>
	<tr><td>Property Tag</td><td><input class='form-control' type='text' name='property_tag' value='<?php echo $property_tag; ?>'></td></tr>
	<tr><td colspan='2'><b>Advanced</b></td></tr>
	<tr><td>URL</td><td><div class='input-group'>
		<input class='form-control' type='text' name='url' value='<?php echo $url; ?>'>
		<div class='input-group-append'>
		<a class='btn btn-primary btn-sm <?php echo ($url == "") ? "disabled": "" ?>' role='button' target='_blank' href='<?php echo ($url != "") ? $url : "#" ?>'>
			<i class='fas fa-link'></i></a>
		</div></div>
		
	</td></tr>
	<tr><td>Username</td><td><input class='form-control' type='text' name='username' value='<?php echo $username; ?>'></td></tr>
	<tr><td>Password</td><td><input class='form-control' type='text' name='password' value='<?php echo $password; ?>'></td></tr>
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

	<h4>Aliases</h4>
	<table class='table table-bordered table-sm table-striped '>
		<thead>
			<th colspan='2'>Alias (CNAME)</th>
		</thead>
	<?php 
		echo $alias_html;
		if ($device->get_aname() != "spare") { 
			echo "<tr><td><input class='form-control' type='text' name='new_alias' value='";
			if (isset($_POST['new_alias'])) { echo $_POST['new_alias']; }
			echo "'></td>";
			echo "<td><input class='btn btn-primary btn-sm' type='submit' name='add_alias' value='Add' onClick='return confirm_alias()'></td></tr>";
		}
	?>
	</table>

</div>
<div class='col-md-6 col-lg-6 col-xl-6'>

</div>
</div>
<div class='col-md-12 col-lg-12 col-xl-12'>
<input class='btn btn-primary' type='submit' value='Update' name='update' onClick='return confirm_update()'>
<a class='btn btn-warning' href='<?php if (isset($_SERVER['HTTP_REFERER'])) { echo $_SERVER['HTTP_REFERER']; } ?>'>Cancel</a>
<?php if ($device->get_aname() != 'spare') {
echo "<input class='btn btn-danger' type='submit' value='Delete' name='delete' onClick='return confirm_delete()'>";
}
?>
&nbsp;<button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#macModal">Hardware (MAC) Address Formats</button>
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
require_once 'includes/mac.inc.php';
require_once 'includes/footer.inc.php';
?>
