<?php
class SlideWizardSource_500px extends Slides {
  var $label = "500px Photos";
  var $name = "500px";

  var $consumer_key = 'ftMVEAntn1dpMjw5DD8TM9Ixf0xErxNzGFeHItCh';
  var $api_url = 'https://api.500px.com/v1/';

  // Specific options for this source
  var $source_options = array(
    'Setup' => array(
      "photo_resources" => array(
        "label" => "Photo Resources",
        "type" => "select",
        "value" => array(
          "Photos" => "photos"
        ),
        "datatype" => "string",
        "default" => "photos"
      ),
      "feature" => array(
        "label" => "Feature",
        "type" => "select",
        "value" => array(
          "Return photos in Popular" => "popular",
          "Return photos in Upcoming" => "upcoming",
          "Return photos in Editors Choice" => "editors",
          "Return photos in Fresh Today" => "fresh_today",
          "Return photos in Fresh Yesterday" => "fresh_yesterday",
          "Return photos in Fresh This Week" => "fresh_week",
          "Return photos of a user" => "user",
        ),
        "description" => "Photo stream to be retrieved",
        "datatype" => "string",
        "default" => "fresh_today"
      ),
      "username" => array(
        "label" => "Username",
        "type" => "text",
        "description" => "Required if Photos Resources Feature is user",
        "datatype" => "string",
        "default" => ""
      ),
      "only" => array(
        "label" => "Category",
        "type" => "text",
        "description" => "Name of the category to return photos from. Case sensitive, optional",
        "datatype" => "string",
        "default" => ""
      ),
      "exclude" => array(
        "label" => "Exclude Category",
        "type" => "text",
        "description" => "Name of the category to exclude photos by. Case sensitive, optional, separate multiple values with a comma",
        "datatype" => "string",
        "default" => ""
      ),
      "sort" => array(
        "label" => "Sort photos",
        "type" => "select",
        "value" => array(
          "Sort by time of upload, most recent first" => "created_at",
          "Sort by rating, highest rated first" => "rating",
          "Sort by view count, most viewed first" => "times_viewed",
          "Sort by votes count, most voted first" => "votes_count",
          "Sort by favorites count, most favorited first" => "favorites_count",
          "Sort by comments count, most commented first" => "comments_count",
          "Sort by the original date of the image extracted from metadata, most recent first (might not be available for all images)" => "taken_at",
        )
      )
    )
  );

  /**
   * Add Hooks
   */
  function add_hooks() {
    add_action( "{$this->namespace}_form_content_source", array( &$this, "slidewizard_form_content_source" ), 10, 2 );
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

  /**
   * Hook into slidewizard options
   * 
   */
  function slidewizard_slide_options( $options, $slidewizard ) {
    if( $this->is_valid( $slidewizard['source'] ) ) {
      unset( $options['Content']['show_readmore'] );
      unset( $options['Content']['show_excerpt'] );
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
   * Get Slides item sourced from Youtube Videos
   * 
   * @param array $slidewizard SlideWizard data
   * 
   * @return array
   */
  function get_slides_item( $slidewizard ) {
    $slides = array();

    // Get 500px options
    $args = array( 
      'rpp' => $slidewizard['options']['number_of_slides'],
      'image_size' => '4'
    );
    foreach( $this->source_options as $section ) {
      foreach( $section as $option_name => $option ) {
        if( $slidewizard['options'][ $option_name ] != '' && $option_name != 'photo_resources' ) {
          $args[ $option_name ] = $slidewizard['options'][ $option_name ];
        }
      }
    }

    $photos = $this->api_wrapper( $slidewizard['options']['photo_resources'], $args, $slidewizard );

    if( $photos['error'] == 'false' ) {
      foreach( $photos['data']->photos as $index => $photo ) {
        $slides[ $index ] = array(
          'id' => $photo->id,
          'title' => $photo->name,
          'image' => $photo->image_url,
          'permalink' => 'http://500px.com/photo/' . $photo->id,
          'author_id' => $photo->user->id,
          'author_name' => "{$photo->user->firstname} {$photo->user->lastname}",
          'author_username' => $photo->user->username,
          'author_url' => 'http://500px.com/' . $photo->user->username,
          'author_avatar' => '<img src="'. $photo->user->userpic_url .'">',
          'content' => $this->build_500px_content( $photo, $slidewizard ),
          'created_at' => strtotime( $photo->created_at ),
          'local_created_at' => $photo->created_at
        );
      }
    }

    return $slides;
  }

  function build_500px_content( $photo, $slidewizard ) {
    $content = '<span class="px500-votes-count">'. $photo->votes_count .'</span>';
    $content .= '<span class="px500-fave-count">'. $photo->favorites_count .'</span>';
    $content .= '<span class="px500-comment-count">'. $photo->comments_count .'</span>';

    return $content;
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

    if( !$slides_item )
      return $slides;

    // Loop through all slides item to build slides array
    foreach( $slides_item as $slide_item ) {
      $slide = array(
        'source' => $this->name,
        'title' => $slide_item['title'],
        'created_at' => $slide_item['created_at'],
        'classes' => array( 'no-excerpt' )
      );

      $slide = array_merge( $this->slide_item_prop, $slide );

      // Link target
      $slide_item['target'] = $slidewizard['options']['open_link_in'];

      $slide['content'] = $SlideWizard->Themes->process_template( $slide_item, $slidewizard );

      $slides[] = $slide;
    }

    return $slides;
  }

  public function api_wrapper( $endpoints, $params = array(), $slidewizard = array() ) {
    $params = wp_parse_args( $params, array( 'consumer_key' => $this->consumer_key ) );
    $url = "{$this->api_url}{$endpoints}";
    $url = add_query_arg( $params, $url );
    $return = array( 'error' => 'true' );

    $cache_duration = $slidewizard['options']['cache_duration'];
    $cache_key = "500px_api_{$endpoints}_{$cache_duration}_" . md5( http_build_query( $params ) );

    $return = slidewizard_cache_read( $cache_key );

    // If cache not exists
    if( !$return ) {
      $return = array();
      $response = wp_remote_get( $url, array(
        'timeout' => 20,
        'sslverify' => false
      ) );

      if( !is_wp_error( $response ) ) {
        $return['error'] = 'false';
        $return['data'] = json_decode( wp_remote_retrieve_body( $response ) );

        slidewizard_cache_write( $cache_key, $return, $cache_duration );
      }
    }

    return $return;
  }
}