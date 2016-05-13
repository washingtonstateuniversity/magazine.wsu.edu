<?php

class WSU_Magazine_Theme {
	/**
	 * @var WSU_Magazine_Theme
	 */
	private static $instance;

	/**
	 * Maintain and return the one instance and initiate hooks when
	 * called the first time.
	 *
	 * @return \WSU_Magazine_Theme
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WSU_Magazine_Theme;
			self::$instance->load_plugins();
			self::$instance->setup_hooks();
		}
		return self::$instance;
	}

	/**
	 * Load "plugins" included with the theme.
	 */
	public function load_plugins() {
		require_once( dirname( __FILE__ ) . '/includes/class-magazine-author.php' );
		require_once( dirname( __FILE__ ) . '/includes/class-magazine-article.php' );
		require_once( dirname( __FILE__ ) . '/includes/class-magazine-web-extra.php' );
		require_once( dirname( __FILE__ ) . '/includes/class-magazine-issue.php' );
		require_once( dirname( __FILE__ ) . '/includes/class-magazine-photo.php' );
		require_once( dirname( __FILE__ ) . '/includes/class-magazine-section.php' );
		require_once( dirname( __FILE__ ) . '/includes/class-magazine-colorbox.php' );

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			require_once( dirname( __FILE__ ) . '/includes/class-wp-cli.php' );
		}
	}

	public function setup_hooks() {
		add_action( 'admin_init', array( $this, 'editor_style' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_print_footer_scripts', array( $this, 'featured_image_colorbox' ), 11 );
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
		add_filter( 'admin_post_thumbnail_html', array( $this, 'meta_wsm_banner_color' ), 10, 2 );
		add_filter( 'pre_site_option_upload_filetypes', array( $this, 'set_upload_filetypes' ), 11, 1 );
		add_filter( 'upload_mimes', array( $this, 'set_mime_types' ), 11, 1 );
		add_filter( 'pre_get_posts', array( $this, 'web_extras_in_archives' ) );
		add_shortcode( 'magazine_search_form', array( $this, 'display_magazine_search_form' ) );
	}

	public function editor_style() {
		add_editor_style( 'css/editor-style.css' );
	}

	public function enqueue_scripts() {
		wp_enqueue_script( 'wsu-magazine-typekit', 'https://use.typekit.net/auo5wsi.js', array(), false, false );
		wp_enqueue_script( 'wsu-magazine-typekit-load', get_stylesheet_directory_uri() . '/js/magazine-primary.js', array(), false, true );
		if ( is_home() || is_singular( 'wsu_magazine_issue' ) ) {
			wp_enqueue_script( 'ws-magazine-issue', get_stylesheet_directory_uri() . '/js/magazine-issue.js', array( 'jquery' ) );
		}
		if ( spine_has_featured_image() && get_post( get_post_thumbnail_id() )->post_excerpt ) {
			wp_enqueue_script( 'wsu-magazine-colorbox', get_stylesheet_directory_uri() . '/js/colorbox/jquery.colorbox-min.js', array( 'jquery-core' ) );
			wp_enqueue_style( 'wsu-magazine-colorbox', get_stylesheet_directory_uri() . '/css/colorbox.css' );
		}
	}

	public function featured_image_colorbox() {
		if ( spine_has_featured_image() && get_post( get_post_thumbnail_id() )->post_excerpt ) {
			echo '<script type="text/javascript">(function($){
				$( ".image-information" ).colorbox({
					className: "featured-image-information",
					width: "90%",
					maxHeight: "90%",
					opacity: 0.85,
					returnFocus: false
				});
			})(jQuery);</script>';
		}
	}

	public function save_post( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! isset( $_POST['_wsm_banner_color_nonce'] ) || false === wp_verify_nonce( $_POST['_wsm_banner_color_nonce'], 'save-wsm-banner-color' ) ) {
			return;
		}

		if ( 'auto-draft' === $post->post_status ) {
			return;
		}

		if ( isset( $_POST['wsm_banner_color'] ) && ! empty( sanitize_html_class( $_POST['wsm_banner_color'] ) ) ) {
			update_post_meta( $post_id, '_wsm_banner_color', 1 );
		} else {
			delete_post_meta( $post_id, '_wsm_banner_color' );
		}
	}

	/**
	 * Provide an input to manually adjust a featured image's background position.
	 *
	 * @param string $content HTML output for the featured image area in the post editor.
	 * @param int    $post_id ID of the post.
	 * @return string
	 */
	public function meta_wsm_banner_color( $content, $post_id ) {
		$banner = sanitize_html_class( get_post_meta( $post_id, '_wsm_banner_color', true ) );

		$content .= wp_nonce_field( 'save-wsm-banner-color', '_wsm_banner_color_nonce', true, false );

		$content .= '<div class="featured-image-meta-extra">
						<label for="wsm-banner-color">
							<input type="checkbox" name="wsm_banner_color" id="wsm-banner-color" ' . checked( $banner, true, false ) . ' /> Use Black Banner
						</label>
						<p class="description">If the featured image consists primarily of light colors, check the box above to change the word "Magazine" in the WSM banner to black.</p>
					</div>';

		return $content;
	}

	/**
	 * Adjust the allowed file types to support SVG if a user is an administrator or editor.
	 *
	 * @param string $upload_filetypes
	 *
	 * @return string
	 */
	public function set_upload_filetypes( $upload_filetypes ) {
		if ( current_user_can( 'administrator' ) || current_user_can( 'editor' ) ) {
			$upload_filetypes .= ' svg';
		}

		return $upload_filetypes;
	}

	/**
	 * Adjust the allowed mime types to support SVG if a user is an administrator or editor.
	 *
	 * @param array $mime_types
	 *
	 * @return array
	 */
	public function set_mime_types( $mime_types ) {
		if ( current_user_can( 'administrator' ) || current_user_can( 'editor' ) ) {
			$mime_types['svg'] = 'image/svg+xml';
		}

		return $mime_types;
	}

	/**
	 * Include the Web Extra post type in the query for Category and Tag archive pages.
	 *
	 * @param WP_Query $query Query object.
	 */
	public function web_extras_in_archives( $query ) {
		if ( ( ! is_admin() && $query->is_main_query() ) && ( $query->is_category() || $query->is_tag() ) ) {
			$query->set( 'post_type', array(
				'post',
				'wsu_magazine_we',
			) );
		}
	}

	/**
	 * Display a search form with a short code when requested.
	 *
	 * @return string|void
	 */
	public function display_magazine_search_form() {
		return get_search_form( false );
	}
}

add_action( 'after_setup_theme', 'WSU_Magazine_Theme' );
/**
 * Start things up.
 *
 * @return \WSU_Magazine_Theme
 */
function WSU_Magazine_Theme() {
	return WSU_Magazine_Theme::get_instance();
}
