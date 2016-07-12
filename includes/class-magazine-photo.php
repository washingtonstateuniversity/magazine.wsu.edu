<?php

class WSU_Magazine_Photo {
	/**
	 * @var WSU_Magazine_Photo
	 */
	private static $instance;

	/**
	 * @var string Slug for tracking the post type of an article's photo.
	 */
	public $content_type_slug = 'wsu_magazine_photo';

	/**
	 * Maintain and return the one instance and initiate hooks when
	 * called the first time.
	 *
	 * @return \WSU_Magazine_Photo
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WSU_Magazine_Photo;
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
	 * Register a content type to track information about photos.
	 */
	public function register_content_type() {
		$labels = array(
			'name' => 'Photos',
			'singular_name' => 'Photo',
			'all_items' => 'All Photos',
			'view_item' => 'View Photo',
			'add_new_item' => 'Add New Photo',
			'add_new' => 'Add New',
			'edit_item' => 'Edit Photo',
			'update_item' => 'Update Photo',
			'search_items' => 'Search Photos',
			'not_found' => 'No photos found',
			'not_found_in_trash' => 'No photos found in Trash',
		);

		$args = array(
			'labels' => $labels,
			'description' => 'Photos associated with magazine articles.',
			'public' => true,
			'publicly_queryable' => false,
			'exclude_from_search' => true,
			'hierarchical' => false,
			'menu_position' => 5,
			'menu_icon' => 'dashicons-format-image',
			'supports' => array(
				'title',
			),
			'taxonomies' => array(),
			'has_archive' => false,
			'rewrite' => false,
		);
		register_post_type( $this->content_type_slug, $args );
	}
}

add_action( 'after_setup_theme', 'WSU_Magazine_Photo', 11 );
/**
 * Start things up.
 *
 * @return \WSU_Magazine_Photo
 */
function WSU_Magazine_Photo() {
	return WSU_Magazine_Photo::get_instance();
}
