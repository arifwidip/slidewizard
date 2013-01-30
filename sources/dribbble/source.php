<?php
class SlideWizardSource_Dribbble extends Slides {
  var $label = "Dribbble Shot";
  var $name = "dribbble";
  var $taxonomies = array( 'dribbble' );

  // Specific options for this source
  var $source_options = array(
    'Setup' => array(
      "username" => array(
        "label" => "Username",
        "type" => "text",
        "datatype" => "string",
        "default" => ""
      )
    )
  );

  function add_hooks() {
    add_action( "{$this->namespace}_form_content_source", array( &$this, "slidewizard_form_content_source" ), 10, 2 );
  }

  /**
   * Hook into slidewizard options
   * 
   */
  function slidewizard_slide_options( $options, $slidewizard ) {
    if( $this->is_valid( $slidewizard['source'] ) ) {
      // unset( $options['Content']['show_excerpt'] );
      unset( $options['Content']['show_readmore'] );
      unset( $options['Content']['excerpt_length_with_media'] );
      unset( $options['Content']['excerpt_length_no_media'] );
    }

    return $options;
  }


  /**
   * Hook into slidewizard_get_source_file_basedir filter
   * 
   * Modifies the source's basedir value for relative file referencing
   * 
   * @param string $basedir The defined base directory
   * @param string $source_slug The slug of the source being requested
   * 
   * @return string
   */
  function slidewizard_get_source_file_basedir( $basedir, $source_slug ) {    
    if( $this->is_valid( $source_slug ) ) {
      $basedir = dirname( __FILE__ );
    }
    
    return $basedir;
  }


  /**
   * Hook into slidewizard_get_source_file_baseurl filter
   * 
   * Modifies the source's basedir value for relative file referencing
   * 
   * @param string $baseurl The defined base directory
   * @param string $source_slug The slug of the source being requested
   * 
   * @return string
   */
  function slidewizard_get_source_file_baseurl( $baseurl, $source_slug ) {
    if( $this->is_valid( $source_slug ) ) {
      $baseurl = SLIDEWIZARD_PLUGIN_URL . '/sources/' . basename( dirname( __FILE__ ) );
    }
  
    return $baseurl;
  }


  /**
   * Build dribbble content
   * 
   */
  function build_dribbble_content( $shot, $user, $slidewizard ) {
    $content = '<span class="dribbble-like-count">'. $shot->likes_count .'</span>';
    $content .= '<a class="dribbble-rebound-count" href="'. $shot->url .'/rebounds">'. $shot->rebounds_count .'</a>';
    $content .= '<span class="dribbble-views-count">'. $shot->views_count .'</span>';

    return $content;
  }


  /**
   * Get Slides item sourced from Dribbble shots
   * 
   * @param array $slidewizard SlideWizard data
   * 
   * @return array
   */
  function get_slides_item( $slidewizard ) {
    $username = $slidewizard['options']['username'];
    $slide_id = $slidewizard['id'];

    if( isset( $username ) && !empty( $username ) ) {
      $feed_url = 'http://api.dribbble.com/players/'. urlencode($username) .'/shots?per_page=' . urlencode($slidewizard['options']['number_of_slides']);
    } else {
      $feed_url = 'http://api.dribbble.com/shots?per_page=' . urlencode($slidewizard['options']['number_of_slides']);
    }

    // Create cache key
    $cache_key = $slide_id . $feed_url . $slidewizard['options']['cache_duration'] . $this->name;

    $dribbble_shots = slidewizard_cache_read( $cache_key );

    // If cache doesn't exists
    if( !$dribbble_shots ) {
      $dribbble_shots = array();

      $response = wp_remote_get( $feed_url );
      if( !is_wp_error( $response ) ) {
        $response_json = json_decode( $response['body'] );

        if( isset( $response_json->shots ) ) {
          foreach( $response_json->shots as $index => $result ) {
            $dribbble_shots[ $index ] = array(
              'id' => $result->id,
              'title' => $result->title,
              'permalink' => $result->short_url,
              'image' => $result->image_url,
              'author_username' => $result->player->username,
              'author_name' => $result->player->name,
              'author_url' => $result->player->url,
              'author_email' => false,
              'author_avatar' => '<img src="' . $result->player->avatar_url . '">',
              'created_at' => strtotime( $result->created_at ),
              'local_created_at' => $result->created_at,
              'content' => $this->build_dribbble_content( $result, $result->player, $slidewizard )
            );
          }
        }
      } else {
        return false;
      }

      // Write the cache
      slidewizard_cache_write( $cache_key, $dribbble_shots, $slidewizard['options']['cache_duration'] );
    }
    
    return $dribbble_shots;
  }


  /**
   * Get Slides for this type of source
   * 
   * @param array $slides Slides array
   * @param array $slidewizard SlideWizard data
   * 
   * @return array
   */
  function slidewizard_get_slides( $slides, $slidewizard ) {
    global $SlideWizard;

    // Fail silently if this source type
    if( !$this->is_valid( $slidewizard['source'] ) ) {
      return $slides;
    }

    // Get Slides item
    $slides_item = $this->get_slides_item( $slidewizard );

    // Loop through all slides item to build slides array
    foreach( $slides_item as $slide_item) {
      $slide = array(
        'source' => $this->name,
        'title' => $slide_item['title'],
        'created_at' => $slide_item['created_at'],
        'classes' => array( 'no-excerpt' )
      );

      $slide = array_merge( $this->slide_item_prop, $slide );

      // Check if post has image
      $has_image = !empty( $slide_item['image'] );

      if( $has_image ) {
        $slide['classes'][] = "has-image";
      } else {
        $slide['classes'][] = "no-image";
      }

      if( !empty( $slide_item['title'] ) ) {
        $slide['classes'][] = "has-title";
      } else {
        $slide['classes'][] = "no-title";
      }

      // Link target
      $slide_item['target'] = $slidewizard['options']['open_link_in'];

      $slide['content'] = $SlideWizard->Themes->process_template( $slide_item, $slidewizard );

      $slides[] = $slide;
    }

    return $slides;
  }


  /**
   * Hook for rendering options for this source
   * 
   * @param array $slidewizard SlideWizard object
   * @param array $source Source name
   * 
   */
  function slidewizard_form_content_source( $slidewizard, $source ) {
    // Fail silently if the SlideWizard is not this source
    if( !$this->is_valid( $source ) ) {
      return false;
    }

    $namespace = $this->namespace;

    include( dirname( __FILE__ ) . '/views/options.php' );
  }
}