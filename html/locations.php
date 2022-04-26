<?php
require_once 'includes/main.inc.php';
require_once 'includes/session.inc.php';
require_once 'includes/header.inc.php';


$start = 0;
if (isset($_GET['start']) && is_numeric($_GET['start'])) {
        $start = $_GET['start'];
}

$count = COUNT;

$locations = location::get_locations($db);
$num_locations = count($locations);
$pages_html = html::get_pages_html($pages_url,$num_locations,$start,$count);

$locations_html = "";
foreach ($locations as $location) {
	$locations_html .= "<tr>";
	$locations_html .= "<td>" . $location['switch'] . "</td>";
	$locations_html .= "<td>" . $location['port'] . "</td>";
	$locations_html .= "<td>" . $location['room'] . "</td>";

}
?>

<h4>Locations</h4>

<form class='form-inline' method='get' action='<?php echo $_SERVER['PHP_SELF'];?>'>
        <div class='input-group mb-4'>
                <input type='text' name='search' class='form-control' value='<?php echo $search; ?>'>
                <div class='input-group-append'>
                <button type='submit' class='btn btn-primary'>Search</button>
		<button type='submit' class='btn btn-secondary'>Clear</button>
                </div>

        </div>
</form>


<table class='table table-bordered table-sm table-striped table-hover'>
        <thead class='thead-dark'>
                <tr>
                        <th>Switch</th>
                        <th>Port</th>
                        <th>Room</th>
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
