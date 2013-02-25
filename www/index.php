<?php
include_once 'includes/main.inc.php';
include_once 'includes/header.inc.php';
include_once 'functions.inc.php';

$start = 0;
if (isset($_GET['start']) && is_numeric($_GET['start'])) {
        $start = $_GET['start'];
}

$network = "";
if (isset($_GET['network'])) {
	$network = $_GET['network'];
}

$search = "";
if (isset($_GET['search'])) {
	$search = $_GET['search'];
}
$count = 30;
$devices = get_devices($db,$network,$search,$start,$count);
$num_devices = get_num_devices($db,$network,$search);
$pages_url = $_SERVER['PHP_SELF'] . "?search=" . $search;
//$pages_html = get_pages_html($pages_url,$num_devices,$start,$count);
$pages_html = get_pages_html($pages_url,$num_devices,$start,$count);
$current_time = date('Y-m-d H:i:s');
$devices_html = "";
foreach ($devices as $device) {
        if ($device['aname'] == "spare") {
		$devices_html .= "<tr class='warning'>";
	}
	else {
		$devices_html .= "<tr>";
	}
	$last_seen = get_last_seen($device['last_seen']);
	if ($last_seen == 1) {
		$devices_html .= "<td><span class='badge badge-success'>&nbsp</span></td>";
	}
	elseif ($last_seen == 2) {
		$devices_html .= "<td><span class='badge badge-warning'>&nbsp</span></td>";
	}
	elseif ($last_seen == 3) {
		$devices_html .= "<td><span class='badge badge-info'>&nbsp</span></td>";
	}
	elseif ($last_seen == 4) {
		$devices_html .= "<td><span class='badge badge-important'>&nbsp</span></td>";
	}
	else {
		$devices_html .= "<td><span class='badge'>&nbsp</span></td>";
	}
        $devices_html .= "<td><a href='device.php?ipnumber=" . $device['ipnumber'] . "'>" . $device['ipnumber'] . "</a></td>";
	$devices_html .= "<td>" . $device['aname']. "</td>";
        $devices_html .= "<td>" . $device['hardware'] . "</td>";
	$devices_html .= "<td>" . $device['user'] . "</td>";
	$devices_html .= "<td>" . $device['email'] . "</td>";
	$devices_html .= "<td>" . $device['room'] . "</td>";
	$devices_html .= "<td>" . $device['os'] . "</td>";
	$devices_html .= "<td>" . $device['description'] . "</td>";
	if ($device['backpass']) {
		$devices_html .= "<td><i class='icon-ok'></i></td>";
	}
	else {
		$devices_html .= "<td><i class='icon-remove'></i></td>";
	}
        $devices_html .= "</tr>";
}
?>
<h3>Devices</h3>
<form class='form-search' method='get' action='<?php echo $_SERVER['PHP_SELF'] . "?count=" . $count; ?>'>
	<div class='input-append'>
		<input type='text' name='search' class='input-long search-query' value='<?php echo $search; ?>'>
		<button type='submit' class='btn'>Search</button>
	</div>
</form>
<ul class='unstyled inline'>
<li><span class='badge badge-success'>&nbsp</span> Less than 1 Day</li>
<li><span class='badge badge-warning'>&nbsp</span> Less than 1 Month</li>
<li><span class='badge badge-info'>&nbsp</span> Less than 6 Months</li>
<li><span class='badge badge-important'>&nbsp</span> Greater than 6 Months</li>
<li><span class='badge'>&nbsp</span> Never Seen</li>
</ul>   

<table class='table table-condensed table-bordered table-hover'>
        <thead>
                <tr>
			<th></th>
                        <th>IP Address</th>
                        <th>Name</th>
                        <th>Hardware Address</th>
			<th>User</th>
			<th>Email</th>
			<th>Room</th>
			<th>OS</th>
			<th>Description</th>
			<th>Retrospect</th>
                </tr>
        </thead>
        <tbody>
                <?php echo $devices_html; ?>
        </tbody>
</table>
<?php echo $pages_html; ?>


<?php

include_once 'includes/footer.inc.php';
?>
