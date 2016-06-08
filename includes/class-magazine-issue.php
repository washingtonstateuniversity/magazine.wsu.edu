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
		add_action( 'current_screen', array( $this, 'remove_builder_sections' ) );
		add_action( 'current_screen', array( $this, 'add_builder_sections' ) );
		add_filter( 'get_post_metadata', array( $this, 'force_page_builder_meta' ), 10, 3 );
		add_filter( 'spine_builder_force_builder', array( $this, 'force_builder' ) );
		add_filter( 'make_will_be_builder_page', array( $this, 'force_builder' ) );
		add_action( 'pre_get_posts', array( $this, 'front_page_issue' ) );
		add_action( 'restrict_manage_posts', array( $this, 'filter_media_by_issue' ) );
		add_filter( 'attachment_fields_to_edit', array( $this, 'media_modal_issue_labels' ), 16, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 10 );
		add_action( 'save_post_' . $this->content_type_slug, array( $this, 'save_post' ), 10, 2 );
		add_action( 'wp_ajax_set_issue_articles', array( $this, 'ajax_callback' ), 10 );
		add_action( 'wp_ajax_nopriv_set_issue_articles', array( $this, 'ajax_callback' ), 10 );
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
	 * Remove sections defined for the default implemenation of the page builder.
	 */
	public function remove_builder_sections( $current_screen ) {
		if ( $this->content_type_slug !== $current_screen->id ) {
			return;
		}
		ttfmake_remove_section( 'wsuwpsingle' );
		ttfmake_remove_section( 'wsuwphalves' );
		ttfmake_remove_section( 'wsuwpsidebarleft' );
		ttfmake_remove_section( 'wsuwpsidebarright' );
		ttfmake_remove_section( 'wsuwpthirds' );
		ttfmake_remove_section( 'wsuwpquarters' );
		ttfmake_remove_section( 'wsuwpheader' );
		ttfmake_remove_section( 'banner' );
	}

	/**
	 * Add the custom sections used in the magazine issue implementation of the page builder.
	 */
	public function add_builder_sections( $current_screen ) {
		if ( $this->content_type_slug !== $current_screen->id ) {
			return;
		}
		ttfmake_add_section(
			'wsuwpsingle',
			'First Words',
			get_template_directory_uri() . '/inc/builder/sections/css/images/blank.png',
			'A single column.',
			array( $this, 'save_columns' ),
			'admin/issue-columns',
			'front-end/issue-columns',
			100,
			'builder-templates/'
		);
		ttfmake_add_section(
			'wsuwphalves',
			'Halves',
			get_template_directory_uri() . '/inc/builder-custom/images/halves.png',
			'Two equal columns.',
			array( $this, 'save_columns' ),
			'admin/issue-columns',
			'front-end/issue-columns',
			200,
			'builder-templates/'
		);
		ttfmake_add_section(
			'wsuwpsecondary',
			'Secondary',
			get_template_directory_uri() . '/inc/builder-custom/images/halves.png',
			'Two equal columns.',
			array( $this, 'save_columns' ),
			'admin/issue-columns',
			'front-end/issue-columns',
			300,
			'builder-templates/'
		);
	}

	/**
	 * Clean the data being passed from the save of a columns layout.
	 *
	 * @param array $data Array of data inputs being passed.
	 *
	 * @return array Clean data.
	 */
	public function save_columns( $data ) {
		$clean_data = array();
		if ( isset( $data['columns-number'] ) ) {
			if ( in_array( $data['columns-number'], range( 1, 4 ) ) ) {
				$clean_data['columns-number'] = $data['columns-number'];
			}
		}
		if ( isset( $data['columns-order'] ) ) {
			$clean_data['columns-order'] = array_map( array( 'TTFMake_Builder_Save', 'clean_section_id' ), explode( ',', $data['columns-order'] ) );
		}
		if ( isset( $data['columns'] ) && is_array( $data['columns'] ) ) {
			$i = 1;
			$background_sizes = array(
				'thumbnail',
				'medium',
				'large',
				'spine-large_size',
			);
			$background_positions = array(
				'center',
				'center-top',
				'right-top',
				'right-center',
				'right-bottom',
				'center-bottom',
				'left-bottom',
				'left-center',
				'left-top',
			);
			foreach ( $data['columns'] as $id => $item ) {
				if ( isset( $item['toggle'] ) ) {
					if ( in_array( $item['toggle'], array( 'visible', 'invisible' ) ) ) {
						$clean_data['columns'][ $id ]['toggle'] = $item['toggle'];
					}
				}
				if ( isset( $item['post-id'] ) ) {
					$clean_data['columns'][ $id ]['post-id'] = sanitize_text_field( $item['post-id'] );
				}
				if ( isset( $item['headline'] ) ) {
					$clean_data['columns'][ $id ]['headline'] = sanitize_text_field( $item['headline'] );
				}
				if ( isset( $item['subtitle'] ) ) {
					$clean_data['columns'][ $id ]['subtitle'] = sanitize_text_field( $item['subtitle'] );
				}
				if ( isset( $item['background-id'] ) ) {
					$clean_data['columns'][ $id ]['background-id'] = sanitize_text_field( $item['background-id'] );
				}
				if ( isset( $item['background-size'] ) && in_array( $item['background-size'], $background_sizes ) ) {
					$clean_data['columns'][ $id ]['background-size'] = $item['background-size'];
				}
				if ( isset( $item['background-position'] ) && in_array( $item['background-position'], $background_positions ) ) {
					$clean_data['columns'][ $id ]['background-position'] = $item['background-position'];
				}
				$i++;
			}
		}
		if ( isset( $data['label'] ) ) {
			$clean_data['label'] = sanitize_text_field( $data['label'] );
		}
		$clean_data = apply_filters( 'spine_builder_save_columns', $clean_data, $data );
		return $clean_data;
	}

	/**
	 * Force the page builder to be enabled for this content type.
	 *
	 * @param null|int $check     Whether to short circuit the meta check. Null by default.
	 * @param int      $object_id ID of the post being saved.
	 * @param string   $meta_key  The current meta key being saved.
	 *
	 * @return null|int Unchanged check value or 1.
	 */
	public function force_page_builder_meta( $check, $object_id, $meta_key ) {
		if ( '_ttfmake-use-builder' !== $meta_key ) {
			return $check;
		}

		$post = get_post( $object_id );

		if ( $this->content_type_slug === $post->post_type ) {
			return 1;
		}

		return $check;
	}

	/**
	 * Hide the checkbox used to disable the page builder.
	 *
	 * The page builder interface is forced via meta key. Returning true
	 * here will force the checkbox to be hidden as well.
	 *
	 * @param bool $use_builder Whether the page builder show be used.
	 *
	 * @return bool True if the magazine issue content type. False if not.
	 */
	public function force_builder( $use_builder ) {
		if ( $this->content_type_slug !== get_post_type() ) {
			return $use_builder;
		}

		return true;
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

		$current_issue = ( isset( $_GET[ $this->taxonomy_slug ] ) ) ? sanitize_key( $_GET[ $this->taxonomy_slug ] ) : 0;

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

	/**
	 * Enqueue the scripts used for managing issue building.
	 */
	public function admin_enqueue_scripts( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		if ( $this->content_type_slug === get_current_screen()->id ) {
			wp_enqueue_script( 'wsm-issue-admin', get_stylesheet_directory_uri() . '/js/issue-admin.js', array( 'jquery-ui-draggable', 'jquery-ui-sortable' ), false, true );
			wp_enqueue_style(  'wsu-issue-admin', get_stylesheet_directory_uri() . '/css/issue-admin.css', array( 'ttfmake-builder' ) );
		}
	}

	/**
	 * Add the meta boxes used by the issue content type.
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'wsm_issue_items',
			'Issue Articles',
			array( $this, 'display_issue_articles_meta_box' ),
			$this->content_type_slug,
			'side'
		);
	}

	/**
	 * Display a staging area for loading/sorting issue articles.
	 *
	 * @param WP_Post $post Object for the post currently being edited.
	 */
	public function display_issue_articles_meta_box( $post ) {
		wp_nonce_field( 'save-wsm-issue-build', '_wsm_issue_build_nonce' );

		$localized_data = array( 'post_id' => $post->ID );

		$stage_articles = '';

		// If this issue has articles assigned already, we want to make them available to our JS.
		if ( $post_ids = get_post_meta( $post->ID, '_issue_staged_articles', true ) ) {
			$localized_data['items'] = $this->_build_magazine_issue_response( $post_ids );
			$stage_articles = implode( ',', $post_ids );
		}

		wp_localize_script( 'wsm-issue-admin', 'wsm_issue', $localized_data );

		if ( $term_list = get_the_terms( $post->ID, $this->taxonomy_slug ) ) {
			$issue_label = $term_list[0]->slug;
		} else {
			$issue_label = '';
		}

		wp_dropdown_categories( array(
			'show_option_all' => 'Issue Label',
			'taxonomy' => $this->taxonomy_slug,
			'orderby' => 'name',
			'order' => 'DESC',
			'name' => 'issue_label',
			'id' => 'issue_label_slug',
			'echo' => 1,
			'selected' => $issue_label,
			'value_field' => 'slug',
		) );
		?>
		<input type="button" value="Load Articles" id="load-issue-articles" class="button button-large button-secondary" />
		<div id="issue-articles" class="wsuwp-spine-builder-column"></div>
		<input type="hidden" id="issue-staged-articles" name="issue_staged_articles" value="<?php echo esc_attr( $stage_articles ); ?>" />
		<?php
	}

	/**
	 * Capture the build of an issue.
	 *
	 * @param int     $post_id ID of the current post being saved.
	 * @param WP_Post $post    Object of the current post being saved.
	 */
	public function save_post( $post_id, $post ) {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! isset( $_POST['_wsm_issue_build_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['_wsm_issue_build_nonce'], 'save-wsm-issue-build' ) ) {
			return;
		}

		if ( 'auto-draft' === $post->post_status ) {
			return;
		}

		if ( $this->content_type_slug !== $post->post_type ) {
			return;
		}

		if ( ! empty( $_POST['issue_staged_articles'] ) ) {
			$issue_staged_articles = explode( ',', $_POST['issue_staged_articles'] );
			$issue_staged_articles = array_map( 'absint', $issue_staged_articles );
			update_post_meta( $post_id, '_issue_staged_articles', $issue_staged_articles );
		} else {
			delete_post_meta( $post_id, '_issue_staged_articles' );
		}
	}

	/**
	 * Build the list of articles that should be included in an issue.
	 *
	 * @param array        $post_ids    List of specific post IDs to include. Defaults to an empty array.
	 * @param null|string  $issue_label Issue label to assign to the issue. A null default indicates no issue label.
	 *
	 * @return array Containing information on each issue article.
	 */
	private function _build_magazine_issue_response( $post_ids = array(), $issue_label = null ) {
		$query_args = array(
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_type'      => array( 'post', 'wsu_magazine_we' ),
			'posts_per_page' => 100,
		);

		if ( $issue_label ) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => $this->taxonomy_slug,
					'field'    => 'slug',
					'terms'    => $issue_label,
				)
			);
		}

		// If an array of post IDs has been passed, use only those.
		if ( ! empty( $post_ids ) ) {
			$query_args['post__in'] = $post_ids;
			$query_args['orderby']  = 'post__in';
		}

		$items = array();
		$issue_query = get_posts( $query_args );
		foreach ( $issue_query as $post ) {
			setup_postdata( $post );
			$sections = wp_get_object_terms( $post->ID, 'wsu_magazine_section', array( 'fields' => 'names' ) );
			$section = ( $sections ) ? $sections[0] : '';
			$items[] = array(
				'id'        => $post->ID,
				'title'     => $post->post_title,
				'headline'  => esc_attr( get_post_meta( $post->ID, '_wsu_home_headline', true ) ),
				'subtitle'  => esc_attr( get_post_meta( $post->ID, '_wsu_home_subtitle', true ) ),
				'section'   => $section,
			);
		}
		wp_reset_postdata();

		return $items;
	}

	/**
	 * Handle the ajax callback to push a list of articles to an issue.
	 */
	public function ajax_callback() {
		if ( ! DOING_AJAX || ! isset( $_POST['action'] ) || 'set_issue_articles' !== $_POST['action'] ) {
			die();
		}

		if ( isset( $_POST['issue_label'] ) ) {
			$issue_label = $_POST['issue_label'];
		} else {
			$issue_label = false;
		}

		if ( isset( $_POST['post_ids'] ) ) {
			$post_ids = explode( ',', $_POST['post_ids'] );
		} else {
			$post_ids = array();
		}

		echo json_encode( $this->_build_magazine_issue_response( $post_ids, $issue_label ) );

		exit();
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

/**
 * Output the input fields for configuring article displays.
 *
 * @param string $column_name
 * @param array $section_data
 * @param int $column
 */
function wsm_issue_article_configuration_output( $column_name, $section_data, $column = false ) {
	if ( $column ) {
		$headline    = ( isset( $section_data['data']['columns'][ $column ]['headline'] ) ) ? $section_data['data']['columns'][ $column ]['headline'] : '';
		$subtitle    = ( isset( $section_data['data']['columns'][ $column ]['subtitle'] ) ) ? $section_data['data']['columns'][ $column ]['subtitle'] : '';
		$bg_id       = ( isset( $section_data['data']['columns'][ $column ]['background-id'] ) ) ? $section_data['data']['columns'][ $column ]['background-id'] : '';
		$bg_image    = ( ! empty( $bg_id ) ) ? wp_get_attachment_image_src( $bg_id, 'full' )[0] : '';
		$bg_size     = ( isset( $section_data['data']['columns'][ $column ]['background-size'] ) ) ? $section_data['data']['columns'][ $column ]['background-size'] : '';
		$bg_position = ( isset( $section_data['data']['columns'][ $column ]['background-position'] ) ) ? $section_data['data']['columns'][ $column ]['background-position'] : '';
	} else {
		$headline    = ( isset( $section_data['data']['headline'] ) ) ? $section_data['data']['headline'] : '';
		$subtitle    = ( isset( $section_data['data']['subtitle'] ) ) ? $section_data['data']['subtitle'] : '';
		$background  = ( isset( $section_data['data']['background-id'] ) ) ? $section_data['data']['background-id'] : '';
		$bg_size     = ( isset( $section_data['data']['background-size'] ) ) ? $section_data['data']['background-size'] : '';
		$bg_position = ( isset( $section_data['data']['background-position'] ) ) ? $section_data['data']['background-position'] : '';
	}
	?>
	<div class="wsuwp-builder-meta">
		<label for="<?php echo $column_name; ?>[headline]">Headline</label>
		<input type="text"
			   id="<?php echo $column_name; ?>[headline]"
			   name="<?php echo $column_name; ?>[headline]"
			   class="spine-builder-column-headline wsm-article-meta widefat"
			   value="<?php echo esc_attr( $headline ); ?>"/>
		<p class="description">Enter text to display in place of the original article headline or title.</p>
	</div>
	<div class="wsuwp-builder-meta">
		<label for="<?php echo $column_name; ?>[subtitle]">Subtitle</label>
		<input type="text"
			   id="<?php echo $column_name; ?>[subtitle]"
			   name="<?php echo $column_name; ?>[subtitle]"
			   class="spine-builder-column-subtitle wsm-article-meta widefat"
			   value="<?php echo esc_attr( $subtitle ); ?>"/>
		<p class="description">Enter text to display as the article subtitle.</p>
	</div>
	<div class="wsuwp-builder-meta">
		<label>Background Image</label>
		<p class="hide-if-no-js">
			<input type="hidden"
				   id="<?php echo $column_name; ?>[background-id]"
				   name="<?php echo $column_name; ?>[background-id]"
				   class="spine-builder-column-background-id wsm-article-meta"
				   value="<?php echo esc_attr( $bg_id ); ?>" />
			<a href="#" class="spine-builder-column-set-background-image"><?php
				echo ( $bg_image ) ? '<img src="' . $bg_image . '" />' : 'Set background image';
			?></a>
			<a href="#" class="spine-builder-column-remove-background-image"<?php if ( ! $bg_image ) { echo 'style="display:none;"'; } ?>>Remove background image</a>
		</p>
		<p class="description">Select an image to apply as the article background.</p>
	</div>
	<div class="wsuwp-builder-meta">
		<label for="<?php echo $column_name; ?>[background-size]">Background Size</label>
		<select id="<?php echo $column_name; ?>[background-size]"
			    name="<?php echo $column_name; ?>[background-size]"
			    class="spine-builder-column-background-size wsm-article-meta">
			<option value="">Full</option>
			<?php
			$sizes = array(
				'thumbnail',
				'medium',
				'large',
				'spine-large_size'
			);

			foreach ( $sizes as $size ) {
				$image = wp_get_attachment_image_src( $bg_id, $size );

				if ( ! empty( $image ) ) {
					$name = ucfirst( $size ) . ' (' . $image[1] . 'x' . $image[2] . ')';
					?><option value="<?php echo $size; ?>" <?php selected( esc_attr( $bg_size ), $size ); ?>><?php echo $name; ?></option><?php
				}
			}
			?>
		</select>
		<p class="description">Set the size of the background image.</p>
	</div>
	<div class="wsuwp-builder-meta">
		<label for="<?php echo $column_name; ?>[background-position]">Background Position</label>
		<select id="<?php echo $column_name; ?>[background-position]"
			    name="<?php echo $column_name; ?>[background-position]"
			    class="spine-builder-column-background-position wsm-article-meta">
			<option value="">Select</option>
			<option value="center" <?php selected( esc_attr( $bg_position ), 'center' ); ?>>Center</option>
			<option value="center-top" <?php selected( esc_attr( $bg_position ), 'center-top' ); ?>>Center-Top</option>
			<option value="right-top" <?php selected( esc_attr( $bg_position ), 'right-top' ); ?>>Right-Top</option>
			<option value="right-center" <?php selected( esc_attr( $bg_position ), 'right-center' ); ?>>Right-Center</option>
			<option value="right-bottom" <?php selected( esc_attr( $bg_position ), 'right-bottom' ); ?>>Right-Bottom</option>
			<option value="center-bottom" <?php selected( esc_attr( $bg_position ), 'center-bottom' ); ?>>Center-Bottom</option>
			<option value="left-bottom" <?php selected( esc_attr( $bg_position ), 'left-bottom' ); ?>>Left-Bottom</option>
			<option value="left-center" <?php selected( esc_attr( $bg_position ), 'left-center' ); ?>>Left-Center</option>
			<option value="left-top" <?php selected( esc_attr( $bg_position ), 'left-top' ); ?>>Left-Top</option>
		</select>
		<p class="description">Change the positioning of the background image.</p>
	</div>
	<?php
}
