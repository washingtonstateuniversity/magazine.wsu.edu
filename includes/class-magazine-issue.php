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
	public $taxonomy_slug = 'wsu_mag_issue_tax';

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
		add_action( 'init', array( $this, 'register_taxonomy' ), 15 );
		add_filter( 'body_class', array( $this, 'season_body_class' ) );
		add_action( 'admin_init', array( $this, 'register_builder_support' ) );
		add_filter( 'spine_builder_force_builder', array( $this, 'force_builder' ) );
		add_filter( 'make_will_be_builder_page', array( $this, 'force_builder' ) );
		add_action( 'pre_get_posts', array( $this, 'front_page_issue' ) );
		add_action( 'restrict_manage_posts', array( $this, 'filter_media_by_issue' ) );
		add_filter( 'attachment_fields_to_edit', array( $this, 'media_modal_issue_labels' ), 16, 2 );
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
			'rewrite' => array( 'slug' => 'issue' ),
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
			'separate_items_with_commas' => 'Separate issue labels with commas'
		);
		$args = array(
			'labels'            => $labels,
			'description'       => 'The magazine issue taxonomy attached to articles and issues.',
			'public'            => false,
			'hierarchical'      => false,
			'show_ui'           => true,
			'show_in_menu'      => true,
			'show_admin_column' => true,
			'rewrite'           => false,
			'query_var'         => $this->taxonomy_slug,
			'update_count_callback' => '_update_generic_term_count',
		);
		register_taxonomy( $this->taxonomy_slug, array( $this->content_type_slug, 'post', 'attachment', 'wsu_magazine_we' ), $args );
		//register_taxonomy_for_object_type( $this->taxonomy_slug, 'post' );
		//register_taxonomy_for_object_type( $this->taxonomy_slug, 'attachment' );
		//register_taxonomy_for_object_type( $this->taxonomy_slug, 'wsu_magazine_we' );
	}

	public function get_issue_season( $post_id ) {
		$issues = wp_get_object_terms( $post_id, $this->taxonomy_slug );
		if ( ! empty( $issues ) && 1 >= count( $issues ) ) {
			$issue = explode( ' ', $issues[0]->name );
			return $issue;
		}

		return '';
	}

	/**
	 * Determine the issue associated with the current page view.
	 *
	 * @return false|int The integer ID of the issue if found, false if not available.
	 */
	public function get_current_issue_id() {
		$args = array(
			'post_type' => $this->content_type_slug,
			'posts_per_page' => 1,
			'fields' => 'ids',
		);

		if ( is_singular() && ! is_page() ) {
			$issues = wp_get_object_terms( get_the_ID(), $this->taxonomy_slug );

			if ( 0 == count( $issues ) ) {
				return false;
			}

			$args['tax_query'] = array(
				array(
					'taxonomy' => $this->taxonomy_slug,
					'field' => 'term_id',
					'terms' => $issues[0]->term_id,
				),
			);
		}

		$issue = get_posts( $args );

		if ( ! empty( $issue ) ) {
			return $issue[0];
		}

		return false;
	}

	/**
	 * Add season to the list of body classes for individual articles and individual issues.
	 *
	 * @param array $body_classes List of current body classes.
	 *
	 * @return array Modified list of body classes.
	 */
	public function season_body_class( $body_classes ) {
		$object_id = $this->get_current_issue_id();

		if ( false === $object_id ) {
			return $body_classes;
		}

		$issues = wp_get_object_terms( $object_id, $this->taxonomy_slug );

		if ( 1 >= count( $issues ) ) {
			$issue = explode( ' ', $issues[0]->name );
			$body_classes[] = 'season-' . esc_attr( strtolower( $issue[0] ) );
		}

		return $body_classes;
	}

	/**
	 * Retrieve the issue name for the current article or issue view. If an issue
	 * is not assigned, return the most current issue.
	 *
	 * @param int $object_id ID of the object associated with an issue.
	 *
	 * @return string Issue name.
	 */
	public function get_issue_name( $object_id = 0 ) {

		if ( 0 === $object_id ) {
			$object_id = $this->get_current_issue_id();
		}

		if ( false === $object_id ) {
			return '';
		}

		$issues = wp_get_object_terms( $object_id, $this->taxonomy_slug );

		if ( ! empty( $issues ) && 1 >= count( $issues ) ) {
			return $issues[0]->name;
		}

		return '';
	}

	/**
	 * Return the current page view's issue URL. If this is the front page or an issue
	 * page, don't return a URL as we're already there.
	 *
	 * @return bool|false|string
	 */
	public function get_issue_url() {
		$object_id = $this->get_current_issue_id();

		if ( is_front_page() || is_singular( $this->content_type_slug ) ) {
			return false;
		}

		return get_the_permalink( $object_id );
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
	 * Hides the checkbox to disable the builder if an issue has been saved
	 * with the builder interface already. In the future, this will be removed
	 * in favor of a new issue building interface.
	 *
	 * @param bool $use_builder Whether the page builder show be used.
	 *
	 * @return bool True if the magazine issue content type. False if not.
	 */
	public function force_builder( $use_builder ) {
		if ( $this->content_type_slug !== get_post_type() ) {
			return $use_builder;
		}

		$using_builder = get_post_meta( get_the_ID(), '_ttfmake-use-builder', true );

		if ( $using_builder ) {
			return true;
		} else {
			return false;
		}
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

	/**
	 * Add a select input for filtering media by issue.
	 */
	public function filter_media_by_issue() {
		$screen = get_current_screen();

		if ( 'upload' !== $screen->id && 'edit-wsu_magazine_we' !== $screen->id ) {
			return;
		}

		$current_issue = filter_input( INPUT_GET, 'term', FILTER_SANITIZE_STRING );

		wp_dropdown_categories( array(
			'show_option_all' => 'All issues',
			'taxonomy' => $this->taxonomy_slug,
			'orderby' => 'name',
			'order' => 'DESC',
			'hide_empty' => 0,
			'name' => $this->taxonomy_slug,
			'id' => 'issue-term',
			'selected' => $current_issue,
			'value_field' => 'slug',
		) );
	}

	/**
	 * Add an input for adding issue labels through the media modal.
	 *
	 * @param array   $fields Array of attachment form fields.
	 * @param WP_Post $post
	 *
	 * @return Modified array of attachment form fields.
	 */
	public function media_modal_issue_labels( $fields, $post ) {
		$taxonomy = get_taxonomy( $this->taxonomy_slug );
		$issue_labels = wp_get_post_terms( $post->ID, $this->taxonomy_slug, array( 'fields' => 'slugs' ) );
		$value = $issue_labels ? implode( ',', $issue_labels ) : '';
		ob_start();
		?>
		<input type="text"
			   class="text"
			   id="attachments-<?php echo $post->ID; ?>-<?php echo $this->taxonomy_slug; ?>"
			   name="attachments[<?php echo $post->ID; ?>][<?php echo $this->taxonomy_slug; ?>]"
			   value="<?php echo $value; ?>" />
		<?php
		$metabox = ob_get_clean();

		$fields[ $this->taxonomy_slug ] = array(
			'label'        => $taxonomy->labels->singular_name,
			'input'        => 'html',
			'html'         => $metabox,
			'show_in_edit' => false,
			'helps'        => $taxonomy->labels->separate_items_with_commas,
		);

		return $fields;
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

function magazine_get_issue_name( $post_id = 0 ) {
	$magazine_issue = WSU_Magazine_Issue();
	return $magazine_issue->get_issue_name( $post_id );
}

function magazine_get_issue_url() {
	$magazine_issue = WSU_Magazine_Issue();
	return $magazine_issue->get_issue_url();
}

function magazine_get_issue_season_class( $post_id, $prefix = '' ) {
	$magazine_issue = WSU_Magazine_Issue();
	$season = $magazine_issue->get_issue_season( $post_id );

	if ( ! empty( $season[0] ) ) {
		return esc_attr( $prefix . 'season-' . strtolower( $season[0] ) );
	}

	return '';
}
