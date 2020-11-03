<?php

// If WordPress Gutenberg is not available, do not run.
if ( !function_exists('has_blocks') || ! defined( 'ABSPATH' ) ) {
	return;
}


/**
 * Gutenberg Block class.
 *
 * @since 0.1.0
 *
 * Class SALFWP_Block
 */
class SALFWP_Block {

	/**
	 * Contains an instance of this block, if available.
	 *
	 * @since  0.1.0
	 * @var    SALFWP_Block $_instance If available, contains an instance of this block.
	 */
	private static $_instance = null;

	/**
	 * Handle of primary block script.
	 *
	 * @since 0.1.0
	 * @var   string
	 */
	public $script_handle = 'salfwp_block';

	/**
	 * Block attributes.
	 *
	 * @since 0.1.0
	 * @var   array
	 */
	public $attributes = array();

	/**
	 * Register block type.
	 * Enqueue editor assets.
	 *
	 * @since  0.1.0
	 *
	 * @uses   SALFWP_Block::register_block_type()
	 */
	function __construct()
	{
		self::register_block_type();

		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_scripts' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_styles' ) );
	}

	/**
	 * Get instance of this class.
	 *
	 * @since  0.1.0
	 *
	 * @return SALFWP_Block
	 */
	public static function getInstance() {

		if ( null === self::$_instance ) {
			self::$_instance = new self;
		}

		return self::$_instance;

	}



	// # BLOCK REGISTRATION --------------------------------------------------------------------------------------------

	/**
	 * Register block with WordPress.
	 *
	 * @since  0.1.0
	 */
	public function register_block_type() {

		register_block_type( "salfwp/amazon", array(
			'render_callback' => array( $this, 'render_block' ),
			'editor_script'   => $this->script_handle,
			'attributes'      => $this->attributes,
		) );

	}





	// # SCRIPT ENQUEUEING ---------------------------------------------------------------------------------------------

	/**
	 * Enqueue block scripts.
	 *
	 * @since  0.1.0
	 *
	 * @uses   SALFWP_Block::scripts()
	 */
	public function enqueue_scripts() {

		$file = "/blocks/products.js";

		wp_enqueue_script( $this->script_handle, SALFWP_ASSETS_URL . $file, array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-i18n', 'wp-editor' ), filemtime( SALFWP_ASSETS_DIR . $file ) );

	}





	// # STYLE ENQUEUEING ----------------------------------------------------------------------------------------------

	/**
	 * Enqueue block styles.
	 *
	 * @since  0.1.0
	 */
	public function enqueue_styles() {
		$file = "/blocks/products.css";
		// Enqueue style.
		wp_enqueue_style( $this->script_handle, SALFWP_ASSETS_URL . $file, array(), filemtime( SALFWP_ASSETS_DIR . $file ), 'all' );
	}



	// # BLOCK RENDER -------------------------------------------------------------------------------------------------

	/**
	 * Display block contents on frontend.
	 *
	 * @since  0.1.0
	 *
	 * @param array $attributes Block attributes.
	 *
	 * @return string
	 */
	public function render_block( $attributes = array() ) {

		// Prepare variables.
		$productIds   = isset( $attributes['productIds'] ) ? $attributes['productIds'] : false;

		// Use Gravity Forms function for REST API requests.
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {

			// Start output buffering.
			ob_start();

			echo esc_html__( 'Amazon Products will Display Here', 'salfwp' );

			// Get output buffer contents.
			$buffer_contents = ob_get_contents();
			ob_end_clean();

			// Return buffer contents with form string.
			return $buffer_contents . $form_string;

		}

		$productIds = preg_replace("/((\r?\n)|(\r\n?))/", '', $productIds);
		return sprintf( '[salfwp product_id="%s"]', $productIds );

	}

}

$GLOBALS['SALFWP_Block'] = SALFWP_Block::getInstance();