<?php

class WSU_Magazine_Section {
	/**
	 * @var WSU_Magazine_Section
	 */
	private static $instance;

	/**
	 * @var string Slug for tracking the taxonomy of a magazine section.
	 */
	public $taxonomy_slug = 'wsu_magazine_section';

	/**
	 * Maintain and return the one instance and initiate hooks when
	 * called the first time.
	 *
	 * @return \WSU_Magazine_Section
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WSU_Magazine_Section();
			self::$instance->setup_hooks();
		}
		return self::$instance;
	}

	/**
	 * Setup hooks for the plugin.
	 */
	public function setup_hooks() {
		add_action( 'init', array( $this, 'register_taxonomy' ) );
	}

	/**
	 * Register a magazine section taxonomy that will be attached to articles and web extras.
	 */
	public function register_taxonomy() {
		$labels = array(
			'name'          => 'Section',
			'singular_name' => 'Section',
			'search_items'  => 'Sections',
			'all_items'     => 'All Sections',
			'edit_item'     => 'Edit Section',
			'update_item'   => 'Update Section',
			'add_new_item'  => 'Add New Section',
			'new_item_name' => 'New Section Name',
			'menu_name'     => 'Sections',
		);
		$args = array(
			'labels'            => $labels,
			'description'       => 'The magazine section taxonomy attached to articles and web extras.',
			'public'            => false,
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_in_menu'      => true,
			'rewrite'           => false,
			'query_var'         => $this->taxonomy_slug,
		);
		register_taxonomy( $this->taxonomy_slug, array( 'post', 'wsu_magazine_we' ), $args );
	}
}

add_action( 'after_setup_theme', 'WSU_Magazine_Section', 11 );
/**
 * Start things up.
 *
 * @return \WSU_Magazine_Section
 */
function WSU_Magazine_Section() {
	return WSU_Magazine_Section::get_instance();
}