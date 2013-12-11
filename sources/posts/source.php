<?php
class SlideWizardSource_Posts extends Slides {
  var $label = "Your Posts";
  var $name = "posts";
  var $taxonomies = array( 'posts' );

  // Available sorting methods
  var $post_type_sorts = array(
    "Recent" => "recent",
    "Popular" => "popular"
  );

  // Specific options for this source
  var $source_options = array(
    'Setup' => array(
      "image_source" => array(
        "label" => "Image Source",
        "type" => "select",
        "value" => array(
          "No Image" => "none",
          "First image in content" => "content",
          "Featured Image" => "featured"
        ),
        "default" => "featured"
      ),
      "post_type" => array(
        "label" => "Post Type",
        "type" => "select",
        "default" => "post"
      ),
      "sorting_method" => array(
        "label" => "Order slide by?",
        "type" => "select",
        "default" => "recent"
      ),
      "use_custom_excerpt" => array(
        "label" => "Use Custom Excerpt",
        "type" => "radio",
        "value" => array(
          "On" => 1,
          "Off" => 0
        ),
        "default" => "off"
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
      unset( $options['Content']['cache_duration'] );
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
   * Get Slides item sourced from WordPress posts
   * 
   * @param array $slidewizard SlideWizard data
   * 
   * @return array
   */
  function get_slides_item( $slidewizard ) {
    // TODO: Change this
    $post_type = $slidewizard['options']['post_type'];
    $post_type_sort = $slidewizard['options']['sorting_method'];

    // Default query arguments
    $query_args = array(
      'post_type' => $post_type,
      'post_status' => 'publish',
      'posts_per_page' => $slidewizard['options']['number_of_slides'],
      'ignore_sticky_posts' => 1
    );

    // Check sort options
    switch( $post_type_sort ) {
      case "recent":
        $query_args['orderby'] = "date";
        $query_args['order'] = "DESC";
      break;

      case "popular":
        $query_args['orderby'] = "comment_count date";
        $query_args['order'] = "DESC";
      break;
    }

    $query = new WP_Query( $query_args );

    $slides = array();

    foreach( (array) $query->posts as $post ) {
      $post_id = $post->ID;

      $author = get_userdata( $post->post_author );
      $post_content = $post->post_content;

      $slide = array(
        'id' => $post_id,
        'title' => $post->post_title,
        'permalink' => get_permalink( $post_id ),
        'author_id' => $post->post_author,
        'author_name' => $author->display_name,
        'author_url' => $author->user_url,
        'author_email' => $author->user_email,
        'author_avatar' => get_avatar( $author->user_email, '96' ),
        'content' => $post_content,
        'excerpt' => $post->post_excerpt,
        'created_at' => strtotime( $post->post_date_gmt ),
        'local_created_at' => $post->post_date
      );

      $slides[] = $slide;
    }

    return $slides;
  }


  /**
   * Get image for the slide
   * 
   * @param array $slide The slide data
   * @param array $slidewizard The SlideWizard data
   * 
   */
  function get_image( $slide, $slidewizard, $is_thumbail = false, $source = null, $tried_sources = array() ) {
    global $SlideWizard;

    // Get with and height of the slide
    $slide_dimensions = $this->get_dimensions( $slidewizard );

    $outer_width = $slide_dimensions['outer_width'];
    $outer_height = $slide_dimensions['outer_height'];
    $image_size = array( $outer_width, $outer_height );

    if( $is_thumbail ) {
      $image_size = array( 80, 80 );
    }

    // Set default return value
    $image_src = false;

    // If the image is actually set already, just use it.
    if( isset( $slide['image'] ) && !empty( $slide['image'] ) ){
      $image_src = $slide['image'];
      return $image_src;
    }

    if( !isset( $slidewizard['options']['image_source'] ) )
      $slidewizard['options']['image_source'] = "content";

    $sources = array( 'content', 'featured' );

    if( !isset( $source ) )
      $source = $slidewizard['options']['image_source'];

    // If user doesn't want any images
    if( $slidewizard['options']['image_source'] == "none" )
      return false;

    // Check image source
    switch( $source ) {
      default:
      case "content":
        $images = $SlideWizard->Themes->get_images_from_html( $slide['content'] );
        if( !empty( $images ) ) {
          $image_src = reset( $images );
          $image_src = vt_resize( '', $image_src, $image_size[0], $image_size[1], true);
          $image_src = $image_src['url'];
        }
      break;

      case "featured":
        if( current_theme_supports( 'post-thumbnails' ) ) {
          if( is_numeric( $slide['id'] ) ) {
            $thumbnail_id = get_post_thumbnail_id( $slide['id'] );
            if( $thumbnail_id ) {
              // $thumbnail = wp_get_attachment_image_src( $thumbnail_id, $image_size );
              // $image_src = $thumbnail[0];
              $image_src = vt_resize( $thumbnail_id, '', $image_size[0], $image_size[1], true);
              $image_src = $image_src['url'];
            }
          }
        }
      break;
    }

    if( $image_src == false ) {
      $tried_sources[] = $source;
      // Only try other sources if we haven't tried them all
      if( count( array_intersect( $sources, $tried_sources) ) < count( $sources ) ) {
        // Loop through sources to find an untried source to try
        $next_source = false;
        foreach( $sources as $untried_source ) {
          if( !in_array( $untried_source, $tried_sources ) ) {
            $next_source = $untried_source;
          }
        }
        if( $next_source ) {
          $image_src = $this->get_image( $slide, $slidewizard, $is_thumbail, $next_source, $tried_sources );
        }
      }
    }
    return $image_src;
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

    // Slide counter
    $slide_counter = 1;

    // Loop through all slides item to build slides array
    foreach( $slides_item as $slide_item) {
      $slide = array(
        'source' => $this->name,
        'title' => $slide_item['title'],
        'created_at' => $slide_item['created_at']
      );

      $slide = array_merge( $this->slide_item_prop, $slide );

      // Check if post has image
      $has_image = $this->get_image( $slide_item, $slidewizard );
      
      // If user choose to use custom excerpt
      if( isset( $slidewizard['options']['use_custom_excerpt'] ) && !empty( $slidewizard['options']['use_custom_excerpt'] ) ) {
        if( !empty( $slide_item['excerpt'] ) ) {
          $slide_item['content'] = $slide_item['excerpt'];
        }
      }
      $slide_item['content'] = strip_shortcodes( $slide_item['content'] );      

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

      // Truncate excerpt
      $excerpt_length = $has_image ? $slidewizard['options']['excerpt_length_with_media'] : $slidewizard['options']['excerpt_length_no_media'];
      $slide_item['content'] = slidewizard_truncate_text( $slide_item['content'], $excerpt_length );

      // Link target
      $slide_item['target'] = $slidewizard['options']['open_link_in'];

      // Set image
      if( $has_image ) $slide['thumbnail'] = $this->get_image( $slide_item, $slidewizard, true, 'featured' );
      if( $has_image ) $slide_item['image'] = $has_image;

      $slide['content'] = $SlideWizard->Themes->process_template( $slide_item, $slidewizard );

      $slide_counter++;
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

    // Available post types to choose from excluding SlideWizard related post types and invalid post types like navigation and revisions
    $post_types = get_post_types( array(), 'objects' );
    $invalid_post_types = array( 'revision', 'attachment', 'nav_menu_item', SLIDEWIZARD_POST_TYPE, SLIDEWIZARD_SLIDE_POST_TYPE );
    foreach( $invalid_post_types as &$invalid_post_type )
      unset( $post_types[$invalid_post_type] );
    
    foreach( $post_types as &$post_type )
      $post_type = $post_type->labels->name;

    $namespace = $this->namespace;    

    // Change post_type options value
    $post_types = array_flip( $post_types );
    $this->source_options['Setup']['post_type']['value'] = $post_types;

    // Post Sorting Methods
    $post_type_sorts = $this->post_type_sorts;
    $this->source_options['Setup']['sorting_method']['value'] = $post_type_sorts;

    include( dirname( __FILE__ ) . '/views/options.php' );
  }
}