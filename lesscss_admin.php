<?php
/**
 * Provide administration and parsing of LESSCSS files
 *
 * @package WordPress
 * @subpackage layout_engine
 * @since 1.0.0.0
 */
class LE_LESSCSS_Admin
{
	public static $cache_settings = array();
	public static $cache_theme_settings = array();
	
	/**
	 * Parse and show the lesscss options
	 * 
	 * @uses apply_filters() calls 'le_layout_group_title' hook :: convert group slug into title.
	 * 
	 * @access public
	 * @since 1.0.0.0
	 */	
	public static function admin()
	{
		//Compile Callback :: Callback to update style.css
		if(isset($_REQUEST['settings-updated']))
		{
			LE_LESSCSS_Admin::compile();
		}	
		
		load_template( LE_ABSPATH . 'views' . DS .'theme_settings.php');
	}
	
	/**
	 * Init wp customizer
	 * @uses apply_filters() calls 'le_customize_register' hook :: to decide to enable wp customizer.
	 *
	 * @access public
	 * @since 1.0.0.0
	 */	
	public static function init()
	{
		if(apply_filters('le_customize_register', true))
		{
			add_action( 'customize_register', array('LE_LESSCSS_Admin', 'customize_register'));
		}		
	}
	
	/**
	 * Init wordpress stylesheet options
	 *
	 * @access public
	 * @since 1.0.0.0
	 */	
	public static function theme_options_init()
	{
		$settings = LE_LESSCSS_Admin::getSettings();
		
		register_setting
		(
				'layout_manager_options',     
				'layout_manager_theme_options',
				array('LE_LESSCSS_Admin','validate')
		);		
		
		foreach($settings as $group=>$priorities)
		{
			// Register our settings field group
			$group_title = apply_filters('le_layout_group_title', $group);
			
			add_settings_section
			(
					$group, // Unique identifier for the settings section
					$group_title, // Section title (we don't want one)
					'__return_false', // Section callback (we don't want anything)
					'layout_manager' //slug 
			);	
			
			foreach($priorities as $priority)
			{
				foreach($priority as $setting)
				{
					add_settings_field( $setting['id'], __( $setting['label'],'layout-engine' ), array('LE_LESSCSS_Admin','input_field'), 'layout_manager', $group, array('id'=>$setting['id'],'description'=>$setting['description'],'value'=>$setting['value'],'type'=>$setting['type'], 'object'=>$setting) );
				}
			}
		}
		
		//Compile Callback :: Customizer Callback to update style.css
		add_action('customize_save', array('LE_LESSCSS_Admin','compile_on_customizer_save'));
		
		
		//Default Metaboxes
		add_meta_box('help',__('Help','layout-engine'), array('LE_LESSCSS_Admin','metabox_help'), 'appearance_page_layout_engine', 'side');
	}
	

	
	/**
	 * Using wordpress customizer to easily change theme settings.
	 *
	 *
	 * @access public
	 * @since 1.0.0.0
	 */
	public static function customize_register($wp_customize)
	{
		$settings = LE_LESSCSS_Admin::getSettings();
		
		$settings = apply_filters('le_customize_register', $settings);
		
		foreach($settings as $group=>$priorities)
		{
			// Register our settings field group
			$group_title = apply_filters('le_layout_group_title', $group);
				
			//Adding section
			$group_id = sanitize_title($group_title);
			$wp_customize->add_section( $group_id, array(
					'title'    =>  $group
			) );			
			
			foreach($priorities as $priority)
			{
				foreach($priority as $setting)
				{
							$control_id = "layout_manager_theme_options[".$setting['id']."]";
					
							//Adding Setting
							$wp_customize->add_setting($control_id, array(
									'default'    => $setting['value'],
									'type'       => 'option',
									'capability' => 'edit_theme_options',
							) );			

							
							

							//Adding control
							switch($setting['type'])
							{
								case "checkbox":
									//checkbox are ignored
								break;
								
								case "radio":
								//case "checkbox":
								case "select":
																	
									$choices = $setting['choices'];
									if(!empty($choices) && is_string($choices))
									{
										$choices_temp = explode(",",$choices);
										array_walk($choices_temp, 'trim');
										
										$choices = array();
										foreach($choices_temp as $k)
										{
											$choices[$k] = $k;
										}
									}									
									
									$wp_customize->add_control( $control_id, array(
											'section'    => $group_id,
											'type'       => $setting['type'],
											'choices'    => $choices,
											'label'		 => $setting['label']
									) );									
									
								break;
									
								case "color":
								case "colour":
									
									$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $control_id, array(
											'label'		 => $setting['label'],
											'section' 	 => $group_id,
									) ) );									
									
								break;
									
								case "textarea":
									
								break;
								
								case "image":
										
									$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, $control_id, array(
											'label'		 => $setting['label'],
											'section' 	 => $group_id,
											'context'  => 'custom-header',
											'removed'  => 'remove-header',
											'get_url'  => 'get_header_image',
											'statuses' => array(
													''                      => __('Default'),
													'remove-header'         => __('No Image'),
													'random-default-image'  => __('Random Default Image'),
													'random-uploaded-image' => __('Random Uploaded Image'),
											)											
									) ) );									
									
								break;
									
								
								default:
									
									$wp_customize->add_control( $control_id, array(
											'section'    => $group_id,
											'type'       => "text",
											'label'		 => $setting['label']
									) );									
								
							}
				}
			}
		}		
		
		
	}	

	/**
	 * Return lesscss options into key, value pair array.
	 *
	 *
	 * @access public
	 * @since 1.0.0.0
	 * @return array
	 */
	public static function get_theme_options()
	{
		//Get settings from cache
		if(!empty(LE_LESSCSS_Admin::$cache_theme_settings)) return LE_LESSCSS_Admin::$cache_theme_settings;	

		$settings = LE_LESSCSS_Admin::getSettings();
		if(!empty($settings))
		{
			foreach($settings as $group=>$group_settings)
			{
				if(!is_array($group_settings)) continue;
				
				foreach($group_settings as $inner_settings)
				{
					if(!is_array($inner_settings)) continue;
					
					foreach($inner_settings as $s)
					{
						LE_LESSCSS_Admin::$cache_theme_settings[$s['id']] = $s['value'];
					}
				}
			}
		}
		
		return LE_LESSCSS_Admin::$cache_theme_settings;
	}	
	
	/**
	 * Parse less css file and format settings into associative arrays.
	 *
	 *
	 * @access public
	 * @since 1.0.0.0
	 */
	public static function getSettings()
	{
		global $wp_filesystem;
		
		//Get settings from cache
		if(!empty(LE_LESSCSS_Admin::$cache_settings)) return LE_LESSCSS_Admin::$cache_settings;
		
		$settings = array();
		
		//Loading wordpress filesystem factory
		if ( ! $wp_filesystem || !is_object($wp_filesystem) )
		{
			if(!function_exists('WP_Filesystem'))
			{
				require_once(ABSPATH . 'wp-admin/includes/file.php');
				WP_Filesystem();
				//return new WP_Error('fs_unavailable', __('Could not access filesystem.'));
			}else{
				WP_Filesystem();
			}	
		}
		
		$options_saved = get_option('layout_manager_theme_options');
		
		$stylesheet_path = get_template_directory().DS.'style.less';
		if(file_exists($stylesheet_path))
		{
						
			$less_css_data = $wp_filesystem->get_contents($stylesheet_path);
			preg_match_all( '/\/'.preg_quote('***').'(.*)'.preg_quote('***').'\/[^@]+?@([a-z-0-9]+)\s?:\s?([^;]+);/imsU', $less_css_data, $match );
			
			if(!empty($match[0]))
			{
				for($i = 0; $i < count($match[1]); $i++)
				{
							$id = $match[2][$i];
					
							$defaults = array(
									'label' => sprintf(__('Setting %d'), $i ),
									'id' => $id,
									'description' => '',
									'group' => 'default',
									'type' => 'text',
									'default-value' => $match[3][$i],
									'priority' => '0'
							);
							
							$args = array();
							
							$parse_line = $match[1][$i];
							$parse_line = explode("\n",$parse_line);
							for($j = 0; $j < count($parse_line); $j++)
							{
								$line = rtrim(trim($parse_line[$j]));
								
								$pos = strpos($line, ':');
								$label = trim(strtolower(substr($line, 0, $pos)));
								$value = trim(substr($line, ($pos + 1), strlen($line)));
								
								if(!empty($label) && !empty($value))
									$args[$label] = $value;
							}
							
							$settings[$id] = wp_parse_args( $args, $defaults );	
							
							//Removing quote from CSS value provided by LessCSS
							if(!empty($settings[$id]['default-value']))
							{
								$settings[$id]['default-value'] = trim(rtrim($settings[$id]['default-value']));
								$first_char = substr($settings[$id]['default-value'],0,1);
								$last_char = substr($settings[$id]['default-value'], -1);
								
								if($first_char == "'" && $last_char == "'")
								{
									$settings[$id]['default-value'] = substr($settings[$id]['default-value'], 1, ((strrpos($settings[$id]['default-value'], "'")) - 1));
								}							
							}							
							
							if(array_key_exists($id, $options_saved))
								$settings[$id]['value'] = $options_saved[$id];
							else 
								$settings[$id]['value'] = trim($settings[$id]['default-value']);
				}			
			}
		}
		
		$settings = apply_filters('le_lesscss_settings', $settings);
		
		if(!empty($settings))
		{
			$settings_priority = array();
			foreach($settings as $s)
			{
				if(empty($s['group'])) $s['group'] = 'default';
		
				$settings_priority[$s['group']][$s['priority']][] = $s;
			}
			
			$settings = $settings_priority;
		}	

		LE_LESSCSS_Admin::$cache_settings = $settings;
		
		return $settings;
	}	
	
	/**
	 * Parse less css file and format settings into associative arrays.
	 *
	 * @param array $args :: id, description, default-value, type
	 * @access public
	 * @since 1.0.0.0
	 */	
	public static function input_field($args = array())
	{
		$id = $args['id'];
		
		if($args['type'] == "radio")
		{

			$choices = $args['object']['choices'];
			if(!empty($choices) && is_string($choices))
			{
				$choices = explode(",",$choices);
				array_walk($choices, 'trim');
			}
			
			$sep = "<br />";
			if(array_key_exists('sep', $args))
			{
				$sep = $args['sep'];
			}
			
			$before_item = '<div class="input_item input_item_type_'.$args['type'].'">';
			if(array_key_exists('before_item', $args))
			{
				$before_item = $args['before_item'];
			}	

			$after_item = '</div>';
			if(array_key_exists('before_item', $args))
			{
				$after_item = $args['after_item'];
			}			
			
			foreach($choices as $i=>$choice)
			{
				printf($before_item.'<input name="layout_manager_theme_options[%1$s]" type="radio" id="%1$s_%4$s" value="%2$s" class="regular-radio %3$s" %5$s /><label for"%1$s_%4$s" class="selectit">%2$s</label>'.$sep.PHP_EOL.$after_item, $args['id'], esc_attr($choice), esc_attr($args['class']), $i, checked( $choice, $args['value'], false ));
			}

		}elseif($args['type'] == "checkbox")
		{
			
			$choices = $args['object']['choices'];
			if(!empty($choices) && is_string($choices))
			{
				$choices = explode(",",$choices);
				array_walk($choices, 'trim');
			}
				
			$sep = "<br />";
			if(array_key_exists('sep', $args))
			{
				$sep = $args['sep'];
			}
				
			$before_item = '<div class="input_item input_item_type_'.$args['type'].'">';
			if(array_key_exists('before_item', $args))
			{
				$before_item = $args['before_item'];
			}
			
			$after_item = '</div>';
			if(array_key_exists('before_item', $args))
			{
				$after_item = $args['after_item'];
			}
			
			if(!is_array($args['value'])) $args['value'] = array($args['value']);
				
			foreach($choices as $i=>$choice)
			{
				if(in_array($choice, $args['value']))
					$checked = ' checked="true" ';
				else 
					$checked = '';
				
				printf($before_item.'<input name="layout_manager_theme_options[%1$s][]" type="checkbox" id="%1$s_%4$s" value="%2$s" class="regular-radio %3$s" %5$s /><label for"%1$s_%4$s" class="selectit">%2$s</label>'.$sep.PHP_EOL.$after_item, $args['id'], esc_attr($choice), esc_attr($args['class']), $i, $checked);
			}			
			
		}elseif($args['type'] == "select")
		{

			$choices = $args['object']['choices'];
			if(!empty($choices) && is_string($choices))
			{
				$choices = explode(",",$choices);
				array_walk($choices, 'trim');
			}
			
			printf('<select name="layout_manager_theme_options[%1$s]" id="%1$s" class="regular-select %2$s">'.PHP_EOL, $args['id'], esc_attr($args['class']));
			foreach($choices as $i=>$choice)
			{
				printf('<option value="%1$s" %2$s />%1$s</option>'.PHP_EOL, esc_attr($choice), selected( $choice, $args['value'], false ));
			}
			echo '</select>';

		}elseif($args['type'] == "textarea")
		{
			$textarea_options = array(
										'rows' => (isset($args['rows']) ? $args['rows'] : 3),
										'cols' => (isset($args['cols']) ? $args['cols'] : 48)
					                 );
			printf('<textarea id="%1$s" name="%1$s" rows="%4$d" cols="%5$d" class="%3$s">%2$s</textarea>', $args['id'], esc_attr($args['value']), esc_attr($args['class']), $textarea_options['rows'], $textarea_options['cols']);
		}elseif($args['type'] == "color" || $args['type'] == "colour")
		{
			$textarea_options = array(
										'rows' => (isset($args['rows']) ? $args['rows'] : 3),
										'cols' => (isset($args['cols']) ? $args['cols'] : 48)
					                 );
			
			//RGB to hex
			if(strpos($args['value'],'rgb') !== false)
			{
				$args['value'] = LE_LESSCSS_Admin::rgb2hex($args['value']);
			}
			
			$default_value_hex = $args['object']['default-value'];
			if(strpos($args['object']['default-value'],'rgb') !== false)
			{
				$default_value_hex = LE_LESSCSS_Admin::rgb2hex($args['object']['default-value']);
			}			
			
		?>
		<div class="le_color_picker <?php echo $args['class']; ?>" id="le_color_picker_<?php echo $args['id']; ?>">
						<input type="text" name="layout_manager_theme_options[<?php echo $args['id']; ?>]" id="link-color-<?php echo $args['id']; ?>" class="link-color" value="<?php echo esc_attr( $args['value'] ); ?>" />
						<a href="#" class="pickcolor hide-if-no-js link-color-example"></a>
						<input type="button" class="pickcolor button hide-if-no-js" value="<?php esc_attr_e( 'Select a Color' ); ?>" />
						<div id="colorPickerDiv_<?php echo $args['id']; ?>" class="colorPickerDiv" style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none;"></div>
						<br />
						<span><?php printf( __( 'Default color: %s' ), '<a id="default-color_'.$args['id'].'" class="default-color" data-color="' . $default_value_hex. '">' . $args['object']['default-value']. '</a>' ); ?></span>
		</div>						
		<?php
		}else{
			printf('<input name="layout_manager_theme_options[%1$s]" type="text" id="%1$s" value="%2$s" class="regular-text %3$s" />', $args['id'], esc_attr($args['value']), esc_attr($args['class']));
		}
	}
	
	/**
	 * Validate settings.
	 *
	 * @todo implement
	 * @param array $args :: id, description, default-value, type
	 * @access public
	 * @since 1.0.0.0
	 */	
	public static function validate( $input ) 
	{
		/**
		 		$input['show_menu'] = ( $input['show_menu'] == 1 ? true : false );
				$input['tag_line'] =  wp_filter_nohtml_kses($input['tag_line']);
		 */
		return $input;
	}	
	
	/**
	 * Register a callback to save data on customizer submit action
	 *
	 *
	 * @access public
	 * @since 1.0.0.0
	 */
	public static function compile_on_customizer_save($wp_customize)
	{
		//As there is no callback after customizer save data, so we change the variables through posted data
		add_filter('le_lesscss_variables', array('LE_LESSCSS_Admin','less_css_variables_reset'), 10, 2);
		
		//Run compiler
		LE_LESSCSS_Admin::compile();
	}	
	
	/**
	 * Helper filter callback to reset the settings based on new setting sent by user.
	 * As customizer do not provide any callback for aftersave; we have to intercept data in advance.
	 *
	 * @access public
	 * @since 1.0.0.0
	 */
	public static function less_css_variables_reset($less_css_src, $lesscss_variables)
	{
			if ( isset( $_POST['customized'] ) )
			{
				$post_values = json_decode( stripslashes( $_POST['customized'] ), true );
				
				if(is_array($post_values) && !empty($post_values))
				{
					foreach($less_css_src  as $k=>$v)
					{
						$posted_key = "layout_manager_theme_options[".$k."]";
						if(array_key_exists($posted_key, $post_values))
						{
							$less_css_src[$k] = $post_values[$posted_key];
						}
					}	
				}
				
			}
			
			return $less_css_src;
	}	
	
	/**
	 * Helper function to compile style.less
	 *
	 * @return boolean
	 * @access public
	 * @since 1.0.0.0
	 */	
	public static function compile()
	{
		global $wp_filesystem;
		
		//Loading wordpress filesystem factory
		if ( ! $wp_filesystem || !is_object($wp_filesystem) )
		{
			if(!function_exists('WP_Filesystem'))
			{
				require_once(ABSPATH . 'wp-admin/includes/file.php');
				WP_Filesystem();
				//return new WP_Error('fs_unavailable', __('Could not access filesystem.'));
			}else{
				WP_Filesystem();
			}
		}
		
		
		$stylesheet_path = get_template_directory().DS.'style.less';
		if(file_exists($stylesheet_path))
		{
			$settings = (array) LE_LESSCSS_Admin::getSettings();
			
			$lesscss_variables = array();
			$lesscss_data = array();

			foreach($settings as $group=>$priorities)
			{
				foreach($priorities as $priority)
				{
					foreach($priority as $setting)
					{
						$lesscss_data[$setting['id']] = $setting;
						
						if(is_array($setting['value'])) $setting['value'] = implode(", ", $setting['value']);
						$lesscss_variables[$setting['id']] = $setting['value'];
						
					}	
				}
			}		
			
			$lesscss_variables = apply_filters('le_lesscss_variables', $lesscss_variables, $lesscss_data);
			$less_css_src = $wp_filesystem->get_contents($stylesheet_path);
			
			$less_compiler = new lessc_le_compiler();
			$less_css_compiled = $less_compiler->parse($less_css_src, $lesscss_variables);
			
			//Appending theme information (LessCSS remove all comments, so try find and append again)
			preg_match_all('/\/\*.*?\*\//s', $less_css_src, $m);
			if(!empty($m[0]))
			{
				foreach($m[0] as $s)
				{
					if(strpos($s,'Theme Name') !== false)
					{
						$less_css_compiled = $s.PHP_EOL.PHP_EOL.$less_css_compiled;
						break;
					}	
				}
			}
			
			//Compiling stylesheet
			$stylesheet_path = get_template_directory().DS.'style.css';
			return $wp_filesystem->put_contents($stylesheet_path, $less_css_compiled);
		}
	}
	
	/**
	 * Helper function to css rgb color into hex value
	 *
	 * @param string $color
	 * @return string $color in hex format
	 * @access public
	 * @since 1.0.0.0
	 */
	public static function rgb2hex($color)
	{
		    if (is_array($color) && sizeof($color) == 3)
		    {
		        list($r, $g, $b) = $color;
		    }elseif(strpos($color, "rgb") !== false)
		    {
		    	preg_match("/rgb\s*\(\s*([^,]+)\s*,\s*([^,]+)\s*,\s*([^,]+)\s*\)/i", $color, $arr);
		    	
		    	if(!empty($arr))
		    	{
		    		list($search, $r, $g, $b) =  $arr;
		    	}
		    	
			}
			
		    $r = intval($r); 
		    $g = intval($g);
		    $b = intval($b);
		
		    $r = dechex($r<0?0:($r>255?255:$r));
		    $g = dechex($g<0?0:($g>255?255:$g));
		    $b = dechex($b<0?0:($b>255?255:$b));
		
		    $color = (strlen($r) < 2?'0':'').$r;
		    $color .= (strlen($g) < 2?'0':'').$g;
		    $color .= (strlen($b) < 2?'0':'').$b;
		    return '#'.$color;	
	}
	
	/**
	 * Helper function to css hex color into css rgb
	 *
	 * @param string $color
	 * @return string $color in hex format
	 * @access public
	 * @since 1.0.0.0
	 */
	public static function hex2rgb($color)
	{
		    if ($color[0] == '#')
		        $color = substr($color, 1);
		
		    if (strlen($color) == 6)
		        list($r, $g, $b) = array($color[0].$color[1],
		                                 $color[2].$color[3],
		                                 $color[4].$color[5]);
		    elseif (strlen($color) == 3)
		        list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
		    else
		        return false;
		
		    $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);
		
		    return "rgb($r, $g, $b)";
	}	
	
	/**
	 * Default metabox for setting page
	 *
	 * @access public
	 * @since 1.0.0.0
	 */	
	public static function metabox_help()
	{
		?>
		<ul class="list_metabox">
			<li><a href="https://twitter.com/intent/tweet?text=<?php echo urlencode(__("type your question here...")); ?>&amp;hashtags=layout_engine&amp;via=simple_ux" target="_blank"><?php _e('Ask your question on twitter','layout-engine'); ?></a></li>
			<li><a href="http://simpleux.co.uk/plugins/wordpress/layout-engine" target="_blank"><?php _e('Documentation','layout-engine'); ?></a></li>
			<li><a href="<?php echo admin_url("theme-install.php?tab=search&features[]=layout-engine"); ?>"><?php _e('Browse LE compatible themes','layout-engine'); ?></a></li>
		</ul>
		<?php
	}
	
}

/**
 * Extended version of Less CSS compiler which prefer injected variable values over style sheet variable values.
 *
 * @package WordPress
 * @subpackage layout_engine
 * @since 1.0.0.0
 */
class lessc_le_compiler extends lessc
{
	function compileProp($prop, $block, $tags, &$_lines, &$_blocks) 
	{
		if($prop[0] == "assign") 
		{
			list(, $name, $value) = $prop;
			if ($name[0] == $this->vPrefix) 
			{
				if(in_array($name, array_keys($this->env->parent->store)))
				{
					//Do not override default value
					return false;
				}
			}			
		}
		
		return parent::compileProp($prop, $block, $tags, &$_lines, &$_blocks);
	}	
}

add_action( 'admin_init', array('LE_LESSCSS_Admin','theme_options_init' ));
add_action( 'init', array('LE_LESSCSS_Admin', 'init')); //callback for customizer as it is frontend based.


?>