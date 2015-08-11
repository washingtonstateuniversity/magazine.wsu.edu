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
	}

	public function setup_hooks() {
		add_action( 'admin_init', array( $this, 'editor_style' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	public function editor_style() {
		add_editor_style( 'css/editor-style.css' );
	}

	public function enqueue_scripts() {
		wp_enqueue_script( 'wsu-magazine-typekit', 'https://use.typekit.net/roi0hte.js', array(), false, false );
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