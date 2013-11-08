<?php
/*
Plugin Name: SlideWizard
Plugin URI: http://colorlabsproject.com/plugins/slidewizard/
Description: SlideWizard helps you to create beautiful slider from various source. 
Version: 1.0.2
Author: ColorLabs & Company
Author URI: http://colorlabsproject.com/
Text Domain: slidewizard
*/

/**
 * Copyright (c) 2013 ColorLabs & Company. All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * **********************************************************************
 */

/**
 * SlideWizard for WordPress
 *
 * Create awesome slider on your WordPress platform. Manage slide and 
 * insert them into posts.
 *
 * @author colorlabs
 */

class SlideWizard {

  public $post_type;

  public $namespace = "slidewizard";
  static $friendly_name = "SlideWizard";
  
  static $version = '1.0.2';

  // Environment, 'development' or 'production'
  // Don't forget to change back to production
  static $env = 'development';

  // WordPress Admin panel menu
  var $menu = array( );

  // Available Sources
  var $sources = array( );

  // Available Themes
  var $themes = array( );

  // SlideWizard Sizes
  var $sizes = array(
    'small' => array(
      'label' => "Small",
      'width' => 300,
      'height' => 300
    ),
    'medium' => array(
      'label' => 'Medium',
      'width' => 500,
      'height' => 500
    ),
    'large' => array(
      'label' => 'Large',
      'width' => 960,
      'height' => 500
    ),
    'custom' => array(
      'label' => 'Custom',
      'width' => 500,
      'height' => 500
    )
  );

  // JavaScript to be run in the footer of the page
  var $footer_scripts = "";

  // Styles to override slidewizard themes
  var $footer_styles = "";

  // Array of themes that need loading on a page
  var $themes_included = array();

  var $sources_included = array();

  var $slidewizard_ids = array();


  /**
   * Constructor method
   * 
   */
  function __construct() {
    SlideWizard::load_constant();

    /**
     * Make this plugin available for translation.
     * Translations can be added to the /languages/ directory.
     */
    load_plugin_textdomain( $this->namespace, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

    // SlideWizard Themes primary class
    include_once (SLIDEWIZARD_PLUGIN_DIR . '/classes/slidewizard-themes.php');
    $this->Themes = new SlideWizardThemes();

    include_once (SLIDEWIZARD_PLUGIN_DIR . '/classes/slidewizard-themes-helper.php');

    // Primary Class for SlideWizard Slides
    include_once (SLIDEWIZARD_PLUGIN_DIR . '/classes/slidewizard-slides.php');

    // Template function helper
    include_once( dirname( __FILE__ ) . '/includes/template-functions.php' );

    // Get All Available Themes
    $themes_files = (array) glob( SLIDEWIZARD_PLUGIN_DIR . '/themes/*/themes.php' );
    foreach( $themes_files as $filename ) {
      if( is_readable( $filename ) ) {
        include_once( $filename );

        $slug = basename( dirname( $filename ) );
        $classname = slidewizard_get_classname_from_filename( dirname( $filename ) );
        $prefix_classname = "SlideWizardThemes_{$classname}";
        if( class_exists( $prefix_classname ) ) {
          $this->themes[$slug] = new $prefix_classname;
        }
      }
    }

    // Get All Available sources
    $source_files = (array) glob( SLIDEWIZARD_PLUGIN_DIR . '/sources/*/source.php' );
    foreach( (array) $source_files as $filename ) {
      if( is_readable( $filename ) ) {
        include_once ($filename);

        $slug = basename( dirname( $filename ) );
        $classname = slidewizard_get_classname_from_filename( dirname( $filename ) );
        $prefix_classname = "SlideWizardSource_{$classname}";
        if( class_exists( $prefix_classname ) ) {
          $this->sources[$slug] = new $prefix_classname;
        }
      }
    }

    $this->Slides = new Slides();

    $this->add_hooks();
  }


  /**
   * Initialization function to hook into the WordPress init action
   *
   * Instantiates the class on a global variable and sets the class, actions
   * etc. up for use.
   */
  static function instance( ) {
    global $SlideWizard;

    // Only instantiate the Class if it hasn't been already
    if( !isset( $SlideWizard ) )      
      $SlideWizard = new SlideWizard();
  }


  /**
   * Add in various hooks
   *
   * Place all add_action, add_filter, add_shortcode hook-ins here
   */
  function add_hooks( ) {

    // Add page custom options page
    add_action( 'admin_menu', array( &$this, 'admin_menu' ) );

    // Register all JavaScript files used by this plugin
    add_action( 'init', array( &$this, 'wp_register_scripts' ), 1 );

    // Register all Stylesheets used by this plugin
    add_action( 'init', array( &$this, 'wp_register_styles' ), 1 );

    // Add custom post type
    add_action( 'init', array( &$this, 'register_post_types' ) );

    // Route requests for form processing
    add_action( 'init', array( &$this, 'route' ) );

    // Hook into slidewizard source control
    add_action( "{$this->namespace}_source_control", array( &$this, 'slidewizard_source_control' ) );

    // Enqueue javascripts and stylesheets on specific page
    add_action( 'admin_print_scripts-toplevel_page_' . SLIDEWIZARD_PLUGIN_NAME, array( &$this, 'admin_print_scripts' ) );
    add_action( 'admin_print_styles-toplevel_page_' . SLIDEWIZARD_PLUGIN_NAME, array( &$this, 'admin_print_styles' ) );

    // AJAX Action Hooks
    add_action( "wp_ajax_{$this->namespace}_source-modal", array( &$this, 'ajax_source_modal' ) );
    add_action( "wp_ajax_{$this->namespace}_delete-slide", array( &$this, 'ajax_delete_slide' ) );
    add_action( "wp_ajax_{$this->namespace}_preview-iframe", array( &$this, 'ajax_preview_iframe' ) );
    add_action( "wp_ajax_{$this->namespace}_preview-iframe-update", array( &$this, 'ajax_preview_iframe_update' ) );
    add_action( "wp_ajax_{$this->namespace}_get_theme_options", array( &$this, 'ajax_get_theme_options' ) );
    add_action( "wp_ajax_{$this->namespace}_insert-iframe", array( &$this, 'ajax_insert_iframe' ) );

    // Front-end only actions
    if( !is_admin() ) {
      // Pre-loading for themes use by SlideWizard in post or page
      add_action( 'wp', array( &$this, 'wp_hook' ) );

      // Print required themes stylesheets
      add_action( 'wp_print_styles', array( &$this, 'wp_print_styles' ) );
    }

    // Append scripts for slidewizard initiation at the footer
    add_action( 'wp_print_footer_scripts', array( &$this, 'print_footer_scripts' ) );

    // Add required JavaScripts and Stylesheets for displaying SlideWizards in front-end
    add_action( 'wp_print_scripts', array( &$this, 'wp_print_scripts' ) );

    // Add SlideWizard shortcode
    add_shortcode( 'slidewizard', array( &$this, 'shortcode' ) );

    add_action( "media_buttons", array( &$this, "media_button" ), 19 );
		
		// Add SlideWizard Documentation
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'slidewizard_action_links' ) );
		
    // Hook when plugin activated
    register_activation_hook( __FILE__, array( &$this, 'activate' ) );
  }


  /**
   * Add SlideWizard button editor
   * 
   */
  function media_button() {
    global $post;

    if( in_array( basename( $_SERVER['PHP_SELF'] ), array( 'post-new.php', 'page-new.php', 'post.php', 'page.php' ) ) ) {
      echo '<a href="'. $this->get_insert_iframe_url() .'" class="button thickbox add-slidewizard" id="add-slidewizard" title="' . esc_attr__( 'Insert your SlideWizard', $this->namespace ) . '" onclick="return false;">'. __('Insert SlideWizard', $this->namespace) .'</a>';
    }
  }


  /**
   * Register custom post type for slide and custom slide
   * 
   */
  public function register_post_types() {
    register_post_type( SLIDEWIZARD_POST_TYPE,
      array(
        'labels' => array(
          'name' => 'slidewizard',
          'singular_name' => __( 'SlideWizard', $this->namespace )
        ),
        // 'public' => true,
      )
    );
    register_post_type( SLIDEWIZARD_SLIDE_POST_TYPE,
      array(
        'labels' => array(
          'name' => 'slidewizard_slide',
          'singular_name' => __( 'SlideWizard Slide', $this->namespace )
        ),
        // 'public' => true
      )
    );
  }


  /**
   * Register javascript for enqueueing elsewhere
   * 
   */
  public function wp_register_scripts() {
    // Twitter bootstrap javascript plugin
    wp_register_script( 'bootstrap', SLIDEWIZARD_PLUGIN_URL . "/js/bootstrap" . (SLIDEWIZARD_ENV == 'development' ? '.dev' : '') . '.js', array('jquery'), '2.2.2', true);
    // SlideWizard Admin Script
    wp_register_script( 'slidewizard-admin-script', SLIDEWIZARD_PLUGIN_URL . "/js/slidewizard-admin-script.js", array('jquery', 'bootstrap'), SLIDEWIZARD_VERSION);
    // SlideWizard Admin Preview Script
    wp_register_script( 'slidewizard-admin-preview', SLIDEWIZARD_PLUGIN_URL . "/js/slidewizard-admin-preview.js", array('jquery', 'bootstrap'), SLIDEWIZARD_VERSION);
    // SlideWizard Insert Slide
    wp_register_script( 'slidewizard-insert-slide', SLIDEWIZARD_PLUGIN_URL . "/js/slidewizard-insert-slide.js", array('jquery'), SLIDEWIZARD_VERSION);
    // CarouFredSel Script
    wp_register_script( 'slidewizard-caroufredsel', SLIDEWIZARD_PLUGIN_URL . "/js/jquery.carouFredSel-6.1.0.js", array( 'jquery' ), '6.1.0' );
  }


  /**
   * Register stylesheets for enqueueing elsewhere
   * 
   */
  public function wp_register_styles() {
    // Twitter bootstrap style
    wp_register_style( 'bootstrap', SLIDEWIZARD_PLUGIN_URL . "/css/bootstrap" . (SLIDEWIZARD_ENV == 'development' ? '.dev' : '') . '.css', array(), '2.2.2' );
    // SlideWizard Admin Style
    wp_register_style( "slidewizard-admin-style", SLIDEWIZARD_PLUGIN_URL . "/css/slidewizard-admin-style.css" , array(), SLIDEWIZARD_VERSION );
    // SlideWizard base styles for front-end
    wp_register_style( "slidewizard-base", SLIDEWIZARD_PLUGIN_URL . "/css/slidewizard-base.css", array(), SLIDEWIZARD_VERSION );
  }


  /**
   * Determine which slidewizard are being loaded in this page
   * 
   */
  function wp_hook() {
    global $posts;

    if( isset( $posts ) && !empty( $posts ) ) {
      $this->slidewizard_ids = array();

      // SlideWizard being loaded with iframe=1
      $iframe_slidewizards = array();

      // Process through $posts for the existence of SlideWizards
      foreach( (array) $posts as $post ) {
        $matches = array( );
        preg_match_all( '/\[slidewizard( ([a-zA-Z0-9]+)\=\'?\"?([a-zA-Z0-9\%\-_\.]+)\'?\"?)*\]/', $post->post_content, $matches );
        if( !empty( $matches[0] ) ) {
          foreach( $matches[0] as $match ) {
            $str = $match;
            $str_pieces = explode( " ", $str );
            foreach( $str_pieces as $piece ) {
              $attrs = explode( "=", $piece );
              if( $attrs[0] == "id" ) {
                // Add the ID of this SlideWizard to the ID array for loading
                $this->slidewizard_ids[] = intval( str_replace( "\"", '', $attrs[1] ) );

                // Check for iframe = 1, yes, true
                if( preg_match( "/(iframe)=('|\")?(1|yes|true)('|\")?/", $str, $matches ) ) {
                  $iframe_slidewizards[] = $attrs[1];
                }
              }
            }
          }
        }
      }

      if( !empty( $this->slidewizard_ids ) ) {
        // Load SlideWizards used on this URL passing the array of IDs
        $slidewizards = $this->Slides->get( $this->slidewizard_ids );

        // Loop through SlideWizard used on this page and add their themes
        // to the $themes_included array for later use
        foreach( (array) $slidewizards as $slidewizard ) {
          // Only queue assets to be loaded if the SlideWizard is not being loaded via iframe
          if( !in_array( $slidewizard['id'], $iframe_slidewizards ) ) {
            $themes_slug = isset( $slidewizard['themes'] ) && !empty( $slidewizard['themes'] ) ? $slidewizard['themes'] : 'default';

            $this->themes_included[$themes_slug] = true;
            foreach( $slidewizard['source'] as $source ) {
              $this->sources_included[$source] = true;
            }
          }
        }
      }
    }
  }

	function slidewizard_action_links( $links ) {

		$plugin_links = array(
			'<a href="http://colorlabsproject.com/documentation/slidewizard/" target="_blank">' . __( 'Documentation' ) . '</a>'
		);

		return array_merge( $plugin_links, $links );
	}
	
  /**
   * Load javascripts for slidewizard options page
   * 
   */
  public function admin_print_scripts() {
    wp_enqueue_script( 'jquery-ui-slider' );
    wp_enqueue_script( 'bootstrap' );
    wp_enqueue_script( 'slidewizard-admin-script' );
    wp_enqueue_script( 'slidewizard-admin-preview' );

    // Create object for text that generated with javascript, so it's
    // still translateable
    $translate_message = array(
      'confirm_delete' => __("Are you sure you want to delete this slide? This can't be undone.", $this->namespace),
      'use_slide' => __("Copy & Paste this shortcode into your post or page", $this->namespace)
    );
    wp_localize_script( 'slidewizard-admin-script', "{$this->namespace}_message", $translate_message );
  }


  /**
   * Load stylesheets for slidewizard options page
   * 
   */
  public function admin_print_styles() {
    wp_enqueue_style( 'bootstrap' );
    wp_enqueue_style( 'slidewizard-admin-style' );

    // Only load this only when on SlideWizard page
    if( $this->is_plugin() ) {
      if( isset( $_GET['id'] ) ) {
        $slidewizard = $this->Slides->get( $_GET['id'] );
        $themes = $slidewizard['themes'];
      } else {
        $themes = SLIDEWIZARD_DEFAULT_THEMES;
      }

      $this->themes_included = array( $themes => 1 );
    }
  }


  /**
   * Load javascripts required by SlideWizard on front-end
   * 
   */
  function wp_print_scripts() {
    foreach( (array) $this->themes_included as $themes_slug => $val ) {
      wp_enqueue_script( "{$this->namespace}-themes-js-{$themes_slug}" );
    }
  }

  /**
   * Load stylesheet required by SlideWizard on front-end
   * 
   */
  function wp_print_styles() {
    foreach( (array) $this->themes_included as $themes_slug => $val ) {
      wp_enqueue_style( "{$this->namespace}-themes-{$themes_slug}" );
    }
  }


  /**
   * Print javascript at the footer
   * 
   */
  function print_footer_scripts() {
    echo $this->footer_scripts;
  }


  /**
   * Load Constant
   *
   * Conveninece function to load the constants files for the 
   * activation and construct
   */
  public function load_constant() {
    if( !defined( 'SLIDEWIZARD_PLUGIN_NAME' ) ) define('SLIDEWIZARD_PLUGIN_NAME', trim(dirname(plugin_basename(__FILE__)), '/'));

    require_once (dirname( __FILE__ ) . '/includes/constants.php');
  }


  /**
   * Add options page for configuration
   *
   */
  public function admin_menu() {
    $show_menu = true;
    if( !current_user_can( 'manage_options' ) ) {
      $show_menu = false;
    }

    // If user role can manage options
    if( $show_menu === true ) {
      add_menu_page( 'Manage SlideWizard', 'SlideWizard', 'publish_posts', SLIDEWIZARD_PLUGIN_NAME, array( &$this, 'page_route' ), plugin_dir_url( __FILE__ )."images/menu-icon.png", 40 );
    }
  }


  /**
   * SlideWizard Page Router
   *
   * Check "action" parameter and determine what page will be shown
   *
   */
  public function page_route() {
    $action = array_key_exists( 'action', $_REQUEST ) ? $_REQUEST['action'] : "";

    switch( $action ) {

      // Create new slide
      case "create":
        $this->page_create_edit();
      break;

      // Edit existing slide
      case "edit":
        $this->page_create_edit();
      break;

      // SlideWizard Dashboard Page
      default:
        $this->page_dasboard();
      break;
    }
  }


  /**
   * SlideWizard Dashboard page
   * 
   */
  public function page_dasboard() {
    $slidewizards = $this->Slides->get(null, 'publish');

    // Render the Dashboard views
    include (SLIDEWIZARD_PLUGIN_DIR . '/views/dashboard.php');
  }


  /**
   * SlideWizard Create and Edit page
   * 
   */
  public function page_create_edit() {

    $form_action = "create";
    if( isset( $_REQUEST['id'] ) ) {
      $form_action = "edit";
    }

    $sources = $this->get_sources();
    $namespace = $this->namespace;

    // Redirect to dashboard if invalid source was specified
    if( $form_action == "create" ) {
      $source = $_REQUEST['source'];

      if( !is_array( $source ) )
        $source = array( $source );

      $source_intersect = array_intersect( $source, array_keys( $sources ) );

      if( !isset( $_REQUEST['source'] ) OR empty( $source_intersect ) ) {
        echo '<script type="text/javascript">document.location.href="'. $this->action() .'"</script>';
      }
    }

    // If user editing a slide
    if( $form_action == 'edit' ) {
      $slidewizard = $this->Slides->get( $_REQUEST['id'] );
    } else {
      $slidewizard = $this->Slides->create( $source );
    }

    // Options
    $options = $this->get_options( $slidewizard );

    // Get Size
    $sizes = apply_filters( "{$this->namespace}_sizes", $this->sizes, $slidewizard );
    $slidewizard_dimensions = $this->get_dimensions( $slidewizard );

    // Get Themes
    $themes = $this->get_slidewizard_themes( $slidewizard );

    // Render views
    include (SLIDEWIZARD_PLUGIN_DIR . '/views/slide-form.php');
  }


  /**
   * This function will handling routing of form submissions to the appropriate
   * form processor.
   * 
   */
  public function route() {
    $uri = $_SERVER['REQUEST_URI'];
    $protocol = isset( $_SERVER['HTTPS'] ) ? 'https' : 'http';
    $hostname = $_SERVER['HTTP_HOST'];
    $url = "{$protocol}://{$hostname}{$uri}";
    $is_post = (bool)(strtoupper( $_SERVER['REQUEST_METHOD'] ) == "POST");
    $nonce = isset( $_REQUEST['_wpnonce'] ) ? $_REQUEST['_wpnonce'] : false;

    // Check if wp nonce exists
    if( $nonce ) {
      // Handle Post Request
      if( $is_post ) {

        // Create or edit slide
        if( wp_verify_nonce( $nonce, "{$this->namespace}-create-slidewizard" ) ||
            wp_verify_nonce( $nonce, "{$this->namespace}-edit-slidewizard" ) ) {
          $this->save();
        }

      }
    }
  }


  /**
   * Form processor for saving Slide
   * 
   */
  public function save() {
    if( !isset( $_POST['id'] ) ) {
      return false;
    }

    $slide_id = intval( $_POST['id'] );

    $slidewizard = $this->Slides->save( $slide_id, $_POST );

    $action = '&action=edit&id=' . $slide_id;

    if( $_POST['action'] == "create" ) {
      $action .= '&firstsave=1';
    }

    wp_redirect( $this->action( $action ) );
    exit ;

  }


  /**
   * Render Source control at top of the slide creation form
   * 
   * @param  obj $slidewizard Slide Object
   * 
   */
  function slidewizard_source_control($slidewizard) {
    $namespace = $this->namespace;

    $sources = $this->get_sources( $slidewizard['source'] );

    $slide_id = $slidewizard['id'];

    include( SLIDEWIZARD_PLUGIN_DIR . '/views/partials/_source.php' );
  }


  /**
   * Ajax Source Modal
   * 
   * This modal will be shown when Create Slide button clicked
   */
  public function ajax_source_modal() {

    $sources = $this->get_sources();
    $namespace = $this->namespace;
    $title = "Choose a source to create new Slider";
    $action = "create";

    include (SLIDEWIZARD_PLUGIN_DIR . "/views/partials/_source-modal.php");
    exit;
  }


  /**
   * Action for deleting SlideWizard
   * 
   */
  public function ajax_delete_slide() {
    $nonce = $_REQUEST['nonce'];
    $id = $_REQUEST['id'];

    if( wp_verify_nonce( $nonce, "{$this->namespace}_delete_slide" ) ) {
      $this->Slides->delete( $id );
    }
    exit;
  }


  /**
   * Ajax action for preview slide within iframe
   * 
   */
  function ajax_preview_iframe() {
    global $wp_scripts, $wp_styles;

    $slide_id = $_GET['id'];
    if( isset( $_GET['width'] ) && is_numeric( $_GET['width'] ) )
      $width = $_GET['width'];
    if( isset( $_GET['height'] ) && is_numeric( $_GET['height'] ) )
      $height = $_GET['height'];
    if( isset( $_GET['outer_width'] ) && is_numeric( $_GET['outer_width'] ) )
      $outer_width = $_GET['outer_width'];
    if( isset( $_GET['outer_height'] ) && is_numeric( $_GET['outer_height'] ) )
      $outer_height = $_GET['outer_height'];

    $slidewizard = $this->Slides->get( $slide_id );
    $themes = $this->Themes->get( $slidewizard['themes'] );

    $namespace = $this->namespace;

    $scripts = apply_filters( "{$this->namespace}_iframe_scripts", array( 'jquery', 'slidewizard-caroufredsel' ), $slidewizard );
    $content_url = defined( 'WP_CONTENT_URL' ) ? WP_CONTENT_URL : '';
    $base_url = !site_url( ) ? wp_guess_url( ) : site_url( );

    include (SLIDEWIZARD_PLUGIN_DIR . '/views/preview-iframe.php');
    exit;
  }


  /**
   * Ajax action for getting a new preview URL in an iframe
   * 
   */
  function ajax_preview_iframe_update() {
    $slide_id = intval( $_REQUEST['id'] );
    $response = $this->_save_autodraft( $slide_id, $_REQUEST );

    header( "Content-Type: application/json" );
    die( json_encode( $response ) );
  }


  /**
   * Ajax action for showing list of created SlideWizard
   * 
   */
  function ajax_insert_iframe() {
    global $wp_scripts;

    $namespace = $this->namespace;
    $scripts = array( 'jquery', 'slidewizard-insert-slide' );
    $content_url = defined( 'WP_CONTENT_URL' ) ? WP_CONTENT_URL : '';
    $base_url = !site_url( ) ? wp_guess_url( ) : site_url( );

    $slidewizards = $this->Slides->get(null, 'publish');

    include ( SLIDEWIZARD_PLUGIN_DIR . '/views/insert-slidewizard.php' );
    exit;
  }


  /**
   * Ajax action for receiving options for specific SlideWizard Themes
   */
  function ajax_get_theme_options() {
    $slide_id = intval( $_REQUEST['id'] );
    $slidewizard = $this->Slides->save_preview( $slide_id, $_REQUEST );
    $sizes = apply_filters( "{$this->namespace}_sizes", $this->sizes, $slidewizard );
    $themes = $this->get_slidewizard_themes( $slidewizard );
    $options = $this->get_options( $slidewizard );

    include( SLIDEWIZARD_PLUGIN_DIR . "/views/partials/_options.php" );
    exit;
  }


  /**
   * Get all available sources
   * 
   */
  public function get_sources( $source_slugs = array() ) {
    $sources = $this->sources;

    if( !empty( $source_slugs ) ) {
        if( !is_array( $source_slugs ) ) {
            $source_slugs = array( $source_slugs );
        }

        $filtered_sources = array( );
        foreach( $sources as $source_name => $source_object ) {
            if( in_array( $source_name, $source_slugs ) ) {
                $filtered_sources[$source_name] = $source_object;
            }
        }
        $sources = $filtered_sources;
    }

    return $sources;
  }


  /**
   * Get available Themes
   * 
   * @param array $slidewizard SlideWizard data
   * @return array
   */
  public function get_slidewizard_themes( $slidewizard ) {
    $themes = $this->Themes->get();

    $filtered = array();
    foreach( $themes as $theme ) {
      $themes_intersect = array_intersect( (array)$slidewizard['source'], $theme['meta']['sources'] );
      if( !empty( $themes_intersect ) ) {
        $filtered[] = $theme;
      }
    }
    $themes = $filtered;

    return $themes;
  }


  /**
   * Get Options
   *
   * @param  array $slidewizard Array containing information about 
   * the Slide
   */
  public function get_options( $slidewizard ) {
    $options = apply_filters("{$this->namespace}_slide_options", $this->Slides->options, $slidewizard );
        
    return $options;
  }


  /**
   * Fired when the plugin is activated.
   * 
   */
  public function activate() {
    $this->register_plugin_version();

    // Register plugin version
    if( SLIDEWIZARD_VERSION != '' ) {
      update_option( "{$this->namespace}_version", SLIDEWIZARD_VERSION);
    }
  }


  /**
   * Get the URL for the specified plugin action
   *
   * @param object $str [optional] Expects the handle passed in the menu
   * definition
   *
   * @return The absolute URL to the plugin action specified
   */
  function action( $str = "" ) {
    $path = admin_url( "admin.php?page=" . SLIDEWIZARD_PLUGIN_NAME );

    if( !empty( $str ) ) {
        return $path . $str;
    } else {
        return $path;
    }
  }


  /**
   * Function to check if we are viewing SlideWizard plugin page
   * 
   * @return boolean 
   */
  function is_plugin() {
    global $pagenow;
    
    $is_plugin = false;
    
    if( !function_exists( 'get_current_screen' ) )
      return false;

    $screen_id = get_current_screen( );
    if( empty( $screen_id ) )
      return false;
    
    if( isset( $screen_id->id ) )
      $is_plugin = (boolean) in_array(  $screen_id->id, array_values( $this->menu ) );
    
    return $is_plugin;
  }


  /**
   * SlideWizard shortcode
   * 
   * @param array $atts shortcode options
   */
  function shortcode( $atts ) {
    global $post;

    // if( isset( $atts['id'] ) && !empty( $atts['id'] ) )

    // Filter shortoce attributes
    $atts = apply_filters( "{$this->namespace}_shortcode_atts", $atts );

    extract( shortcode_atts( array(
      'id' => (boolean) false,
      'width' => null,
      'height' => null,
      // 'iframe' => (boolean) false,
      'preview' => (boolean) false,
    ), $atts ) );

    if( $id !== false ) {
      return $this->Slides->render( $id, $preview );
    } else {
      return "";
    }
  } 


  /**
   * Get SlideWizard dimensions
   * 
   * @return array
   */
  function get_dimensions( $slidewizard ) {
    $dimensions = array( );

    $sizes = apply_filters( "{$this->namespace}_sizes", $this->sizes, $slidewizard );

    $dimensions['width'] = $slidewizard['options']['size'] != "custom" ? $sizes[$slidewizard['options']['size']]['width'] : $slidewizard['options']['width'];
    $dimensions['height'] = $slidewizard['options']['size'] != "custom" ? $sizes[$slidewizard['options']['size']]['height'] : $slidewizard['options']['height'];
    $dimensions['outer_width'] = $dimensions['width'];
    $dimensions['outer_height'] = $dimensions['height'];

    do_action_ref_array( "{$this->namespace}_dimensions", array( &$dimensions['width'], &$dimensions['height'], &$dimensions['outer_width'], &$dimensions['outer_height'], &$slidewizard ) );

    return $dimensions;
  }


  /**
   * Get Iframe URL for inserting SlideWizard to the post or page
   */
  function get_insert_iframe_url() {
    global $post;

    $url = admin_url( "admin-ajax.php?action={$this->namespace}_insert-iframe&post_id={$post->ID}&TB_iframe=1&width=640&height=515" );

    return $url;
  }


  /**
   * Get Iframe URL
   * 
   */
  function get_iframe_url( $slide_id, $width, $height, $preview = false ) {
    $slidewizard = $this->Slides->get( $slide_id );
    if( empty( $slidewizard ) )
      return '';

    $slidewizard_dimensions = $this->get_dimensions( $slidewizard );

    if( !$preview ) $uniqid = strtotime( $slidewizard['updated_at'] );

    if( !isset( $width ) )
      $width = $slidewizard_dimensions['width'];

    if( !isset( $height ) )
      $height = $slidewizard_dimensions['height'];

    if( !isset( $outer_width ) )
      $outer_width = $slidewizard_dimensions['outer_width'];

    if( !isset( $outer_height ) )
      $outer_height = $slidewizard_dimensions['outer_height'];

    $dimensions = array(
      'width' => $width,
      'height' => $height,
      'outer_width' => $outer_width,
      'outer_height' => $outer_height
    );

    $url = admin_url( "admin-ajax.php?action={$this->namespace}_preview-iframe&id={$slide_id}&" . http_build_query( $dimensions, '', '&' ) );

    if( $preview ) $url .= '&preview=1';

    return $url;
  }


  /**
   * Save a SlideWizard autodraft
   *
   * Saves a SlideWizard auto-draft and return an array containing
   * information about the Slides
   * 
   * @param int $slide_id Slide ID
   * @param array $data All data about the SlideWizard
   * 
   * @return array
   */
  private function _save_autodraft( $slide_id, $data ) {
    // Preview SlideWizard object
    $preview = $this->Slides->save_preview( $slide_id, $data );

    $dimensions = $this->get_dimensions( $preview );

    $iframe_url = $this->get_iframe_url( $preview['id'], $dimensions['outer_width'], $dimensions['outer_height'], true );

    $response = $dimensions;
    $response['preview_id'] = $preview['id'];
    $response['preview'] = $preview;
    $response['url'] = $iframe_url;

    return $response;

    // global $wpdb;
    // $preview = $this->Slides->get( $slide_id );
    // $sql = "SELECT * FROM {$wpdb->posts} WHERE post_status = %s AND post_type = %s";
    // $sql = $wpdb->prepare( $sql, 'auto-draft', SLIDEWIZARD_POST_TYPE );
    // return $wpdb->get_results( $sql );
  }


} // end class

// Initialize plugin
add_action( 'plugins_loaded', array( 'SlideWizard', 'instance' ), 15 );