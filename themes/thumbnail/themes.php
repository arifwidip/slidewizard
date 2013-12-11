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

  function render_slide_nav( $nav, $slides, $slidewizard ) {
    foreach( $slides as $slide ) {
      $nav .= '<a href="" class="slidewizard-nav-item">';
        $nav .= '<div class="slidewizard-nav-item-img">';

          if( $slide['thumbnail'] ) {
            $nav .= '<img src="'. $slide['thumbnail'] .'">';
          }

        $nav .= '</div>';
      $nav .= '</a>';
    }

    return $nav;
  }

  /**
   * Slides Dimensions
   */
  function slidewizard_dimensions( &$width, &$height, &$outer_width, &$outer_height, &$slidewizard ) {
    global $SlideWizard;
    if( $this->is_valid( $slidewizard['themes'] ) ) {
      // $width = $width - 20;
      // $height = $height - 20;
      // $outer_height = $outer_height;
      // $outer_width = $outer_width;
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