<?php

class WSU_Magazine_Issue {
	/**
	 * @var WSU_Magazine_Issue
	 */
	private static $instance;

	public $content_type_slug = 'wsu_magazine_issue';

	/**
	 * @var string Slug for tracking the taxonomy of a magazine issue.
	 */
	public $taxonomy_slug = 'wsu_magazine_issue';

	/**
	 * Maintain and return the one instance and initiate hooks when
	 * called the first time.
	 *
	 * @return \WSU_Magazine_Issue
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WSU_Magazine_Issue;
			self::$instance->setup_hooks();
		}
		return self::$instance;
	}

	/**
	 * Setup hooks for the plugin.
	 */
	public function setup_hooks() {
		add_action( 'init', array( $this, 'register_content_type' ) );
		add_action( 'init', array( $this, 'register_taxonomy' ) );
	}
	
	public function register_content_type() {
		$args = array();
		register_post_type( $this->content_type_slug, $args );
	}

	/**
	 * Register a magazine issues taxonomy that will be attached to both issue content types and
	 * articles to provide an easy association.
	 */
	public function register_taxonomy() {
		$labels = array(
			'name'          => 'Issue',
			'singular_name' => 'Issue',
			'search_items'  => 'Search Issues',
			'all_items'     => 'All Issues',
			'edit_item'     => 'Edit Issue',
			'update_item'   => 'Update Issue',
			'add_new_item'  => 'Add New Issue',
			'new_item_name' => 'New Issue Name',
			'menu_name'     => 'Issue Taxonomy',
		);
		$args = array(
			'labels'            => $labels,
			'description'       => 'The magazine issue taxonomy attached to articles and issues.',
			'public'            => true,
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_in_menu'      => true,
			'rewrite'           => true,
			'query_var'         => $this->taxonomy_slug,
		);
		register_taxonomy( $this->taxonomy_slug, $this->content_type_slug, $args );
	}
}

add_action( 'after_setup_theme', 'WSU_Magazine_Issue', 11 );
/**
 * Start things up.
 *
 * @return \WSU_Magazine_Issue
 */
function WSU_Magazine_Issue() {
	return WSU_Magazine_Issue::get_instance();
}