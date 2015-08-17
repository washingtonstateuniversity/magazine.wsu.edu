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
		add_filter( 'body_class', array( $this, 'season_body_class' ) );
		add_action( 'admin_init', array( $this, 'register_builder_support' ) );
		add_filter( 'spine_builder_force_builder', array( $this, 'force_builder' ) );
		add_filter( 'make_will_be_builder_page', array( $this, 'force_builder' ) );
		add_action( 'pre_get_posts', array( $this, 'front_page_issue' ) );
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
		register_taxonomy_for_object_type( $this->taxonomy_slug, 'wsu_magazine_we' );
	}

	/**
	 * Add season to the list of body classes for individual articles and individual issues.
	 *
	 * @param array $body_classes List of current body classes.
	 *
	 * @return array Modified list of body classes.
	 */
	public function season_body_class( $body_classes ) {
		if ( is_singular() ) {
			$issues = wp_get_object_terms( get_the_ID(), $this->taxonomy_slug );

			if ( 1 >= count( $issues ) ) {
				$issue = explode( ' ', $issues[0]->name );
				$body_classes[] = 'season-' . esc_attr( strtolower( $issue[0] ) );
			}
		}

		return $body_classes;
	}

	/**
	 * Retrieve the issue name for the current article or issue view.
	 *
	 * @return string
	 */
	public function get_issue_name() {
		if ( is_singular() ) {
			$issues = wp_get_object_terms( get_the_ID(), $this->taxonomy_slug );

			if ( 1 >= count( $issues ) ) {
				return $issues[0]->name;
			}
		}

		return '';
	}

	/**
	 * Add support for the page builder to magazine issues.
	 */
	public function register_builder_support() {
		add_post_type_support( $this->content_type_slug, 'make-builder' );
	}

	/**
	 * Force builder to be used on every magazine issue.
	 *
	 * @return bool True if the magazine issue content type. False if not.
	 */
	public function force_builder() {
		return $this->content_type_slug === get_post_type();
	}

	/**
	 * Set the front page to display the most recent magazine issue.
	 *
	 * @param WP_Query $query
	 */
	public function front_page_issue( $query ) {
		if ( $query->is_main_query() && $query->is_home ) {
			$query->set( 'post_type', $this->content_type_slug );
			$query->set( 'posts_per_page', 1 );
		}
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

function magazine_get_issue_name() {
	$magazine_issue = WSU_Magazine_Issue();
	return $magazine_issue->get_issue_name();
}