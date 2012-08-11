<?php
/**
 * Extended Query conditions to detect the current query type i.e. is_page, is_post
 *
 * @package WordPress
 * @subpackage layout_engine
 * @since 1.0.0.0
 */


class Query_Conditions
{
	/**
	 * If it is a single page?
	 * 
	 * @access public
	 * @since 1.0.0.0
	 * @return bool
	 */	
	public static function is_page()
	{
		if(is_admin())
			return ((!empty($_REQUEST['id'])) && (strpos($_REQUEST['id'], 'index/single/page') !== false));
		else
			return is_page();
	}
	
	/**
	 * If it is a single post?
	 *
	 * @access public
	 * @since 1.0.0.0
	 * @return bool
	 */
	public static function is_single()
	{
		if(is_admin())
			return ((!empty($_REQUEST['id'])) && (strpos($_REQUEST['id'], 'index/single') !== false));
		else
			return is_single();
	}	
	
	/**
	 * Is the query a 404 (returns no results)?
	 *
	 * @access public
	 * @since 1.0.0.0
	 * @return bool
	 */
	function is_404() 
	{
		if(is_admin())
			return ((!empty($_REQUEST['id'])) && ($_REQUEST['id'] == 'index/404'));
		else
			return is_page();
	}	
	
	/**
	 * Is the query for a search?
	 *
	 * @access public
	 * @since 1.0.0.0
	 * @return bool
	 */
	function is_search() 
	{
		if(is_admin())
			return ((!empty($_REQUEST['id'])) && ($_REQUEST['id'] == 'index/search'));
		else
			return is_search();
	}	
	
	/**
	 * Is the query for an archive page?
	 *
	 * Month, Year, Category, Author, Post Type archive...
	 *
	 * @access public
	 * @since 1.0.0.0
	 * @return bool
	 */
	function is_archive() 
	{
		if(is_admin())
			return ((!empty($_REQUEST['id'])) && (strpos($_REQUEST['id'], '/archive') !== false));
		else
			return is_archive();
	}

	/**
	 * Is the query for a post type archive page?
	 *
	 * @access public
	 * @since 1.0.0.0
	 * @return bool
	 */
	function is_post_type_archive() 
	{
		if(is_admin())
			return ((!empty($_REQUEST['id'])) && (strpos($_REQUEST['id'], 'index/archive/post_types') !== false));
		else
			return is_post_type_archive();
	}	
	
	/**
	 * Is the query for an author archive page?
	 *
	 * @access public
	 * @since 1.0.0.0
	 * @return bool
	 */	
	function is_author() 
	{
		if(is_admin())
			return ((!empty($_REQUEST['id'])) && (strpos($_REQUEST['id'], 'index/archive/author') !== false));
		else
			return is_author() ;
	}	
	
	/**
	 * Is the query for a category archive page?
	 *
	 * @access public
	 * @since 1.0.0.0
	 * @return bool
	 */
	function is_category() 
	{
		if(is_admin())
			return ((!empty($_REQUEST['id'])) && (strpos($_REQUEST['id'], 'index/archive/taxonomies/category') !== false));
		else
			return is_category() ;		
	}	
	
	/**
	 * Is the query for a tag archive page?
	 *
	 * @access public
	 * @since 1.0.0.0
	 * @return bool
	 */
	function is_tag() 
	{
		if(is_admin())
			return ((!empty($_REQUEST['id'])) && (strpos($_REQUEST['id'], 'index/archive/taxonomies/post_tag') !== false));
		else
			return is_tag();		
	}	
	
	/**
	 * Is the query for a taxonomy archive page?
	 *
	 * @access public
	 * @since 1.0.0.0
	 * @return bool
	 */
	function is_tax()
	{
		if(is_admin())
			return ((!empty($_REQUEST['id'])) && (strpos($_REQUEST['id'], 'index/archive/taxonomies') !== false));
		else
			return is_tax();
	}	
	
	/**
	 * Is the query for a date archive?
	 *
	 * @access public
	 * @since 1.0.0.0
	 * @return bool
	 */
	function is_date() 
	{
		if(is_admin())
			return ((!empty($_REQUEST['id'])) && (strpos($_REQUEST['id'], 'index/archive/date') !== false));
		else
			return is_date();
	}	
	
	/**
	 * Is the query for a day archive?
	 *
	 * @access public
	 * @since 1.0.0.0
	 * @return bool
	 */
	function is_day() 
	{
		if(is_admin())
			return ((!empty($_REQUEST['id'])) && (strpos($_REQUEST['id'], 'index/archive/date/day') !== false));
		else
			return is_day();
	}	
	
	/**
	 * Is the query for a month archive?
	 *
	 * @access public
	 * @since 1.0.0.0
	 * @return bool
	 */
	function is_month()
	{
		if(is_admin())
			return ((!empty($_REQUEST['id'])) && (strpos($_REQUEST['id'], 'index/archive/date/month') !== false));
		else
			return is_month();
	}	
	
	/**
	 * Is the query for the blog homepage?
	 *
	 * @access public
	 * @since 1.0.0.0
	 * @return bool
	 */
	function is_home()
	{
		if(is_admin())
			return ((!empty($_REQUEST['id'])) && ($_REQUEST['id'] == 'index'));
		else
			return is_home();
	}	
	
	/**
	 * Is the query for the front page of the site?
	 *
	 * @access public
	 * @since 1.0.0.0
	 * @return bool
	 */
	function is_front_page()
	{
		if(is_admin())
			return ((!empty($_REQUEST['id'])) && ($_REQUEST['id'] == 'index'));
		else
			return is_front_page();
	}	
}




?>