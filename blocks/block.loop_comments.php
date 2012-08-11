<?php
/**
 * Wordpress comments loop
 *
 * @package WordPress
 * @subpackage Layout_Manager
 * @since 1.0.0.0
 */

//Register
add_filter('le_layout_block_objects', array('LE_Commentloop','register'));


class LE_Commentloop
{
	/**
	 * {@internal Missing Short Description}
	 *
	 * @access public
	 * @since 1.0.0.0
	 */	
	public static function register($objects)
	{
		$objects['comments_loop'] = array
		(
				'name' => __('Comments loop','layout-engine'),
				'callback' => array('LE_Commentloop','render'),
				'callback_frontend' => array('LE_Commentloop','render_frontend')
		);
			
		return $objects;
	}
	
	/**
	 * {@internal Missing Short Description}
	 *
	 * @access public
	 * @since 1.0.0.0
	 */	
	public static function render()
	{
		_e('Sorry; but widget does not have any options');	
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
		comments_template( '', true );
	}	
}


?>