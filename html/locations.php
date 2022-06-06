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

$count = COUNT;

$locations = location::get_locations($db,$search);
$num_locations = count($locations);
$pages_url = $_SERVER['PHP_SELF'] . "?search=" . $search;
$pages_html = html::get_pages_html($pages_url,$num_locations,$start,$count);

$locations_html = "";
for ($i=$start; $i<$start+$count; $i++) {
	if (array_key_exists($i,$locations)) {
		$locations_html .= "<tr>";
		$locations_html .= "<td>" . $locations[$i]['room'] . "</td>";
		$locations_html .= "<td>" . $locations[$i]['switch'] . "</td>";
		$locations_html .= "<td>" . $locations[$i]['port'] . "</td>";
		$locations_html .= "<td>" . $locations[$i]['jack_number'] . "</td>";
		$locations_html .= "<td>" . $locations[$i]['mac'] . "</td>";
		$locations_html .= "<td>" . $locations[$i]['last_seen'] . "</td>";
		$locations_html .= "</tr>";
	}

}
?>

<h4>Locations</h4>
<form class='form-inline' method='get' action='<?php echo $_SERVER['PHP_SELF'];?>'>
        <div class='input-group mb-4'>
                <input type='text' name='search' class='form-control' value='<?php echo $search; ?>'>
                <div class='input-group-append'>
                <button type='submit' class='btn btn-primary'>Search</button>
		<input type='submit' class='btn btn-secondary' name='clear' value='Clear'>
                </div>

        </div>
</form>


<table class='table table-bordered table-sm table-striped table-hover'>
        <thead class='thead-dark'>
                <tr>
			<th>Room</th>
                        <th>Switch</th>
			<th>Port</th>
			<th>Jack Number</th>
			<th>Hardware Address</th>
			<th>Last Seen</th>
                </tr>
        </thead>
        <tbody>
                <?php echo $locations_html; ?>
        </tbody>
</table>

<?php

echo $pages_html;


require_once 'includes/footer.inc.php';
?>
