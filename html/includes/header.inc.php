<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
<link rel="stylesheet" type="text/css"
	href="vendor/components/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css"
href="vendor/components/bootstrap/css/bootstrap-responsive.css">
<script src='vendor/components/jquery/jquery.min.js' type='text/javascript'></script>
<script src='vendor/components/bootstrap/js/bootstrap.min.js' type='text/javascript'></script>
<script src='includes/main.inc.js' type='text/javascript'></script>

<title><?php echo __TITLE__; ?></title>
</head>

<body>
	<div class='navbar navbar-inverse'>
		<div class='navbar-inner'>
			<div class='container'>
				<div class='span9 brand'>
					<?php echo __TITLE__; ?>
				</div>
				<div class='span3 pull-right'>
					<p class='navbar-text pull-right'>
					<small>Version <?php echo __VERSION__; ?></small>
					</p>
				</div>
			</div>
		</div>
	</div>

	<div class='container-fluid'>
		<div class='row-fluid'>
			<div class='span3 well'>
				<div class='sidebar-nav'>
					<ul class='nav nav-list'>
					<li><a href='index.php'>Home</a></li>
					<li class='divider'></li>
					<?php echo $nav_html; ?>
						<li class='divider'></li>
						<li><a href='hardware.php'>Mac Addresses</a></li>
						<li><a href='networks.php'>Networks</a></li>
						<li><a href='domains.php'>Domains</a></li>
						<li class='divider'></li>
						<li><a href='logout.php'>Logout</a></li>
					</ul>
					
				</div>
			</div>
			<div class='span9'>
