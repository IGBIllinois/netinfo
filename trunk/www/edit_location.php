<?php
include_once 'includes/main.inc.php';
include_once 'includes/header.inc.php';

if (isset($_GET['location_id']) && is_numeric($_GET['location_id'])) {
	$location = new location($db,$_GET['location_id']);

}

if (isset($_POST['cancel'])) {
	unset($_POST);
	$message = "<div class='alert alert-success'>Edit Location was canceled</div>";
}
elseif (isset($_POST['edit_location'])) {
	$location = new location($db,$_POST['location_id']);
	$result = $location->edit($_POST['switch_id'],
			$_POST['port'],
			$_POST['jack'],
			$_POST['room'],
			$_POST['building']);
	if ($result['RESULT']) {
		unset($_POST);
	}
}

$switches = get_switches($db);
$buildings = get_buildings();

$port = $location->get_port();
$jack = $location->get_jack();
$room = $location->get_room();
$location_id = $location->get_id();
$building = $location->get_building();
$switch_name = $location->get_switch();

$switches_html = "<select name='switch'>";
foreach ($switches as $selected_switch) {
	$switches_html .= "<option ";
	if ($switch_name == $selected_switch['name']) {
		$switches_html .= "selected='selected' ";
	}
	$switches_html .= "value='" . $selected_switch['id'] . "'>" . $selected_switch['name'] . "</option>";
}
$switches_html .= "</select>";

$buildings_html = "<select name='building'>";
foreach ($buildings as $selected_building) {
	$buildings_html .= "<option value='" . $selected_building . "' ";
	if ($building == $selected_building) {
		$buildings_html .= "selected='selected' ";
	}
	$buildings_html .= ">" . $selected_building . "</option>";
	}
	$buildings_html .= "</select></td>";

?>
<h3>Edit Location</h3>
<div class='span5'>
<form method='post' action='<?php echo $_SERVER['PHP_SELF'] . "?location_id=" . $location_id; ?>'>
<input type='hidden' name='location_id' value='<?php echo $location_id; ?>'>
<table class='table table-condensed table-bordered'>
<tr><td>Switch</td><td><?php echo $switches_html; ?></td></tr>
<tr><td>Port</td><td><input type='text' name='port' value='<?php echo $port; ?>'></td></tr>
<tr><td>Jack</td><td><input type='text' name='jack' value='<?php echo $jack; ?>'></td></tr>
<tr><td>Room</td><td><input type='text' name='room' value='<?php echo $room; ?>'></td></tr>
<tr><td>Building</td><td><?php echo $buildings_html; ?></td></tr>

<tr>
	<td colspan='2'><input class='btn btn-primary' type='submit' name='edit_location' value='Edit Location'>
		<input class='btn btn-warning' type='submit' name='cancel' value='Cancel'></td>
</tr>

</table>
</form>
</div>
<div class='span7'>
<?php if (isset($message)) { echo $message; } ?>
</div>
<?php

include_once 'includes/footer.inc.php';
?>
