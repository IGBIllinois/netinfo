<?php
require_once 'includes/main.inc.php';
require_once 'includes/session.inc.php';
require_once 'includes/header.inc.php';

define('COLUMNS',3);
$network_stats = functions::get_networks_stats($db);
$network_stats_html = "";
$column_count = 1;
foreach ($network_stats as $network) {
	if ($column_count == 1) {
		$network_stats_html .= "<div class='row'>";
	}
	$network_stats_html .= "<table class='table table-sm table-bordered col-sm-3 col-md-3 col-lg-3 col-xl-3'>";
	$network_stats_html .= "<thead class='thead-dark'>";
	$network_stats_html .= "<tr><th colspan='2'>VLAN" . $network['vlan'] . " - " . $network['name'] . " - " . $network['network'] . "</th></tr>";
	$network_stats_html .= "</thead>";
	$network_stats_html .= "<tbody>";

	$network_stats_html .= "<tr><td>Registered Devices</td>";
	$network_stats_html .= "<td>" . $network['num_devices'] . "</td></tr>";
	$network_stats_html .= "<tr><td>Spares</td><td>" . $network['num_spares'] . "</td></tr>";
	$network_stats_html .= "<tr><td>Older Than 6 Months</td><td>" . $network['num_six_months'] . "</td></tr>";
	$network_stats_html .= "<tr><td>Never Seen</td><td>" . $network['num_never_seen'] . "</td></tr>";
	$network_stats_html .= "<tr><td>Total</td><td>" . $network['total'] . "</td></tr>";
	$network_stats_html .= "</tbody>";
	$network_stats_html .= "</table>&nbsp;";
	if ($column_count == COLUMNS) {
		$network_stats_html .= "</div>&nbsp;";
		$column_count = 1;
	}
	else {
		$column_count++;
	}
}
$recent_devices = functions::get_recent_devices($db);
$recent_devices_html = "";
foreach ($recent_devices as $device) {


                $recent_devices_html .= "<tr>";
                $last_seen = functions::get_last_seen($device['last_seen']);
                switch($last_seen) {
                        case 1:
                                $recent_devices_html .= "<td><span class='badge badge-pill badge-success'>&nbsp</span></td>";
                                break;

                        case 2:
                                $recent_devices_html .= "<td><span class='badge badge-pill badge-warning'>&nbsp</span></td>";
                                break;
                        case 3:
                                $recent_devices_html .= "<td><span class='badge badge-pill badge-info'>&nbsp</span></td>";
                                break;
                        case 4:
                                $recent_devices_html .= "<td><span class='badge badge-pill badge-danger'>&nbsp</span></td>";
                                break;
                        default:
                                $recent_devices_html .= "<td><span class='badge badge-pill badge-secondary'>&nbsp</span></td>";
                                break;
                }
                $recent_devices_html .= "<td><a href='device.php?ipnumber=" . $device['ipnumber'] . "'>" . $device['ipnumber'] . "</a></td>";
                $recent_devices_html .= "<td>" . $device['aname']. "</td>";
                $recent_devices_html .= "<td>" . $device['hardware'] . "</td>";
                $recent_devices_html .= "<td>" . $device['user'] . "</td>";
                $recent_devices_html .= "<td>" . $device['email'] . "</td>";
                $recent_devices_html .= "<td>" . $device['room'] . "</td>";
                $recent_devices_html .= "<td>" . $device['os'] . "</td>";
                $recent_devices_html .= "<td>" . $device['description'] . "</td>";
                $recent_devices_html .= "</tr>";
}

?>


<div class='jumbotron'>
	<h1 class='display-4'><img src="images/imark_bw.gif">&nbsp;Network Information Database</h1>
	<p class='lead'>View and Manage Devices on the network</p>
</div>
<h4>Network Statistics</h4>
<?php echo $network_stats_html; ?>
<div class='row'>
<h4>Recently Updated Devices</h4>
<table class='table table-sm table-bordered table-striped'>
	<thead class='thead-dark'>
		<th>&nbsp;</th>
		<th>IP Address</th>
		<th>Name</th>
		<th>Hardware Address</th>
		<th>User</th>
		<th>Email</th>
		<th>Room</th>
		<th>OS</th>
		<th>Description</th>

	</thead>
	<tbody>
		<?php echo $recent_devices_html; ?>
	</tbody>

</table>
</div>
<?php

require_once 'includes/footer.inc.php';
?>
