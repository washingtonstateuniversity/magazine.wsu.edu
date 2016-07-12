<?php

/**
 * Each article in the magazine may have one or more authors and those authors may
 * not be users in WordPress yet.
 *
 * Class WSU_Magazine_Author
 */
class WSU_Magazine_Author {
	/**
	 * @var WSU_Magazine_Author
	 */
	private static $instance;

	/**
	 * @var string Slug for the author content type.
	 */
	public $content_type_slug = 'wsu_magazine_author';

	/**
	 * Maintain and return the one instance and initiate hooks when
	 * called the first time.
	 *
	 * @return \WSU_Magazine_Author
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WSU_Magazine_Author;
			self::$instance->setup_hooks();
		}
		return self::$instance;
	}

	/**
	 * Setup hooks for the plugin.
	 */
	public function setup_hooks() {
		add_action( 'init', array( $this, 'register_content_type' ) );
	}

	/**
	 * Register a content type to track information about magazine authors.
	 */
	public function register_content_type() {
		$labels = array(
			'name' => 'Authors',
			'singular_name' => 'Author',
			'all_items' => 'All Authors',
			'view_item' => 'View Author',
			'add_new_item' => 'Add New Author',
			'add_new' => 'Add New',
			'edit_item' => 'Edit Author',
			'update_item' => 'Update Author',
			'search_items' => 'Search Authors',
			'not_found' => 'No authors found',
			'not_found_in_trash' => 'No authors found in Trash',
		);

		$args = array(
			'labels' => $labels,
			'description' => 'Authors of articles in the WSU Magazine.',
			'public' => false,
			'hierarchical' => false,
			'menu_position' => 8,
			'menu_icon' => 'dashicons-groups',
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
}

add_action( 'after_setup_theme', 'WSU_Magazine_Author', 11 );
/**
 * Start things up.
 *
 * @return \WSU_Magazine_Author
 */
function WSU_Magazine_Author() {
	return WSU_Magazine_Author::get_instance();
}

/**
 * @param int $post_id ID of the post to retrieve an author for.
 *
 * @return string Author name.
 */
function magazine_get_author( $post_id = 0 ) {
	if ( 0 === absint( $post_id ) ) {
		$post_id = get_the_ID();
	}

	return get_the_author();
}
