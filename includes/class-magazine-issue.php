<?php

class WSU_Magazine_Issue {
	/**
	 * @var WSU_Magazine_Issue
	 */
	private static $instance;

	/**
	 * @var string Slug for tracking the post type of a magazine issue.
	 */
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

	/**
	 * Register a content type to track information about magazine issues.
	 */
	public function register_content_type() {
		$labels = array(
			'name' => 'Magazine Issues',
			'singular_name' => 'Magazine Issue',
			'all_items' => 'All Magazine Issues',
			'view_item' => 'View Issue',
			'add_new_item' => 'Add New Issue',
			'add_new' => 'Add New',
			'edit_item' => 'Edit Issue',
			'update_item' => 'Update Issue',
			'search_items' => 'Search Issues',
			'not_found' => 'No issues found',
			'not_found_in_trash' => 'No issues found in Trash',
		);

		$args = array(
			'labels' => $labels,
			'description' => 'Issues of the WSU Magazine.',
			'public' => true,
			'hierarchical' => false,
			'menu_position' => 5,
			'menu_icon' => 'dashicons-book-alt',
			'supports' => array(
				'title',
				'editor',
				'thumbnail',
				'revisions',
			),
			'taxonomies' => array(),
			'has_archive' => true,
			'rewrite' => false,
		);
		register_post_type( $this->content_type_slug, $args );
	}

	/**
	 * Register a magazine issues taxonomy that will be attached to both issue content types and
	 * articles to provide an easy association.
	 */
	public function register_taxonomy() {
		$labels = array(
			'name'          => 'Issue Label',
			'singular_name' => 'Issue Label',
			'search_items'  => 'Search Issue Labels',
			'all_items'     => 'All Issue Labels',
			'edit_item'     => 'Edit Issue Label',
			'update_item'   => 'Update Issue Label',
			'add_new_item'  => 'Add New Issue Label',
			'new_item_name' => 'New Issue Label Name',
			'menu_name'     => 'Issue Labels',
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
		register_taxonomy_for_object_type( $this->taxonomy_slug, 'post' );
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