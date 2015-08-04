<?php

/**
 * Magazine articles use the built in post post type in WordPress.
 *
 * Class WSU_Magazine_Article
 */
class WSU_Magazine_Article {
	/**
	 * @var WSU_Magazine_Article
	 */
	private static $instance;

	/**
	 * Maintain and return the one instance and initiate hooks when
	 * called the first time.
	 *
	 * @return \WSU_Magazine_Article
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WSU_Magazine_Article;
			self::$instance->setup_hooks();
		}
		return self::$instance;
	}

	/**
	 * Setup hooks for the plugin.
	 */
	public function setup_hooks() {
	}
}

add_action( 'after_setup_theme', 'WSU_Magazine_Article', 11 );
/**
 * Start things up.
 *
 * @return \WSU_Magazine_Article
 */
function WSU_Magazine_Article() {
	return WSU_Magazine_Article::get_instance();
}