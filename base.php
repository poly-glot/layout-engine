<?php
/**
 * Layout Engine base class to provide necessary functionality to help both frontend (theme) and backend (administration)
 *
 * @package WordPress
 * @subpackage layout_engine
 * @since 1.0.0.0
 */


class LE_Base
{
	public static $_blocks = array();
	public static $_blocks_item_byruntimeid = array();
	
	/**
	 * Get Layout Tree based on wordpress template hierarchy.
	 * 
	 * @uses apply_filters() calls 'le_layout_tree' hook :: list of different templates
	 * @uses apply_filters() calls 'le_layout_list_types' sub tabs to show tree in different format
	 * 
	 * @access public
	 * @since 1.0.0.0
	 * @return array $tree list of templates in associative array format.
	 */	
	public static function getTree()
	{
				$tree = array();
				$tree['index'] = array(
											'title' => __('Default','layout-engine'),
											'404' => __('Error 404','layout-engine'),
											'search' => __('Search page result','layout-engine'),
											'archive' => array('title' => __('Archive page','layout-engine')),
											'single' => array('title' => __('Singular','layout-engine')),
									  );
				
				$taxonomies = get_taxonomies(array(),'objects');
				$tree['index']['archive']['taxonomies'] = array('title' => __('Taxonomies','layout-engine'));
			
				foreach($taxonomies as $key => $taxonomy)
				{
					if($taxonomy->public == 1)
					{
						$tree['index']['archive']['taxonomies'][$key] = $taxonomy->labels->name;
					}
				}
				
				$post_types = get_post_types( array(),'objects');
				$tree['index']['archive']['post_types'] = array('title' => __('Post Types','layout-engine'));
				
				foreach($post_types as $key => $post_type)
				{
					if('page' != $post_type->post_type)
					{
							if($post_type->public == 1)
							{
								$tree['index']['archive']['post_types'][$key] = $post_type->labels->name;
								$tree['index']['single'][$key] = $post_type->labels->name;
							}
					}
				}	
				
				//Template Support (for pages)
				$templates = get_page_templates();
				if(count($templates) > 0)
				{
					ksort( $templates );	
					$tree['index']['single']['page'] = array('title' => __('Page','layout-engine'));
			
					foreach ($templates as $template_name => $template_file )
					{
						$tree['index']['single']['page'][$template_file] = $template_name;
					}
					
				}else{
					$tree['index']['single']['page'] = __('Page','layout-engine');
				}
				
				$tree['index']['archive']['author'] = __('Author','layout-engine');
				$tree['index']['archive']['date'] = array(
											'title' => __('Date','layout-engine'),
											'year' => __('Yearly','layout-engine'),
											'month' => __('Monthly','layout-engine'),
											'day' => __('Daily','layout-engine')
									  );
				
				$tree = apply_filters('le_layout_tree', $tree);
				
				return $tree;
	}
	
	/**
	 * Return the existing layout engine setting(s).
	 *
	 * @uses get_option() to reterieve the settings
	 * @uses apply_filters() calls 'le_layout_settings' hook.
	 *
	 * @access public
	 * @since 1.0.0.0
	 * @return array $setting
	 */
	public static function getSettings()
	{
		$layout = get_option('le_layout_settings');
		$layout = (array) apply_filters('le_layout_settings', $layout);
		return $layout;
	}

	/**
	 * Save drag and drop layout settings.
	 *
	 * @uses update_option() to save the settings
	 * @uses apply_filters() calls 'le_layout_settings' hook.
	 *
	 * @access public
	 * @since 1.0.0.0
	 * @return bool False if value was not updated and true if value was updated.
	 */
	public static function saveSettings($layout)
	{
		//Empty Runtime cache
		LE_Base::$_blocks_item_byruntimeid = array();
		
		return update_option('le_layout_settings', $layout);
	}	
	
	/**
	 * Reset layout settings
	 *
	 * @uses do_action_ref_array() calls 'le_reset_before' hook.
	 * @uses do_action() calls 'le_reset_after' hook.
	 *
	 * @access public
	 * @since 1.0.0.0
	 * @return bool
	 */
	public static function resetSettings()
	{
		$settings = LE_Base::getSettings();
		
		do_action_ref_array('le_reset_before', array(&$settings));
		
		if(!empty($settings))
			update_option('le_layout_settings_backup', $settings);
		
		do_action('le_reset_after');
		
		return delete_option('le_layout_settings');
	}	
	
	/**
	 * Restore last saved layout settings
	 *
	 * @uses do_action_ref_array() calls 'le_restore' hook.
	 *
	 * @access public
	 * @since 1.0.0.0
	 * @return bool
	 */
	public static function restoreSettings()
	{
		$settings = get_option('le_layout_settings_backup', array());
		
		if(!empty($settings))
		{
			do_action_ref_array('le_restore', array(&$settings));
			return LE_Base::saveSettings($settings);
		}
		
		return false;
	}	
	
	/**
	 * Builds the definition for a block in a layout.
	 *
	 * The $args parameter takes either a string or an array with 'name' and 'id'
	 * contained in either usage. it will be advised to define id
	 *
	 * name - The name of the block, which presumably the title which will be displayed.
	 * id - The unique identifier by which the block will be called by.
	 * before_block - The content that will prepended to the layout block when they are displayed.
	 * after_block - The content that will be appended to the layout block when they are displayed.
	 * before_item - The content that will be prepended to the item in each block when displayed.
	 * after_item - The content that will be appended to the item in each block when displayed.
	 *
	 * priority - The priority in layout.
	 *
	 * @access public
	 * @since 1.0.0.0
	 */
	public static function register_block($args = array()) 
	{
		global $wp_registered_sidebars;
	
		$i = count(LE_Base::$_blocks) + 1;
	
		$defaults = array(
				'name' => sprintf(__('Block %d'), $i ),
				'id' => "",
				'description' => '',
				'class' => '',
				'before_block' => '<section id="%1$s" class="block %2$s">',
				'after_block' => "</section>\n",
				'before_item' => '<aside class="blockitem %1$s %2$s %3$s">',
				'after_item' => "</aside>\n",
				'priority' => '0'
		);
	
		$block = wp_parse_args( $args, $defaults );
		
		//Default ID
		if(empty($block['id']))
			$block['id'] = sanitize_title($block['id']);
		
		//Appending Block base on priority
		if(empty($block['priority']))
			$block['priority'] = "0";
			
		LE_Base::$_blocks[$block['priority']][$block['id']] = $block;
	}
	
	/**
	 * Removes a block from layout.
	 *
	 * @param string $name_or_id The ID of the block when it was added.
	 * @access public
	 * @since 1.0.0.0
	 * @return boolean return true on success otherwise false.
	 */
	public static function unregister_block( $name_or_id ) 
	{
		$dynamic_id = sanitize_title($name_or_id);
		
		foreach(LE_Base::$_blocks as $priority => $blocks)
		{
			$key = null;
			$ids = array();
			foreach($blocks as $b)
				$ids[] = $b['id'];
			
			//Searching user provided key
			if(in_array($name_or_id, $ids))
				$key = array_search($name_or_id, $ids);
			
			//Searching dyanmic id
			if(in_array($dynamic_id, $ids))
				$key = array_search($dynamic_id, $ids);

			//Block found!
			if($key > -1)
			{
				$key = $ids[$key];
				unset(LE_Base::$_blocks[$priority][$key]);
				
				if(empty(LE_Base::$_blocks[$priority])) unset(LE_Base::$_blocks[$priority]);
				
				return true;
			}
		}
		
		return false;
	}	
	
	/**
	 * Get defined layout blocks.
	 *
	 * @uses apply_filters() calls 'le_layout_blocks' hook with list of blocks in order.
	 * @access public
	 * @since 1.0.0.0
	 * @return array
	 */
	public static function getBlocks()
	{
		$blocks = array();
		ksort(LE_Base::$_blocks);
		
		foreach(LE_Base::$_blocks as $priority => $t_blocks)
		{
			foreach($t_blocks as $block)
			{
				$blocks[$block['id']] = $block;
			}
		}
		
		$blocks = apply_filters('le_layout_blocks', $blocks);
				
		return $blocks;
	}	
	
	/**
	 * Layout Engine :: Allows user to create drag & drop layout elements
	 *
	 * @uses apply_filters() calls 'le_layout_block_objects' hook with list of blocks in order.
	 * @access public
	 * @since 1.0.0.0
	 * @return array
	 */	
	public static function getBlockObjects()
	{
		$objects = array();
		$objects = apply_filters('le_layout_block_objects', $objects);
		return $objects;
	}
	
	/**
	 * Get block item in a layout through its temporary runtime id.
	 *
	 * @access public
	 * @since 1.0.0.0
	 * @return array
	 */
	public static function getBlockItemByRuntimeID($id)
	{
		//Cache the settings by runtime id.
		if(empty(LE_Base::$_blocks_item_byruntimeid) || !array_key_exists($id, LE_Base::$_blocks_item_byruntimeid))
		{
			$settings = LE_Base::getSettings();
			foreach($settings as $layout_page => $blocks)
			{
				if(empty($blocks)) continue;
				
				foreach($blocks as $block_name => $widgets)
				{
					foreach($widgets as $k=>$widget)
					{
						LE_Base::$_blocks_item_byruntimeid[$widget['runtime_id']] = $widget;
						
						//Position in a layout
						LE_Base::$_blocks_item_byruntimeid[$widget['runtime_id']]['layout'] = $layout_page;
						LE_Base::$_blocks_item_byruntimeid[$widget['runtime_id']]['section'] = $block_name;
						LE_Base::$_blocks_item_byruntimeid[$widget['runtime_id']]['pos'] = $k;
					}
				}
			}			
		}
		
		return LE_Base::$_blocks_item_byruntimeid[$id];
	}	
}




?>