</div>
</div>
</div>
<br>
<?php 

if (isset($nav_js)) {
	echo $nav_js;
}

if (settings::get_debug()) {
echo "<div class='alert alert-danger'>DEBUG MODE ENABLED</div>";

}

?>

<footer class='footer'>
<div class='container'>
	<p class='text-center'>
	<span class='text-muted'>
		<br><em><a href='https://www.vpaa.uillinois.edu/resources/web_privacy' target='_blank'> University of Illinois System Web Privacy Notice</a></em>
                <br><em>&copy;<?php echo date('Y') . "&nbsp;" . FOOTER; ?></em>
        </span>
	</p>

</div>
</footer>

</body>
</html>
