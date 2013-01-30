<?php
class SlideWizardSource_Feed extends Slides {
  var $label = "RSS Feed";
  var $name = "feed";

  var $source_options = array(
    'Setup' => array(
      "rss_feed_url" => array(
        "label" => "RSS Feed URL",
        "type" => "text",
        "datatype" => "string",
        "default" => "http://feeds.feedburner.com/colorlabs"
      )
    )
  );


  function add_hooks() {
    add_action( "{$this->namespace}_form_content_source", array( &$this, "slidewizard_form_content_source" ), 10, 2 );
  }


  /**
   * Get Feed Image
   */
  function get_feed_image( $feed, $slidewizard ) {
    global $SlideWizard;

    $image_arr = $SlideWizard->Themes->get_images_from_html( $feed->get_content() );

    if( !count( $image_arr ) ) {
      return false;
    } else {
      return $image_arr[0];
    }
  }


  /**
   * Get Author Avatar
   */
  function get_author_avatar( $feed, $slidewizard ) {
    return get_avatar( $feed->get_author()->email );
  }


  /**
   * Get Slides item sourced from RSS Feed
   * 
   * @param array $slidewizard SlideWizard data
   * 
   * @return array
   */
  function get_slides_item( $slidewizard ) {
    $slide_id = $slidewizard['id'];
    $feed_url = $slidewizard['options']['rss_feed_url'];

    if( !isset( $feed_url ) )
      $feed_url = $this->source_options['Setup']['rss_feed_url']['default'];

    // Set temporary reference to current slidewizard, so it can be accessed from wp_feed_options
    $this->current_slidewizard = $slidewizard;
    add_action( 'wp_feed_options', array( &$this, 'wp_feed_options' ), 10, 2 );
    // Fetch RSS
    $rss = fetch_feed( $feed_url );
    remove_action( 'wp_feed_options', array( &$this, 'wp_feed_options' ) , 10, 2 );
    // Remove temporary reference
    unset( $this->current_slidewizard );

    if( !is_wp_error( $rss ) ) {
      $maxitems = $rss->get_item_quantity( $slidewizard['options']['number_of_slides'] );
      $rss_items = $rss->get_items( 0, $maxitems );
      $feed = array();

      foreach( $rss_items as $index => $item ) {
        $feed[ $index ] = array(
          'title' => $item->get_title(),
          'created_at' => strtotime( $item->get_date( "Y-m-d H:i:s" ) ),
          'thumbnail' => $item->get_enclosure()->thumbnails[0],
          'permalink' => $item->get_permalink(),
          'author_name' => $item->get_author()->name,
          'author_url' => '',
          'author_email' => $item->get_author()->email,
          'author_avatar' => $this->get_author_avatar( $item, $slidewizard )
        );

        // Check image
        $has_image = $this->get_feed_image( $item, $slidewizard );
        if( $has_image ) {
          $feed[ $index ]['image'] = $has_image;
          $excerpt_length = $slidewizard['options']['excerpt_length_with_media'];
        } else {
          $excerpt_length = $slidewizard['options']['excerpt_length_no_media'];
        }

        // Set Content
        $feed[ $index ]['content'] = slidewizard_truncate_text( $item->get_content(), $excerpt_length );
      }
    } else {
      return false;
    }

    return $feed;
  }


  /**
   * Hook into feed options to change cache duration
   */
  function wp_feed_options( $feed, $url ) {
    $feed->set_cache_duration( $this->current_slidewizard['options']['cache_duration'] );
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

    // Validate source type
    if( !$this->is_valid( $slidewizard['source'] ) )  {
      return $slides;
    }

    // Get Slides Item
    $slides_item = $this->get_slides_item( $slidewizard );

    foreach( $slides_item as $slide_item ) {
      $slide = array(
        'source' => $this->name,
        'title' => $slide_item['title'],
        'created_at' => $slide_item['created_at']
      );

      $slide = array_merge( $this->slide_item_prop, $slide );

      // Check if post has image
      $has_image = !empty( $slide_item['image'] );

      if( !empty( $slide_item['content'] ) ) {
        $slide['classes'][] = "has-excerpt";
      } else {
        $slide['classes'][] = "no-excerpt";
      }

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
}