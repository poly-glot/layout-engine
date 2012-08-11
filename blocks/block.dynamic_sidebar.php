<?php
/**
 * Dynamic sidebar management
 *
 * @package WordPress
 * @subpackage Layout_Manager
 * @since 1.0.0.0
 */

//Register
add_filter('le_layout_block_objects', array('LE_Sidebar','register'));

//Load user defined sidebars into wordpress
add_action( 'init', array('LE_Sidebar','runtime_sidebar_creation'));

//Ajax handler
add_action( 'wp_ajax_layout_manager_blockitem_sidebar_save', array('LE_Sidebar','layout_manager_blockitem_sidebar_save'));

//Add additional data on export
add_action('le_export', array('LE_Sidebar','export'), 10);

//Import settings
add_action('le_import', array('LE_Sidebar','import'), 10);

class LE_Sidebar
{
	/**
	 * Register dynamic sidebar object into drag and drop element area.
	 *
	 * @access public
	 * @since 1.0.0.0
	 */	
	public static function register($objects)
	{
		$objects['sidebar'] = array
		(
				'name' => __('Sidebar','layout-engine'),
				'callback' => array('LE_Sidebar','render'),
				'callback_frontend' => array('LE_Sidebar','render_frontend')
		);
			
		return $objects;
	}
	
	/**
	 * Show Dynamic sidebar form
	 *
	 * @access public
	 * @since 1.0.0.0
	 */	
	public static function render()
	{
				global $wp_registered_sidebars;
				
				$sidebars = LE_Sidebar::getSidebars();
				?>
					<div class="layout_engine_form_widget">
						<div class="updated hidden" id="message"></div>
								
						<form method="post" id="sidebar_form">
									<div id="delete_confirm" class="hidden"><p><?php _e('Are you sure you want to remove this sidebar, this step cannot be undone?','layout-engine'); ?> <a href="#" id="confirm_yes"><?php _e('Yes','layout-engine'); ?></a> <a href="#" id="confirm_no"><?php _e('No','layout-engine'); ?></a>.</p></div>
									<ul class="note">
										<li><?php _e('Select an existing sidebar by clicking title name or define a new using the form below.','layout-engine'); ?></li>
										<li><?php _e('You cannot modify sidebars defined in theme.','layout-engine'); ?></li>
									</ul>
									
									<img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" class="ajax-feedback" title="" alt="" />
									<div id="add_new">
											<input type="text" name="name" id="name" class="resetable" />
											<input type="submit" class="button" value="<?php _e('Add','layout-engine'); ?>" id="submitButton">		
											<a href="#" id="advance-options">Advance options</a>	
											
											<div id="add_new_advance">
												<input type="hidden" name="id" id="id" value=""  class="resetable"/>
												<input type="hidden" name="action" id="action" value="layout_manager_blockitem_sidebar_save" />
												<?php wp_nonce_field( 'save-le-layout-block', '_wpnonce_layout_manager_admin', false ); ?>
												<input type="hidden" name="runtime_id" id="runtime_id" value="<?php echo $_REQUEST['runtime_id']; ?>" />
												
												<div class="row">
													<div class="row-50">
														<div class="label"><label for="before_widget">Before widget</label></div>
														<div class="input"><input type="text" name="before_widget" id="before_widget" class="resetable" /></div>
														<div class="description"></div>
													</div>
													
													<div class="row-50">
															<div class="label"><label for="after_widget">After widget</label></div>
															<div class="input"><input type="text" name="after_widget" id="after_widget" class="resetable" /></div>
															<div class="description"></div>
													</div>
												</div>
												
												<div class="row">
													<div class="row-50">
														<div class="label"><label for="before_title">Before title</label></div>
														<div class="input"><input type="text" name="before_title" id="before_title" class="resetable" /></div>
														<div class="description"></div>
													</div>
													
													<div class="row-50">
															<div class="label"><label for="after_title">After title</label></div>
															<div class="input"><input type="text" name="after_title" id="after_title" class="resetable" /></div>
															<div class="description"></div>
													</div>
												</div>	
												
												<div class="row">
													<div class="row-50">
														<div class="label"><label for="description">Description</label></div>
														<div class="input"><input type="text" name="description" id="description" class="resetable" /></div>
														<div class="description"></div>
													</div>
													
													<div class="row-50">
															<div class="label"><label for="class">CSS Class</label></div>
															<div class="input"><input type="text" name="class" id="class"  class="resetable" /></div>
															<div class="description"></div>
													</div>
												</div>																		
												
												<div class="row">
													<p>
														You can find detail of each field on <a href="http://codex.wordpress.org/Function_Reference/register_sidebar" target="_blank">wordpress developer website.</a>
														or <a href="#" id="setup_default_markup">click here to insert default html markup.</a>
													</p>
												</div>
											</div>								
									</div>									
									<div id="widget_list">
										<ul class="sortable">
											<?php foreach ( $wp_registered_sidebars as $sidebar => $registered_sidebar ) : ?>
											<?php if(array_key_exists($sidebar,$sidebars)): ?>
											<li>
													
													<div class="drag-widget"></div>
													<h3><a href="#" rel="<?php echo $registered_sidebar['id']; ?>" class="selectable"><span><?php echo $registered_sidebar['name']; ?></span></a></h3>
													<div class="options">
														<a href="#" class="configure">Modify</a>
														<a href="#" class="delete">x</a>
													</div>
											</li>
											<?php else: ?>
											<li class="uneditable"><h3><a href="#" rel="<?php echo $registered_sidebar['id']; ?>"><span><?php echo $registered_sidebar['name']; ?></span></a></h3></li>
											<?php endif; ?>
											
											<?php endforeach; ?>
										</ul>
									</div>
						</form>
					</div>
				<?php 
	}
	
	/**
	 * Register user defined sidebars.
	 *
	 * @access public
	 * @since 1.0.0.0
	 */	
	public static function runtime_sidebar_creation()
	{
		$sidebars = LE_Sidebar::getSidebars();
	
		foreach($sidebars as $sidebar)
		{
			if(!empty($sidebar['name']))
			{
				register_sidebar($sidebar);
			}
		}
	}
	
	/**
	 * Get user defined sidebars
	 *
	 * @access public
	 * @since 1.0.0.0
	 */	
	public static function getSidebars()
	{
		$sidebars = (array) get_option('layout_manager_sidebars');
		return $sidebars;
	}
	
	/**
	 * Ajax callback to manage dynamic sidebars.
	 *
	 * @access public
	 * @since 1.0.0.0
	 */
	public static function layout_manager_blockitem_sidebar_save()
	{
		check_ajax_referer(  'save-le-layout-block', '_wpnonce_layout_manager_admin' );
		
		$json_output = array();
		
		$sidebars = LE_Sidebar::getSidebars();
		$data = stripslashes_deep(array_diff_key($_POST, array('action'=>'','_wpnonce_layout_manager_admin'=>'','get'=>'','delete'=>'','layout_section'=>'','layout_index','args','runtime_id')));
		
		unset($data['_wpnonce_simpleuxlayout']);
		
		if(array_key_exists("delete", $_POST))
		{
		
			if(!array_key_exists($data['id'], $sidebars))
			{
				$json_output['error'][] = __("You cannot remove sidebar defined in theme.","layout-engine");
			}else{
				unset($sidebars[$data['id']]);
				$json_output['success'][] =  __("Successfully removed the dynamic sidebar.","layout-engine");
			}
		
		}elseif(array_key_exists("get", $_POST))
		{
			$json_output = $sidebars[$data['id']];
		}elseif(array_key_exists("sort", $_POST))
		{
			$new_sidebars_position = array();
			foreach($data['ids'] as $k=>$v)
			{
				if(array_key_exists($v, $sidebars))
				{
					$new_sidebars_position[$v] = $sidebars[$v];
				}
			}
		
			//Update it
			$sidebars = $new_sidebars_position;
			$json_output['success'][] =  __("Successfully updated the order of sidebars.","layout-engine");
		}elseif(array_key_exists("update", $_POST))
		{
			//Runtime Values :: preseve args values
			$settings = layout_engine_layout_settings();
		
			$runtime_id = $_POST['runtime_id'];
			$args = $_POST['args'];
			$data = stripslashes_deep($args);
				
			//Update it
			$widget = LE_Base::getBlockItemByRuntimeID($runtime_id);

			if(!empty($widget))
			{
				$layout = $widget['layout'];
				$layout_section = $widget['section'];
				$layout_index = $widget['pos'];
		
		
				if(isset($settings[$layout][$layout_section][$layout_index]))
				{
					$settings[$layout][$layout_section][$layout_index]['args'] = (array) wp_parse_args($args, $settings[$layout][$layout_section][$layout_index]['args']);
					$json_output['data'] = $settings[$layout][$layout_section][$layout_index];
						
					//Saving
					$settings = apply_filters('layout_engine_layout_settings_save', $settings);
					update_option('layout_engine_layout_settings', $settings);
				}
			}
				
			$json_output['success'][] =  __("Success.","layout-engine");
		}else{
		
			if(!empty($data['name']))
			{
				if(!empty($data['id']))
				{
					$sidebars[$data['id']] = $data;
					$json_output['success'][] =  __("Successfully updated the dynamic sidebar.","layout-engine");
				}else{
						
		
					//Create unique id
					$data['id'] = sanitize_title($data['name']);
		
					if(array_key_exists($data['id'], $sidebars))
					{
						$i = 1;
						while(array_key_exists($data['id'] . "-" .$i, $sidebars))
						{
							$i++;
						}
		
						//Unique ID
						$data['id'] = $data['id'] . "-" .$i;
					}
						
					$sidebars[$data['id']] = $data;
					$json_output['success'][] =  __("Successfully added new dynamic sidebar.","layout-engine");
				}
		
				//Return Newly Added/Edited object as well
				$json_output['data'] = $sidebars[$data['id']];
			}
		}
		
		//Updating options
		update_option('layout_manager_sidebars', $sidebars);
		
		echo json_encode($json_output);
		wp_die();		
	}	
	
	/**
	 * Render a block item
	 *
	 * @since 1.0.0.0
	 *
	 * @param array $block_item
	 * @param string $layout layout id
	 * @param array $block block settings
	 * @return void
	 */
	public static function render_frontend($block_item = array(), $layout = "", $block = array())
	{
		$id = $block_item['args']['id'];
		if(!empty($id))
			dynamic_sidebar($id);
	}	
	
	/**
	 * Export sidebars
	 *
	 * @since 1.0.0.0
	 *
	 * @param ref array $settings
	 * @return void
	 */
	public static function export(&$settings)
	{
		global $wp_registered_sidebars;

		if(!empty($settings))
		{
					foreach($settings as $layout_page => $blocks)
					{
						if(empty($blocks)) continue;
					
						foreach($blocks as $block_name => $widgets)
						{
							foreach($widgets as $k => $widget)
							{
									if($widget['id'] == "sidebar")
									{
										$sidebar_id = $widget['args']['id'];
										if(!empty($sidebar_id))
										{
											//Exporting sidebar settings
											$settings[$layout_page][$block_name][$k]['args'] = $wp_registered_sidebars[$sidebar_id];
											unset($settings[$layout_page][$block_name][$k]['args']['runtime_id']);
										}
									}
							}
						}
					}		
		}
	}	
	
	/**
	 * Import sidebars
	 *
	 * @since 1.0.0.0
	 *
	 * @param ref array $settings
	 * @return void
	 */	
	public static function import(&$settings)
	{
		global $wp_registered_sidebars;
		
		$sidebars_in_database = (array) LE_Sidebar::getSidebars();
		$changed = false;
		
		if(!empty($settings))
		{
			foreach($settings as $layout_page => $blocks)
			{
				if(empty($blocks)) continue;
					
				foreach($blocks as $block_name => $widgets)
				{
					foreach($widgets as $k => $widget)
					{
						//staisfy the creteria to be sidebar
						if($widget['id'] == "sidebar" && !empty($widget['args']['id']))
						{
									//Only register if sidebar is not registered
									if(!array_key_exists($widget['args']['id'], $sidebars_in_database))
									{
										$sidebars_in_database[$widget['args']['id']] = $widget['args'];
										$changed = true;
									}
									
									//Translate settings
									$settings[$layout_page][$block_name][$k]['args'] = array(
																								'id' => $widget['args']['id'],
																								'title' => $widget['args']['name']
																							);
						}
						
					} // end of loop on block items
				} //end of loop on blocks
			}//end of loop on layouts
		}

		if($changed)
		{
			update_option('layout_manager_sidebars', $sidebars_in_database);
		}
	}
}


?>