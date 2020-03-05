<?php
require_once 'includes/main.inc.php';
require_once 'includes/session.inc.php';
require_once 'includes/header.inc.php';


$domains = functions::get_domains($db);
if (isset($_POST['domain'])) {
        $selected_domain = $_POST['domain'];
}
else {
        $selected_domain = $domains[0]['name'];
}

$domains_html = "<select class='col-md-4 col-lg-4 col-xl-4 form-control custom-select' name='domain' onchange='this.form.submit()'>";
if (count($domains)) {

        foreach ($domains as $domain) {
                if ($selected_domain == $domain['name']) {
                        $domains_html .= "<option selected='selected' value='" . $domain['name'] . "'>" . $domain['name'] . "</option>";
                }
                else {
                        $domains_html .= "<option value='" . $domain['name'] . "'>" . $domain['name'] . "</option>";
                }

        }

}
$domains_html .= "</select>";

$domain = new domain($db,$selected_domain);
?>
<h3>Domains</h3>
<form method='post' action='<?php echo $_SERVER['PHP_SELF']; ?>'>
<?php echo $domains_html; ?>
</form>
<p>



<table class='table table-sm table-bordered'>

<tr><td>Name</td><td><?php echo $domain->get_name(); ?></td></tr>
<tr><td>Alternate Names</td><td><?php echo implode(",",$domain->get_alt_names()); ?></td></tr>
<tr><td>Enabled</td><td><?php echo $domain->is_enabled(); ?></td></tr>
<tr><td>Serial</td><td><?php echo $domain->get_serial(); ?></td></tr>
<tr><td>Last Updated</td><td><?php echo $domain->get_last_updated(); ?></td></tr>
<tr><td>Header</td><td><textarea class='form-control' rows='20' cols='80' readonly><?php echo $domain->get_header(); ?></textarea></td></tr>
<tr><td>Options</td><td><textarea class='form-control' rows='20' cols='80' readonly><?php echo $domain->get_options(); ?></textarea></td></tr>
</table>


<?php

require_once 'includes/footer.inc.php';
?>
