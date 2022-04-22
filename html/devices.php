<?php
require_once 'includes/main.inc.php';
require_once 'includes/session.inc.php';
require_once 'includes/header.inc.php';


$start = 0;
if (isset($_GET['start']) && is_numeric($_GET['start'])) {
        $start = $_GET['start'];
}

$network = "";
$network_name = "";
if (isset($_GET['network'])) {
	$network = $_GET['network'];
	$network_name = network::get_name_by_cidr($db,$network);
}	


$search = "";
if (isset($_GET['search'])) {
	$search = $_GET['search'];
	//unset($_GET['start_date']);
	//unset($_GET['end_date']);
}
$exact = 0;
if (isset($_GET['exact'])) {
	$exact = $_GET['exact'];
}

$start_date = "";
$end_date = "";
if ((isset($_GET['start_date'])) && (isset($_GET['end_date']))) {
	$start_date = $_GET['start_date'];
	$end_date = $_GET['end_date'];
}
$count = COUNT;
$devices = functions::get_devices($db,$network,$search,$exact,$start_date,$end_date);
$num_devices = count($devices);
$pages_url = $_SERVER['PHP_SELF'] . "?search=" . $search . "&exact=" . $exact;
if ($network != "") {
	$pages_url .= "&network=" . $network;
}
if (($start_date != "") && ($end_date != "")) {
	$pages_url .= "&start_date=" . $start_date . "&end_date=" . $end_date;
}
$pages_html = html::get_pages_html($pages_url,$num_devices,$start,$count);
$current_time = date('Y-m-d H:i:s');
$devices_html = "";
for ($i=$start;$i<$start+$count;$i++) {
	if (array_key_exists($i,$devices)) {

	
	        if ($devices[$i]['aname'] == "spare") {
			$devices_html .= "<tr class='warning'>";
		}
		else {
			$devices_html .= "<tr>";
		}
		$last_seen = functions::get_last_seen($devices[$i]['last_seen']);
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
	        $devices_html .= "<td><a href='device.php?ipnumber=" . $devices[$i]['ipnumber'] . "'>" . $devices[$i]['ipnumber'] . "</a></td>";
		$devices_html .= "<td>" . $devices[$i]['aname']. "</td>";
	        $devices_html .= "<td>" . $devices[$i]['hardware'] . "</td>";
		$devices_html .= "<td>" . $devices[$i]['user'] . "</td>";
		$devices_html .= "<td>" . $devices[$i]['email'] . "</td>";
		$devices_html .= "<td>" . $devices[$i]['room'] . "</td>";
		$devices_html .= "<td>" . $devices[$i]['os'] . "</td>";
		$devices_html .= "<td>" . $devices[$i]['description'] . "</td>";
	        $devices_html .= "</tr>";
	}
}
?>
<h3>Devices <?php if ($network != "") { echo " - " . $network . " - " . $network_name; } ?></h3>
<form class='form-inline' method='get' action='<?php echo $_SERVER['PHP_SELF'];?>'>
		<input type='hidden' name='network' value='<?php echo $network; ?>'>
		<input type='hidden' name='count' value='<?php echo $count; ?>'>
		<input type='hidden' name='exact' value='<?php echo $exact; ?>'>
		<input type='hidden' name='start_date' value='<?php echo $start_date; ?>'>
		<input type='hidden' name='end_date' value='<?php echo $end_date; ?>'>
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
        <thead class='thead-dark'>
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
                </tr>
        </thead>
        <tbody>
                <?php echo $devices_html; ?>
        </tbody>
</table>
<?php echo $pages_html; ?>

<form class='form-inline' action='report.php' method='post'>
        <input type='hidden' name='network' value='<?php echo $network; ?>'> 
	<input type='hidden' name='search' value='<?php echo $search; ?>'>
	<input type='hidden' name='exact' value='<?php echo $exact; ?>'> 
	<input type='hidden' name='start_date' value='<?php echo $start_date; ?>'>
	<input type='hidden' name='end_date' value='<?php echo $end_date; ?>'>

	<select name='report_type' class='form-control'>
                <option value='xlsx'>Excel (.xlsx)</option>
                <option value='csv'>CSV (.csv)</option>
		
        </select> &nbsp;
<input class='btn btn-primary' type='submit' name='create_report_full' value='Download Full Report'>&nbsp;
<input class='btn btn-primary' type='submit' name='create_report_no_spares' value='Download Device In Use Report'>&nbsp;
<input class='btn btn-primary' type='submit' name='create_host_file' value='Download Host File'>
</form>


<?php

require_once 'includes/footer.inc.php';
?>
