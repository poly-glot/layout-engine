<?php
/**
 * Provide utiltiy functions/tasks
 *
 * @package WordPress
 * @subpackage layout_engine
 * @since 1.0.0.0
 */
class LE_Utilities
{
	/**
	 * Hooks and attach to different tasks(s)
	 *
	 * @access public
	 * @since 1.0.0.0
	 */	
	public static function init()
	{
		add_action('admin_action_le_export', array('LE_Utilities','export'));
		add_action('admin_action_le_reset', array('LE_Utilities','reset'));
		add_action('admin_action_le_reset_undo', array('LE_Utilities','restore'));

	}
	
	/**
	 * Show List of available utilities
	 * 
	 * @access public
	 * @since 1.0.0.0
	 */	
	public static function admin()
	{
		load_template( LE_ABSPATH . 'views' . DS .'utilities.php');
	}
	
	/**
	 * Print LE Settings in php exportable arrays
	 *
	 * @access public
	 * @since 1.0.0.0
	 */
	public static function export()
	{
		header('Content-Type: text/plain');
		header('Content-Encoding: UTF-8');
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
				
		$settings = LE_Base::getSettings();
		do_action_ref_array('le_export', array(&$settings));
		
		var_export($settings);
	}
	
	/**
	 * Remove all layout settings
	 *
	 * @access public
	 * @since 1.0.0.0
	 */	
	public static function reset()
	{
		LE_Base::resetSettings();
		$goback = add_query_arg( 'message', '2',  wp_get_referer() );
		wp_redirect( $goback );		
	}
	
	/**
	 * Remove all layout settings
	 *
	 * @access public
	 * @since 1.0.0.0
	 */
	public static function restore()
	{
		LE_Base::restoreSettings();
		$goback = add_query_arg( 'message', '3',  wp_get_referer() );
		wp_redirect( $goback );
	}	
	
	/**
	 * Import settings defined by theme or hooks.
	 *
	 * @param ref array $settings raw settings; which needs to be imported.
	 * @access public
	 * @since 1.0.0.0
	 * @return void
	 */
	public static function import(&$settings)
	{
		do_action_ref_array('le_import', array(&$settings));
	}	
}

LE_Utilities::init();



?>