<?php	
	//Grab the settings
	$settings = LE_Base::getSettings();
	$layout = $_REQUEST['id'];
	
?>
<div id="instruction">
	<p>
	<?php 
		$back_link = remove_query_arg('id');
		echo sprintf(__('Empty block(s) will be filled automically by parent template. <a href="%s">Click here to go back.</a>','layout-engine'), $back_link);
	?>
	</p>
</div>	
	
<div id="wp_layout_manager_admin">
	<form action="" method="post">
	<input type="hidden" name="action" id="action" value="layout_manager_ajax_savelayout" />
	<?php wp_nonce_field( 'save-le-layout-admin', '_wpnonce_layout_manager_admin', false ); ?>
							
								<input type="hidden" name="layout" id="layout" value="<?php echo $layout; ?>" />
								<div class="widget-liquid-left">
								<div id="widgets-left">

                                                		<?php 
                                                				
                                                				$blocks = LE_Base::getBlocks();
                                                				foreach($blocks as $k=>$block):
                                                				
                                                				$layout_block_id = $block['id'];
                                                		?>
                                                			<div class="widgets-holder-wrap orphan-sidebar" rel="<?php echo $layout_block_id; ?>">
                                                				<div class="sidebar-name">
                                                					<div class="sidebar-name-arrow"><br /></div>
                                                					<div class="sidebar-feedback"><img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" class="ajax-feedback" title="" alt="" /></div>
                                                					<h3><?php _e($block['name'], 'layout-engine'); ?></h3>
                                                				</div>
                                                				
                                                				<div class="widgets-sortables-container">
			                                                						 <div id="<?php echo $layout_block_id; ?>" class="widgets-sortables">
			                                                						 		<?php 
			                                                						 				//Display Existing widgets
			                                                						 				$widgets = (array) $settings[$layout][$layout_block_id];
			                                                						 				
			                                                						 				foreach($widgets as $pos=>$w):
			                                                						 				
			                                                						 				$columns = intval($w['columns']);
			                                                						 				if($columns == 0) $columns = 1;   
			                                   														
			                                                						 				$div_id = "widget-".$layout_block_id."-".$pos;
			                                                						 				
			                                                						 				$configure_link = admin_url(sprintf('admin.php?action=layout_manager_blockitem_form&widget=%s', $w['id']));
			                                                						 				
			                                                						 				$title = "";
			                                                						 				if(!empty($w['args']) && is_array($w['args']))
			                                                						 				{
			                                                						 					$title = $w['args']['title'];
			                                                						 				}
			                                                						 		?>
			                                                						 			
			                                                                                                                        <div id="<?php echo $div_id; ?>" class="widget column-<?php echo $columns; ?>" data-columns="<?php echo $columns; ?>">	
			                                                                                                                                <div class="widget-top">
			                                                                                                                                            <div class="widget-title-action">
			                                                                                                                                            	<a class="widget-action-configure" href="<?php echo $configure_link; ?>">Configure</a>
			                                                                                                                                                <a class="widget-action-resize hide-if-no-js" href="#"></a>
			                                                                                                                                                <a class="widget-action-resize widget-action-resize-right" href="#"></a>
			                                                                                                                                            </div>
			                                                                                                                                            <div class="widget-title"><h4><?php echo $w['name']; ?> <span class="in-widget-title"><?php echo $title; ?></span></h4></div>
			                                                                                                                                </div>
			                                                                                                                        
			                                                                                                                                <div class="widget-inside"></div>		
			                                                                                                                                <div class="widget-description"></div>
			                                                                                                                                <div class="widget-form">
			                                                                                                                                	<input type="hidden" name="id" value="<?php echo $w['id']; ?>" class="widget-id" />
			                                                                                                                                	<input type="hidden" name="name" value="<?php echo $w['name']; ?>" class="widget-name" />
			                                                                                                                                	<input type="hidden" name="title" value="<?php echo $w['title']; ?>" class="widget-title" />
			                                                                                                                                	<input type="hidden" name="columns" value="<?php echo $w['columns']; ?>" class="widget-columns" />     
			                                                                                                                                	<input type="hidden" name="runtime_id" value="<?php echo $w['runtime_id']; ?>" class="widget-runtime-id" />                                                                                                            	
			                                                                                                                                </div>
			                                                                                                                                
			                                                                                                                          </div>                                                    						 			
			                                                						 			
			                                                						 		<?php	
			                                                						 				endforeach;
			                                                						 		?>
			                                                						 <br class="clear">
			                                                						 </div><!-- .widgets-sortables -->
                                                				</div>                                                				
                                                			</div>
                                                		<?php endforeach; ?>



                               </div>                
							</div><!-- left -->
                            
                            
							<div class="widget-liquid-right">
								<div id="widgets-right">
								
								<?php LE_Admin::layout_preview_button(); ?>
								
								<div id="available-widgets" class="widgets-holder-wrap">
									<div class="sidebar-name">
									<div class="sidebar-name-arrow"><br /></div>
									<h3><?php _e('Available Objects', 'layout-engine'); ?> <span id="removing-widget"><?php _ex('Deactivate', 'removing-widget'); ?> <span></span></span></h3></div>
									<div class="widget-holder">
									<p class="description"><?php _e('Drag objects from here to a sidebar on the left to activate them. Drag object back here to deactivate them and delete their settings.', 'layout-engine'); ?></p>
									<div id="widget-list">
					
														                    <?php 
					                                                				$objects = LE_BASE::getBlockObjects();
					                                                				foreach($objects as $k=>$v):
					                                                				
					                                                				$configure_link = admin_url(sprintf('admin.php?action=layout_manager_blockitem_form&widget=%s', $k));
					                                                		?>
			                                                                    
			                                                                    <div id="object-<?php echo $k; ?>" class="widget widget-new">	
			                                                                                <div class="widget-top">
			                                                                                                    <div class="widget-title-action">
                                                                                                                         <a class="widget-action-configure" href="<?php echo $configure_link; ?>">Configure</a>
                                                                                                                         <a class="widget-action-resize" href="#"></a>
                                                                                                                         <a class="widget-action-resize widget-action-resize-right" href="#"></a>	                                                                                                   
			                                                                                                     </div>
			                                                                                                    <div class="widget-title"><h4><?php echo $v['name']; ?><span class="in-widget-title"><?php echo $v['title']; ?></span></h4></div>
			                                                                                    </div>
			                    														
			                                                                                    <div class="widget-inside"></div>
			                                                                                    <div class="widget-description"></div>
                                                                                                
                                                                                                <div class="widget-form hidden">
                                                                                                         <input type="hidden" name="id" value="<?php echo $k; ?>" class="widget-id" />
                                                                                                         <input type="hidden" name="name" value="<?php echo $v['name']; ?>" class="widget-name" />
                                                                                                         <input type="hidden" name="title" value="<?php echo $v['title']; ?>" class="widget-title" />
                                                                                                         <input type="hidden" name="columns" value="1" class="widget-columns" />
                                                                                                         <input type="hidden" name="runtime_id" value="" class="widget-runtime-id" />
                                                                                                </div>			                                                                                    
			                                                                                    
			                                                                    </div> 	
			                                                               <?php endforeach; ?>											
										
									</div>
									<br class='clear' />
									</div>
									<br class="clear" />
								</div>
							
							
							</div>
							</div><!-- right -->                                 
							
							<br class="clear" />
	</form>
</div>