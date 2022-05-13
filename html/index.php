<?php
require_once 'includes/main.inc.php';
require_once 'includes/session.inc.php';
require_once 'includes/header.inc.php';

$start = 0;
if (isset($_GET['start']) && is_numeric($_GET['start'])) {
        $start = $_GET['start'];
}

$search = "";
if (isset($_GET['search']) && !isset($_GET['clear'])) {
        $search = $_GET['search'];
}

$exact = 0;
$network = "";
if ($search == "") {
	$devices = functions::get_recent_devices($db);
}
else {
	$devices = functions::get_devices($db,$network,$search,$exact);
}
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
                $devices_html .= "<td><a href='device.php?ipnumber=" . $device['ipnumber'] . "'>" . $device['ipnumber'] . "</a></td>";
                $devices_html .= "<td>" . $device['aname']. "</td>";
                $devices_html .= "<td>" . $device['hardware'] . "</td>";
                $devices_html .= "<td>" . $device['user'] . "</td>";
                $devices_html .= "<td>" . $device['email'] . "</td>";
                $devices_html .= "<td>" . $device['room'] . "</td>";
                $devices_html .= "<td>" . $device['os'] . "</td>";
                $devices_html .= "<td>" . $device['description'] . "</td>";
                $devices_html .= "</tr>";
}

?>


<div class='col-md-12 col-lg-12 col-xl-12'>
<h4>Quick Search</h4>
<form class='form-inline' method='get' action='<?php echo $_SERVER['PHP_SELF'];?>'>
                <input type='hidden' name='network' value='<?php echo $network; ?>'>
        <div class='input-group mb-4'>
                <input type='text' name='search' class='form-control' value='<?php echo $search; ?>'>
                <div class='input-group-append'>
                <button type='submit' class='btn btn-primary'>Search</button>
                </div>
		<div class='input-group-append'>
		<input type='submit' class='btn btn-secondary' name='clear' value='Clear'>
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
		<?php echo $devices_html; ?>
	</tbody>

</table>
</div>
<form class='form-inline' action='report.php' method='post'>
        <input type='hidden' name='search' value='<?php echo $search; ?>'>

        <select name='report_type' class='form-control'>
                <option value='xlsx'>Excel (.xlsx)</option>
                <option value='csv'>CSV (.csv)</option>

        </select> &nbsp;
<input class='btn btn-primary' type='submit' name='create_report_full' value='Download Report'>&nbsp;
</form>

<?php

require_once 'includes/footer.inc.php';
?>
