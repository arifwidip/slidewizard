<?php
class SlideWizardSource_Instagram extends Slides {
  var $label = "Instagram Photo";
  var $name = "instagram";

  var $auth_url = "https://instagram.com/oauth/authorize/";
  var $client_id = "a0392b4db0da46f49239530d65dab923";

  // You can find the script for instagram auth at sources/instagram/instagram-auth.php
  var $redirect_uri = "http://colorlabsproject.com/instagram-auth.php";

  var $default_userid = "53993811"; // If user id invalid, use colorlabs user id

  var $source_options = array(
    "Setup" => array(
      "instagram_access_token" => array(
        "label" => "Access Token",
        "type" => "text",
        "hide_field" => true,
        "datatype" => "string"
      ),
      "instagram_user_name" => array(
        "label" => "Username",
        "type" => "text",
        "datatype" => "string",
        "default" => ""
      )
    )
  );

  function add_hooks() {
    add_action( "{$this->namespace}_form_content_source", array( &$this, "slidewizard_form_content_source" ), 10, 2 );
    add_action( "wp_ajax_{$this->namespace}_get-instagram-token", array( &$this, 'get_instagram_token' ) );
  }


  /**
   * Ajax Action for receiving instagram access token
   */
  function get_instagram_token() {
    $referrer_url = wp_get_referer();
    
    // Fail silently if rererrer url is false
    if( !$referrer_url ) {
      return false;
    }

    $forward_url = base64_encode( $referrer_url );

    $api_url = "{$this->auth_url}?client_id={$this->client_id}";
    $api_url .= "&redirect_uri={$this->redirect_uri}?forward_url={$forward_url}";
    $api_url .= "&response_type=code";
    
    wp_redirect( $api_url );
    die;
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
   * Get Slides item sourced from Instagram Photo
   * 
   * @param array $slidewizard SlideWizard data
   * 
   * @return array
   */
  function get_slides_item( $slidewizard ) {
    $username = $slidewizard['options']['instagram_user_name'];
    $slide_id = $slidewizard['id'];
    $api_url = "https://api.instagram.com/v1";

    if( !isset( $slidewizard['options']['instagram_access_token'] ) ) {
      $token = get_option( "{$this->namespace}_last_saved_instagram_token" );
    } else {
      $token = $slidewizard['options']['instagram_access_token'];
    }

    // Make sure token is exists
    if( isset( $token ) ) {
      if( !isset( $username ) || empty( $username ) ) {
        $feed_url = "{$api_url}/users/self/media/recent";
      } else {
        $user_id = $this->get_instagram_user_id( $token, $username );
        $feed_url = "{$api_url}/users/{$user_id}/media/recent";
      }
      $feed_url .= "?access_token={$token}&count={$slidewizard['options']['number_of_slides']}";
      
      $cache_key = $slide_id . $feed_url . $slidewizard['options']['cache_duration'] . $this->name;

      $instagram_photos = slidewizard_cache_read( $cache_key );

      // If cache doesn't exist
      if( !$instagram_photos ) {
        $instagram_photos = array();

        $response = wp_remote_get( $feed_url );
        if( !is_wp_error( $response ) ) {
          $response_json = json_decode( $response['body'] );

          if( isset( $response_json->data ) ) {
            foreach( $response_json->data as $index => $result ) {
              if( is_object( $result ) ) {
                $instagram_photos[ $index ] = array(
                  'id' => $result->id,
                  'title' => $result->caption->text,
                  'permalink' => $result->link,
                  'image' => $result->images->standard_resolution->url,
                  'author_username' => $result->user->username,
                  'author_name' => $result->user->full_name,
                  'author_url' => "http://instagram.com/" . $result->user->username,
                  'author_email' => false,
                  'author_avatar' => '<img src="' . $result->user->profile_picture . '">',
                  'content' => false,
                  'excerpt' => false,
                  'created_at' => $result->created_time
                );
              }
            }
          }
        } else {
          return false;
        }
      }

      // Write result to the cache
      slidewizard_cache_write( $cache_key, $instagram_photos, $slidewizard['options']['cache_duration'] );
    }

    return $instagram_photos;
  }


  /**
   * Get Instagram User ID
   * 
   * @param string $token Instagram access token
   * @param string $username Instagram username
   * 
   * @return string Instagram User ID
   */
  function get_instagram_user_id( $token, $username ) {
    $api_url = "https://api.instagram.com/v1/users/search?access_token={$token}&q={$username}";

    $response = wp_remote_get( $api_url );
    if( !is_wp_error( $response ) ) {
      $response_json = json_decode( $response['body'] );
      return $response_json->data[0]->id;
    } else {
      return $this->default_userid;
    }
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

    if( !$this->is_valid( $slidewizard['source'] ) ) {
      return $slides;
    }

    // Get Slides item
    $slides_item = $this->get_slides_item( $slidewizard );

    if( isset( $slides_item ) ) {
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

    // If Instagram token not exists
    if( !isset($slidewizard['options']['instagram_access_token']) ) {
      if( isset( $_REQUEST['token'] ) ) {
        $this->source_options['Setup']['instagram_access_token']['default'] = $_REQUEST['token'];

        // Save instagram token for later use
        update_option( "{$namespace}_last_saved_instagram_token", $_REQUEST['token']);
      }
    }

    // If last saved key exists
    if( get_option( "{$namespace}_last_saved_instagram_token" ) ) {
      $this->source_options['Setup']['instagram_access_token']['default'] = get_option( "{$namespace}_last_saved_instagram_token" );
    }

    $this->source_options['Setup']['instagram_access_token']['description'] = 'Instagram API need access token to fetch your photo. Get your access token <a href="'. admin_url("admin-ajax.php?action={$namespace}_get-instagram-token") .'">here</a>';

    include( dirname( __FILE__ ) . '/views/options.php' );
  }

}