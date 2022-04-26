<?php
require_once '../includes/main.inc.php';
require_once '../includes/session.inc.php';
require_once 'includes/header.inc.php';

$error = false;
if (isset($_POST['upload_csv'])) {
	$file_name = $_FILES['iris_csv']['name'];
	$file_size =$_FILES['iris_csv']['size'];
	$file_tmp =$_FILES['iris_csv']['tmp_name'];
	$file_error = $_FILES['iris_csv']['error'];
	$file_type=$_FILES['iris_csv']['type'];
	$file_ext_array = explode('.',$_FILES['iris_csv']['name']);
	$file_ext = end($file_ext_array);
	$file_ext=strtolower($file_ext);
	if ($file_error) {
		$error = true;
		$message = html::alert("Error Uploading File: " . \IGBIllinois\Helper\functions::get_php_upload_error($file_error),0);
	}

	elseif ($file_ext != location::get_iris_filetype()) {
		$error = true;
		$message = html::alert("Invalid File Type.  Only " . location::get_iris_filetype() . " is allowed",0);
	}

	else {
		$result = location::import_iris($db,$file_tmp);
		$log->send_log("User " . $session->get_var('username') . ": Imported locations from file " . $file_name . ". " . $result . " Locations imported");
		$message = html::alert("Imported " . $file_name . " successfully. " . $result . " Locations imported",1);
	}
	
}

?>
<h4>Locations</h4>

<h5>Upload IRIS Location CSV</h5>
<form method='post' action='<?php echo $_SERVER['PHP_SELF']; ?>' enctype='multipart/form-data'>
<input type='hidden' name='MAX_FILE_SIZE' value='<?php echo \IGBIllinois\Helper\functions::get_max_upload(); ?>'>

	<div class='form-group'>
		<label for='iris_csv'>IRIS CSV: Max File Size: <?php echo ini_get('upload_max_filesize'); ?></label>
		<input type='file' class='form-control-file' name='iris_csv'>
	</div>
	<input type='submit' class='btn btn-primary' value='Upload' name='upload_csv'>

</form>
<?php

if (isset($message)) {
	echo $message;
}
require_once 'includes/footer.inc.php';
?>
