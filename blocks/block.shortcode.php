<?php
/**
 * Shortcode management
 *
 * @package WordPress
 * @subpackage Layout_Manager
 * @since 1.0.0.0
 */

//Register
add_filter('le_layout_block_objects', array('LE_Shortcode','register'));


class LE_Shortcode
{
	/**
	 * Register dynamic sidebar object into drag and drop element area.
	 *
	 * @access public
	 * @since 1.0.0.0
	 */	
	public static function register($objects)
	{
		$objects['shortcode'] = array
		(
				'name' => __('Shortcode','layout-engine'),
				'callback' => array('LE_Shortcode','render'),
				'callback_frontend' => array('LE_Shortcode','render_frontend')
		);
			
		return $objects;
	}
	
	/**
	 * Show shortcode form
	 *
	 * @access public
	 * @since 1.0.0.0
	 */	
	public static function render()
	{
		$shortcode_title = "";
		$shortcode_text = "";
		
		$widget = LE_Base::getBlockItemByRuntimeID($_GET['runtime_id']);
		if(!empty($widget))
		{
			$shortcode_title = $widget['args']['title'];
			$shortcode_text = $widget['args']['text'];
		}
		?>
				<div class="layout_engine_form_widget">
					<div class="updated hidden" id="message"></div>
							
					<form method="post" id="shortcode_form">
								<input type="hidden" name="id" id="id" value=""  class="resetable"/>
								<input type="hidden" name="action" id="action" value="layout_engine_ajax_save_sidebar" />
								<?php wp_nonce_field( 'save-le-layout-block', '_wpnonce_layout_manager_admin', false ); ?>
								<input type="hidden" name="runtime_id" id="runtime_id" value="<?php echo $_REQUEST['runtime_id']; ?>" />	
										
								<p><label for="shortcode_title"><?php _e('Title (optional):','layout-engine')?></label>
								<input type="text" value="<?php echo $shortcode_title; ?>" name="shortcode_title" id="shortcode_title" class="widefat"></p>			
								
								<p><label for="shortcode_text"><?php _e('Shortcode:','layout-engine')?></label>
								<textarea name="shortcode_text" id="shortcode_text" cols="20" rows="16" class="widefat"><?php echo $shortcode_text; ?></textarea></p>	
								
								<p><input type="button" accesskey="s" tabindex="5" value="Save" class="button-primary" id="shortcode_save" name="shortcode_save"></p>	
					</form>
				</div>	
			<?php	
	}
	
	/**
	 * Render a block item :: shortcode
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
		$text = $block_item['args']['text'];
		if(!empty($text))
			do_shortcode($text);
	}	
}


?>