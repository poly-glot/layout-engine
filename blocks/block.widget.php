<?php
/**
 * Wordpress widgets management.
 *
 * @package WordPress
 * @subpackage Layout_Manager
 * @since 1.0.0.0
 */

//Register
add_filter('le_layout_block_objects', array('LE_Widget','register'));

//Ajax handler
add_action( 'wp_ajax_layout_manager_blockitem_widget_save', array('LE_Widget','layout_manager_blockitem_widget_save'));

//Add additional data on export
add_action('le_export', array('LE_Widget','export'), 20);

//Import settings
add_action('le_import', array('LE_Widget','import'), 20);

class LE_Widget
{
	
	/**
	 * Default widget runtime sidebar argument.
	 *
	 * @since 1.0.0.0
	 * @access public
	 * @var array
	 */	
	public static $runtime_sidebar = array(
											'name' => 'Runtime Sidebar',
											'id' => 'runtime_sidebar',
											'description' => '',
											'class' => '',
											'before_widget' => '<div class="widget">',
											'after_widget' => '</div>',
											'before_title' => '<h3 class="widget-title">',
											'after_title' => '</h3>'
										);
	
	/**
	 * Register widget object into drag and drop element area.
	 *
	 * @access public
	 * @since 1.0.0.0
	 */	
	public static function register($objects)
	{
		$objects['widget'] = array
		(
				'name' => __('Widget','layout-engine'),
				'callback' => array('LE_Widget','render'),
				'callback_frontend' => array('LE_Widget','render_frontend')
		);
			
		return $objects;
	}
	
	/**
	 * Show list of widgets and their management form.
	 *
	 * @access public
	 * @since 1.0.0.0
	 */	
	public static function render()
	{
		global $wp_registered_widgets, $sidebars_widgets, $wp_registered_sidebars, $wp_registered_widget_updates;
		
		//Including Widget API
		if(!function_exists('_sort_name_callback'))
		{
			require_once(ABSPATH . 'wp-admin/includes/widgets.php');
		}
		
		$sort = $wp_registered_widgets;
		usort( $sort, '_sort_name_callback' );
		
		$sidebars_widgets_by_id_base = array();
		foreach($sidebars_widgets as $sidebar=>$sidebar_widgets)
		{
			foreach($sidebar_widgets as $widget_id)
			{
				$sidebars_widgets_by_id_base[$widget_id] = $sidebar;
			}
		}
		
		$available_widgets_instances = array();
		$available_widgets = array();
		foreach ( $sort as $widget )
		{
			$id = $widget['id'];
		
		
			if(isset($widget['callback']) && is_object($widget['callback'][0]) && isset($widget['callback'][0]->id_base))
			{
				$id = $widget['callback'][0]->id_base;
			}
				
			$available_widgets[$id] = array($widget['id'], $widget['name']);
			$available_widgets_instances[$id][] = $widget;
		}
		
		
		//Preselected widget
		$wordpress_widget_id_base = "";
		$wordpress_widget_id = "";
		
		
		if(isset($_GET['wordpress_widget_id_base'])) $wordpress_widget_id_base = $_GET['wordpress_widget_id_base'];
		if(isset($_GET['wordpress_widget_id'])) $wordpress_widget_id = $_GET['wordpress_widget_id'];
		
		$widget = LE_Base::getBlockItemByRuntimeID($_GET['runtime_id']);
		if(!empty($widget))
		{
			if(isset($widget['args']) && !empty($widget['args']['id']))
			{
				if(empty($wordpress_widget_id_base))
					$wordpress_widget_id_base = substr($widget['args']['id'], 0 , strrpos($widget['args']['id'],'-'));
					
				if(empty($wordpress_widget_id))
					$wordpress_widget_id = $widget['args']['id'];
			}
		}
		
		//Add New widget support
		if(isset($wp_registered_widget_updates[$wordpress_widget_id_base]['callback']) && is_callable($wp_registered_widget_updates[$wordpress_widget_id_base]['callback']))
		{
			$widget_new = &$wp_registered_widget_updates[$wordpress_widget_id_base]['callback'][0];

			$widget_new_args = array(
										'action' =>	'save-widget',
										'add_new' => 'multi',
										'id_base' => $widget_new->id_base,
										'multi_number' =>	$widget_new->number + 1,
										'savewidgets' => wp_nonce_field( 'save-sidebar-widgets', 'savewidgets', false ),
										'sidebar' => 'inactive',
										'widget-height' => 200,
										'widget-width' => 200,
										'widget-id' => $widget_new->id,
										'widget_number' => $widget_new->number
									);
			
		}
		
		
		
		?>
				<div class="layout_engine_form_widget" id="dynamic_wordpress_widget">
					<div class="updated hidden" id="message"></div>
											
											<?php 
											
											//Proxy form for add new widget into dyanmic sidebar
											if(isset($widget_new_args) && !empty($widget_new_args)) : ?>
											<form method="post" id="widget_add_new_form">
												<?php 
														foreach($widget_new_args as $k=>$v) : 
															
															if($k == "savewidgets")
																echo "\t".$v.PHP_EOL;
															else 	
																echo "\t".'<input type="hidden" name="'.$k.'" value="'.$v.'" />'.PHP_EOL;
																
														endforeach;
												?>
											</form>
											<?php endif; ?>
											
											<form method="post" id="sidebar_form">
																	<input type="hidden" name="id" id="id" value=""  class="resetable"/>
																	<input type="hidden" name="action" id="action" value="layout_manager_blockitem_widget_save" />
																	<?php wp_nonce_field( 'save-le-layout-block', '_wpnonce_layout_manager_admin', false ); ?>
																	<input type="hidden" name="runtime_id" id="runtime_id" value="<?php echo $_REQUEST['runtime_id']; ?>" />			
											</form>		
															
											<div id="delete_confirm" class="hidden"><p><?php _e('Are you sure you want to remove this widget, this step cannot be undone?','layout-engine'); ?> <a href="#" id="confirm_yes"><?php _e('Yes','layout-engine'); ?></a> <a href="#" id="confirm_no"><?php _e('No','layout-engine'); ?></a>.</p></div>
											
											<ul class="note">
												<li><?php _e('Select an existing widget by clicking name at left hand side and then pick the instance at right hand side.','layout-engine'); ?></li>
											</ul>
								
											<img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" class="ajax-feedback" title="" alt="" />
								
											<div class="row">
												<div class="row-50">													
															<div id="widget_list">
																<ul>
																	<?php 
																			foreach ($available_widgets as $id => $name )  :
																			$link = add_query_arg('wordpress_widget_id_base', $id);
																			
																			$class = ($wordpress_widget_id_base == $id) ? ' class="active" ' : '';
																	?>
																	<li<?php echo $class; ?>>
																			<h3><a href="<?php echo $link; ?>"><span><?php echo $name[1]; ?></span></a></h3>
																	</li>
																	
																	<?php endforeach; ?>
																</ul>
															</div>
												</div>
												<div class="row-50">
															<div id="dyanmic_form_area">
															<?php 
															$widget_settings = (array) $available_widgets_instances[$wordpress_widget_id_base];
															
															//FIXME :: Proxy sidebar
															$wp_registered_sidebars['wp_inactive_widgets'] = array(
															'name' => __('Inactive Widgets'),
															'id' => 'wp_inactive_widgets',
															'class' => 'inactive-sidebar',
															'description' => __( 'Drag widgets here to remove them from the sidebar but keep their settings.' ),
															'before_widget' => '',
															'after_widget' => '',
															'before_title' => '',
															'after_title' => '');												
															
															
															$sidebar = $wp_registered_sidebars['wp_inactive_widgets'];
															
															if(!empty($widget_settings))
															{
																$link = remove_query_arg('wordpress_widget_id');
																$link = parse_url($link);
																$query_vars = array();
																parse_str($link['query'], $query_vars);
															?>
																<form method="get" action="<?php echo admin_url('admin.php'); ?>" id="redirect_wordpress_widget_form">
																<?php 
																foreach($query_vars as $k=>$v)
																	printf('<input type="hidden" name="%s" value="%s" />', $k,$v);
																?>
																		<div class="row">
																			<div class="row-70">
																				<select name="wordpress_widget_id" id="wordpress_widget_id">
																					
																					<?php 
																						foreach($widget_settings as $index_id => $setting) : 
																						
																						$sidebar_id = $sidebars_widgets_by_id_base[$setting['id']];
																						$sidebar = $wp_registered_sidebars[$sidebar_id];
																						$sidebar_name = $sidebar['name'];
																						
																						if(empty($sidebar_name)) $sidebar_name = __('Inactive','layout-engine');
																						
																						$title = $sidebar_name. " :: " .$setting['name'];
																						
																						echo "<option value='" .$setting['id']. "'".selected($wordpress_widget_id, $setting['id'], false).">".$title."</option>";
																						
																						endforeach; ?>
																				</select>
																			</div>
																			<div class="row-30"><input type="button" accesskey="s" tabindex="5" value="Select" class="button-primary" id="select" name="select"></div>
																		</div>
																		
																		<div class="row">
																			<a href="#" id="widget_link_modify"><?php _e('Modify above selected widget','layout-engine')?></a>
																			|
																			<a href="#" id="widget_link_add"><?php _e('Add new','layout-engine')?></a>
																		</div>
																</form>
															<?php 
															}
															?>
															
															<!--  show new widget creation form -->
															<?php 
																if(!empty($wordpress_widget_id))
																{
																		$sidebars = $wp_registered_sidebars;
															?>
																		<form method="post" action="<?php echo admin_url('admin.php'); ?>" id="callback_modify_wordpress_widget_form">
															<?php 
																		$sidebar_id = ""; 
																		$sidebars_list = wp_get_sidebars_widgets();
																		
																		foreach($sidebars_list as $k_sidebar_id=>$v_widgets)
																		{
																			if(in_array($wordpress_widget_id, $v_widgets))
																			{
																				$sidebar_id = $k_sidebar_id;
																			}
																		}
															
																		if(empty($sidebar_id)) $sidebar_id = "wp_inactive_widgets";													
																		
																		$sidebar_selector = '<p>';
																		$sidebar_selector .= '<label for="sidebar_id">'.__('Sidebar:','layout-engine').'</label>';
																		$sidebar_selector .= '<input type="hidden" id="old_sidebar_id" name="old_sidebar_id" value="'.$sidebar_id.'" />';
																		$sidebar_selector .= '<select id="sidebar_id" name="sidebar_id">';
																		
																		foreach($sidebars as $k=>$v)
																		{
																			$sidebar_selector .= sprintf("<option value='%s'%s>%s</option>", $k, selected($sidebar_id, $k, false), $v['name']);
																		}
																		
																		$sidebar_selector .= '</select>';
																		$sidebar_selector .= '</p>';
															
																		echo $sidebar_selector;
															
																		$id = $wordpress_widget_id;
																		$sidebar = $wp_registered_sidebars['wp_inactive_widgets'];
																		$params = array_merge(
																				array( array_merge( $sidebar, array('widget_id' => $id, 'widget_name' => $wp_registered_widgets[$id]['name']) ) ),
																				(array) $wp_registered_widgets[$id]['params']
																		);
																		
																		$classname_ = '';
																		foreach ( (array) $wp_registered_widgets[$id]['classname'] as $cn ) {
																			if ( is_string($cn) )
																				$classname_ .= '_' . $cn;
																			elseif ( is_object($cn) )
																			$classname_ .= '_' . get_class($cn);
																		}
																		$classname_ = ltrim($classname_, '_');
																		$params[0]['before_widget'] = sprintf($params[0]['before_widget'], $id, $classname_);
																		
																		$params = apply_filters( 'dynamic_sidebar_params', $params );
																		
																		
																		$callback = $wp_registered_widgets[$id]['callback'];
																		
																		do_action( 'dynamic_sidebar', $wp_registered_widgets[$id] );
																		
																		if ( is_callable($callback) ) {
																			call_user_func_array('wp_widget_control', $params);
																		}	
															?>
																		</form>
															<?php 															
																}															
															?>							
															
															</div>
												</div>
										</div><!--  end of two columns row -->
				</div>
			<?php 
	}
	
	/**
	 * Ajax callback to save widget settings.
	 *
	 * @access public
	 * @since 1.0.0.0
	 */
	public static function layout_manager_blockitem_widget_save()
	{
		global $wp_registered_widget_updates, $wp_registered_widgets;

		check_ajax_referer(  'save-le-layout-block', '_wpnonce_layout_manager_admin' );
		
		$json_output = array();
		
		//Mocking the exisitng wordpress functionality
		do_action('load-widgets.php');
		do_action('widgets.php');
		do_action('sidebar_admin_setup');
		
		$id_base = $_POST['id_base'];
		$widget_id = $_POST['widget-id'];
		$sidebar_id = $_POST['sidebar_id'];
		$old_sidebar_id = $_POST['old_sidebar_id'];
		$multi_number = !empty($_POST['multi_number']) ? (int) $_POST['multi_number'] : 0;
		$settings = isset($_POST['widget-' . $id_base]) && is_array($_POST['widget-' . $id_base]) ? $_POST['widget-' . $id_base] : false;
		$error = '<p>' . __('An error has occurred. Please reload the page and try again.') . '</p>';
		
		
		if(!empty($widget_id))
		{
			$sidebars = wp_get_sidebars_widgets();
			$sidebar = isset($sidebars[$sidebar_id]) ? $sidebars[$sidebar_id] : array();
		
			//Moving to different sidebar?
			if($sidebar_id != $old_sidebar_id)
			{
				$old_index = array_search($widget_id, $sidebars[$old_sidebar_id]);
				unset($sidebars[$old_sidebar_id][$old_index]);
					
				$sidebars[$sidebar_id][] = $widget_id;
					
				wp_set_sidebars_widgets($sidebars);
			}
		
			if ( isset($_POST['delete_widget']) && !empty($_POST['delete_widget']))
			{
				if ( !isset($wp_registered_widgets[$widget_id]) )
					wp_die( $error );
		
				$sidebar = array_diff( $sidebar, array($widget_id) );
				$_POST = array('sidebar' => $sidebar_id, 'widget-' . $id_base => array(), 'the-widget-id' => $widget_id, 'delete_widget' => '1');
		
				print_r($_POST);
				exit;
			}
		
			//Updating
			foreach ( (array) $wp_registered_widget_updates as $name => $control )
			{
				if ( $name == $id_base )
				{
					if ( !is_callable( $control['callback'] ) )
						continue;
						
					ob_start();
					call_user_func_array( $control['callback'], $control['params'] );
					ob_end_clean();
					break;
				}
			}
		}
		
		$json_output[] = $sidebar_id;
		$json_output[] = $_POST;		
		
		echo json_encode($json_output);
		wp_die();		
	}	
	
	/**
	 * Render a block item :: widget
	 * 
	 * @since 1.0.0.0
	 *
	 * @uses apply_filters() calls 'le_widget_%s_params' where %s is widget id. to organize markup for widget as it is runtime sidebar.
	 * @param array $block_item, widget setting
	 * @param string $layout layout id
	 * @param array $block block settings
	 * @return void
	 */	
	public static function render_frontend($block_item = array(), $layout = "", $block = array())
	{
		global $wp_registered_widgets;
		
		$id = $block_item['args']['id'];
		if(array_key_exists($id, $wp_registered_widgets))
		{
			$callback = $wp_registered_widgets[$id]['callback'];
			$sidebar = LE_Widget::$runtime_sidebar;
			if ( is_callable($callback) ) 
			{
				$params = (array) $wp_registered_widgets[$id]['params'];
				$params['widget_id'] = $id;
				$params['widget_name'] = $wp_registered_widgets[$id]['name'];

				$params = array_merge
				(
						array( array_merge( $sidebar, array('widget_id' => $id, 'widget_name' => $wp_registered_widgets[$id]['name']) ) ),
						(array) $wp_registered_widgets[$id]['params']
				);
				$params = apply_filters('le_widget_'.$id.'_params', $params);
		
				call_user_func_array($callback, $params);
			}			
		}
					
	}

	/**
	 * Export widget settings
	 *
	 * @since 1.0.0.0
	 *
	 * @param ref array $settings
	 * @return void
	 */
	public static function export(&$settings)
	{
		global $wp_registered_widgets, $wp_registered_widget_updates, $sidebars_widgets;

		$cache_widget_settings = array();
		
		$widgets_to_sidebar = array();
		if(!empty($sidebars_widgets))
		{
			foreach($sidebars_widgets as $sidebar=>$in_widgets)
			{
				foreach($in_widgets as $w)
				{
					$widgets_to_sidebar[$w] = $sidebar;
				}
			}
		}		
		
		if(!empty($settings))
		{
			foreach($settings as $layout_page => $blocks)
			{
				if(empty($blocks)) continue;
					
				foreach($blocks as $block_name => $widgets)
				{
					foreach($widgets as $k => $widget)
					{
						if($widget['id'] == "widget")
						{
							$widget_id = $widget['args']['id'];
							$id = strtolower($id);
							$id_base = _get_widget_id_base(strtolower($widget_id));
							
							if(!empty($widget_id))
							{
								//Exporting sidebar settings
								if(!array_key_exists($id_base, $cache_widget_settings))
								{
											if(is_callable($wp_registered_widget_updates[$id_base]['callback']))
											{
												$wp_registered_widget_updates[$id_base]['callback'][1] = "get_settings";
												$cache_widget_settings[$id_base] = call_user_func_array($wp_registered_widget_updates[$id_base]['callback'], array());
											}
								}
								
								$number = str_replace($id_base."-", "", $widget_id);

								$settings[$layout_page][$block_name][$k]['args'] = $cache_widget_settings[$id_base][$number];
								
								//Additional widget information
								$settings[$layout_page][$block_name][$k]['args']['widget_export_id'] = $widget_id;
								$settings[$layout_page][$block_name][$k]['args']['widget_export_base_id'] = $id_base;
								$settings[$layout_page][$block_name][$k]['args']['widget_export_sidebar'] = $widgets_to_sidebar[$widget_id];
								
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
		global $wp_registered_widgets, $wp_registered_widget_updates, $sidebars_widgets;
		
		$cache_widget_updates = array();
		$cache_widget_settings = array();
		
		$sidebars = wp_get_sidebars_widgets();
		$sidebars_le = (array) LE_Sidebar::getSidebars();
		
		$available_widgets = array();
		if(!empty($sidebars_widgets))
		{
			foreach($sidebars_widgets as $sidebar=>$in_widgets)
			{
				foreach($in_widgets as $w)
				{
					$available_widgets[] = $w;
				}
			}
		}
		
		if(!empty($settings))
		{
			foreach($settings as $layout_page => $blocks)
			{
				if(empty($blocks)) continue;
					
				foreach($blocks as $block_name => $widgets)
				{
					foreach($widgets as $k => $widget)
					{
						//staisfy the creteria to be widget
						if($widget['id'] == "widget" && !empty($widget['args']['widget_export_id']))
						{
										//Getting widget settings
										
										//Exporting sidebar settings
										$id_base = $widget['args']['widget_export_base_id'];
										if(!array_key_exists($id_base, $cache_widget_settings))
										{
											if(is_callable($wp_registered_widget_updates[$id_base]['callback']))
											{
												$wp_registered_widget_updates[$id_base]['callback'][1] = "get_settings";
												$cache_widget_settings[$id_base] = call_user_func_array($wp_registered_widget_updates[$id_base]['callback'], array());
											}
										}
							
										//Register widget settings if it is not available
										if(!in_array($widget['args']['widget_export_id'], $available_widgets))
										{
											$number = str_replace($widget['args']['widget_export_base_id']."-","", $widget['args']['widget_export_id']);
											$wp_registered_widget_updates[$id_base]['callback'][1] = "_set";
											call_user_func_array($wp_registered_widget_updates[$id_base]['callback'], array($number));
											
											$new_instance = $widget['args'];
											unset($new_instance['widget_export_id'], $new_instance['widget_export_base_id'], $new_instance['widget_export_sidebar']);
											$new_instance = stripslashes_deep($new_instance);
											$old_instance = array();
											
											//Proxified widget callbacks
											$wp_registered_widget_updates[$id_base]['callback'][1] = "update";
											$instance = call_user_func_array($wp_registered_widget_updates[$id_base]['callback'], array($new_instance, $old_instance));											
											
											//Saving widget settings
											$cache_widget_settings[$id_base][$number] = $instance;
											
											//Save temp. so we can later on register it (at the end of loop)
											$cache_widget_updates[$id_base]	= $cache_widget_settings[$id_base];

											//Registering it into sidebar
											$widget_export_sidebar = $widget['args']['widget_export_sidebar'];
											$widget_id = $widget['args']['widget_export_id'];
											if(!array_key_exists($widget_export_sidebar, $sidebars))
											{
												if(!array_key_exists($widget_export_sidebar, $sidebars_le))
												{
													$widget_export_sidebar = "wp_inactive_widgets";
												}
											}
											
											$sidebars[$widget_export_sidebar][] = $widget_id;
											wp_set_sidebars_widgets($sidebars);
																				
										}// end of registering widget

										//Translate settings into layout engine
										$settings[$layout_page][$block_name][$k]['args'] = array(
												'id' => $widget['args']['widget_export_id'],
												'title' => $widget['args']['title']
										);										
						}
	
					} // end of loop on block items
				} //end of loop on blocks
			}//end of loop on layouts
			
			//If there are any new widgets :: register their settings into database
			if(!empty($cache_widget_updates))
			{
				foreach($cache_widget_updates as $id_base => $all_instances)
				{
					$wp_registered_widget_updates[$id_base]['callback'][1] = "save_settings";
					call_user_func_array($wp_registered_widget_updates[$id_base]['callback'], array($all_instances));
				}	
			}
			
			
		}// end of making sure we got settings
	
	}	
	
}


?>