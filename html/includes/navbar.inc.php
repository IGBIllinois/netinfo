<?php

$networks = functions::get_networks($db);
$nav_html = "<div class='accordion md-accordion' role='tablist' id='nav_accordion'>";
$i=1;
foreach ($networks as $network) {

        $cidr = functions::mask2cidr($network['netmask']);
        $nav_html .= "<div>\n";
        $nav_html .= "<div class='none' id='headingCollapse$i'>\n";
        $nav_html .= "<a class='nav-link' data-toggle='collapse' data-parent='#nav_accordion' href='#collapse$i'>\n";
        //$nav_html .= "<i class='fa fa-caret-right' id='iconCollapse$i'></i> VLAN" . $network['vlan'] . " - " . $network['name'] . " - " . $network['network'] . "/" . $cidr . "</a>\n";
$nav_html .= "<i class='fa fa-caret-right' id='iconCollapse$i'></i> " . $network['name'] . " - " . $network['network'] . "/" . $cidr . "</a>\n";
        $nav_html .= "</div>\n";
        $nav_html .= "<div id='collapse$i' class='collapse' role='tabpanel' data-parent='#nav_accordion'>\n";
        $nav_html .= "<li class='px-2 nav-item'><a class='nav-link' href='devices.php?network=" . $network['network'] . "/" . $cidr . "'>All Devices</a></li>\n";
        $nav_html .= "<li class='px-2 nav-item'><a class='nav-link' href='devices.php?network=" . $network['network'] . "/" . $cidr . "&search=spare&exact=1'>Spares</a></li>\n";
        $nav_html .= "<li class='px-2 nav-item'><a class='nav-link' href='devices.php?network=" . $network['network'] . "/" . $cidr . "&start_date=" . date('Y:m:d',strtotime("-6 month",time())) . "&end_date=0'>Older Than 6 Months</a></li>\n";
        $nav_html .= "<li class='px-2 nav-item'><a class='nav-link' href='devices.php?network=" . $network['network'] . "/" . $cidr . "&start_date=0&end_date=0'>Never Seen</a></li>\n";
        $nav_html .= "</div>\n";
        $nav_html .= "</div>\n";
        $i++;
}
$nav_html .= "</div>";


$nav_js = "<script type='text/javascript'>\n";
$nav_js .= "$(document).ready(function() {\n";
$nav_js .= "	$('.collapse').on('show.bs.collapse', function () {\n";
$nav_js .= "		$(this).prev(\".none\").find(\".fa\").removeClass(\"fa-caret-right\").addClass(\"fa-caret-down\");\n";
$nav_js .= "	}).on('hide.bs.collapse', function () {\n";
$nav_js .= "		 $(this).prev(\".none\").find(\".fa\").removeClass(\"fa-caret-down\").addClass(\"fa-caret-right\");\n";
$nav_js .= "	});";
$nav_js .= "});\n";
$nav_js .= "</script>\n";

?>

