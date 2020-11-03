<?php
/*
Plugin Name: Simple Emzon Links
Plugin URI: https://github.com/wddportfolio/simple-emzon-links/
Description: Simple Emzon Links is a simple WordPress plugin that allow you to display Amazon Affiliates Products in a Post, Page or Sidebar.
Version: 0.1.0
Author: WDDPortfolio
Author URI: https://www.wddportfolio.com/
Text Domain: salfwp
Domain Path: /languages/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Copyright © 2019 WDDPortfolio and Muhammad Furqan Abid

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

*/

class SALFWP {

  	private static $instance = null;
  	private $menuIcon;
    private $parentMenuId;
    private $aboutMenuId;

    public $pluginName;
    public $pluginSlug    = 'simple-emzon-links';
    public $pluginVersion = "0.1.0";

    public static $optionKey = "salfwp-options";

  	function __construct()
  	{
  		// Defining Constants
  		$this->constants();

  		// Initializing Actions
  		add_action( 'init', array($this, 'init') );

        // Do admin head action for this page
        add_action( 'admin_head', array( $this, 'admin_head' ) );

  		// load language files.
		load_plugin_textdomain( 'salfwp', false, dirname( SALFWP_BASE ) . '/languages/' );

        // Plugin Action Links
        add_action( 'plugin_action_links_' . SALFWP_BASE, array($this, 'plugin_action_links') );
  	}

  	private function constants()
  	{
  		// define some constants.
  		define( 'SALFWP_ASSETS_URL', plugins_url( '/assets', __FILE__ ) );
		define( 'SALFWP_JS_URL', plugins_url( '/assets/js', __FILE__ ) );
		define( 'SALFWP_CSS_URL', plugins_url( '/assets/css', __FILE__ ) );
		define( 'SALFWP_IMAGES_URL', plugins_url( '/assets/images', __FILE__ ) );
		define( 'SALFWP_PATH', dirname( __FILE__ ) );
		define( 'SALFWP_ASSETS_DIR', SALFWP_PATH . '/assets' );
		define( 'SALFWP_BASE', plugin_basename( __FILE__ ) );
		define( 'SALFWP_FILE', __FILE__ );

		require_once ( SALFWP_PATH . '/includes/WelcomePage.php' );
		require_once ( SALFWP_PATH . '/includes/ConfigurationPage.php' );
		require_once ( SALFWP_PATH . '/includes/class-shortcode.php' );
		require_once ( SALFWP_PATH . '/includes/blocks/class-salfwp-block.php' );

        //
        $this->pluginName       = esc_html__('Simple Emzon Links', 'salfwp');
        $this->menuIcon         = plugins_url('assets/images/salfwp-icon.png', SALFWP_FILE);
        $this->pluginShortSlug  = 'salfwp';
  	}

  	public function init()
  	{

		//Saving Options
		$this->saveSettings ();

		//Getting Options
		$options = $this->getSettings();

  		//Add Menu
  		add_action('admin_menu', array($this, 'addMenuPage'));

  		//Enqueue Styles & Scripts
  		add_action( 'wp_enqueue_scripts', array($this, 'front_style_scripts') );
  	}

    static function plugin_action_links( $links ) {
        $links = array_merge( array(
            '<a href="' . esc_url( admin_url( '/admin.php' ) ) . '?page=' . $this->pluginSlug . '">' . __( 'Settings', 'salfwp' ) . '</a>',
            '<a href="' . esc_url( 'https://wordpress.org/support/plugin/simple-emzon-links/reviews/' ) . '" target="_blank">' . __( 'Rate Us', 'salfwp' ) . '</a>'
        ), $links );
        return $links;
    }

  	public function addMenuPage()
  	{

  		$parentPage = array("SALFWP_ConfigurationPage", 'getPage');
  		$this->parentMenuId = add_menu_page( $this->pluginName, $this->pluginName, 'manage_options', $this->pluginSlug, $parentPage, $this->menuIcon );
  							  add_submenu_page( $this->pluginSlug, esc_html__('Configuration', 'salfwp'), esc_html__('Configuration', 'salfwp'), 'manage_options', $this->pluginSlug, $parentPage, 0);
  		$this->aboutMenuId  = add_submenu_page( $this->pluginSlug, esc_html__('About', 'salfwp'), esc_html__('About', 'salfwp'), 'manage_options', $this->pluginShortSlug.'-about', array('SALFWP_WelcomePage', 'aboutPage'), 11 );

  		add_action( "load-{$this->parentMenuId}", array( $this, '_load_page' ) );
  		add_action( "load-{$this->aboutMenuId}" , array( $this, '_load_page' ) );
  	}

  	public function _load_page(){

  		// Add admin body class for this page
  		add_filter( 'admin_body_class', function($classes){
  			$classes .= ' salfwp-page';
  			return $classes;
  		}, 10 );

  		// Add required script and styles for this page
        add_action('admin_enqueue_scripts', array($this, 'enqueueAssets'));

        // Add Ajax
        add_action( 'wp_ajax_salfwp-save'		, array( "SALFWP_ConfigurationPage", 'updateSettings'), 10 );
        add_action( "wp_ajax_nopriv_salfwp-save" , array( "SALFWP_ConfigurationPage", 'updateSettings'), 10 );

        // Do admin footer text hook
        add_filter( 'admin_footer_text', array( &$this, 'admin_footer_text' ) );
		
  	}

  	function front_style_scripts()
  	{

    	//Getting Options
		$options = $this->getSettings();

		//Load Stylesheet at Frontend?
		if ($options['salfwp-load-css-scripts'])
		{
  			wp_enqueue_style( 'salfwp-style', plugins_url('assets/css/salfwp-widgets.css', SALFWP_FILE), array(), $this->pluginVersion );
  		}
  	}

  	public function enqueueAssets ( $hook ){
  		//if ($hook && ($hook == $this->parentMenuId || $hook == $this->aboutMenuId)) {
  			wp_enqueue_style ($this->pluginShortSlug . '-admin', SALFWP_CSS_URL . '/salfwp-admin.css', array(), $this->pluginVersion);
  		//}
  	}

  	static function updateSettings ()
  	{
  		if (!wp_verify_nonce($_POST['salfwp-nonce'], $this->pluginSlug)) {
            exit;
        }

        $this->saveSettings(false, false);

        $result = array();

        exit(json_encode($result));
  	}

  	static function admin_footer_text($text) 
  	{
        return '';
    }

    static function admin_head ()
    {
        echo '<style>.menu-top.toplevel_page_simple-emzon-links img {height:24px!important;padding-top:6px!important}</style>';
    }

    static function getSettings()
    {
    	//delete_option( self::$optionKey );
    	$options = get_option( self::$optionKey );
    	if ( empty( $options ) )
    	{
    		$options = array(
    			'salfwp-api-mode'                => 'paapi5',
                'salfwp-tag-id'			 		 => '',
				'salfwp-access-key-id'			 => '',
				'salfwp-access-key-secret'		 => '',
				'salfwp-load-css-scripts'		 => true,
			);
    	}
    	return $options;
    }

    static function saveSettings($checkNonce=true, $redirect = true)
    {
    	if ( isset($_POST['action']) && $_POST['action']=="salfwp-save" )
    	{
	    	if ($checkNonce && isset($_POST['salfwp-nonce']) && !wp_verify_nonce($_POST['salfwp-nonce'], $this->pluginSlug)) 
	    	{
	            exit;
	        }

	        $api_mode      = sanitize_text_field($_POST['salfwp-api-mode']);
            $tag_id		   = sanitize_text_field($_POST['salfwp-tag-id']);
	        $access_key    = sanitize_text_field($_POST['salfwp-access-key-id']);
	        $access_secret = sanitize_text_field($_POST['salfwp-access-key-secret']);

	        // wp_die(
	        // 	'Tag ID:        ' . $tag_id 	 . "<br>" . 
	        // 	'Access Key:    ' . $access_key . "<br>" . 
	        // 	'Access Secret: ' . $access_secret . "<br>"
	        // );

	        $optSave = array(
                'salfwp-api-mode'                => $api_mode,
				'salfwp-access-key-id'			 => $access_key,
				'salfwp-access-key-secret'		 => $access_secret,
				'salfwp-tag-id'			 		 => $tag_id,
				'salfwp-load-css-scripts'		 => false
			);
			if ( isset($_POST['salfwp-load-css-scripts']) && sanitize_title($_POST['salfwp-load-css-scripts']) == "true" ){
				$optSave['salfwp-load-css-scripts']	= true;
			}

			update_option( self::$optionKey, $optSave );

	        if ($redirect)
	        {
	        	wp_redirect( admin_url('admin.php?page=' . $GLOBALS['SALFWP']->pluginSlug) );
	        	exit;
	        }
        }

    }

	public static function getInstance()
	{
		if ( is_null( self::$instance ) )
			self::$instance = new SALFWP();

		return self::$instance;
	}
}

$GLOBALS['SALFWP'] = SALFWP::getInstance();

?>