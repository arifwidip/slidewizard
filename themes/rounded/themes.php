<?php
/**
 * Rounded Themes for SlideWizard
 * 
 */
class SlideWizardThemes_Rounded extends SlideWizardThemes_Helper {

  /**
   * Add options for this theme
   */
  var $options_themes = array(
    "Setup" => array(
      "number_of_slides" => array(
        "label" => "Number of Slides",
        "type" => "slider",
        "datatype" => "integer",
        "options" => array(
          "min" => 1,
          "max" => 5
        ),
        "default" => 5
      )
    )
  );

  /**
   * Construct
   */
  function __construct() {
    parent::__construct();

    add_filter('slidewizard_render_slidewizard_before', array( &$this, 'render_before'), 10, 2 );
    add_filter('slidewizard_render_slidewizard_after', array( &$this, 'render_after'), 10, 2 );
  }

  /**
   * Prepend HTML element before the Slider
   */
  function render_before( $html, $slidewizard ) {
    if( $this->is_valid( $slidewizard['themes'] ) ) {
      $html .= '<div class="slider-rounded-border"></div>';
      $html .= '<div class="slider-rounded-wrapper">';
    }
    return $html;
  }

  /**
   * Append HTML element after the Slider
   */
  function render_after( $html, $slidewizard ) {
    if( $this->is_valid( $slidewizard['themes'] ) ) {
      $html .= '</div><!-- .slider-rounded-wrapper -->';
      $html .= '<div class="slider-rounded-pager"></div>';

      $html .= '<div class="slider-rounded-desc">';
        $html .= '<div class="slide-title"></div>';
        $html .= '<div class="slide-text"></div>';
      $html .= '</div>';
    }
    return $html;
  }

  /**
   * Remove some options
   */
  function slidewizard_slide_options( $options, $slidewizard ) {
    if( $this->is_valid( $slidewizard['themes'] ) ) {
      unset($options['Setup']['size']);

      unset($options['Content']['show_title']);
      unset($options['Content']['show_avatar']);
      unset($options['Content']['link_avatar']);
      unset($options['Content']['excerpt_length_with_media']);
      unset($options['Content']['excerpt_length_no_media']);
      unset($options['Content']['show_readmore']);
      unset($options['Content']['show_excerpt']);

      unset($options['Slide']['slide_transition']['value']['Fade']);
      unset($options['Slide']['slide_transition']['value']['Cross Fade']);
      unset($options['Slide']['slide_transition']['value']['Cover Fade']);
      unset($options['Slide']['slide_transition']['value']['Uncover Fade']);
      unset($options['Navigation']);
    }

    return $options;
  }

  /**
   * Override default options for this theme
   */
  function slidewizard_default_options( $options, $themes, $source ) {
    if( $this->is_valid( $themes ) ) {
      $options['navigation_type'] = 'none';
      $options['show_slide_controls'] = 'none';
    }
    
    return $options;
  }

  /**
   * Override Slider dimensions
   */
  function slidewizard_dimensions( &$width, &$height, &$outer_width, &$outer_height, &$slidewizard ) {
    global $SlideWizard;
    if( $this->is_valid( $slidewizard['themes'] ) ) {
      $width = 250;
      $height = 250;
      $outer_height = 450;
      $outer_width = 350;
    }
  }


}