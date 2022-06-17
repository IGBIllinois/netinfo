<div class='modal fade' id='macModal' tabindex='-1' role='dialog' aria-labelledby="macModalLabel" aria-hidden="true">
	<div class='modal-dialog modal-lg' role='document'>
	<div class='modal-content'>
	<div class='modal-header'>
		<h5 class='modal-title'>Hardware (MAC) Address Formats</h5>
		<button type="button" class="close" data-dismiss="modal" aria-label="Close">
		<span aria-hidden="true">&times;</span>
	</div>

<div class='modal-body'>
 <table class='table table-bordered table-sm'>
                <tr>
                        <td><?php echo $device->get_hardware(); ?>&nbsp;</td>
                        <td><?php echo $device->get_hardware(true); ?>&nbsp;</td>
                </tr>
                <tr>
                        <td><?php echo $device->get_hardware_cisco(); ?>&nbsp;</td>
                        <td><?php echo $device->get_hardware_cisco(true); ?>&nbsp;</td>
                </tr>
                <tr>
                        <td><?php echo $device->get_hardware_dashes(); ?>&nbsp;</td>
                        <td><?php echo $device->get_hardware_dashes(true); ?>&nbsp;</td>
                </tr>
                <tr>
                        <td><?php echo $device->get_hardware_colon(); ?>&nbsp;</td>
                        <td><?php echo $device->get_hardware_colon(true); ?>&nbsp;</td>
                </tr>
	</table>

</div>

</div>
</div>
</div>
