<?php
require_once 'includes/main.inc.php';
require_once 'includes/session.inc.php';
require_once 'includes/header.inc.php';

$devicetypes = functions::get_operating_systems($db);

$devicetypes_html = "";
foreach ($devicetypes as $devicetype) {
	$devicetypes_html .= "<tr><td>" . $devicetype['os'] . "</td></tr>";

}
?>
<h3>Device Type/OS</h3>

<table class='table table-sm table-bordered'>

<?php echo $devicetypes_html; ?>
</table>

<?php

require_once 'includes/footer.inc.php';
?>
