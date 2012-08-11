<?php
/***
Plugin Name: Layout Engine
Plugin URI: http://simpleux.co.uk/plugins/wordpress/layout-engine
Description: Provide drag & drop layout designer support, necessary tools and administration to give more control on dynamic sidebar creation, widget and layout management also empower lesscss features and theme options management.
Author: Junaid Ahmed
Author URI: http://www.simpleux.co.uk
Version: 1.0.0.0
***/

if(!defined("DS"))
	define("DS", DIRECTORY_SEPARATOR);

if(!defined('LE_ABSPATH'))
	define( 'LE_ABSPATH', plugin_dir_path( __FILE__ ) );

/**
 * External Libraries
 */
//LessCSS Support
if(!class_exists('lessc'))
{
	require_once(LE_ABSPATH.'vendors'.DS.'lesscss'.DS.'lessc.inc.php');
}


//Core Class
require_once(LE_ABSPATH.'base.php');
require_once(LE_ABSPATH.'query_conditions.php');
require_once(LE_ABSPATH.'lesscss_admin.php');
require_once(LE_ABSPATH.'utilities.php');


//Administration (Backend)
require_once(LE_ABSPATH.'admin.php');

//Block items (Default)
require_once(LE_ABSPATH.'blocks'.DS.'block.core.php');
require_once(LE_ABSPATH.'blocks'.DS.'block.dynamic_sidebar.php');
require_once(LE_ABSPATH.'blocks'.DS.'block.widget.php');
require_once(LE_ABSPATH.'blocks'.DS.'block.shortcode.php');
require_once(LE_ABSPATH.'blocks'.DS.'block.loop.php');
require_once(LE_ABSPATH.'blocks'.DS.'block.loop_comments.php');
require_once(LE_ABSPATH.'blocks'.DS.'block.navigation.php');



?>