<?php
/**
 * Slidewizard theme with image thumbnails as navigation
 */
class SlideWizardThemes_Thumbnail extends SlideWizardThemes_Helper {

  /**
   * Constructor
   */
  function __construct() {
    parent::__construct();

    add_filter( "{$this->namespace}_render_slidewizard_navigation", array( &$this, 'render_slide_nav' ), 10, 3 );
  }

  /**
   * Hook into slidewizard options
   * 
   */
  function slidewizard_slide_options( $options, $slidewizard ) {
    if( $this->is_valid( $slidewizard['themes'] ) ) {
      // unset( $options['Content']['show_excerpt'] );
      unset( $options['Content']['show_readmore'] );
      unset( $options['Navigation'] );
    }

    return $options;
  }

  /**
   * Slide Navigation thumbnail
   */
  function render_slide_nav( $nav, $slides, $slidewizard ) {
    $counter = 0;

    $nav .= '<div class="slidewizard-nav-inner">';
    foreach( $slides as $slide ) {
      $img_style = '';
      if( $slide['thumbnail'] ) {
        $img_style = 'background-image: url('. $slide['thumbnail'] .')';
      }

      $nav .= '<a href="" class="slidewizard-nav-item" data-index="'. $counter .'">';
        $nav .= '<div class="slidewizard-nav-item-img" style="'. $img_style .'">';
        $nav .= '</div>';
      $nav .= '</a>';

      $counter++;
    }
    $nav .= '</div>';

    $nav .= '<a class="slidewizard-thumb-nav thumb-prev">&lsaquo;</a>';
    $nav .= '<a class="slidewizard-thumb-nav thumb-next">&rsaquo;</a>';

    return $nav;
  }

  /**
   * Slides Dimensions
   */
  function slidewizard_dimensions( &$width, &$height, &$outer_width, &$outer_height, &$slidewizard ) {
    global $SlideWizard;
    if( $this->is_valid( $slidewizard['themes'] ) ) {
      $width = $width - 4;
      $height = $height - 4;
      $outer_height = $outer_height;
      $outer_width = $outer_width;
    }
  }

  /**
   * SlideWizard wrapper classes
   */
  function slidewizard_wrapper_classes( $wrapper_classes, $slidewizard ) {
    if( $this->is_valid( $slidewizard['themes'] ) ) {
      $show_title = $slidewizard['options']['show_title'] ? 'show-title' : 'no-title';
      $show_avatar = $slidewizard['options']['show_avatar'] ? 'show-avatar' : 'no-avatar';
      $show_excerpt = $slidewizard['options']['show_excerpt'] ? 'show-excerpt' : 'no-excerpt';
      $show_readmore = $slidewizard['options']['show_readmore'] ? 'show-readmore' : 'no-readmore';

      $wrapper_classes[] = "{$this->namespace}-{$show_title}";
      $wrapper_classes[] = "{$this->namespace}-{$show_avatar}";
      $wrapper_classes[] = "{$this->namespace}-{$show_excerpt}";
      $wrapper_classes[] = "{$this->namespace}-{$show_readmore}";
    }

    return $wrapper_classes;
  }

}