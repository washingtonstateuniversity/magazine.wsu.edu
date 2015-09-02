<?php

class WSU_Magazine_Web_Extra {
	/**
	 * @var WSU_Magazine_Web_Extra
	 */
	private static $instance;

	/**
	 * @var string Slug for tracking the post type of an article's web extra.
	 */
	public $content_type_slug = 'wsu_magazine_we';

	/**
	 * Maintain and return the one instance and initiate hooks when
	 * called the first time.
	 *
	 * @return \WSU_Magazine_Web_Extra
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WSU_Magazine_Web_Extra;
			self::$instance->setup_hooks();
		}
		return self::$instance;
	}

	/**
	 * Setup hooks for the plugin.
	 */
	public function setup_hooks() {
		add_action( 'init', array( $this, 'register_content_type' ), 12 );
	}

	/**
	 * Register a content type to track information about web Extras.
	 */
	public function register_content_type() {
		$labels = array(
			'name' => 'Web Extras',
			'singular_name' => 'Web Extra',
			'all_items' => 'All Web Extras',
			'view_item' => 'View Web extra',
			'add_new_item' => 'Add New Web extra',
			'add_new' => 'Add New',
			'edit_item' => 'Edit Web extra',
			'update_item' => 'Update Web extra',
			'search_items' => 'Search Web extras',
			'not_found' => 'No web extras found',
			'not_found_in_trash' => 'No web extras found in Trash',
		);

		$args = array(
			'labels' => $labels,
			'description' => 'Web extras associated with magazine articles.',
			'public' => true,
			'hierarchical' => false,
			'menu_position' => 5,
			'menu_icon' => 'dashicons-format-aside',
			'supports' => array(
				'title',
				'editor',
				'thumbnail',
				'revisions',
				'author',
			),
			'taxonomies' => array(
				'post_tag',
				'category',
			),
			'has_archive' => true,
			'rewrite' => array( 'slug' => 'web-extra' ),
		);
		register_post_type( $this->content_type_slug, $args );
		register_taxonomy_for_object_type( 'wsuwp_university_category', $this->content_type_slug );

		if ( class_exists( 'MultiPostThumbnails' ) ) {
			$thumbnail_args = array(
				'label' => 'Thumbnail Image',
				'id' => 'thumbnail-image',
				'post_type' => $this->content_type_slug,
			);
			new MultiPostThumbnails( $thumbnail_args );
		}
	}
}

add_action( 'after_setup_theme', 'WSU_Magazine_Web_Extra', 11 );
/**
 * Start things up.
 *
 * @return \WSU_Magazine_Web_Extra
 */
function WSU_Magazine_Web_Extra() {
	return WSU_Magazine_Web_Extra::get_instance();
}