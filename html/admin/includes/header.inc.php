<?php

require_once __DIR__ . '/navbar.inc.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" href="../images/favicon.ico" type="image/x-icon">
<link rel="stylesheet" type="text/css"
	href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="../vendor/fortawesome/font-awesome/css/all.min.css" type="text/css" />
<script src='../vendor/components/jquery/jquery.min.js' type='text/javascript'></script>
<script src='../vendor/twbs/bootstrap/dist/js/bootstrap.min.js' type='text/javascript'></script>
<script src='../includes/main.inc.js' type='text/javascript'></script>
<title>Network Information Database Admin - <?php settings::get_title(); ?></title>

</head>

<body style='padding-top: 70px; padding-bottom: 60px;'>
<nav class="navbar fixed-top navbar-dark bg-dark">
        <a class='navbar-brand py-0' href='#'><img src='../images/igb_small.png'>Network Information Database Admin - <?php echo settings::get_title(); ?></a>
	<span class='navbar-text py-0'><a class='btn btn-sm btn-primary' href='../index.php'>User Section</a> Version <?php echo settings::get_version(); ?>&nbsp;</span>

</nav>


<div class='container-fluid'>
	<div class='row'>
		<div class='col-md-2 col-lg-2 col-xl-2'>
			<div class='sidebar-nav'>
				<ul class='nav flex-column'>
				<li class='nav-item'><a class='nav-link' href='index.php'>Home</a></li>
					<span class="border-top my-2"></span>
					<li class='nav-item'><a class='nav-link' href='locations.php'>Locations</a></li>
					<li class='nav-item'><a class='nav-link' href='../logout.php'>Logout</a></li>
				</ul>
				
			</div>
		</div>
		<div class='col-md-10 col-lg-10 col-xl-10'>
