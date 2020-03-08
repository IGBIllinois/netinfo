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

require_once 'includes/main.inc.php';

$message = "";
if (isset($_POST['login'])) {

	$username = trim(rtrim($_POST['username']));
	$password = $_POST['password'];

	$error = false;
	if ($username == "") {
		$error = true;
		$message = html::alert("Please enter your username",false);
	}
	if ($password == "") {
		$error = true;
		$message .= html::alert("Please enter your password",false);
	}
	if ($error == false) {
		$ldap = new ldap(__LDAP_HOST__,__LDAP_SSL__,__LDAP_PORT__,__LDAP_BASE_DN__);
		$success = functions::authenticate($ldap,$username,$password,__LDAP_GROUP__);
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
			$message = html::alert("Invalid Username or Password",false);
		}
	}
}



?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset='utf-8'>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css"
	href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="vendor/fortawesome/font-awesome/css/all.min.css" type="text/css" />
<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
<title>Network Information Database - <?php echo __TITLE__; ?></title>
</head>
<body style='padding-top: 70px; padding-bottom: 60px;' OnLoad="document.login.username.focus();">

<nav class="navbar fixed-top navbar-dark bg-dark">
        <a class='navbar-brand py-0' href='#'>Network Information Database - <?php echo __TITLE__;  ?></a>
	<span class='navbar-text py-0'>Version <?php echo __VERSION__; ?>&nbsp;
	</span>

</nav>


<div class='container'>
<div class='col-md-6 col-lg-6 col-xl-6 offset-md-3 offset-lg-3 offset-xl-3'>
<form class='form-signin' action='<?php echo $_SERVER['PHP_SELF']; ?>' method='post' name='login'>
	<div class='form-group row'>
		<label for='username' class='col-form-label'>Username</label>
			<div class='input-group'> 
			<input class='form-control' type='text'
				name='username' tabindex='1' placeholder='Username'
				value='<?php if (isset($username)) { echo $username; } ?>'>
			<div class="input-group-append">
			<span class='input-group-text'> <span class='fa fa-user'></span></span>
			</div>
			</div>
	</div>
	<div class='form-group row'>
		<label for='password' class='col-form-label'>Password</label>
			<div class='input-group'>
			<input class='form-control' type='password' name='password' 
			placeholder='Password' tabindex='2'>		
			<div class='input-group-append'>
				<span class='input-group-text'><span class='fa fa-lock'></span></span>
			</div>
			</div>

	</div>
	<div class='form-group row'>
		<button type='submit' name='login' class='btn btn-primary'>Login</button>
	</div>
</form>
<?php if (isset($message)) { echo $message; } ?>
</div>
<?php require_once 'includes/footer.inc.php'; ?>
