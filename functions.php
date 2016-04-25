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

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			require_once( dirname( __FILE__ ) . '/includes/class-wp-cli.php' );
		}
	}

	public function setup_hooks() {
		add_action( 'admin_init', array( $this, 'editor_style' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'pre_site_option_upload_filetypes', array( $this, 'set_upload_filetypes' ), 11, 1 );
		add_filter( 'upload_mimes', array( $this, 'set_mime_types' ), 11, 1 );
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
