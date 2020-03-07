<?php
require_once 'includes/main.inc.php';
require_once 'includes/session.inc.php';
require_once 'includes/header.inc.php';


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
	<h1 class='display-3'><img src="images/imark_bw.gif">&nbsp;Network Information Database</h1>
	<p class='lead'>View and Manage Devices on the network</p>
</div>
<h4>Recently Updated Devices</h4>
<table class='table table-sm table-bordered table-stripped'>
	<thead>
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

<?php

require_once 'includes/footer.inc.php';
?>
