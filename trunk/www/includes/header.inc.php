<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script src='includes/main.inc.js' type='text/javascript'></script>
<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
<link rel="stylesheet" type="text/css"
	href="includes/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css"
	href="includes/bootstrap/css/bootstrap-responsive.min.css">
<title><?php echo __TITLE__; ?></title>

<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
	<div class='navbar navbar-inverse'>
		<div class='navbar-inner'>
			<div class='container'>
				<div class='span8 brand'>
					<?php echo __TITLE__; ?>
				</div>
				<div class='span2 offset7'>
					<p class='navbar-text pull-right'>
					<small>Version <?php echo __VERSION__; ?></small>
					</p>
				</div>
			</div>
		</div>
	</div>

	<div class='container-fluid'>
		<div class='row-fluid'>
			<div class='span2'>
				<div class='sidebar-nav'>
					<ul class='nav nav-tabs nav-stacked'>

						<li><a href='index.php'>Main</a></li>
						<li><a href='index.php?network=128.174.124.0/22'>128.174.124.0/22</a></li>
						<li><a href='index.php?network=128.174.50.0/24'>128.174.50.0/24</a></li>
						<li><a href='index.php?network=172.22.87.0/24'>172.22.87.0/22</a></li>
						<li><a href='locations.php'>Locations</a></li>
						<li><a href='add_locations.php'>Add Locations</a></li>
						<li><a href='switches.php'>Network Switches</a></li>
						<li><a href='logout.php'>Logout</a></li>
					</ul>
					
				</div>
			</div>
			<div class='span10'>
