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

include_once 'includes/main.inc.php';
include_once 'authenticate.inc.php';


$message = "";

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
		$success = authenticate($ldap,$username,$password,__LDAP_GROUP__);
		if ($success == true) {
			$session = new session(__SESSION_NAME__);
                        $session_vars = array('login'=>true,
                                        'username'=>$username,
                                        'timeout'=>time()
                                        );
                        $session->set_session($session_vars);
			if ($session->get_var("webpage")) {
				$webpage = $session->get_var("webpage");
			}
			else {
				$webpage = "index.php";
			}
			header("Location: " . $webpage);
		}
		else {
			$message .= "<div class='alert alert-error'>Invalid username or password.  Please try again. </div>";
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
<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
<title><?php echo __TITLE__; ?></title>
</head>
<body OnLoad="document.login.username.focus();">
	<div class='navbar navbar-inverse'>
		<div class='navbar-inner'>
			<div class='container'>
				<a class="btn btn-navbar" data-toggle="collapse"
					data-target=".nav-collapse"></a> <a class="brand" href="#"><?php echo __TITLE__; ?></a>
			</div>
		</div>
	</div>
	<p>
	
	
	<div class='container'>
		<div class='row'>
			<div class='span5 offset3'>
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

<?php include_once 'includes/footer.inc.php'; ?>
