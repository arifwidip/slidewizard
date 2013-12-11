<?php
class SlideWizardSource_Flickr extends Slides {
  var $label = "Flickr Photo";
  var $name = "flickr";
  var $taxonomies = array( 'flickr' );

  var $source_options = array(
    'Setup' => array(
      "flickr_user_or_group" => array(
        "label" => "User or Group?",
        "type" => "radio",
        "datatype" => "string",
        "value" => array(
          "User" => "user",
          "Group" => "group"
        ),
        "default" => "user"
      ),
      "flickr_user_id" => array(
        "label" => "User/Group ID",
        "type" => "text",
        "datatype" => "string",
        "default" => "",
        "description" => 'Get your Flick ID from <a target="_blank" href="http://idGettr.com">idGettr.com</a>'
      )
    )
  );

  function add_hooks() {
    add_action( "{$this->namespace}_form_content_source", array( &$this, "slidewizard_form_content_source" ), 10, 2 );
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
   * Get Slides item sourced from Flickr Photo
   * 
   * @param array $slidewizard SlideWizard data
   * 
   * @return array
   */
  function get_slides_item( $slidewizard ) {
    $user_id = $slidewizard['options']['flickr_user_id'];
    $slide_id = $slidewizard['id'];

    switch( $slidewizard['options']['flickr_user_or_group'] ) {
      case "user":
        $feed_url = "http://api.flickr.com/services/feeds/photos_public.gne?id=";
      break;

      case "group":
        $feed_url = "http://api.flickr.com/services/feeds/groups_pool.gne?id=";
      break;
    }

    $feed_url .= $user_id . "&format=rss_200_enc&nojsoncallback=1";

    // Set temporary reference to current slidewizard, so it can be accessed from wp_feed_options
    $this->current_slidewizard = $slidewizard;
    add_action( 'wp_feed_options', array( &$this, 'wp_feed_options' ), 10, 2 );
    // Fetch RSS
    $rss = fetch_feed( $feed_url );
    remove_action( 'wp_feed_options', array( &$this, 'wp_feed_options' ) , 10, 2 );
    // Remove temporary reference
    unset( $this->current_slidewizard );

    $images = array();
    if( !is_wp_error( $rss ) ) {
      $maxitems = $rss->get_item_quantity( $slidewizard['options']['number_of_slides'] );
      $rss_items = $rss->get_items( 0, $maxitems );

      if( isset( $rss_items ) ) {
        foreach( $rss_items as $index => $item ) {
          $images[ $index ] = array(
            'title' => $item->get_title(),
            'width' => $item->get_enclosure()->width,
            'height' => $item->get_enclosure()->height,
            'created_at' => strtotime( $item->get_date( "Y-m-d H:i:s" ) ),
            'image' => $item->get_enclosure()->link,
            'thumbnail' => $item->get_enclosure()->thumbnails[0],
            'permalink' => $item->get_permalink(),
            'content' => '',
            'author_avatar' => false,
            'author_name' => $item->get_enclosure()->credits[0]->name,
            'author_url' => 'http://www.flickr.com/photo/' . $user_id
          );
        }
      }
    }

    return $images;
  }

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

      // If image is smaller than container
      $slide_dimensions = $this->get_dimensions( $slidewizard );
      if( $slide_dimensions['width'] > $slide_item['width'] ) {
        $slide['classes'][] = "smaller-image";
      }


      // Link target
      $slide_item['target'] = $slidewizard['options']['open_link_in'];

      $slide['thumbnail'] = $slide_item['image'];
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