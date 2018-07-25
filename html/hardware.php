<?php
require_once 'includes/main.inc.php';
require_once 'includes/header.inc.php';

$count = __COUNT__;
$start = 0;
if (isset($_GET['start']) && is_numeric($_GET['start'])) {
        $start = $_GET['start'];
}
$search = "";
if (isset($_GET['search'])) {
        $search = $_GET['search'];
}

$devices = functions::get_hardware_addresses($db,$search,$start,$count);
$num_devices = functions::get_num_hardware_addresses($db,$search);
$pages_url = $_SERVER['PHP_SELF'] . "?search=" . $search;
$pages_html = functions::get_pages_html($pages_url,$num_devices,$start,$count);
$current_time = date('Y-m-d H:i:s');
$devices_html = "";

foreach ($devices as $device) {
	$devices_html .= "<tr>";
	$last_seen = functions::get_last_seen($device['last_seen']);
        switch($last_seen) {
                        case 1:
                                $devices_html .= "<td><span class='badge badge-pill badge-success'>&nbsp</span></td>";
                                break;

                        case 2:
                                $devices_html .= "<td><span class='badge badge-pill badge-warning'>&nbsp</span></td>";
                                break;
                        case 3:
                                $devices_html .= "<td><span class='badge badge-pill badge-info'>&nbsp</span></td>";
                                break;
                        case 4:
                                $devices_html .= "<td><span class='badge badge-pill badge-danger'>&nbsp</span></td>";
                                break;
                        default:
                                $devices_html .= "<td><span class='badge badge-pill badge-secondary'>&nbsp</span></td>";
                                break;
                }

		$devices_html .= "<td>" . $device['mac'] . "</td>";
		$devices_html .= "<td>";
                if ($devices[$i]['ipnumber'] != "") {
                        $devices_html .= "<a href='device.php?ipnumber=" . $device['ipnumber'] . "'>" . $device['ipnumber'] . "</a>";
                }
                $devices_html .= "</td>";

		$devices_html .= "<td>" . $device['switch'] . "</td>";
		$devices_html .= "<td>" . $device['port'] . "</td>";
		$devices_html .= "<td>" . $device['vendor'] . "</td>";
		$devices_html .= "<td>" . $device['last_seen'] . "</td>";
		$devices_html .= "</tr>";
}
?>

<h3>Hardware Addresses</h3>
<form class='form-inline' method='get' action='<?php echo $_SERVER['PHP_SELF'];?>'>
                <input type='hidden' name='count' value='<?php echo $count; ?>'>
	<div class='input-group mb-4'>
                <input type='text' name='search' class='form-control' value='<?php echo $search; ?>'>
		<div class='input-group-append'>
	                <button type='submit' class='btn btn-primary'>Search</button>
		</div>
        </div>
</form>
<ul class='list-inline'>
<li class='list-inline-item'><span class='badge badge-pill badge-success'>&nbsp</span> Less than 1 Day</li>
<li class='list-inline-item'><span class='badge badge-pill badge-warning'>&nbsp</span> Less than 1 Month</li>
<li class='list-inline-item'><span class='badge badge-pill badge-info'>&nbsp</span> Less than 6 Months</li>
<li class='list-inline-item'><span class='badge badge-pill badge-danger'>&nbsp</span> Greater than 6 Months</li>
<li class='list-inline-item'><span class='badge badge-pill badge-secondary'>&nbsp</span> Never Seen</li>
</ul>

<table class='table table-bordered table-sm table-striped table-hover'>
	<thead>
		<tr>
		<th></th>
		<th>Hardware Address</th>
		<th>IP Address</th>
		<th>Switch</th>
		<th>Port</th>
		<th>Vendor</th>
		<th>Last Seen</th>
		</tr>
		
	</thead>

	<?php echo $devices_html; ?>
</table>
<?php echo $pages_html; ?>

<?php

require_once 'includes/footer.inc.php';
?>
