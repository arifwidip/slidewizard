<?php
/**
 * Default Themes for SlideWizard
 * 
 */
class SlideWizardThemes_Default extends SlideWizardThemes_Helper {

  function __construct(){
    parent::__construct();
    // add_filter( "{$this->namespace}_get_slides", array( &$this, "slidewizard_get_slides" ), 11, 2 );
  }

  function slidewizard_dimensions( &$width, &$height, &$outer_width, &$outer_height, &$slidewizard ) {
    global $SlideWizard;
    if( $this->is_valid( $slidewizard['themes'] ) ) {
      $width = $width - 20;
      $height = $height - 20;
      $outer_height = $outer_height;
      $outer_width = $outer_width;
    }
  }

  /**
   * SlideWizard wrapper classes
   * 
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