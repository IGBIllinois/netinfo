<?php
include_once 'includes/main.inc.php';
include_once 'includes/header.inc.php';
include_once 'functions.inc.php';

if (isset($_POST['add_switch'])) {
	$switch = new network_switch($db);
	$result = $switch->create($_POST['hostname']);
	$message = $result['MESSAGE'];
	if ($result['RESULT']) {
		unset($_POST);
	}
}
elseif (isset($_POST['cancel'])) {
	unset($_POST);


}
$switches = get_switches($db);

$switches_html = "";
foreach ($switches as $switch) {
	$switches_html .= "<tr>";
	$switches_html .= "<td>" . $switch['name'] . "</td>";
	$switches_html .= "<td></td><td></td>";
	$switches_html .= "</tr>";

}
?>
<h3>Network Switches</h3>
<div class='span6'>
<table class='table table-condensed table-striped table-bordered'>
<thead>
	<tr><th>Switch Name</th><th>Room</th><th>Building</th></tr>
</thead>
<tbody>
	<?php echo $switches_html; ?>
</tbody>

</table>
</div>
<div class='span6'>
<form class='form-horizontal' method='post' action='<?php echo $_SERVER['PHP_SELF']; ?>'
        name='form'>
        <fieldset>
                <legend>Add Switch</legend>
                <div class='control-group'>
                        <label class='control-label' for='hostname_input'>Hostname:</label>
                        <div class='controls'>
                                <input type='text' name='hostname' id='hostname_input'
                                        value='<?php if (isset($_POST['hostname'])) { echo $_POST['hostname']; } ?>'>
                        </div>
                </div>
		<div class='control-group'>
                        <div class='controls'>
                                <input class='btn btn-primary' type='submit' name='add_switch'
                                        value='Add Switch'> <input class='btn btn-warning' type='submit'
                                        name='cancel' value='Cancel'>
                        </div>
                </div>
        </fieldset>
</form>
</div>
<?php

if (isset($message)) { echo $message; }
?>
<?php

include_once 'includes/footer.inc.php';
?>
