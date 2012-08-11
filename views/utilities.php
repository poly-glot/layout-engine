<?php if(!empty($_REQUEST['message'])) : ?>
<div id="message" class="updated"><p>
<?php 
	$messages = array(
						__('Your settings has been exported successfully'),
						__('Your settings has been reset successfully'),
						__('Your settings has been restored successfully'),
					 );
	$key = absint($_REQUEST['message']) - 1;
	echo $messages[$key];
	
$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'message' ), $_SERVER['REQUEST_URI'] );
?>
</p></div>
<?php endif; ?>

<div id="le_utilities">
	<ul>
		<li>
				<div class="task_link"><a href="<?php echo admin_url('admin.php?action=le_export'); ?>" target="_blank"><?php _e('Export Layout settings', 'le'); ?></a></div>
				<div class="task_instruction"><?php _e('Developer Utility to allow you to export layout settings in distributable php arrays.', 'le'); ?></div>
		</li>
		<li>
				<div class="task_link"><a href="<?php echo admin_url('admin.php?action=le_reset'); ?>"><?php _e('Reset Layout settings', 'le'); ?></a></div>
				<div class="task_instruction"><?php _e('Remove all layout settings.', 'le'); ?></div>
		</li>		
		<li>
				<div class="task_link"><a href="<?php echo admin_url('admin.php?action=le_reset_undo'); ?>"><?php _e('Recover last reset settings', 'le'); ?></a></div>
				<div class="task_instruction"><?php _e('In case if you want to undo your last layout settings reset.', 'le'); ?></div>
		</li>		
	</ul>
</div>	<!-- template_hierarchy -->