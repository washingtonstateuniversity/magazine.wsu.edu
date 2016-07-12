<?php

class WSU_Magazine_Colorbox {
	/**
	 * @var WSU_Magazine_Colorbox
	 */
	private static $instance;

	/**
	 * @var array Colorbox instances for the current request.
	 */
	protected $colorbox_instances = array();

	/**
	 * Maintain and return the one instance and initiate hooks when
	 * called the first time.
	 *
	 * @return \WSU_Magazine_Colorbox
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WSU_Magazine_Colorbox;
			self::$instance->setup_hooks();
		}
		return self::$instance;
	}

	/**
	 * Setup hooks for the plugin.
	 */
	public function setup_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_print_footer_scripts', array( $this, 'add_colorbox_calls' ), 11 );
		add_shortcode( 'magazine_colorbox', array( $this, 'display_magazine_colorbox' ) );
	}

	/**
	 * Load the Colorbox library.
	 */
	public function enqueue_scripts() {
		$post = get_post();
		if ( isset( $post->post_content ) && has_shortcode( $post->post_content, 'magazine_colorbox' ) ) {
			wp_enqueue_script( 'wsu-magazine-colorbox', get_stylesheet_directory_uri() . '/js/colorbox/jquery.colorbox-min.js', array( 'jquery-core' ) );
			wp_enqueue_style( 'wsu-magazine-colorbox', get_stylesheet_directory_uri() . '/css/colorbox.css' );
		}
	}

	/**
	 * Add JavaScript code for invoking Colorbox.
	 */
	public function add_colorbox_calls() {

		if ( empty( $this->colorbox_instances ) ) {
			return;
		}

		$invocations = array();

		foreach ( $this->colorbox_instances as $selector => $settings ) {
			$invocation = "$('{$selector}').colorbox({$settings})";
			$invocations[] = $invocation;
		}

		if ( empty( $invocations ) ) {
			return;
		}

		$invocations = implode( ';', $invocations );

		echo '<script type="text/javascript">(function($){' . $invocations . '})(jQuery);</script>';
	}

	/**
	 * Escape shortcode attribute values and add to the `colorbox_instances` array.
	 *
	 * @param array $atts {
	 *     Attributes passed with the shortcode.
	 *
	 *     @type string $selector The `.class` or `#id` of HTML element(s) to apply Colorbox to.
	 *     @type string $settings The key/value pairs documented at http://www.jacklmoore.com/colorbox/.
	 * }
	 *
	 * @return void
	 */
	public function display_magazine_colorbox( $atts ) {

		$defaults = array(
			'selector' => '',
			'settings' => '',
		);

		$atts = shortcode_atts( $defaults, $atts );

		$selector = esc_js( trim( $atts['selector'] ) );

		$settings = explode( ',', $atts['settings'] );

		$settings_whitelist = array(
			'transition',
			'speed',
			'href',
			'title',
			'rel',
			'scalePhotos',
			'scrolling',
			'opacity',
			'open',
			'returnFocus',
			'trapFocus',
			'fastIframe',
			'preloading',
			'overlayClose',
			'escKey',
			'arrowKey',
			'loop',
			'data',
			'className',
			'fadeOut',
			'closeButton',
			'current',
			'previous',
			'next',
			'close',
			'xhrError',
			'imgError',
			'iframe',
			'inline',
			'html',
			'photo',
			'ajax',
			'width',
			'height',
			'innerWidth',
			'innerHeight',
			'initialWidth',
			'initialHeight',
			'maxWidth',
			'maxHeight',
			'slideshow',
			'slideshowSpeed',
			'slideshowAuto',
			'slideshowStart',
			'slideshowStop',
			'fixed',
			'top',
			'bottom',
			'left',
			'right',
			'reposition',
			'retinaImage',
			'retinaUrl',
			'retinaSuffix',
		);

		$callbacks_whitelist = array(
			'onOpen',
			'onLoad',
			'onComplete',
			'onCleanup',
			'onClosed',
		);

		$callback_values_whitelist = array(
			'curtain',
		);

		$sanitized_settings = array();

		foreach ( $settings as $setting ) {
			$setting = explode( ':', $setting );
			$key = trim( $setting[0] );

			if ( in_array( $key, $settings_whitelist, true ) ) {
				$value = esc_js( trim( $setting[1] ) );

				if ( $value == 'true' || $value == 'false' ) {
					$sanitized_settings[] = $key . ':' . $value;
				} else {
					$sanitized_settings[] = $key . ':"' . $value . '"';
				}
			}

			if ( in_array( $key, $callbacks_whitelist, true ) ) {
				$value = esc_js( trim( $setting[1] ) );
				if ( in_array( $value, $callback_values_whitelist, true ) ) {
					$sanitized_settings[] = $key . ':function(){colorbox_' . $value . '()}';
				}
			}
		}

		$sanitized_settings = '{' . implode( ',', $sanitized_settings ) . '}';

		if ( ! isset( $this->colorbox_instances[ $selector ] ) ) {
			$this->colorbox_instances[ $selector ] = $sanitized_settings;
		}

		return;
	}
}

add_action( 'after_setup_theme', 'WSU_Magazine_Colorbox', 11 );
/**
 * Start things up.
 *
 * @return \WSU_Magazine_Colorbox
 */
function WSU_Magazine_Colorbox() {
	return WSU_Magazine_Colorbox::get_instance();
}
