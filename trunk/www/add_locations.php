<?php
include_once 'includes/main.inc.php';
include_once 'includes/header.inc.php';

$num_locations = 1;
$message = "";
if (isset($_POST['cancel'])) {
	unset($_POST);
}
elseif (isset($_POST['add_locations'])) {
	for($i=1;$i<=$num_locations;$i++) {
		$location = new location($db);
		$switch = $_POST["switch" . $i];
		$port = $_POST["port" . $i];
		$jack = $_POST["jack" . $i];
		$room = $_POST["room" . $i];
		$building = $_POST["building" . $i];
		$result = $location->create($switch,$port,$jack,$room,$building);
		if ($result['RESULT']) {
			unset($_POST['switch' . $i]);
			unset($_POST['port' . $i]);
			unset($_POST['jack' . $i]);
			unset($_POST['room' . $i]);
			unset($_POST['building' . $i]);
		}
		$message .= $result['MESSAGE'];
	} 
}
$switches = get_switches($db);
$buildings = get_buildings();

$add_location_html = "";
for ($i=1;$i<=$num_locations;$i++) {
	$add_location_html .= "<tr>";
	$add_location_html .= "<td>" . $i . "</td>";
	$add_location_html .= "<td><select name='switch" . $i . "'>";
	$add_location_html .= "<option value=''></option>";
	foreach ($switches as $switch) {
		$add_location_html .= "<option ";
		if ((isset($_POST['switch' . $i])) && ($_POST['switch' . $i] == $switch['id'])) {
			$add_location_html .= "selected='selected' ";

		}
		$add_location_html .= "value='" . $switch['id'] . "'>" . $switch['name'] . "</option>";
	}
	$add_location_html .= "</select></td>";
	$add_location_html .= "<td><input class='input-small' type='text' maxlength='10' name='port" . $i . "' ";
	if (isset($_POST['port' . $i])) { 
		$add_location_html .= "value='" . $_POST['port' . $i] . "'"; 
	}
	$add_location_html .= "'></td>";
	$add_location_html .= "<td><input class='input-small' type='text' maxlength='10' name='jack" . $i . "' ";
	if (isset($_POST['jack' . $i])) {
		$add_location_html .= "value='" . $_POST['jack' . $i] . "' ";
	}
	$add_location_html .= "></td>";
	$add_location_html .= "<td><input class='input-small' type='text' maxlength='10' name='room" . $i . "' ";
	if (isset($_POST['room' . $i])) {
		$add_location_html .= "value='" . $_POST['room' . $i] . "' ";
	}
	$add_location_html .= "></td>";
	$add_location_html .= "<td><select class='input-small' name='building" . $i . "'>";
	$add_location_html .= "<option value=''></option>";
	foreach ($buildings as $building) {
		$add_location_html .= "<option value='" . $building . "' ";
		if ((isset($_POST['building' . $i])) && ($_POST['building' . $i] == $building)) {
			$add_location_html .= "selected='selected' ";
		}
		$add_location_html .= ">" . $building . "</option>";
	}
	$add_location_html .= "</select></td>";
	$add_location_html .= "</tr>";	


}
?>
<h3>Add Locations</h3>
<form method='post' action='<?php echo $_SERVER['PHP_SELF']; ?>'>
<table class='table table-bordered table-condensed table-striped table-hover'>
<thead>
	<tr><th></th><th>Switch</th><th>Switch Port</th><th>Jack Number</th><th>Room</th><th>Building</th></tr>
</thead>
<tbody>
	<?php echo $add_location_html; ?>
	<tr>
		<td></td>
		<td colspan='5'><input class='btn btn-primary' type='submit' name='add_locations' value='Add Locations'>
		<input class='btn btn-warning' type='submit' name='cancel' value='Cancel'></td>
	</tr>
</tbody>


</table>

</form>
<?php
if (isset($message)) {
	echo $message;
}

?>
<?php

include_once 'includes/footer.inc.php';
?>
