<?php require_once __DIR__ . '/navbar.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
<link rel="stylesheet" type="text/css" href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="vendor/fortawesome/font-awesome/css/all.min.css" type="text/css" />
<script src='vendor/components/jquery/jquery.min.js' type='text/javascript'></script>
<script src='vendor/twbs/bootstrap/dist/js/bootstrap.js' type='text/javascript'></script>
<script src='vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js' type='text/javascript'></script>
<script src='includes/main.inc.js' type='text/javascript'></script>
<title>Network Information Database - <?php echo settings::get_title(); ?></title>

</head>

<body style='padding-top: 70px; padding-bottom: 60px;'>
<?php require_once __DIR__ . '/about.inc.php'; ?>

<nav class="navbar fixed-top navbar-dark bg-dark">
        <a class='navbar-brand py-0' href='#'><img src='images/igb_small.png'>Network Information Database - <?php echo settings::get_title(); ?></a>
	<span class='navbar-text py-0'>
		<button type="button" class="btn btn-sm btn-secondary" data-toggle="modal" data-target="#aboutModal"><i class='fas fa-info-circle'></i> About</button>
		<a class='btn btn-sm btn-danger' href='admin/index.php' onClick='return confirm_admin();'><i class='fas fa-lock'></i> Admin</a>
		<a class='btn btn-sm btn-warning' href='logout.php'><i class='fas fa-sign-out-alt'></i> Logout</a>
	</span>
</nav>


<div class='container-fluid'>
	<div class='row'>
		<div class='col-md-2 col-lg-2 col-xl-2'>
			<div class='sidebar-nav'>
				<ul class='nav flex-column'>
				<li class='nav-item'><a class='nav-link' href='index.php'>Home</a></li>
				<span class="border-top my-2"></span>
				<?php echo $nav_html; ?>
					<span class="border-top my-2"></span>
					<li class='nav-item'><a class='nav-link' href='networks.php'>Networks</a></li>
					<li class='nav-item'><a class='nav-link' href='domains.php'>Domains</a></li>
					<li class='nav-item'><a class='nav-link' href='devicetype.php'>Device Types</a></li>
					<li class='nav-item'><a class='nav-link' href='locations.php'>Locations</a></li>
					<li class='nav-item'><a class='nav-link' href='statistics.php'>Statistics</a></li>
					<li class='nav-item'><a class='nav-link' href='log.php'>View Log</a></li>
				</ul>
				
			</div>
		</div>
		<div class='col-md-10 col-lg-10 col-xl-10'>

