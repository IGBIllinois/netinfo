<?php
include_once 'includes/main.inc.php';
include_once 'includes/header.inc.php';

$count = __COUNT__;
$start = 0;
if (isset($_GET['start']) && is_numeric($_GET['start'])) {
        $start = $_GET['start'];
}
$search = "";
if (isset($_GET['search'])) {
        $search = $_GET['search'];
}
$devices = get_hardware_addresses($db,$search);
$num_devices = count($devices);
$pages_url = $_SERVER['PHP_SELF'] . "?search=" . $search;
$pages_html = get_pages_html($pages_url,$num_devices,$start,$count);
$current_time = date('Y-m-d H:i:s');
$devices_html = "";
for ($i=$start;$i<$start+$count;$i++) {
        if (array_key_exists($i,$devices)) {
		$devices_html .= "<tr>";

                $last_seen = get_last_seen($devices[$i]['last_seen']);
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
		$devices_html .= "<td>" . $devices[$i]['mac'] . "</td>";
		$devices_html .= "<td>";
                if ($devices[$i]['ipnumber'] != "") {
                        $devices_html .= "<a href='device.php?ipnumber=" . $devices[$i]['ipnumber'] . "'>" . $devices[$i]['ipnumber'] . "</a>";
                }
                $devices_html .= "</td>";

		$devices_html .= "<td>" . $devices[$i]['switch'] . "</td>";
		$devices_html .= "<td>" . $devices[$i]['port'] . "</td>";
		$devices_html .= "<td>" . $devices[$i]['vendor'] . "</td>";
		$devices_html .= "<td>" . $devices[$i]['last_seen'] . "</td>";
		$devices_html .= "</tr>";
	}
}
?>

<h3>Hardware Addresses</h3>
<form class='form-search' method='get' action='<?php echo $_SERVER['PHP_SELF'];?>'>
        <div class='input-append'>
                <input type='hidden' name='count' value='<?php echo $count; ?>'>
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

include_once 'includes/footer.inc.php';
?>
