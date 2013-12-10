<?php
class SlideWizardSource_Vimeo extends Slides {
  var $label = "Vimeo Videos";
  var $name = "vimeo";

  var $vimeo_api_url = 'http://vimeo.com/api/v2/';

  // Specific options for this source
  var $source_options = array(
    'Setup' => array(
      "username" => array(
        "label" => "Username",
        "type" => "text",
        "datatype" => "string",
        "default" => "colorlabsproject"
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

      // Remove unneeded options
      unset( $options['Content']['show_readmore'] );
      unset( $options['Content']['excerpt_length_with_media'] );
      unset( $options['Content']['excerpt_length_no_media'] );

      // Set max number of slides
      $options['Setup']['number_of_slides']['options']['max'] = 20;
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
    $username = $slidewizard['options']['username'];
    $user_videos = $this->vimeo_simple_api( $username );
    $slides = array();

    if( $user_videos['error'] == 'false' ) {
      foreach( $user_videos['data'] as $index => $video ) {
        $video_embed_url = add_query_arg( array(
          'wmode' => 'opaque',
          'badge' => '0',
          'byline' => '0',
          'player_id' => $video->id,
          'title' => '0',
          'portrait' => '0',
          'api' => '1'
        ), '//player.vimeo.com/video/' . $video->id );

        $slides[ $index ] = array(
          'id' => $video->id,
          'title' => $video->title,
          'image' => $video->thumbnail_large,
          'video_embed' => '<iframe id="'. $video->id .'" src="'. $video_embed_url .'" width="100%" height="100%" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>',
          'permalink' => $video->url,
          'author_id' => $video->user_id,
          'author_name' => $video->user_name,
          'author_url' => $video->user_url,
          'author_avatar' => '<img src="'.$video->user_portrait_large.'">',
          'content' => $video->description,
          'created_at' => strtotime( $video->upload_date ),
          'local_created_at' => $video->upload_date
        );

        if( ($index + 1) == $slidewizard['options']['number_of_slides'] ) break;
      }
    }

    return $slides;
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
      $slide_item['content'] = slidewizard_truncate_text( $slide_item['content'], 150 );

      $slide['content'] = $SlideWizard->Themes->process_template( $slide_item, $slidewizard );

      $slides[] = $slide;
    }

    return $slides;
  }

  /**
   * Vimeo Simple API wrapper
   * 
   * @param  string $username Vimeo username
   * @param  string $request  The data you want
   * @param  string $output   Output type. JSON, PHP, and XML
   * @return [type]           [description]
   */
  public function vimeo_simple_api( $username, $request = "videos", $output = 'json' ) {
    $url = "{$this->vimeo_api_url}{$username}/{$request}.{$output}";
    $return = array( 'error' => 'true' );

    $cache_duration = $slidewizard['options']['cache_duration'];
    $cache_key = "vimeo_api_{$username}_{$request}_{$output}_{$cache_duration}";

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