<div class='modal fade' id='aboutModal' tabindex='-1' role='dialog' aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class='modal-dialog modal-lg' role='document'>
	<div class='modal-content'>
	<div class='modal-header'>
		<h5 class='modal-title'>About</h5>
		<button type="button" class="close" data-dismiss="modal" aria-label="Close">
		<span aria-hidden="true">&times;</span>
	</div>

<div class='modal-body'>
	<table class='table table-bordered table-sm'>
		<tbody>
		<tr><td>Code Website</td></td><td><a href='<?php echo settings::get_website_url(); ?>' target='_blank'><?php echo settings::get_website_url(); ?></a></td></tr>
		<tr><td>App Version</td><td><?php echo settings::get_version(); ?></td></tr>
		<tr><td>Webserver Version</td><td><?php echo \IGBIllinois\Helper\functions::get_webserver_version(); ?></td></tr>
		<tr><td>MySQL Version</td><td><?php echo $db->get_version(); ?></td>
		<tr><td>DHCPD Version</td><td><?php echo functions::get_dhcpd_version(); ?></td></tr>
		<tr><td>BIND Version</td><td><?php echo functions::get_bind_version(); ?></td></tr>
		<tr><td>PHP Version</td><td><?php echo phpversion(); ?></td></tr>
		<tr><td>PHP Extensions</td><td><?php 
			$extensions_string = "";
			foreach (\IGBIllinois\Helper\functions::get_php_extensions() as $row) {
				$extensions_string .= implode(", ",$row) . "<br>";
			}
			echo $extensions_string;
		?></td></tr>
		</tbody>
	</table>

	<table class='table table-bordered table-sm'>
	<thead>
		<tr><th>Setting</th><th>Value</th></tr>
	</thead>
	<tbody>
		<tr><td>ENABLE_LOG</td><td><?php if (settings::log_enabled()) { echo "TRUE"; } else { echo "FALSE"; } ?></td></tr>
		<tr><td>LOG_FILE</td><td><?php echo settings::get_logfile(); ?></td></tr>
		<tr><td>TIMEZONE</td><td><?php echo settings::get_timezone(); ?></td></tr>
		<tr><td>LDAP_HOST</td><td><?php echo LDAP_HOST; ?></td></tr>
		<tr><td>LDAP_BASE_DN</td><td><?php echo LDAP_BASE_DN; ?></td></tr>
		<tr><td>LDAP_GROUP</td><td><?php echo LDAP_GROUP; ?></td></tr>	
		<tr><td>LDAP_SSL</td><td><?php if (LDAP_SSL) { echo "TRUE"; } else { echo "FALSE"; } ?></td></tr>
		<tr><td>LDAP_PORT</td><td><?php echo LDAP_PORT; ?></td></tr>
		<tr><td>MYSQL_HOST</td><td><?php echo MYSQL_HOST; ?></td></tr>
		<tr><td>MYSQL_DATABASE</td><td><?php echo MYSQL_DATABASE; ?></td></tr>
		<tr><td>MYSQL_USER</td><td><?php echo MYSQL_USER; ?></td></tr>
		<tr><td>SESSION_NAME</td><td><?php echo SESSION_NAME; ?></td></tr>
		<tr><td>SESSION_TIMEOUT</td><td><?php echo SESSION_TIMEOUT; ?></td></tr>
	</tbody>
	</table>

</div>

</div>
</div>
</div>
