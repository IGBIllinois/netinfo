<?php
require_once 'includes/main.inc.php';
require_once 'includes/session.inc.php';
require_once 'includes/header.inc.php';


$networks = functions::get_networks($db);
if (isset($_POST['network'])) {
	$selected_network = $_POST['network'];
}
else {
	$selected_network = $networks[0]['name'];
}

$networks_html = "<select class='col-md-4 col-lg-4 col-xl-4 form-control' name='network' onchange='this.form.submit()'>";
if (count($networks)) {

	foreach ($networks as $network) {
		if ($selected_network == $network['name']) {
			$networks_html .= "<option selected='selected' value='" . $network['name'] . "'>" . $network['name'] . "</option>";
		}
		else {
			$networks_html .= "<option value='" . $network['name'] . "'>" . $network['name'] . "</option>";
		}

	}

}
$networks_html .= "</select>";

$network = new network($db,$selected_network);
?>
<h3>Networks</h3>
<form method='post' action='<?php echo $_SERVER['PHP_SELF']; ?>'>
<?php echo $networks_html; ?>
</form>
<p>



<table class='table table-sm table-bordered'>

<tr><td>Name</td><td><?php echo $network->get_name(); ?></td></tr>
<tr><td>Domain</td><td><?php echo $network->get_domain_name(); ?></td></tr>
<tr><td>Enable DHCP</td><td><?php echo $network->is_enabled(); ?></td></tr>
<tr><td>Network</td><td><?php echo $network->get_network_number(); ?></td></tr>
<tr><td>Netmask</td><td><?php echo $network->get_netmask(); ?></td></tr>
<tr><td>Vlan</td><td><?php echo $network->get_vlan(); ?></td><tr>
<tr><td>Options</td><td><textarea class=' input-block-level' rows='20' cols='80' readonly><?php echo $network->get_options(); ?></textarea></td></tr>
</table>


<?php

require_once 'includes/footer.inc.php';
?>
