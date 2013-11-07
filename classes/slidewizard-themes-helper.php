<?php
/**
 * SlideWizard Themes Helper
 *
 * This the parent class for each SlideWizard Themes
 */

class SlideWizardThemes_Helper {
  var $namespace = "slidewizard";

  var $options_themes = array();

  function __construct() {

    // Filter sizes depend on selected Themes
    if( method_exists( $this, "slidewizard_sizes" ) )
      add_filter( "{$this->namespace}_sizes", array( &$this, "slidewizard_sizes" ), 20, 2 );

    // Filter options
    if( method_exists( $this, "slidewizard_slide_options" ) )
      add_filter( "{$this->namespace}_slide_options", array( &$this, "slidewizard_slide_options" ), 20, 2 );

    // Default SlideWizard options
    if( method_exists( $this, "slidewizard_default_options") )
      add_filter( "{$this->namespace}_default_options", array( &$this, "slidewizard_default_options" ), 20, 3 );

    if( method_exists( $this, "slidewizard_dimensions" ) )
      add_action( "{$this->namespace}_dimensions", array( &$this, "slidewizard_dimensions" ), 20, 5 );

    // Hook into slidewizard wrapper classes
    if( method_exists( $this, "slidewizard_wrapper_classes" ) )
      add_action( "{$this->namespace}_wrapper_classes", array( &$this, "slidewizard_wrapper_classes" ), 20, 2 );

    // Merge size and options
    add_filter( "{$this->namespace}_default_options", array( &$this, "_slidewizard_default_options" ), 19, 3 );
    add_filter( "{$this->namespace}_sizes", array( &$this, "_slidewizard_sizes" ), 19, 2 );
    add_filter( "{$this->namespace}_slide_options", array( &$this, "_slidewizard_slide_options" ), 19, 2 );

    // Register Javascripts used by this themes
    add_action( 'init', array( &$this, '_slidewizard_register_scripts' ), 1 );

    // Register Stylesheets used by this themes
    add_action( 'init', array( &$this, '_slidewizard_register_styles' ), 1);
  }

  function __get( $name ) {
    switch( $name ) {
      case "themes":
        return $this->get_themes();
      break;
      
      case "slug":
        return $this->get_slug();
      break;
      
      default:
        $trace = debug_backtrace();
        trigger_error( "Undefined property via __get(): " . $name . " in " . $trace[0]['file'] . " on line " . $trace[0]['line'], E_USER_NOTICE );
      break;
    }
  }


  /**
   * Merge size options
   * 
   */
  function _slidewizard_sizes( $sizes, $slidewizard ) {
    if( $this->is_valid( $slidewizard['themes'] ) && isset( $this->themes['meta']['sizes'] ) )
      $sizes = array_merge( $sizes, $this->themes['meta']['sizes'] );

    return $sizes;
  }

    
  /**
   * Merge Themes default into SlideWizard options
   * 
   */
  function _slidewizard_default_options( $options, $themes, $source ) {
    if( $this->is_valid( $themes ) ) {
      if( isset( $this->options_themes ) ) {
        $default_options = array();
        foreach( $this->options_themes as &$options_groups ) {
          foreach( $options_groups as $name => $properties ) {
            if( isset( $properties['default'] ) )
              $default_options[$name] = $properties['default'];
          }
        }
        $options = array_merge( $options, $default_options );
      }
    }
    
    return $options;
  }

  /**
   * Hook into options to add additional Themes options
   * 
   * @param  array $options options array
   * @param  array $slidewizard SlideWizard data
   * 
   * @return array
   */
  function _slidewizard_slide_options( $options, $slidewizard ) {
    if( $this->is_valid( $slidewizard['themes'] ) ) {
      // Check if Themes has additional Options
      if( isset( $this->options_themes ) ) {
        // Loop through Option group
        foreach( $this->options_themes as $options_group => $options_item ) {
          // Loop through options item in each Options group
          foreach( $options_item as $option_key => $option_params ) {
            // Check if the option exists and needs merging or addition
            if( isset( $options[$options_group][$option_key] ) ) {
              // Check if this option has value and merge them
              if( isset( $options[$options_group][$option_key]['value'] ) ) {
                // Only merge if the Themes option has additional values
                if( isset( $option_params['value'] ) )
                  $option_params['value'] = array_merge( $options[$options_group][$option_key]['value'], $option_params['value'] );
              }
              // Merge options
              $options[$options_group][$option_key] = array_merge( (array) $options[$options_group][$option_key], $option_params );
            } else {
              // Define options
              $options[$options_group][$option_key] = $option_params;
            }
          }
        }
      }
    }
    
    return $options;
  }



  /**
   * Register javascripts used by this theme
   *
   */
  function _slidewizard_register_scripts() {
    if( isset( $this->themes['script_url'] ) ) {
      wp_register_script( "{$this->namespace}-themes-js-{$this->themes['slug']}", $this->themes['script_url'], array( 'jquery', "slidewizard-caroufredsel" ), SLIDEWIZARD_VERSION );
      if( isset( $this->themes['admin_script_url'] ) ) {
        wp_register_script( "{$this->namespace}-themes-admin-js-{$this->themes['slug']}", $this->themes['admin_script_url'], array( 'jquery', "{$this->namespace}-admin-script" ), SLIDEWIZARD_VERSION, true );
      }
    }
  }


  /**
   * Register stylesheets used by this theme
   *
   */
  function _slidewizard_register_styles() {
    $version = (isset( $this->themes['meta']['version']) && !empty( $this->themes['meta']['version'] ) ) ? $this->themes['meta']['version'] : SLIDEWIZARD_VERSION;
    wp_register_style( "{$this->namespace}-themes-{$this->themes['slug']}", $this->themes['url'], array('slidewizard-base'), $version );
  }


  /**
   * Get themes for current SlideWizard
   *
   */
  function get_themes() {
    global $SlideWizard;

    if( !isset( $this->themes ) )
      $this->themes = $SlideWizard->Themes->get( $this->slug );

    return $this->themes;
  }


  /**
   * Themes Slug
   * 
   * Builds the slug of the Themes based off of the name of the instance's
   * class name.
   * 
   */
  function get_slug() {
    if( !isset( $this->slug ) ) {
      $patterns = array(
        "/^SlideWizardThemes_/",
        "/([A-Z])/",
        "/([a-zA-Z]+)(\d+)([a-zA-Z]+)?/"
      );
      $replacements= array(
        "",
        " $1",
        "$1 $2 $3",
      );

      $classname = get_class( $this );
      $words = trim( preg_replace( $patterns, $replacements, $classname ) );

      $this->slug = strtolower( implode( "-", explode( " ", $words ) ) );
    }

    return $this->slug;
  }


  /**
   * Check if themes is valid
   *
   * @return boolean
   */
  protected final function is_valid( $slug ) {
    return $slug == $this->slug;
  }
}