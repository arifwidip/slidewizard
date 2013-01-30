<?php
/**
 * SlideWizard Model
 *
 * Class for handling CRUD
 */
class Slides {

  var $namespace = "slidewizard";

  // Base file path for a source
  var $basedir = "";
  // Base URL for a source
  var $baseurl = "";

  // SlideWizard Options
  var $options = array( );

  // Default slides array property
  protected $slide_item_prop = array(
    "title" => "",
    "styles" => "",
    "classes" => array(),
    "content" => "",
    "thumbnail" => "",
    "source" => "",
    "type" => "textonly",
  );

  var $slide_types = array(
    'image' => "Image",         // Image only and mixed image/text
    'html' => "HTML",           // Raw HTML
    'textonly' =>"Text Only",   // Text only layouts
    'video' => "Video"          // Video slides
  );

  // Make sure javascript data type is correct
  var $javascript_datatype = array(
    "animation_speed" => "integer",
    "autoplay_interval" => "integer",
    "autoplay_slide" => "boolean",
    "circular" => "boolean",
    "height" => "integer",
    "infinite" => "boolean",
    "number_of_slides" => "boolean",
    "randomize" => "boolean",
    "starting_slide" => "integer"
  );


  function __construct() {
    
    // Separate options array for more readable code
    include( SLIDEWIZARD_PLUGIN_DIR . "/includes/slide-options.php" );
    $this->options = $options;

    add_action( 'admin_init', array( &$this, '_admin_init' ) );
    add_action( 'admin_print_scripts-toplevel_page_' .SLIDEWIZARD_PLUGIN_NAME, array( &$this, '_admin_print_scripts' )  );
    add_action( 'admin_print_styles-toplevel_page_' .SLIDEWIZARD_PLUGIN_NAME, array( &$this, '_admin_print_styles' )  );

    // Cleanup unsaved slides
    add_action( "{$this->namespace}_cleanup_create", array( &$this, 'cleanup_create'), 10, 1 );

    // Hook for modifying options array
    if( method_exists( $this, 'slidewizard_slide_options' ) )
      add_filter( "{$this->namespace}_slide_options", array( &$this, 'slidewizard_slide_options' ), 11, 2 );

    // Set default options when merged with options from source
    add_filter( "{$this->namespace}_default_options", array( &$this, '_slidewizard_default_options' ), 10, 3 );
    if( method_exists( $this, 'slidewizard_default_options') )
      add_filter( "{$this->namespace}_default_options", array( &$this, 'slidewizard_default_options' ), 11, 3 );

    // Define the basedir for the source
    if( method_exists( $this, 'slidewizard_get_source_file_basedir' ) )
      add_filter( "{$this->namespace}_get_source_file_basedir", array( &$this, 'slidewizard_get_source_file_basedir' ), 10, 2 );

    // Define baseurl for the source
    if( method_exists( $this, 'slidewizard_get_source_file_baseurl' ) )
      add_filter( "{$this->namespace}_get_source_file_baseurl", array( &$this, 'slidewizard_get_source_file_baseurl' ), 10, 2 );

    // Filter slide output depend on source
    if( method_exists( $this, 'slidewizard_get_slides') )
      add_filter( "{$this->namespace}_get_slides", array( &$this, 'slidewizard_get_slides' ), 10, 2 );

    if( method_exists( $this, 'add_hooks' ) )
      $this->add_hooks();
  }


  /**
   * Admin init hook
   * 
   */
  function _admin_init() {
    global $SlideWizard;

    // Get the type based off the source in the URL
    if( isset( $_REQUEST['source'] ) ) {
      $this->current_source = array( $_REQUEST['source'] );
    } 
    
    elseif( isset( $_REQUEST['id'] ) ) {
      $slidewizard = $this->get( $_REQUEST['id'] );
      $this->current_source = $slidewizard['source'];
    }

    $this->register_scripts();
    $this->register_styles();
  }


  /**
   * Register scripts used by SlideWizard
   * 
   */
  function register_scripts() {
    // Fail silently if this is not a sub-class instance
    if( !isset( $this->name ) ) {
      return false;
    }
    
    $filename = '/sources/' . $this->name . '/source.js';
    
    if( file_exists( SLIDEWIZARD_PLUGIN_DIR . $filename ) ) {
      wp_register_script( "slidewizard-source-{$this->name}-admin", SLIDEWIZARD_PLUGIN_URL . $filename, array( 'jquery', 'slidewizard-admin-script' ), SLIDEWIZARD_VERSION, true );
    }
  }


  /**
   * Register styles used by SlideWizard
   * 
   */
  function register_styles() {
    // Fail silently if this is not a sub-class instance
    if( !isset( $this->name ) ) {
      return false;
    }
    
    $filename = '/sources/' . $this->name . '/source.css';
    
    if( file_exists( SLIDEWIZARD_PLUGIN_DIR . $filename ) ) {
      wp_register_style( "slidewizard-source-{$this->name}-admin", SLIDEWIZARD_PLUGIN_URL . $filename, array( 'slidewizard-admin-style' ), SLIDEWIZARD_VERSION );
    }
  }

  /**
   * Print Scripts on admin page
   */
  function _admin_print_scripts() {
    global $SlideWizard;

    if( isset( $this->current_source ) ) {
      if( $this->is_valid( $this->current_source ) ) {
        wp_enqueue_script( "slidewizard-source-{$this->name}-admin" );
      }
    }
  }

  /**
   * Print Styles on admin page
   */
  function _admin_print_styles() {
    global $SlideWizard;

    if( isset( $this->current_source ) ) {
      if( $this->is_valid( $this->current_source ) ) {
        wp_enqueue_style( "slidewizard-source-{$this->name}-admin" );
      }
    }
  }


  /**
   * Set Default Options
   * 
   */
  function _slidewizard_default_options( $options, $themes, $source ) {
    if( !isset( $this->name ) )
      return $options;

    if( $this->is_valid( $source ) ) {
      if( isset( $this->source_options ) ) {
        foreach( $this->source_options as $options_group ) {
          foreach( $options_group as $name => $property ) {
            if( isset( $property['default'] ) )
              $options[$name] = $property['default'];
          }
        }
      }

      if( isset( $this->default_options ) )
        $options = array_merge( $options, $this->default_options );
    }

    return $options;
  }


  /**
   * Method for get SlideWizard slide
   * 
   * @param int $id SlideWizard id to retrieve, if null will get all slides
   * @return array
   */
  public function get( $id = null, $post_status = 'any' ) {
    global $wpdb;

    $sql = $wpdb->prepare( "SELECT {$wpdb->posts}.* FROM $wpdb->posts WHERE {$wpdb->posts}.post_type = %s", SLIDEWIZARD_POST_TYPE );

    if( isset( $id ) ) {
      $sql .= " AND {$wpdb->posts}.ID";
      if( is_array( $id ) ) {
        // Mae sure all IDs are numeric
        array_map( 'intval', $id);

        $sql .= " IN(". JOIN(',', $id) .")";
      } else {
        $sql = $wpdb->prepare( $sql . " = %d", $id );
      }
    }

    // Check post status
    if( ! empty( $post_status ) ) {
      if( $post_status != "any" )
        $sql = $wpdb->prepare( $sql . " AND {$wpdb->posts}.post_status = %s", $post_status );
    }

    $query_posts = $wpdb->get_results( $sql );

    // Populate the $slidewizards array with SlideWizard entries
    $slidewizards = array();
    foreach( (array) $query_posts as $post ) {
      $post_id = $post->ID;

      $slidewizard = array(
        'id' => $post_id,
        'author' => $post->post_author,
        'type' => get_post_meta( $post_id, "{$this->namespace}_type", true ),
        'themes' => get_post_meta( $post_id, "{$this->namespace}_themes", true ),
        'source' => $this->get_sources( $post_id ),
        'post_status' => get_post_status( $post_id ),
        'title' => get_the_title( $post_id ),
        'created_at' => $post->post_date,
        'updated_at' => $post->post_modified
      );
      $slidewizard['options'] = $this->get_options( $post_id, $slidewizard['themes'], $slidewizard['source'] );

      $slidewizards[] = $slidewizard;
    }
    
    // If this was a request for a single Slide, only return the requested ID
    if( isset( $id ) && !is_array( $id ) ) {
      foreach ( (array) $slidewizards as $slidewizard ) {
        if( $slidewizard['id'] == $id ) {
          return $slidewizard;
        }
      }
    }

    return $slidewizards;
  }
  

  /**
   * Method for creating SlideWizard Slide
   * 
   */
  public function create( $source = array() ) {
    $form_action = "create";

    $post_status = apply_filters( "{$this->namespace}_default_create_status", "auto-draft", $source );

    $slide_id = wp_insert_post( array(
      'post_content' => '',
      'post_title' => SLIDEWIZARD_NEW_TITLE,
      'post_status' => $post_status,
      'comment_status' => 'closed',
      'ping_status' => 'closed',
      'post_type' => SLIDEWIZARD_POST_TYPE
    ) );

    if( $post_status == 'auto-draft' ) {
      wp_schedule_single_event( time() + 1, "{$this->namespace}_cleanup_create", $slide_id);
    }

    // Set SlideWizard Source
    foreach( $source as $single_source ) {
      add_post_meta($slide_id, "{$this->namespace}_source", $single_source);
    }

    // Set default SlideWizard Themes
    $themes = apply_filters( "{$this->namespace}_default_themes", SLIDEWIZARD_DEFAULT_THEMES, $source );
    update_post_meta( $slide_id, "{$this->namespace}_themes", $themes );

    // Set default SlideWizard Options
    $options = apply_filters( "{$this->namespace}_default_options", $this->default_options(), $themes, $source );
    update_post_meta( $slide_id, "{$this->namespace}_options", $options );

    $slides = $this->get( $slide_id, $post_status );

    return $slides;
  }


  /**
   * Delete Slides
   * 
   */
  public function delete( $slide_id ) {
    $slidewizard = $this->get( $slide_id );

    wp_delete_post( $slide_id, true );
  }


  /**
   * Save the slide
   * 
   * @param integer $slide_id Slide ID
   * @param array $params The Slide parameters to save, if none are passed, returns false
   * 
   * @return object $slidewizard Updated SlideWizard object
   */
  public function save( $slide_id = null, $params = array() ) {
    // Fail silently if not parameters were passed in
    if( !isset( $slide_id ) || empty( $params ) ) {
      return false;
    }

    // @TODO: Clean the data for safe storage
    $data = $params;

    // Get this SlideWizard source
    $source = $data['source'];
    // Get what themes is used by this SlideWizard
    $themes = $data['themes'];

    $options = apply_filters( "{$this->namespace}_slide_options", $this->options, $data );

    // Loop through boolean options and set as false if the value was not passed in
    foreach( $options as $options_group => $options_item ) {
      foreach( $options as $key => $properties ) {
        if( !isset( $properties['datatype'] ) ) $properties['datatype'] = "string";
        if( !isset( $data['options'][$key] ) && $properties['datatype'] == "boolean" ) {
          $data['options'][$key] = false;
        }
      }
    }

    // Properly store the data as the expected option type
    foreach( $data['options'] as $key => &$val ) {
      foreach( $options as $options_group => $options_item ) {
        if( in_array( $key, array_keys( $options_item ) ) ) {
          // Make sure that the response is of the appropriate object type
          if( is_string( $val ) ) {
            $data_type = isset( $options_item[$key]['datatype'] ) ? $options_item[$key]['datatype'] : "string";
              $val = $this->_type_fix( $val, $data_type );
          } elseif( is_array( $val ) ) {
            foreach( $val as $_key => &$_val ) {
            $data_type = isset( $options_item[$key][$_key]['datatype'] ) ? $options_item[$key][$_key]['datatype'] : "string";
              $_val = $this->_type_fix( $_val, $data_type );
            }
          }
        }
      }
    }


    // Allow filter hook to override options values
    $data['options'] = apply_filters( "{$this->namespace}_options", $data['options'], "", $source );

    $post_args = array(
      'ID' => $slide_id,
      'post_status' => 'publish',
      'post_content' => '',
      'post_title' => $data['title']
    );

    if( isset( $data['post_status'] ) && !empty( $data['post_status'] ) )
      $post_args['post_status'] = $data['post_status'];

    if( isset( $data['post_parent'] ) && !empty( $data['post_parent'] ) )
      $post_args['post_parent'] = $data['post_parent'];

    // Save the SlideWizard post type
    wp_update_post( $post_args );

    // Save the content source
    $sources = $this->get_sources( $slide_id );
    foreach( $data['source'] as $source ) {
      if( !in_array( $source, $sources ) ) {
        add_post_meta( $slide_id, "{$this->namespace}_source", $source );
      }
    }

    // Save the themes used by this SlideWizard
    update_post_meta( $slide_id, "{$this->namespace}_themes", $themes );

    // Save SlideWizard Options
    update_post_meta( $slide_id, "{$this->namespace}_options", $data['options'] );

    // Return the newly saved SlideWizard
    $slidewizard = $this->get( $slide_id );

    return $slidewizard;
  }


  /**
   * Save SlideWizard for preview
   * 
   * @param integer $slide_id Slide ID
   * @param array $params The Slide parameters to save, if none are passed, returns false
   * 
   * @return object $slidewizard Updated SlideWizard object of previewing
   */
  public function save_preview( $slide_id, $params ) {
    global $wpdb;

    $slidewizard = $this->get( $slide_id );

    $sql = "SELECT ID FROM {$wpdb->posts} WHERE post_status = %s AND post_parent = %d AND post_type = %s";
    $slidewizard_preview_id = $wpdb->get_var( $wpdb->prepare( $sql, 'auto-draft', $slide_id, SLIDEWIZARD_POST_TYPE ) );

    // Create a new auto-draft to save previews for
    if( empty( $slidewizard_preview_id ) ) {
      $post_args = array(
        'post_status' => "auto-draft",
        'post_parent' => $slide_id,
        'post_type' => SLIDEWIZARD_POST_TYPE,
        'post_title' => $slidewizard['title'] . " Preview"
      );
      $slidewizard_preview_id = wp_insert_post( $post_args );
    }

    $params['post_status'] = "auto-draft";
    $params['post_parent'] = $slide_id;

    $slidewizard_preview = $this->save( $slidewizard_preview_id, $params );
    return $slidewizard_preview;
    // return $slidewizard_preview_id;
  }


  /**
   * Default Options for SlideWizard
   * 
   */
  function default_options() {
    global $SlideWizard;

    $default_options = array();

    foreach( array( $SlideWizard->Slides->options, $this->options ) as $model ) {
      foreach( $model as $options_group => $options ) {
        foreach( $options as $key => $val ) {
          if( array_key_exists( 'type', $val ) ) {
            $default_options[$key] = $val['default'];
          }
        }
      }
    }
    
    return $default_options;
  }

  /**
   * Clean up unsaved slide
   *
   * If user never saves the Slides, it remains an auto-draft. These
   * slides will be cleaned when user never click save.
   */
  public function cleanup_create( $slide_id ) {
    // // Get slides with an auto-draft status
    // $slidewizard = $this->get( $slide_id, 'auto-draft' );

    // if( !empty( $slidewizard ) ) {
    //   $this->delete( $slide_id );
    // }
    echo $slide_id;
  }


  /**
   * Get SlideWizard options
   * @param  integer $slide_id Slide ID
   * 
   * @return array
   */
  public function get_options( $slide_id, $themes, $source ) {
    $slide_options = (array) get_post_meta( $slide_id, "{$this->namespace}_options", true );
    $default_options = apply_filters( "{$this->namespace}_default_options", $this->default_options(), $themes, $source );
    $slide_options = array_merge( (array) $default_options, $slide_options );

    return $slide_options;
  }


  /**
   * Get Slidewizard Slides's sources
   *
   * Get sources used by Slidewizard slide
   */
  public function get_sources( $id ) {
    $sources = get_post_meta( $id, "{$this->namespace}_source" );
    
    return $sources;
  }


  /**
   * Get a file for the source
   * 
   * Allows for sources to hook into the baseurl filter and apply appropriate source
   * path properties to get a base url/path and return an array with the appropriate
   * URL and path data for the file requested within the source's folder.
   * 
   * @param string $filename File path relative to the source's root folder (ex. /images/thumbnail.png)
   * 
   * @return array
   */
  function get_source_file( $filename = "" ) {
      if( empty( $this->basedir ) )
        $this->basedir = apply_filters( "{$this->namespace}_get_source_file_basedir", "", $this->name );
      
      if( empty( $this->baseurl ) )
        $this->baseurl = apply_filters( "{$this->namespace}_get_source_file_baseurl", "", $this->name );
      
      $response = array(
        'dir' => untrailingslashit( $this->basedir ) . $filename,
        'url' => untrailingslashit( $this->baseurl ) . $filename
      );
      
      return $response;
  }


  /**
   * Check if this content source should process
   * 
   * Validates if the content source's name property is in the array of sources being
   * rendered in this SlideWizard.
   * 
   * @param array $sources Sources in this SlideWizard
   * 
   * @return boolean
   */
  protected final function is_valid( $sources ) {
    $valid = false;
    
    if( !is_array( $sources ) ) {
      $sources = array( $sources );
    }
    
    if( isset( $this->name ) ) {
      if( in_array( $this->name, $sources ) ) {
        $valid = true;
      }
    }
    
    return $valid;
  }


  /**
   * Get SlideWizard dimensions
   * 
   * @param  array   $slidewizard     The SlideWizard data
   * @param  boolean $override_width  Width override
   * @param  boolean $override_height Height override
   * 
   * @return array
   */
  function get_dimensions( $slidewizard, $override_width = false, $override_height = false ) {
    global $SlideWizard;

    $sizes = apply_filters( "{$this->namespace}_sizes", $SlideWizard->sizes, $slidewizard );

    // Set Width and height
    if( $slidewizard['options']['size'] != "custom" ) {
      $width = $sizes[$slidewizard['options']['size']]['width'];
      $height = $sizes[$slidewizard['options']['size']]['height'];
    } else {
      $width = $slidewizard['options']['width'];
      $height = $slidewizard['options']['height'];
    }

    // If parameter allow override width or height specified
    if( $override_width )
      $width = $override_width;
    if( $override_height )
      $height = $override_height;

    $outer_width= $width;
    $outer_height= $height;

    do_action_ref_array( "{$this->namespace}_dimensions", array( &$width, &$height, &$outer_width, &$outer_height, &$slidewizard ) );

    $dimensions = array(
      'width' => $width,
      'height' => $height,
      'outer_width' => $outer_width,
      'outer_height' => $outer_height
    );

    return $dimensions;
  }


  /**
   * Fetch slides
   * 
   */
  function fetch_slides( $slidewizard ) {
    // Hook for any SlideWizard type to control the slide output
    $slides = apply_filters( "{$this->namespace}_get_slides", array(), $slidewizard );

    // Shuffle the slides when randomize options is true
    if( $slidewizard['options']['randomize'] == "true" ) {
      shuffle( $slides );
    } else {
      // Sort the slides by time
      usort( $slides, array( &$this, '_sort_by_time' ) );
    }
    
    return $slides;
  }


  /**
   * Comparison function for sorting slides by time
   * 
   */
  private function _sort_by_time( $a, $b ) {
    $a_timestamp = is_numeric( $a['created_at'] ) ? (int) $a['created_at'] : strtotime( $a['created_at'] ) ;
    $b_timestamp = is_numeric( $b['created_at'] ) ? (int) $b['created_at'] : strtotime( $b['created_at'] ) ;
    
    return ( $a_timestamp < $b_timestamp );
  }

  /* Fix javascript data type */
  private function _type_fix( $val, $type ) {
    switch( $type ) {
      case "boolean":
        $val = (boolean) ( in_array( $val, array( "1", "true" ) ) ? true : false );
      break;
      
      case "float":
        $val = (float) floatval( $val );
      break;
      
      case "integer":
        $val = (integer) intval( $val );
      break;
      
      case "string":
      default:
        $val = (string) $val;
      break;
    }
    
    return $val;
  }


  /**
   * Render slide item
   *
   * @param array $slides Slides data
   * @param array $slidewizard SlideWizard data
   * 
   * @return string
   */
  public function render_slide_item( $slides, $slidewizard ) {
    $output = '';

    $slidewizard_dimensions = $this->get_dimensions( $slidewizard );
    extract($slidewizard_dimensions);

    // Inline styles for SlideWizard slide item
    $slide_styles_arr = array();
    $slide_styles_arr['width'] = $width . "px";
    $slide_styles_arr['height'] = $height . "px";
    $slide_styles_arr = apply_filters( "{$this->namespace}_slide_styles_arr", $slide_styles_arr, $slidewizard );
    $slide_styles_str = "";
    foreach( $slide_styles_arr as $property => $value ) {
      $slide_styles_str .= "$property:$value;";
    }

    foreach( $slides as $slide ) {

      $slide_classes = (array) apply_filters( "{$this->namespace}_slide_item_classes", $slide['classes'], $slidewizard );

      $output .= '<div class="slidewizard-slide-item '. implode( " ", $slide_classes ) .'" style="'. $slide_styles_str .'">';
      $output .= $slide['content'];
      $output .= '</div>';
    }

    return $output;
  }


  /**
   * Render the SlideWizard
   * 
   * @param integer $slide_id SlideWizard ID
   * 
   * @return string Rendered html
   */
  public function render( $slide_id, $preview = false ) {
    global $SlideWizard;

    $slidewizard = $this->get( $slide_id );

    // Return empty string if no ID provided
    if( empty( $slidewizard ) ) {
      return "";
    }

    $themes = $SlideWizard->Themes->get( $slidewizard['themes'] );

    // Class for SlideWizard wrapper
    $wrapper_classes = array(
      'slidewizard-wrapper'
    );
    $wrapper_classes[] = "themes-{$slidewizard['themes']}";
    $wrapper_classes[] = "slidewizard-control-{$slidewizard['options']['show_slide_controls']}";
    $wrapper_classes[] = "slidewizard-navigation-{$slidewizard['options']['navigation_position']}";
    $wrapper_classes[] = "slidewizard-direction-{$slidewizard['options']['direction']}";
    foreach( $slidewizard['source'] as $source ) {
      $wrapper_classes[] = "slidewizard-source-{$source}";
    }
    $wrapper_classes = apply_filters( "{$this->namespace}_wrapper_classes", $wrapper_classes, $slidewizard );
    
    // Remove duplicate from wrapper classes
    $wrapper_classes = array_unique( $wrapper_classes );

    $slidewizard_dimensions = $this->get_dimensions( $slidewizard );
    extract($slidewizard_dimensions);

    // Inline styles for SlideWizard wrapper element
    $wrapper_styles_arr = array();
    $wrapper_styles_arr['width'] = $outer_width . "px";
    $wrapper_styles_arr['height'] = $outer_height . "px";
    $wrapper_styles_arr = apply_filters( "{$this->namespace}_wrapper_styles_arr", $wrapper_styles_arr, $slidewizard );
    $wrapper_styles_str = "";
    foreach( $wrapper_styles_arr as $property => $value ) {
      $wrapper_styles_str .= "$property:$value;";
    }

    // Classes for SlideWizard main block
    $slidewizard_classes = array(
      'slidewizard'
    );
    $slidewizard_classes[] = "slidewizard-{$slidewizard['id']}";
    $slidewizard_classes[] = "slidewizard-size-{$slidewizard['options']['size']}";
    $slidewizard_classes = apply_filters( "{$this->namespace}_classes", $slidewizard_classes, $slidewizard );

    // Remove duplicate from slidewizard classes
    $slidewizard_classes = array_unique( $slidewizard_classes );

    $slidewizard_unique_id = "SlideWizard-" . $slidewizard['id'];

    $html = '<div id="'. $slidewizard_unique_id .'-wrapper" class="'. implode( " ", $wrapper_classes ) .'" style="'. $wrapper_styles_str .'">';
    $html .= apply_filters( "{$this->namespace}_render_slidewizard_before", "", $slidewizard );

    $html .= '<div id="'. $slidewizard_unique_id .'" class="'. implode( " ", $slidewizard_classes ) .'">';

    // Render slide item
    $slides = $this->fetch_slides( $slidewizard );
    $html .= $this->render_slide_item( $slides, $slidewizard );

    $html .= '</div>'; // End slidewizard

    // SlideWizard control
    if( $slidewizard['options']['show_slide_controls'] !== 'none' ) {
      $html .= '<a href="#" class="slidewizard-controls slidewizard-controls-next"><span>&rsaquo;</span></a>';
      $html .= '<a href="#" class="slidewizard-controls slidewizard-controls-prev"><span>&lsaquo;</span></a>';
    }

    // SlideWizard Navigation
    if( $slidewizard['options']['navigation_type'] !== 'none' ) {
      $navigation_classes = array();
      $navigation_classes[] = "slidewizard-nav-{$slidewizard['options']['navigation_type']}";
      $navigation_classes[] = "slidewizard-nav-{$slidewizard['options']['navigation_position']}";

      $html .= '<div class="slidewizard-navigation '. implode(' ', $navigation_classes) .'">';
      $html .= '</div>'; // End slidewizard-navigation
    }

    $html .= apply_filters( "{$this->namespace}_render_slidewizard_after", "", $slidewizard );
    $html .= '</div>'; // End slidewizard-wrapper

    // Filter the JavaScript options into an array for JSON output
    $javascript_options = array();
    foreach( $slidewizard['options'] as $opt_name => $opt_val ) {
      // make sure data type is correct
      if( in_array( $opt_name, array_keys( $this->javascript_datatype ) ) ) {
        if( is_string( $opt_val ) ) {
          $opt_val = $this->_type_fix( $opt_val, $this->javascript_datatype[$opt_name] );
        }
      }
      $javascript_options[$opt_name] = $opt_val;
    }
    $javascript_options['theme'] = $themes['slug'];

    // Fix javascript script options for size
    // if( $slidewizard['options']['size'] !== 'custom' ) {
      $javascript_options['width'] = $width;
      $javascript_options['height'] = $height;
    // }

    // Convert autoplay interval to miliseconds
    $javascript_options['autoplay_interval'] = $javascript_options['autoplay_interval'] * 1000;

    // Process the SlideWizard themes assets
    if( !isset( $SlideWizard->themes_included[$themes['slug']] ) ) {
      $SlideWizard->themes_included[$themes['slug']] = true;

      // Enqueue the SlideWizard Themes
      wp_enqueue_style( "{$this->namespace}-themes-{$themes['slug']}" );

      // Print javascript for SlideWizard themes
      if( isset( $themes['script_url'] ) && !empty( $themes['script_url'] ) ) {
        $SlideWizard->footer_scripts .= '<script type="text/javascript" src="' . $themes['script_url'] .'"></script>'; 
      }
    }

    // Add the JavaScript commands to initiate the SlideWizard to the footer_scripts 
    $SlideWizard->footer_scripts .= '<script type="text/javascript">jQuery("#'. $slidewizard_unique_id .'").slidewizard('. json_encode($javascript_options) .')</script>';

    $SlideWizard->footer_scripts .= apply_filters( "{$this->namespace}_footer_scripts", "", $slidewizard );

    return $html;
  }
}