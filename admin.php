<?php
/**
 * Layout Engine wordpress administration
 *
 * @package WordPress
 * @subpackage layout_engine
 * @since 1.0.0.0
 */


class LE_Admin
{
	/**
	 * Attach necessary hooks and assets (css/js) and define default blocks and load default drag and drop objects.
	 *
	 * @uses is_admin() to make sure resources/hooks are only avaiable to backend.
	 * @access public
	 * @since 1.0.0.0
	 */	
	public static function init()
	{
		if(is_admin())
		{
				add_action( 'admin_menu', array('LE_Admin','admin_menu' ));
		
				//Default Tab Action hooks
				add_action('le_admin_theme_settings', array('LE_Admin','theme_settings' ));
				add_action('le_admin_theme_layout', array('LE_Admin','theme_layout' ));
				add_action('le_admin_theme_utilities', array('LE_Utilities','admin' ));
				
				//Loading assets
				add_action( 'admin_print_styles-appearance_page_layout_engine', array('LE_Admin','admin_enqueue_assets' ));
				
				//Ajax callbacks to manage layout blocks
				add_action( 'wp_ajax_layout_manager_ajax_savelayout', array('LE_Admin','layout_manager_ajax_savelayout'));
				add_action( 'wp_ajax_layout_manager_runtime_id', array('LE_Admin','layout_manager_runtime_id'));
				
				//Popup Hanlder
				add_action('admin_action_layout_manager_blockitem_form', array('LE_Admin','layout_manager_blockitem_form'));
		}else{
				//Ribbon Admin support
				add_action('admin_bar_menu',array('LE_Admin','admin_bar'),1000);			
		}
		
		//Default Blocks
		LE_Base::register_block(array
									(
										'id'=> 'header',
										'name' => 'Header',
										'priority' => 50
									)
								);
		
		LE_Base::register_block(array
									(
										'id'=> 'body',
										'name' => 'Body',
										'priority' => 100
									)
								);		

		LE_Base::register_block(array
									(
										'id'=> 'footer',
										'name' => 'Footer',
										'priority' => 150
									)
								);
								
	}
	
	/**
	 * Attach menu item into wordpress administration menu
	 *
	 * @access public
	 * @since 1.0.0.0
	 */	
	public static function admin_menu()
	{
		$theme_page = add_theme_page
		(
				__( 'Layout Engine', 'le' ),   			// Name of page
				__( 'Layout Engine', 'le' ),   			// Label in menu
				'edit_theme_options',                   // Capability required
				'layout_engine',                        // Menu slug, used to uniquely identify the page
				array('LE_Admin','render' )				// Function that renders the options page
		);
		
		if ( ! $theme_page )
			return;
		
		//add_action( "load-$theme_page", array('LE_Admin','help' ));		
	}
	
	/**
	 * Loading javascript/css
	 *
	 * @access public
	 * @since 1.0.0.0
	 */	
	public static function admin_enqueue_assets()
	{
		wp_enqueue_script( 'le-layout-manager-admin', plugins_url('/assets/js/layout_manager_admin.dev.js', __FILE__), array('jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-droppable' ), '2012-07-25' );

		wp_enqueue_style( 'le-layout-manager-admin', plugins_url('/assets/css/layout_manager_admin.css', __FILE__), false, '2012-07-25' );
		
		wp_localize_script( 'le-layout-manager-admin', 'objectL10n', array
		(
				'Add' => __( 'Add' ),
				'Edit' => __( 'Edit' ),
				'AdvanceOptions' => __( 'Advance options' ),
				'HideAdvanceOptions' => __( 'Hide advance options' ),
		) );
		
		add_thickbox();
		
		wp_enqueue_script( 'farbtastic');
		wp_enqueue_style( 'farbtastic' );
		
		
		//Library for Widget Objects like Sidebar, Wordpress Widgets etc
		if(isset($_GET['action']) && $_GET['action'] == "layout_manager_blockitem_form")
		{
			wp_enqueue_script( 'le-layout-manager-blockitem-form', plugins_url('/assets/js/layout_manager_blockitem_form.dev.js', __FILE__), array( 'jquery-ui-sortable'), '2012-07-25');
		}		
	}
	
	
	/**
	 * Provides global layout template for Layout Engine
	 *
	 * @uses apply_filters() calls 'le_admin_tabs' hook to define additional layout engine tabs.
	 * @uses apply_filters() calls 'le_admin_default_tab' hook to select a default tab.
	 * @uses do_action() dalls 'le_admin_%s' hook to execute an action to form a layout where %s is key of active tab e.g. theme_settings.
	 * @uses admin_url() to generate the base url for tab.
	 * @uses add_query_arg() to append the tab index into base url.
	 *
	 * @access public
	 * @since 1.0.0.0
	 */	
	public static function render()
	{
		?>
			<div class="wrap" id="layout_engine_admin">
				<?php screen_icon(); ?>
				<h2 class="nav-tab-wrapper"><?php 
				
						//Show different Layout Options (Tabs)
						$_default_tabs = array
						(
								'theme_settings' => __('Settings','le'),
								'theme_layout' => __('Layout','le'),
								'theme_utilities' => __('Utilities','le')
						);
									
						$tabs = apply_filters('le_admin_tabs', $_default_tabs);		
						
						if ( isset($_GET['tab']) && array_key_exists($_GET['tab'], $tabs) )
							$current_tab = $_GET['tab'];
						else
							$current_tab = apply_filters('le_admin_default_tab', 'theme_settings');				
						
						//Default menu
						$link = admin_url('themes.php?page=layout_engine');
						
						foreach($tabs as $k=>$v)
						{
							$href = add_query_arg(array('tab' => $k), $link);
									
							if($k == $current_tab) $class =" nav-tab-active";
							else $class="";
							
							echo '<a href="'. $href . '" class="nav-tab'.$class.'">'.$v.'</a>';
						}
				
				?></h2>
				<div class="tab-data">
						<?php settings_errors(); ?>
						<?php do_action('le_admin_' . $current_tab); ?>
				</div>
			</div>
			<?php	
	}
	
	/**
	 * Settings tab callback to Allows you to administrate lesscss settings defined.
	 *
	 * @access public
	 * @since 1.0.0.0
	 */	
	public static function theme_settings()
	{
		LE_LESSCSS_Admin::admin();		
	}
	
	/**
	 * Layout tab callback to Allows you to administrate layout.
	 * Layout tab can show you templates either in list or tree format, and if there is any $_REQUEST['id'] argument, it swap the editor layout.
	 *
	 *
	 * @access public
	 * @since 1.0.0.0
	 */
	public static function theme_layout()
	{
		if(isset($_REQUEST['id']) && !empty($_REQUEST['id']))
			load_template( LE_ABSPATH . 'views' . DS .'theme_layout_edit.php');
		else
			load_template( LE_ABSPATH . 'views' . DS .'theme_layout.php');
	}	
	
	/**
	 * Walk through arrays of Template Hierarchy and output html list. 
	 * Utitlity function for views.
	 *
	 * @param array $tree associative_array of different views available in wordpress.
	 * @param string $tab tab delimiter to make pretty indent tree structure
	 * @param string $parent reference the parent element to create unique id
	 * @param string $link
	 *
	 * @access public
	 * @since 1.0.0.0
	 * @return void
	 */
	function layout_tree_walk($tree, $tab = "", $parent = "", $link_default = "")
	{
		$tab .= "\t";
	
		if(is_array($tree))
		{
	
			$title = '';
			if(array_key_exists('title', $tree))
			{
				$title = $tree['title'];
				$id = substr($parent, 0, -1); //remove last trailing slash
				$link = add_query_arg('id', $id, $link_default);
	
				printf($tab."\t<li><a href=\"%s\">%s</a>".PHP_EOL, $link, $title);
					
				unset($tree['title']);
					
				echo $tab.'<ul>'.PHP_EOL;
			}
	
			foreach($tree as $k=>$v)
			{
				//Recurrision
				if(is_array($v))
				{
					$parent_sub = $parent.$k."/";
					LE_Admin::layout_tree_walk($v, $tab,$parent_sub,$link_default);
				}else{
	
					$id = $parent.$k;
					$link = add_query_arg('id', $id, $link_default);
					printf($tab."\t<li><a href=\"%s\">%s</a></li>".PHP_EOL, $link, $v);
				}
			}
	
			if(!empty($title))
			{
				echo $tab.'</ul>'.PHP_EOL;
				echo $tab.'</li>'.PHP_EOL;
			}
		}
	}	
	
	/**
	 * Create a preview link for drag and drop layout engine.
	 * Utitlity function for views.
	 *
	 * @access public
	 * @since 1.0.0.0
	 * @return void
	 */	
	function layout_preview_button()
	{
		$id = $_REQUEST['id'];
		$object_id  = $_REQUEST['object_id'];
		$link = home_url();
	
		if(!empty($id))
		{
			$id_arr = explode('/', $id);
	
			//Taxonomies
			if(strpos($id,'taxonomies') !== false)
			{
				$tax = $id_arr[3];
					
				if(!empty($object_id))
				{
					$term = get_term($object_id, $tax);
				}else{
					$terms = get_terms( $tax, array('number'=>1, 'hide_empty' => 1) );
					if ( !empty( $terms) && !is_wp_error( $terms ) )
					{
						$term = $terms[0];
					}
				}
					
				if(!empty($term) && !is_wp_error($term))
					$link = get_term_link($term->slug, $tax);
			}elseif(strpos($id,'post_types') !== false)
			{
				//Post Archieve
				$post_type = $id_arr[3];
				$link = get_post_type_archive_link($post_type);
			}elseif(strpos($id,'date') !== false)
			{
				global $wpdb;
					
				//Daily Archieve
				$date_type = $id_arr[3];
				if(empty($date_type)) $date_type = "year";
					
				$link = get_post_type_archive_link($post_type);
				$limit = "LIMIT 0,1";
					
				$where = "WHERE post_type = 'post' AND post_status = 'publish'";
				$join = "";
					
				if($date_type == "year")
				{
					$query = "SELECT YEAR(post_date) AS `year`, count(ID) as posts FROM $wpdb->posts $join $where GROUP BY YEAR(post_date) ORDER BY posts DESC $limit";
					$arcresults = $wpdb->get_results($query);
					if ( $arcresults )
					{
						$arcresult = $arcresults[0];
						$link = get_year_link($arcresult->year);
					}
				}elseif($date_type == "month")
				{
					$query = "SELECT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, count(ID) as posts FROM $wpdb->posts $join $where GROUP BY YEAR(post_date), MONTH(post_date) ORDER BY posts DESC $limit";
					$arcresults = $wpdb->get_results($query);
					if ( $arcresults )
					{
						$arcresult = $arcresults[0];
						$link = get_month_link( $arcresult->year, $arcresult->month );
					}
				}elseif($date_type == "day")
				{
					$query = "SELECT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, DAYOFMONTH(post_date) AS `dayofmonth`, count(ID) as posts FROM $wpdb->posts $join $where GROUP BY YEAR(post_date), MONTH(post_date), DAYOFMONTH(post_date) ORDER BY posts DESC $limit";
					$arcresults = $wpdb->get_results($query);
					if ( $arcresults )
					{
						$arcresult = $arcresults[0];
						$link = get_day_link($arcresult->year, $arcresult->month, $arcresult->dayofmonth);
					}
				}
			}elseif(strpos($id,'single') !== false)
			{
				global $wpdb;
					
				if(!empty($object_id))
				{
					$link = get_permalink($object_id);
				}else{
					$post_type = $id_arr[2];
					$limit = "LIMIT 0,1";
	
					$where = "WHERE post_type = '".$post_type."' AND post_status = 'publish'";
					$join = "";
	
					$query = "SELECT ID FROM $wpdb->posts $join $where GROUP BY YEAR(post_date), MONTH(post_date), DAYOFMONTH(post_date) ORDER BY post_date DESC $limit";
					$arcresults = $wpdb->get_results($query);
					if ( $arcresults )
					{
						$arcresult = $arcresults[0];
						$link = get_permalink($arcresult->ID);
					}
				}
			}
	
			$link = apply_filters('layout_engine_preview_link', $link, $id, $object_id);
	
	
			if(!empty($link))
			{
				?>
			<div id="preview_button">
				<a tabindex="4" id="post-preview" target="wp-preview" href="<?php echo $link; ?>" class="preview button-primary"><?php _e('Preview'); ?></a>
			</div>	
		<?php
			}
		}
	}
	
	/**
	 * Ajax callback to save the order of elements in block.
	 *
	 * @access public
	 * @since 1.0.0.0
	 * @return void
	 */	
	public static function layout_manager_ajax_savelayout()
	{
		check_ajax_referer(  'save-le-layout-admin', '_wpnonce_layout_manager_admin' );
		
		$data = $_POST;
		$data_new = array();

		//@todo check if we really need to do this
		//Resetting layout
		if(!isset($data['data']) || empty($data['data']))
		{
			if(!empty($data['layout']))
			{
				$settings = LE_Base::getSettings();
				$settings[$data['layout']] = array();
				LE_Base::saveSettings($settings);
				
				//Response
				echo json_encode( array('type' => 'success'));
				wp_die();				
			}
		}
		
		if(!empty($data['data']))
		{
			$settings = LE_Base::getSettings();
			
		
			foreach($data['data'] as $layout=>$layout_blocks)
			{
				$data_new[$layout] = array();

				//Remove Junk/Dummy Data & convert posted data into layout settings
				foreach($layout_blocks as $layout_block_name=>$layout_widgets)
				{
					$i = 0;
		
					if(strpos($layout_block_name,'__') !== false)
					{
						unset($layout_blocks[$layout_block_name]);
					}else{
							
						foreach($layout_widgets as $i => $new_widget_settings)
						{
							$previous_widget = LE_Base::getBlockItemByRuntimeID($new_widget_settings['runtime_id']);
							$new_widget_settings['args'] = (array) $previous_widget['args'];
							$data_new[$layout][$layout_block_name][$i] = $new_widget_settings;
						}
							
					}
				}
					
				//Saving
				$settings[$layout] = $data_new[$layout];
				LE_Base::saveSettings($settings);
			}
		
			//Response
			echo json_encode( array('type' => 'success'));
			wp_die();
		}
	}
	
	/**
	 * Ajax callback to automatically create unique id for each drag block.
	 * Settings are stored in assosicative array and when layout engine provide new settings through URL, it change it array index
	 * so in order to keep the old settings preserved we need to provide support for temporary runtime id to select the element in associative array.
	 *
	 * @access public
	 * @since 1.0.0.0
	 * @return void
	 */
	public static function layout_manager_runtime_id()
	{
		echo wp_generate_password(6,false,false);
		wp_die();	
	}	
	
	/**
	 * Load layout block item configuration in a popup.
	 *
	 * @uses add_action() calls 'admin_print_styles' to load Layout Manager Assets.
	 * @access public
	 * @since 1.0.0.0
	 * @return void
	 */	
	public static function layout_manager_blockitem_form()
	{
		define( 'IFRAME_REQUEST' , true );
		$widget = strtolower($_REQUEST['widget']);

		$available_widgets = LE_Base::getBlockObjects();
	
		if(!empty($widget) && array_key_exists($widget, $available_widgets))
		{
			//FIXME :: load assets for iframe
			add_action( 'admin_print_styles', array('LE_Admin','admin_enqueue_assets' ));
			
			return wp_iframe($available_widgets[$widget]['callback']);
		}else{
			wp_die(__("No Layout block item id specified or callback is defined.","layout-engine"));
		}
	}

	/**
	 * Add Ribbon support to modify the current layout in admin.
	 *
	 * @access public
	 * @since 1.0.0.0
	 * @return void
	 */	
	public static function admin_bar()
	{
		global $wp_admin_bar;

		if(!is_admin())
		{
			$id = get_current_layout_id();
			
			
			$le_admin = admin_url('themes.php?page=layout_engine&tab=theme_layout&id='.$id);
			$wp_admin_bar->add_menu( array( 'title' =>  __('Edit Layout','layout-engine'), 'href' => $le_admin) );			
		}
	}

}

add_action('init',array('LE_Admin', 'init'));



?>