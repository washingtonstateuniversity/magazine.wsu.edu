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
		add_filter( 'wsu_home_headlines_after_title', array( $this, 'display_home_headlines_section' ), 10, 2 );
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
			'public'            => true,
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_in_menu'      => true,
			'rewrite'           => array( 'slug' => 'section' ),
			'query_var'         => $this->taxonomy_slug,
		);
		register_taxonomy( $this->taxonomy_slug, array( 'post', 'wsu_magazine_we' ), $args );
	}

	/**
	 * Filter the home headline block to include the section of this article if it is available.
	 *
	 * @param string $content Current content being displayed after title.
	 * @param array  $atts    Arguments passed for the original shortcode usage.
	 *
	 * @return string Modified content to display.
	 */
	public function display_home_headlines_section( $content, $atts ) {
		$post_id = absint( $atts['id'] );

		$sections = wp_get_object_terms( $post_id, $this->taxonomy_slug );

		if ( isset( $sections[0] ) && isset( $sections[0]->name ) ) {
			return '<div class="article-section">' . esc_html( $sections[0]->name ) . '</div>';
		}

		return '';
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