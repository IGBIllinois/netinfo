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
				<div class='span6 brand'>
					<?php echo __TITLE__; ?>
				</div>
				<div class='span2 pull-right'>
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

						<li><a href='index.php'>All Networks</a></li>
						<li><a href='index.php?network=128.174.124.0/22'>IGB Public - 128.174.124.0/22</a></li>
						<li><a href='index.php?network=128.174.50.0/24'>ICYT Public - 128.174.50.0/24</a></li>
						<li><a href='index.php?network=172.22.87.0/24'>IGB Switches - 172.22.87.0/22</a></li>
						<li><a href='logout.php'>Logout</a></li>
					</ul>
					
				</div>
			</div>
			<div class='span10'>
