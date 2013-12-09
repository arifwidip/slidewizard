<?php
class SlideWizardSource_Youtube extends Slides {
  var $label = "Youtube Videos";
  var $name = "youtube";
  
  var $browser_key = 'AIzaSyCG2fxqb_iA5sfZQcYvSF8j4Uc7UWdPY0I';
  var $server_key = 'AIzaSyCBkbf66aT6R2tEULE-EFjIM_8i1ZSckWA';
  var $youtube_api_url = 'https://www.googleapis.com/youtube/v3/';

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
   * Get Slides item sourced from Youtube Videos
   * 
   * @param array $slidewizard SlideWizard data
   * 
   * @return array
   */
  function get_slides_item( $slidewizard ) {
    $username = $slidewizard['options']['username'];
    $user_channel = $this->youtube_api( 'channels', 'id,snippet,contentDetails', array( 'forUsername' => $username ) );
    $slides = array();

    if( $user_channel['error'] == 'false' ) {
      $user_channel_id = $user_channel['data']->items[0]->contentDetails->relatedPlaylists->uploads;
      $user_uploaded_videos = $this->youtube_api( 'playlistItems', 'id,snippet,contentDetails', array( 
        'playlistId' => $user_channel_id,
        'maxResults' => $slidewizard['options']['number_of_slides']
      ) );
      
      // Get User Data
      $user_data = array(
        'id' => $user_channel['data']->items[0]->id,
        'name' => $user_channel['data']->items[0]->snippet->title,
        'description' => $user_channel['data']->items[0]->snippet->description,
        'thumbnails' => $user_channel['data']->items[0]->snippet->thumbnails,
        'permalink' => 'http://www.youtube.com/user/' . $username,
        'gplus_userid' => $user_channel['data']->items[0]->contentDetails->googlePlusUserId
      );

      // Generate Video Data
      if( $user_uploaded_videos['error'] == 'false' ) {
        foreach( $user_uploaded_videos['data']->items as $index => $video ) {
          $video_embed_url = add_query_arg( array(
            'wmode' => 'opaque',
            'showinfo' => '0', 
            'autohide' => '1',
            'rel' => '0', 
            'disablekb' => '1',
            'cc_load_policy' => '0',
            'iv_load_policy' => '3',
            'modestbranding' => '1',
            'enablejsapi' => '1'
          ), '//www.youtube.com/embed/' . $video->snippet->resourceId->videoId );

          $slides[ $index ] = array(
            'id' => $video->id,
            'video_id' => $video->snippet->resourceId->videoId,
            'video_embed' => '<iframe id="'. $video->snippet->resourceId->videoId .'" class="slidewizard-youtube-video" frameborder="0" allowfullscreen="1" width="100%" height="100%" src="'. $video_embed_url .'" webkitallowfullscreen="true" mozallowfullscreen="true"></iframe>',
            'title' => $video->snippet->title,
            'thumbnails' => $video->snippet->thumbnails,
            'image' => $video->snippet->thumbnails->high->url,
            'permalink' => 'http://www.youtube.com/watch?v=' . $video->snippet->resourceId->videoId,
            'author_data' => $user_data,
            'author_name' => $user_data['name'],
            'author_url' => $user_data['permalink'],
            'author_avatar' => '<img src="' . $user_data['thumbnails']->high->url . '">',
            'content' => $video->snippet->description,
            'created_at' => strtotime( $video->snippet->publishedAt ),
            'local_created_at' => $video->snippet->publishedAt
          );
        }
      }
    }

    // print_r($slidewizard);
    // $slides = array();
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
    foreach( $slides_item as $slide_item) {
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

  /**
   * Get Response from YouTube API
   * 
   * @param  string $type   API Type, see https://developers.google.com/youtube/v3/docs
   * @param  string $part   Part parameter for API, use comma separated
   * @param  array  $params Extra parameter
   * @return object         API Result
   */
  public function youtube_api( $type = '', $part = '', $params = array() ) {
    $url = "{$this->youtube_api_url}{$type}?part={$part}&key={$this->server_key}";
    $url = add_query_arg( $params, $url );
    $return = array(
      'error' => 'true'
    );

    $cache_duration = $slidewizard['options']['cache_duration'];
    $key_params = http_build_query( $params );
    $cache_key = "youtube_api_{$type}_". str_replace(',', '', $part) . "_{$key_params}_{$cache_duration}";

    $return = slidewizard_cache_read( $cache_key );

    // If cache not exists
    if( !$return ) {
      $return = array();
      $response = wp_remote_get( $url, array(
        'timeout' => 20,
        'sslverify' => false 
      ));

      if( !is_wp_error( $response ) ) {
        $return['error'] = 'false';
        $return['data'] = json_decode( wp_remote_retrieve_body( $response ) );
      }

      slidewizard_cache_write( $cache_key, $return, $cache_duration );
    }

    return $return;
  }

}