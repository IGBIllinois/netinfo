<?php
require_once 'includes/main.inc.php';
require_once 'includes/header.inc.php';

$log_contents = log::get_log();
?>
<h4>Log</h4>
<textarea class='form-control' rows='50' readonly><?php echo $log_contents; ?></textarea>


<?php

require_once 'includes/footer.inc.php';
?>
