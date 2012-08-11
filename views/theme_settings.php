<div id="layout_engine_settings">
		<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
							<div id="post-body-content">
							<?php 
							$settings = LE_LESSCSS_Admin::getSettings();
							
							if(empty($settings))
							{
										?>
										<div class="error">
											<?php 
												
												_e('Your current theme do not provide any settings.')
											?>
										</div>
										<?php
							}else{
										//Form settings page
										echo '<form method="post" action="'.admin_url('options.php').'">';
										
										settings_errors();
										settings_fields('layout_manager_options');
										do_settings_sections('layout_manager');
										
										submit_button();
										
										echo '</form>';
							}
							?>
							</div><!-- /post-body-content -->
							
							<div id="postbox-container-1" class="postbox-container">
									<?php
		
											do_action('le_help_box');								
											do_meta_boxes('appearance_page_layout_engine', 'side', null); 
									?>
							</div><!-- /post-body-content -->				
							
				</div><!-- /post-body -->
				<br class="clear" />
		</div><!-- /poststuff -->
</div>