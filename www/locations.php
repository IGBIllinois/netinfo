<?php
include_once 'includes/main.inc.php';
include_once 'includes/header.inc.php';

$start = 0;
if (isset($_GET['start']) && is_numeric($_GET['start'])) {
        $start = $_GET['start'];
}
$search = "";
if (isset($_GET['search'])) {
        $search = $_GET['search'];
}
$count = 30;

$locations = get_locations($db,$search,$start,$count);
$num_locations = get_num_locations($db,$search);
$pages_html = get_pages_html($pages_url,$num_locations,$start,$count);
$locations_html = "";

foreach ($locations as $location) {
	$locations_html .= "<tr>";
	$locations_html .= "<td><a href='edit_location.php?location_id=" . $location['id'] . "'><i class='icon-pencil'></i></a>";
	$locations_html .= "<td>" . $location['switch_name'] . "</td>";
	$locations_html .= "<td>" . $location['port'] . "</td>";
	$locations_html .= "<td>" . $location['jack'] . "</td>";
	$locations_html .= "<td>" . $location['room'] . "</td>";
	$locations_html .= "<td>" . $location['building'] . "</td>";
	$locations_html .= "</tr>";


}
?>
<h3>Locations</h3>
<form class='form-search' method='get' action='<?php echo $_SERVER['PHP_SELF'] . "?count=" . $count; ?>'>
        <div class='input-append'>
                <input type='text' name='search' class='input-long search-query' value='<?php echo $search; ?>'>
                <button type='submit' class='btn'>Search</button>
        </div>
</form>
<table class='table table-condensed table-bordered table-hover table-striped'>
<thead>
        <tr><th></th><th>Switch</th><th>Switch Port</th><th>Jack Number</th><th>Room</th><th>Building</th></tr>
</thead>
<tbody>
<?php echo $locations_html; ?>

</tbody>


</table>
<?php echo $pages_html; ?>

<?php

include_once 'includes/footer.inc.php';
?>
