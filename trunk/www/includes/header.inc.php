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
			<div class='span3 well'>
				<div class='sidebar-nav'>
					<ul class='nav nav-list'>

						<li><a href='index.php'>All Networks</a></li>
						<li class='divider'></li>
					<li class='nav-header'>IGB Public - 128.174.124.0/22</li>
						<li><a href='index.php?network=128.174.124.0/22'>All Devices</a></li>
						<li><a href='index.php?network=128.174.124.1-128.174.124.100'>Servers</a></li>
						<li><a href='index.php?network=128.174.124.1-128.174.124.100&search=spare&exact=1'>Spare Server IPs</a></li>
						<li><a href='index.php?network=128.174.124.101-128.174.127.255'>All User Devices</a></li>
						<li><a href='index.php?network=128.174.124.101-128.174.127.255&search=spare&exact=1'>Spare User IPs</a></li>
						<li><a href='index.php?network=128.174.124.0/22&start_date=<?php echo date('Y:m:d',strtotime("-6 month",time())); ?>
								&end_date=0'>Older Than 6 Months Devices</a></li>
						<li><a href='index.php?network=128.174.124.0/22&start_date=0&end_date=0'>Never Seen Devices</a></li>
					<li class='nav-header'>ICTY Public - 128.174.50.0/24</a></li>	
						<li><a href='index.php?network=128.174.50.0/24'>All Devices</a></li>
					<li class='nav-header'>IGB Switches - 172.22.87.0/22</a></li>
						<li><a href='index.php?network=172.22.87.0/24'>All Devices</a></li>
						<li class='divider'></li>
						<li><a href='logout.php'>Logout</a></li>
					</ul>
					
				</div>
			</div>
			<div class='span9'>
