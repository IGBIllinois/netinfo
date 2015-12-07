<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script src='includes/main.inc.js' type='text/javascript'></script>
<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
<link rel="stylesheet" type="text/css"
	href="includes/bootstrap/css/bootstrap.min.css">
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
					<li><a href='index.php'>All Networks</a></li>
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
