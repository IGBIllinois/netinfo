<?php
require_once 'includes/main.inc.php';
require_once 'includes/session.inc.php';
require_once 'includes/header.inc.php';

define('COLUMNS',3);
$network_stats = functions::get_networks_stats($db);
$network_stats_html = "";
$column_count = 1;
foreach ($network_stats as $network) {
        if ($column_count == 1) {
                $network_stats_html .= "<div class='row'>";
        }
        $network_stats_html .= "<table class='table table-sm table-bordered col-sm-3 col-md-3 col-lg-3 col-xl-3'>";
        $network_stats_html .= "<thead class='thead-dark'>";
        $network_stats_html .= "<tr><th colspan='2'>VLAN" . $network['vlan'] . " - " . $network['name'] . " - " . $network['network'] . "</th></tr>";
        $network_stats_html .= "</thead>";
        $network_stats_html .= "<tbody>";

        $network_stats_html .= "<tr><td>Registered Devices</td>";
        $network_stats_html .= "<td>" . $network['num_devices'] . "</td></tr>";
        $network_stats_html .= "<tr><td>Spares</td><td>" . $network['num_spares'] . "</td></tr>";
        $network_stats_html .= "<tr><td>Older Than 6 Months</td><td>" . $network['num_six_months'] . "</td></tr>";
        $network_stats_html .= "<tr><td>Never Seen</td><td>" . $network['num_never_seen'] . "</td></tr>";
        $network_stats_html .= "<tr><td>Total</td><td>" . $network['total'] . "</td></tr>";
        $network_stats_html .= "</tbody>";
        $network_stats_html .= "</table>&nbsp;";
        if ($column_count == COLUMNS) {
                $network_stats_html .= "</div>&nbsp;";
                $column_count = 1;
        }
        else {
                $column_count++;
        }
}
?>

<div class='col-md-12 col-lg-12 col-xl-12'>
<h4>Network Statistics</h4>
<?php echo $network_stats_html; ?>
</div>

<?php

require_once 'includes/footer.inc.php';
?>
