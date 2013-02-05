<?php
class SlideWizardSource_Twitter extends Slides {
  var $label = "Twitter Timeline";
  var $name = "twitter";
  var $taxonomies = array( 'twitter' );

  // Specific options for this source
  var $source_options = array(
    'Setup' => array(
      "tweets_source" => array(
        "label" => "Tweets from",
        "type" => "radio",
        "datatype" => "string",
        "value" => array(
          "Username" => "user",
          "Search term" => "search_term"
        ),
        "default" => "user"
      ),
      "username" => array(
        "label" => "Username",
        "type" => "text",
        "datatype" => "string",
        "default" => "colorlabs"
      ),
      "search_term" => array(
        "label" => "Search Term",
        "type" => "text",
        "datatype" => "string",
        "default" => "#wordpress"
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
      unset( $options['Content']['link_title'] );
      unset( $options['Content']['show_excerpt'] );
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
   * Fetch twitter image
   *
   * Return media entities if exists, otherwise return the user 
   * background image.
   * 
   * @param object $tweet Twitter tweet object
   * @param array $slidewizard slidewizard data
   * @param object $user twitter user object
   * 
   * @return string
   */
  function try_fetching_tweet_image( $tweet, $slidewizard, $user = null ) {
    $entities = $tweet->entities;

    // Image extraction
    if( !empty( $entities->media ) ) {
      $first_item = reset( $entities->media );
      return $first_item->media_url;
    } elseif( !empty( $entities->urls ) ) {
      /**
       * If the URL extension matches the mime types we're
       * looking for then we can try to fetch it.
       */
      foreach( $entities->urls as $url ) {
        if( preg_match( '/\.(jpg|png|gif)$/i', $url->expanded_url ) ){
          return $url->expanded_url;
        }
        if( preg_match( '/yfrog\.com/i', $url->expanded_url ) ){
          return $url->expanded_url . ':medium';
        }
        if( preg_match( '/twitpic\.com\/(.*)$/i', $url->expanded_url, $twitpic ) ){
          return 'http://twitpic.com/show/thumb/' . $twitpic[1] ;
        }
        // Add YouTube
        if( preg_match( '/youtube\.com[^v]+v.(.{11}).*/i', $url->expanded_url, $youtube_matches)){
          return 'http://img.youtube.com/vi/' . $youtube_matches[1] . '/0.jpg';
        }elseif( preg_match( '/youtube.com\/user\/(.*)\/(.*)$/i', $url->expanded_url, $youtube_matches)){
          return 'http://img.youtube.com/vi/' . $youtube_matches[2] . '/0.jpg';
        }elseif( preg_match( '/youtu.be\/(.*)$/i', $url->expanded_url, $youtube_matches)){
          return 'http://img.youtube.com/vi/' . $youtube_matches[1] . '/0.jpg';
        }
      }
    }

    // Try background image
    if( isset( $user->profile_background_image_url ) && !empty( $user->profile_background_image_url ) ) {
      return $user->profile_background_image_url;
    }

    return '';
  }


  /**
   * Linkify Twitter Text
   * 
   * @param string s Tweet
   * 
   * @return string a Tweet with the links, mentions and hashtags wrapped in <a> tags 
   */
  function linkify_twitter_text($tweet){
    $url_regex = '/((https?|ftp|gopher|telnet|file|notes|ms-help):((\/\/)|(\\\\))+[\w\d:#@%\/\;$()~_?\+-=\\\.&]*)/';
    $tweet = preg_replace($url_regex, '<a href="$1" target="_blank">'. "$1" .'</a>', $tweet);
    $tweet = preg_replace( array(
      '/\@([a-zA-Z0-9_]+)/',    # Twitter Usernames
      '/\#([a-zA-Z0-9_]+)/'    # Hash Tags
    ), array(
      '<a href="http://twitter.com/$1" target="_blank">@$1</a>',
      '<a href="http://twitter.com/search?q=%23$1" target="_blank">#$1</a>'
    ), $tweet );
    
    return $tweet;
  }


  /**
   * Get Slides item sourced from Twitter timeline
   * 
   * @param array $slidewizard SlideWizard data
   * 
   * @return array
   */
  function get_slides_item( $slidewizard ) {
    $username = $slidewizard['options']['username'];
    $slide_id = $slidewizard['id'];

    if( !isset($username) )
      $username = $this->source_options['Setup']['username']['default'];

    $timeline_url = "https://api.twitter.com/1/statuses/user_timeline.json?include_entities=true&include_rts=true&screen_name=" . urlencode( $username ) . "&count=" .urlencode( $slidewizard['options']['number_of_slides'] ) ;

    // Create cache key
    $cache_key = $slide_id . $timeline_url . $slidewizard['options']['cache_duration'] . $this->name;

    $twitter_posts = slidewizard_cache_read( $cache_key );

    // If cache doesn't exist
    if( !$twitter_posts ) {
      $twitter_posts = array();

      $response = wp_remote_get( $timeline_url, array( 'sslverify' => false, 'timeout' => 30 ) );
      if( !is_wp_error( $response ) ) {
        $response_json = json_decode( $response['body'] );

        foreach( $response_json as $index => $result ) {
          if( is_object( $result ) ) {
            if( isset( $result->retweeted_status ) ) {
              // For Retweeted status
              $twitter_posts[ $index ] = array(
                'id' => $result->retweeted_status->id_str,
                'title' => $result->retweeted_status->text,
                'permalink' => 'http://twitter.com/' . $result->retweeted_status->user->screen_name . '/status/' . $result->retweeted_status->id_str,
                'image' => $this->try_fetching_tweet_image( $result->retweeted_status, $slidewizard, $result->retweeted_status->user ),
                'author_username' => $result->retweeted_status->user->screen_name,
                'author_name' => $result->retweeted_status->user->name,
                'author_url' => 'http://twitter.com/' . $result->retweeted_status->user->screen_name,
                'author_email' => false,
                'author_avatar' => $result->retweeted_status->user->profile_image_url,
                'content' => $this->linkify_twitter_text($result->retweeted_status->text),
                'excerpt' => $this->linkify_twitter_text($result->retweeted_status->text),
                'created_at' => strtotime( $result->retweeted_status->created_at ),
                'local_created_at' => $result->retweeted_status->created_at,
                'description' => $result->retweeted_status->user->description,
                'source_app' => $result->source,
                'is_retweet' => true,
                'retweeter_name' => $result->user->name,
                'retweeter_username' => $result->user->screen_name,
                'retweeter_url' => 'http://twitter.com/' . $result->user->screen_name,
                'retweeter_avatar' => $result->user->profile_image_url
              );
              
              // Add Replying to data for original tweet:
              if( !empty( $result->retweeted_status->in_reply_to_screen_name ) ){
                $twitter_posts[ $index ]['in_reply_to_screen_name'] = $result->retweeted_status->in_reply_to_screen_name;
              }
              if( !empty( $result->retweeted_status->in_reply_to_status_id_str ) ){
                $twitter_posts[ $index ]['in_reply_to_status_id_str'] = $result->retweeted_status->in_reply_to_status_id_str;
              }
            } else {
              // For normal status
              $twitter_posts[ $index ] = array(
                'id' => $result->id_str,
                'title' => $result->text,
                'permalink' => 'http://twitter.com/' . $result->user->screen_name . '/status/' . $result->id_str,
                'image' => $this->try_fetching_tweet_image( $result, $slidewizard, $result->user ),
                'author_username' => $result->user->screen_name,
                'author_name' => $result->user->name,
                'author_url' => 'http://twitter.com/' . $result->user->screen_name,
                'author_email' => false,
                'author_avatar' => $result->user->profile_image_url,
                'content' => $this->linkify_twitter_text($result->text),
                'excerpt' => $this->linkify_twitter_text($result->text),
                'created_at' => strtotime( $result->created_at ),
                'local_created_at' => $result->created_at,
                'description' => $result->user->description,
                'source_app' => $result->source
              );

              // If this tweet is replying someone tweet
              if( !empty( $result->in_reply_to_screen_name ) ) {
                $twitter_posts[ $index ]['in_reply_to_screen_name'] = $result->in_reply_to_screen_name;
              }
              if( !empty( $result->in_reply_to_status_id_str ) ){
                $twitter_posts[ $index ]['in_reply_to_status_id_str'] = $result->in_reply_to_status_id_str;
              }
            }
          }
        }
      } else {
        return false;
      }

      // Write the cache
      slidewizard_cache_write( $cache_key, $twitter_posts, $slidewizard['options']['cache_duration'] );
    }
    
    return $twitter_posts;
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