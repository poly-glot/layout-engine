<?php
/**
 * Wordpress posts loop
 *
 * @package WordPress
 * @subpackage Layout_Manager
 * @since 1.0.0.0
 */

//Register
add_filter('le_layout_block_objects', array('LE_Postloop','register'));


class LE_Postloop
{
	/**
	 * {@internal Missing Short Description}
	 *
	 * @access public
	 * @since 1.0.0.0
	 */	
	public static function register($objects)
	{
		$objects['posts_loop'] = array
		(
				'name' => __('Posts loop','layout-engine'),
				'callback' => array('LE_Postloop','render'),
				'callback_frontend' => array('LE_Postloop','render_frontend')
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
	 * Render a block item :: loop
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
		if ( have_posts() ) :

		while ( have_posts() ) : the_post();
			
			if(LE_Postloop::has_template_part('content', get_post_format()))
				get_template_part( 'content', get_post_format() );
			else
				LE_Postloop::default_post_template();
			
		endwhile;
		
		else:
			LE_Postloop::post_404();
		endif;
	}	
	
	/**
	 * Checking if template has content template
	 *
	 * @since 1.0.0.0
	 * @param string $slug The slug name for the generic template.
 	 * @param string $name The name of the specialised template. 
	 * @return bool
	 */	
	public static function has_template_part( $slug, $name = null ) 
	{
		$templates = array();
		if ( isset($name) )
			$templates[] = "{$slug}-{$name}.php";
		
		$templates[] = "{$slug}.php";
		$template = locate_template($templates, false, false);
		return (!empty($template));		
	}
	
	/**
	 * Render a post 404
	 *
	 * @since 1.0.0.0
	 * @return void
	 */
	public static function post_404()
	{
		?>
					<article id="post-0" class="post no-results not-found">
						<header class="entry-header">
							<h1 class="entry-title"><?php _e( 'Nothing Found', 'layout-engine' ); ?></h1>
						</header><!-- .entry-header -->
	
						<div class="entry-content">
							<p><?php _e( 'Apologies, but no results were found for the requested archive. Perhaps searching will help find a related post.', 'layout-engine' ); ?></p>
							<?php 
								if(function_exists('get_search_form'))
									get_search_form(); 
							?>
						</div><!-- .entry-content -->
					</article><!-- #post-0 -->	
		<?php
		}	
	
	/**
	 * Render a default post template (if theme didnt specify any)
	 *
	 * @since 1.0.0.0
	 * @return void
	 */	
	public static function default_post_template()
	{
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<header class="entry-header">
			<?php if ( is_sticky() ) : ?>
				<hgroup>
					<h2 class="entry-title"><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'layout-engine' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
					<h3 class="entry-format"><?php _e( 'Featured', 'layout-engine' ); ?></h3>
				</hgroup>
			<?php else : ?>
			<h1 class="entry-title"><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'layout-engine' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h1>
			<?php endif; ?>

			<?php if ( 'post' == get_post_type() ) : ?>
			<div class="entry-meta">
				<?php LE_Postloop::posted_on(); ?>
			</div><!-- .entry-meta -->
			<?php endif; ?>

			<?php if ( comments_open() && ! post_password_required() ) : ?>
			<div class="comments-link">
				<?php comments_popup_link( '<span class="leave-reply">' . __( 'Reply', 'layout-engine' ) . '</span>', _x( '1', 'comments number', 'layout-engine' ), _x( '%', 'comments number', 'layout-engine' ) ); ?>
			</div>
			<?php endif; ?>
		</header><!-- .entry-header -->

		<?php if ( is_search() ) : // Only display Excerpts for Search ?>
		<div class="entry-summary">
			<?php the_excerpt(); ?>
		</div><!-- .entry-summary -->
		<?php else : ?>
		<div class="entry-content">
			<?php the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'layout-engine' ) ); ?>
			<?php wp_link_pages( array( 'before' => '<div class="page-link"><span>' . __( 'Pages:', 'layout-engine' ) . '</span>', 'after' => '</div>' ) ); ?>
		</div><!-- .entry-content -->
		<?php endif; ?>

		<footer class="entry-meta">
			<?php $show_sep = false; ?>
			<?php if ( 'post' == get_post_type() ) : // Hide category and tag text for pages on Search ?>
			<?php
				/* translators: used between list items, there is a space after the comma */
				$categories_list = get_the_category_list( __( ', ', 'layout-engine' ) );
				if ( $categories_list ):
			?>
			<span class="cat-links">
				<?php printf( __( '<span class="%1$s">Posted in</span> %2$s', 'layout-engine' ), 'entry-utility-prep entry-utility-prep-cat-links', $categories_list );
				$show_sep = true; ?>
			</span>
			<?php endif; // End if categories ?>
			<?php
				/* translators: used between list items, there is a space after the comma */
				$tags_list = get_the_tag_list( '', __( ', ', 'layout-engine' ) );
				if ( $tags_list ):
				if ( $show_sep ) : ?>
			<span class="sep"> | </span>
				<?php endif; // End if $show_sep ?>
			<span class="tag-links">
				<?php printf( __( '<span class="%1$s">Tagged</span> %2$s', 'layout-engine' ), 'entry-utility-prep entry-utility-prep-tag-links', $tags_list );
				$show_sep = true; ?>
			</span>
			<?php endif; // End if $tags_list ?>
			<?php endif; // End if 'post' == get_post_type() ?>

			<?php if ( comments_open() ) : ?>
			<?php if ( $show_sep ) : ?>
			<span class="sep"> | </span>
			<?php endif; // End if $show_sep ?>
			<span class="comments-link"><?php comments_popup_link( '<span class="leave-reply">' . __( 'Leave a reply', 'layout-engine' ) . '</span>', __( '<b>1</b> Reply', 'layout-engine' ), __( '<b>%</b> Replies', 'layout-engine' ) ); ?></span>
			<?php endif; // End if comments_open() ?>

			<?php edit_post_link( __( 'Edit', 'layout-engine' ), '<span class="edit-link">', '</span>' ); ?>
		</footer><!-- #entry-meta -->
	</article><!-- #post-<?php the_ID(); ?> -->
	
	<?php
	}
	
	/**
	 * Prints HTML with meta information for the current post-date/time and author.
	 * 
	 * @since 1.0.0.0
	 * @return void
	 */
	public static function posted_on() {
		printf( __( '<span class="sep">Posted on </span><a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s" pubdate>%4$s</time></a><span class="by-author"> <span class="sep"> by </span> <span class="author vcard"><a class="url fn n" href="%5$s" title="%6$s" rel="author">%7$s</a></span></span>', 'layout-engine' ),
		esc_url( get_permalink() ),
		esc_attr( get_the_time() ),
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() ),
		esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
		esc_attr( sprintf( __( 'View all posts by %s', '' ), get_the_author() ) ),
		get_the_author()
		);
	}	
}


?>