<?php

/**
 * Magazine articles use the built in post post type in WordPress.
 *
 * Class WSU_Magazine_Article
 */
class WSU_Magazine_Article {
	/**
	 * @var WSU_Magazine_Article
	 */
	private static $instance;

	/**
	 * Maintain and return the one instance and initiate hooks when
	 * called the first time.
	 *
	 * @return \WSU_Magazine_Article
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WSU_Magazine_Article;
			self::$instance->setup_hooks();
		}
		return self::$instance;
	}

	/**
	 * Setup hooks for the plugin.
	 */
	public function setup_hooks() {
		add_action( 'restrict_manage_posts', array( $this, 'add_issue_filter' ) );
	}

	/**
	 * Add a filter on the posts list table to selectively view articles by their issue.
	 */
	public function add_issue_filter() {
		global $typenow;

		if ( 'post' !== $typenow ) {
			return;
		}

		$taxonomy = get_taxonomy( WSU_Magazine_Issue()->taxonomy_slug );
		wp_dropdown_categories( array(
			'show_option_all' => 'All issues',
			'taxonomy'        => $taxonomy->name,
			'name'            => $taxonomy->name,
			'orderby'         => 'name',
			'selected'        => isset( $_GET[ $taxonomy->name ] ) ? $_GET[ $taxonomy->name ] : '',
			'show_count'      => true,
			'hide_empty'      => true,
			'value_field'     => 'slug',
		) );
	}
}

add_action( 'after_setup_theme', 'WSU_Magazine_Article', 11 );
/**
 * Start things up.
 *
 * @return \WSU_Magazine_Article
 */
function WSU_Magazine_Article() {
	return WSU_Magazine_Article::get_instance();
}