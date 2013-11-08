<?php
/**
 * Primary class for managing SlideWizard Themes
 * 
 */

class SlideWizardThemes {

  var $namespace = "slidewizard";

  // Meta values
  var $themes_meta = array(
    "name" => "",
    // "uri" => "",
    "sources" => array(),
    "description" => "",
    "version" => ""
  );


  /**
   * Indents a flat JSON string to make it more human-readable.
   * 
   * Script courtesy of recursive-design.com. Original post:
   * http://recursive-design.com/blog/2008/03/11/format-json-with-php/
   *
   * @param string $json The original JSON string to process.
   *
   * @return string Indented version of the original JSON string.
   */
  private function _indent_json( $json ) {
    $result      = '';
    $pos         = 0;
    $strLen      = strlen($json);
    $indentStr   = '    ';
    $newLine     = "\n";
    $prevChar    = '';
    $outOfQuotes = true;
    
    for ($i=0; $i<=$strLen; $i++) {
    
      // Grab the next character in the string.
      $char = substr($json, $i, 1);
  
      // Are we inside a quoted string?
      if ($char == '"' && $prevChar != '\\') {
        $outOfQuotes = !$outOfQuotes;
      
      // If this character is the end of an element, 
      // output a new line and indent the next line.
      } else if(($char == '}' || $char == ']') && $outOfQuotes) {
        $result .= $newLine;
        $pos --;
        for ($j=0; $j<$pos; $j++) {
          $result .= $indentStr;
        }
      }
      
      // Add the character to the result string.
      $result .= $char;
  
      // If the last character was the beginning of an element, 
      // output a new line and indent the next line.
      if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
        $result .= $newLine;
        if ($char == '{' || $char == '[') {
          $pos ++;
        }
        
        for ($j = 0; $j < $pos; $j++) {
          $result .= $indentStr;
        }
      }
      
      $prevChar = $char;
    }
    
    // Add spacing after colons between key/value pairs in JSON object
    $result = preg_replace( "/\":(\"|\{|\[|\d)/", '": $1', $result );
    
    return $result;
  }


  /**
   * Load all themes
   *
   * Load all themes if no slug specified
   * 
   * @param string $slug Theme name
   * @return array 
   */
  function get( $slug = "" ) {
    $themes = array();
    $folders = !empty( $slug ) ? $slug : "*";

    // Get SlideWizard themes metadata
    $themes_files = (array) glob( SLIDEWIZARD_PLUGIN_DIR . '/themes/' . $folders . '/themes.json' );
    foreach( (array) $themes_files as $theme_file ) {
      if( is_readable( $theme_file ) ) {
        $key = basename( dirname( $theme_file ) );
        $theme_meta = $this->get_meta( $theme_file );
        $themes[$key] = $theme_meta;
      }
    }

    if( !empty( $slug ) ) {
      $themes = reset( $themes );
    }

    return $themes;
  }


  /**
   * Get SlideWizard Themes metadata
   *
   */
  function get_meta( $filename ) {
    global $SlideWizard;

    $themes_data = file_get_contents( $filename );
    $themes_folder = dirname( $filename );
    $themes_slug = basename( $themes_folder );

    // Set default values for themes meta
    $themes_meta = $this->themes_meta;
    // Get JSON data from themes
    $themes_file_meta = json_decode( $themes_data, true );
    // Merge with default meta
    $themes_meta = array_merge( $themes_meta, $themes_file_meta );

    // Get the themes' base url
    $themes_url = untrailingslashit( WP_PLUGIN_URL ) . str_replace(str_replace("\\","/",WP_PLUGIN_DIR), "", str_replace("\\","/",$themes_folder));

    // Adjust URL for SSL if we are running the current page through SSL
    if( is_ssl() ) $themes_url = str_replace( "http://", "https://", $themes_url );

    $themes = array(
      'url' => $themes_url . '/themes.css',
      'thumbnail' => $themes_url . '/thumbnail.png',
      'thumbnail-large' => $themes_url . '/thumbnail-large.png',
      'slug' => $themes_slug,
      'templates' => array(
        'default' => $themes_folder . '/template.php'
      ),
      'meta' => $themes_meta,
      'files' => array(
        'meta' => $themes_folder . '/themes.json',
        'css' => $themes_folder . '/themes.css'
      )
    );

    // Themes Javascript
    if( file_exists( $themes_folder . '/themes.js' ) ) {
      $themes['script_url'] = $themes_url . '/themes.js';
      $themes['files']['js'] = $themes_url . '/themes.js';
    }

    // Themes admin Javascript
    if( file_exists( $themes_folder . '/themes.admin.js' ) ) {
      $themes['admin_script_url'] = $themes_url . '/themes.admin.js';
      $themes['files']['admin_js'] = $themes_url . '/themes.admin.js';
    }

    // Themes type
    foreach( array_keys( $SlideWizard->Slides->slide_types ) as $slide_type ) {
      $template_file = $themes_folder . '/template.' . $slide_type . '.php';
      if( file_exists( $template_file ) ) {
        $themes['templates'][$slide_type] = $template_file;
      }
    }
    
    // Loop through sources 
    foreach( $themes['meta']['sources'] as $source ) {
      $template_file = $themes_folder . '/template.source.' . $source . '.php';
      if( file_exists( $template_file ) ) {
        $themes['templates'][$source] = $template_file;
      }
    }

    return $themes;
  }


  /**
   * Process Template
   */
  function process_template( $slide_item = array(), $slidewizard ) {
    global $SlideWizard;

    $themes = $this->get( $slidewizard['themes'] );
    $source = $slidewizard['source'][0];
    $slides = new Slides();

    $slide_item['dimensions'] = $slides->get_dimensions( $slidewizard );

    if( isset( $slide_item['created_at'] ) && !empty( $slide_item['created_at'] ) ) {
      $slide_item['created_at'] = is_numeric( $slide_item['created_at'] ) ? $slide_item['created_at'] : strtotime( $slide_item['created_at'] );
      $date_format = isset( $slidewizard['options']['date_format'] ) ? $slidewizard['options']['date_format'] : "none";
      switch( $date_format ) {
        case "none":
          $slide_item['created_at'] = "";
        break;

        case "timeago":
          $slide_item['created_at'] = human_time_diff( $slide_item['created_at'], current_time( 'timestamp', 1 ) ) . " ago";
        break;

        case "human-readable":
          $slide_item['created_at'] = date( "F j, Y", $slide_item['created_at'] );
        break;
      }
    }

    // Make all keyed node values accessible as variables for the template
    extract( $slide_item );

    // Check for slide type template override
    $template = $themes['templates']['default'];

    // Check for source type template override
    if( isset( $themes['templates'][$source] ) ) {
      $template = $themes['templates'][$source];
    }
    
    ob_start();
      // Load the template to be processed as PHP
      if( file_exists( $template ) ){
        include( $template );
      }
        
      // Grab the output buffer content for rendered template output
      $html = ob_get_contents();
    ob_end_clean();

    return $html;
  }


  /**
   * Get images from raw html
   * 
   * @param  string $html_string HTML raw input
   * @return array
   */
  function get_images_from_html( $html_string = "" ) {
    $html_string = preg_replace( "/([\n\r]+)/", "", $html_string );
    
    $image_strs = array();
    preg_match_all( '/<img(\s*([a-zA-Z]+)\=\"([a-zA-Z0-9\/\#\&\=\|\-_\+\%\!\?\:\;\.\(\)\~\s\,]*)\")+\s*\/?>/', $html_string, $image_strs );
    
    $images_all = array();
    if( isset( $image_strs[0] ) && !empty( $image_strs[0] ) ) {
      foreach( (array) $image_strs[0] as $image_str ) {
        $image_attr = array();
        preg_match_all( '/([a-zA-Z]+)\=\"([a-zA-Z0-9\/\#\&\=\|\-_\+\%\!\?\:\;\.\(\)\~\s\,]*)\"/', $image_str, $image_attr );
        if( in_array( 'src', $image_attr[1] ) ) {
          $images_all[] = array_combine( $image_attr[1], $image_attr[2] );
        }
      }
    }
    
    $images = array();
    if( !empty( $images_all ) ) {
      foreach( $images_all as $image ) {
        // Filter out advertisements and tracking beacons
        if( $this->test_image_for_ads_and_tracking( $image['src'] ) ) {
          $images[] = $image['src'];
        }
      }
    }
    
    return $images;
  }


  /**
   * Parses image URL and returns false if it's a banned image 
   * 
   * @param string $image an image URL
   * 
   * @return mixed false if is an advertisment/banned and the image strign if not
   */
  function test_image_for_ads_and_tracking( $input_image = "" ) {
    // Filter out advertisements and tracking beacons
    if( preg_match( '/(tweetmeme|stats|share-buttons|advertisement|feedburner|commindo|valueclickmedia|imediaconnection|adify|traffiq|premiumnetwork|advertisingz|gayadnetwork|vantageous|networkadvertising|advertising|digitalpoint|viraladnetwork|decknetwork|burstmedia|doubleclick).|feeds\.[a-zA-Z0-9\-_]+\.com\/~ff|wp\-digg\-this|feeds\.wordpress\.com|\/media\/post_label_source|ads\.pheedo\.com/i', $input_image ) )
        return false;
    
    return $input_image;
  }

}