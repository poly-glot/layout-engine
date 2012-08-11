<?php
/**
 * Wordpress next/prev navigation object
 *
 * @package WordPress
 * @subpackage Layout_Manager
 * @since 1.0.0.0
 */

//Register
add_filter('le_layout_block_objects', array('LE_Navigation','register'));


class LE_Navigation
{
	/**
	 * {@internal Missing Short Description}
	 *
	 * @access public
	 * @since 1.0.0.0
	 */	
	public static function register($objects)
	{
		$objects['navigation'] = array
		(
				'name' => __('Navigation','layout-engine'),
				'callback' => array('LE_Navigation','render'),
				'callback_frontend' => array('LE_Navigation','render_frontend')
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
	 * Render a block item :: navigation
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
		global $wp_query;
		
		if ( $wp_query->max_num_pages > 1 ) : ?>
			<nav>
				<div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'layout-engine' ) ); ?></div>
				<div class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'layout-engine' ) ); ?></div>
			</nav>
		<?php endif;		
		
	}	
	
}


?>