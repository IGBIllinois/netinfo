<?php
///////////////////////////////////
//
//	login.php
//
//
//	David Slater
//	May 2009
//
///////////////////////////////////

set_include_path(get_include_path() . ':./libs');
include_once 'includes/settings.inc.php';
function __autoload($class_name) {
        require_once $class_name . '.class.inc.php';
}

$db = new db(__MYSQL_HOST__,__MYSQL_DATABASE__,__MYSQL_USER__,__MYSQL_PASSWORD__);
session_start();

$message = "";
if (isset($_SESSION['webpage'])) {
	$webpage = $_SESSION['webpage'];
}
else {
	$dir = dirname($_SERVER['PHP_SELF']);

	$webpage = $dir . "/index.php";
}

if (isset($_POST['login'])) {

	$username = trim(rtrim($_POST['username']));
	$password = $_POST['password'];

	$error = false;
	if ($username == "") {
		$error = true;
		$message .= "<div class='alert'>Please enter your username.</div>";
	}
	if ($password == "") {
		$error = true;
		$message .= "<div class='alert'>Please enter your password.</div>";
	}
	if ($error == false) {
		$ldap = new ldap(__LDAP_HOST__,__LDAP_SSL__,__LDAP_PORT__,__LDAP_BASE_DN__);
		$ldap->set_ldap_people_ou(__LDAP_PEOPLE_OU__);
		$ldap->set_ldap_group_ou(__LDAP_GROUP_OU__);
		$session = new session($db,$ldap,$username,$password,__LDAP_GROUP__);
		$success = $session->authenticate();
		if ($success == true) {

			session_destroy();
			session_start();
			$_SESSION['login'] = 1;
			$_SESSION['username'] = $username;
			$_SESSION['password'] = $password;
			$location = "http://" . $_SERVER['SERVER_NAME'] . $webpage;
			header("Location: " . $location);
		}
		else {
			$message .= "<div class='alert'>Invalid username or password.  Please try again. </div>";
		}
	}
}



?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css"
	href="includes/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css"
	href="includes/bootstrap/css/bootstrap-responsive.min.css">
<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
<title><?php echo __TITLE__; ?></title>
</head>
<body OnLoad="document.login.username.focus();">
	<div class='navbar navbar-inverse'>
		<div class='navbar-inner'>
			<div class='container'>
				<a class="btn btn-navbar" data-toggle="collapse"
					data-target=".nav-collapse"></a> <a class="brand" href="#"><?php echo __TITLE__; ?>
				</a>
			</div>
		</div>
	</div>
	<p>
	
	
	<div class='container-fluid'>
		<div class='row'>
			<div class='span6 offset4'>

				<form action='login.php' method='post' name='login'
					class='form-vertical'>
					<label>Username:</label> <input class='span3' type='text'
						name='username' tabindex='1'
						value='<?php if (isset($username)) { echo $username; } ?>'> <label>Password:</label>
					<input class='span3' type='password' name='password' tabindex='2'>
					<br>
					<button type='submit' name='login' class='btn btn-primary'>Login</button>

				</form>


				<?php if (isset($message)) { 
					echo $message;
} ?>

				<em>&copy 2012 University of Illinois Board of Trustees</em>
			</div>
		</div>
	</div>
