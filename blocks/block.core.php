<?php
/**
 * Define core default functionality
 *
 * @package WordPress
 * @subpackage Layout_Manager
 * @since 1.0.0.0
 */

//Ajax handler
add_action( 'wp_ajax_layout_manager_blockitem_arguments_save', array('LE_Block_Default','layout_manager_blockitem_arguments_save'));

class LE_Block_Default
{
	/**
	 * Ajax callback to save layout arguments
	 *
	 * @access public
	 * @since 1.0.0.0
	 */
	public static function layout_manager_blockitem_arguments_save()
	{
		check_ajax_referer(  'save-le-layout-block', '_wpnonce_layout_manager_admin' );
		
		$runtime_id = $_POST['runtime_id'];
		$args = $_POST['args'];
		$data = stripslashes_deep($args);
			
		//Update it
		$widget = LE_Base::getBlockItemByRuntimeID($runtime_id);
		if(!empty($widget))
		{
			$settings = LE_base::getSettings();
			$layout = $widget['layout'];
			$layout_section = $widget['section'];
			$layout_index = $widget['pos'];

			if(isset($settings[$layout][$layout_section][$layout_index]))
			{
				$settings[$layout][$layout_section][$layout_index]['args'] = (array) wp_parse_args($args, $settings[$layout][$layout_section][$layout_index]['args']);
				$json_output['data'] = $settings[$layout][$layout_section][$layout_index];
					
				//Saving
				LE_Base::saveSettings($settings);
			}
		}
			
		$json_output['success'][] =  __("Success.","layout-engine");	

		echo json_encode($json_output);
		wp_die();		
	}	
}

/**
 * Get current LE compatible layout id. Also check LE_Base::getTree() to understand how backend generate id.
 *
 * @todo Make it compatible with LE_Base::getTree() 
 * @since 1.0.0.0
 * @return string $id
 */
function get_current_layout_id()
{
	$id = "index";
	
	if(is_home())
	{
		$id ="index";
	}elseif(is_front_page())
	{
		$id ="index";
	}elseif(is_404())
	{
		$id ="index/404";
	}elseif(is_search())
	{
		$id = "index/search";
	}elseif(is_page())
	{
		$page_template = get_page_template_slug( get_queried_object_id() );
		if(empty($page_template))
			$id = "index/single/page";
		else
			$id = "index/single/page/$page_template";
		
	}elseif(is_single())
	{
		$id = "index/single/".get_post_type();
	}elseif(is_archive())
	{

		if(is_post_type_archive())
		{
			$id = "index/archive/post_types/".get_post_type();
		}elseif(is_author())
		{
			$id = "index/archive/author";
		}elseif(is_category())
		{
			$id = "index/archive/taxonomies/category";
		}elseif(is_tag())
		{
			$id = "index/archive/taxonomies/post_tag";
		}elseif(is_tax())
		{
			$object = get_queried_object();
			$id = "index/archive/taxonomies/".$object->taxonomy;
		}elseif(is_date())
		{
			if(is_year())
			{
				$id = "index/archive/date/year";
			}elseif(is_month())
			{
				$id = "index/archive/date/month";
			}elseif(is_day())
			{
				$id = "index/archive/date/day";
			}else{
				$id = "index/archive/date";
			}
		}
	}
	
	return $id;
}

/**
 * Display dynamic block.
 * 
 * block_sections - Default is empty. Plain array of block's section or single block to display; if empty then display all.
 * exclude_block_sections - Default is empty. Exclude provided block's section during loop.
 * enable_callback - Default is true. Allow to call wordpress callbacks (actions) to further customize layout through plugin api.
 * layout - Default is empty. Get layout ID in which block items are present.
 * enable-grid - Default is true. Automatically create grid layout.
 * show_empty_block - Default is false. Show block markup even if no block items present in it.
 * start_row - If auto grid is enabled, it is markup to show before the start of new row. (Column Container)
 * end_row - End of row markup.
 * 
 * @uses do_action_ref_array() calls 'dynamic_block_%s_before' before rendering any block where %s is block name.
 * @uses do_action_ref_array() calls 'dynamic_block_%s_after' after rendering any block where %s is block name.
 * 
 * @since 1.0.0.0
 *
 * @param array|string $args, name of block to render or different customized options as array.
 * @return void
 */
function dynamic_block($args = null) 
{
	$defaults = array(
						'block_sections' => array(),
						'exclude_block_sections' => array(),
						'enable_callback' => true,
						'layout' => null,
						'enable_grid' => true,
						'show_empty_block' => false,
						'start_row' => "<div class=\"row\">\n",
						'end_row' => '</div>'
					);
	
	if(!is_array($args ) && !empty($args))
	{
		$args = array('block_sections' => array($args));
	}
	
	$args = wp_parse_args( $args, $defaults );	
	extract($args);
	
	if(empty($layout))
		$layout = get_current_layout_id();
	
	$block_items_obj = LE_Base::getBlockObjects();
	$blocks_list = LE_Base::getBlocks();
	$block_by_id = array();
	
	//Block items settings
	$settings = LE_Base::getSettings();

	foreach($blocks_list as $block)
		$block_by_id[$block['id']] = $block;
	
	if(empty($block_sections))
		$block_sections = array_keys($block_by_id);

	if(!is_array($block_sections))
		$block_sections = array($block_sections);
	
	//Removing Excluded blocks
	if(is_array($exclude_block_sections))
	{
		foreach($exclude_block_sections as $e_block)
		{
			//finding index
			if(($i = array_search($e_block, $block_sections)) !== false)
			{
				unset($block_sections[$i]);
			}	
		}	
	}

	$settings = apply_filters('le_dynamic_block_settings', $settings);
	
	foreach($block_sections as $i=>$block_name)
	{
		$block = $block_by_id[$block_name];

		//Callback
		if(($enable_callback === true) && (has_action('dynamic_block_'.$block_name.'_before')))
		{
			do_action_ref_array( 'dynamic_block_'.$block_name.'_before', array(&$block, $args));
		}
		
		//Rendering block items
		if(is_array($block) && array_key_exists("hide", $block))
		{
			//Developer force to hide this block
		}else
		{
			//isset($settings[$layout][$block_name]) && !empty($settings[$layout][$block_name])

			$block_items = array();
			$layout_path = explode("/", $layout);
			while((empty($block_items)) && (!empty($layout_path)))
			{
				$layout_temp = implode("/", $layout_path);
				$block_items = $settings[$layout_temp][$block_name];
				
				array_pop($layout_path);
			}
			
			if(empty($block_items) && ($show_empty_block === false))
				continue;
			
			//Unable to get any block items for current block
			$block_class = "block-".($i + 1)." ".$block['class'];
			$block_class = apply_filters('le_block_class', $block_class, $block);
			printf($block['before_block']."\n", $block['id'], $block_class);
			
			//Creating Grid Markup
			if($enable_grid === true)
			{
				echo $start_row;
			}
			
			//Count number of columns shown per row if three then break it.
			$current_columns_in_row = 0;
			
			do_action_ref_array( 'dynamic_block_'.$block_name.'_before_block_items', array(&$block_items));
			
			foreach($block_items as $j => $block_item)
			{
				if(array_key_exists($block_item['id'], $block_items_obj))
				{
					//Render override
					$theme_override_callback = "theme_block_item_".$block_item['id'];
					if(!function_exists($theme_override_callback))
						$theme_override_callback = $block_items_obj[$block_item['id']]['callback_frontend'];
					
					if(!empty($theme_override_callback))
					{
						if($current_columns_in_row == 3)
						{
							$current_columns_in_row = 0;
								
							//Creating Grid Markup :: creating a new row.
							if($enable_grid === true)
							{
								echo PHP_EOL.$end_row.PHP_EOL;
								echo $start_row;
							}
						}
												
						if(isset($block_item['args']['id']))
							$id = $block_item['args']['id'];
						else 
							$id = $block_item['id'];
						
						//Creating Grid Markup
						$css_column_class = "";
						$num_columns = intval($block_item['columns']);
						
						$css_column_class = "block-item-".($j + 1)." ";
						
						if($num_columns == 1)
							$css_column_class .= "span4";
						elseif($num_columns == 2)
							$css_column_class .= "span8";
						elseif($num_columns == 3)
							$css_column_class .= "span12";
						
						$current_columns_in_row += $num_columns;
						
						$css_column_class = apply_filters('le_block_item_class', $css_column_class, $block_item);
						
						printf("\t\t".$block['before_item']."\n", $block_item['id'], $id,$css_column_class, $block_item['columns'], $block['id'], $block['class']);
							
						call_user_func_array($theme_override_callback, array($block_item, $layout, $block));
						
						printf("\n\t\t".$block['after_item']."\n", $block_item['id'], $id, $css_column_class, $block_item['columns'], $block['id'], $block['class']);						
						
					}
				}
			}
			
			//Creating Grid Markup
			if($enable_grid === true)
			{
				echo $end_row;
			}			
			
			
			printf($block['after_block']."\n", $block['id'], $block_class);
		}
		
		
		//Callback
		if(($enable_callback === true) && (has_action('dynamic_block_'.$block_name.'_after')))
		{
			do_action_ref_array( 'dynamic_block_'.$block_name.'_after', array(&$block, $args));
		}		
	}
}


?>