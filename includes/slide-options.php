<?php
/**
 * Array containing options for SlideWizard
 * 
 */

// General Settings
// -------------------------
$options = array(

  // Setup Settings
  // -----------------------
  "Setup" => array(
    "size" => array(
      "label" => "Size",
      "type" => "radio",
      "datatype" => "string",
      "value" => array(
        "Small" => "small",
        "Medium" => "medium",
        "Large" => "large",
        "Custom" => "custom"
      ),
      "data" => array(
        "trigger" => 'custom',
        "target" => '.control-width,.control-height'
      ),
      "default" => "medium",
      "description" => "The size of the slider"
    ),
    "width" => array(
      "label" => "Width",
      "type" => "text",
      "datatype" => "integer",
      'attr' => array(
        'size' => 5,
        'maxlength' => 4
      ),
      "default" => "500",
      "hidden" => true
    ),
    "height" => array(
      "label" => "Height",
      "type" => "text",
      "datatype" => "integer",
      'attr' => array(
        'size' => 5,
        'maxlength' => 4
      ),
      "default" => "300",
      "hidden" => true
    ),
    "number_of_slides" => array(
      "label" => "Number of Slides",
      "type" => "slider",
      "datatype" => "integer",
      "options" => array(
        "min" => 1,
        "max" => 10
      ),
      "default" => 5
    )
  ),


  // Content Settings
  // -----------------------
  "Content" => array(
    "show_title" => array(
      "label" => "Show Title",
      "type" => "radio",
      "datatype" => "boolean",
      "value" => array(
        "On" => true,
        "Off" => false
      ),
      "default" => true
    ),
    "link_title" => array(
      "label" => "Link Title",
      "type" => "radio",
      "datatype" => "boolean",
      "value" => array(
        "On" => true,
        "Off" => false
      ),
      "default" => true,
      "description" => "Make title linkable"
    ),
    "open_link_in" => array(
      "label" => "Open Link In..",
      "type" => "select",
      "datatype" => "string",
      "value" => array(
        "Same Window" => "same_window",
        "New Window" => "new_window"
      ),
      "default" => "same_window"
    ),
    "show_excerpt" => array(
      "label" => "Show Excerpt",
      "type" => "radio",
      "datatype" => "boolean",
      "value" => array(
        "On" => true,
        "Off" => false
      ),
      "default" => true,
      "description" => "Show excerpt on the SlideWizard"
    ),
    "date_format" => array(
      "label" => "Date Format",
      "type" => "select",
      "datatype" => "string",
      "value" => array(
        "None" => "none",
        "2 Days Ago" => "timeago",
        date( "F j, Y" ) => "human-readable"
      ),
      "default" => "timeago"
    ),
    "show_readmore" => array(
      "label" => "Show Read more",
      "type" => "radio",
      "datatype" => "boolean",
      "value" => array(
        "On" => true,
        "Off" => false
      ),
      "default" => false,
      "description" => "Show read more link below the excerpt"
    ),
    "excerpt_length_with_media" => array(
      "label" => "Excerpt length (with media)",
      "type" => "slider",
      "datatype" => "integer",
      "options" => array(
        "min" => 10,
        "max" => 500,
        "suffix" => "Characters",
        "minLabel" => "10Chars",
        "maxLabel" => "500Chars"
      ),
      "default" => 200
    ),
    "excerpt_length_no_media" => array(
      "label" => "Excerpt length (no media)",
      "type" => "slider",
      "datatype" => "integer",
      "options" => array(
        "min" => 10,
        "max" => 800,
        "suffix" => "Characters",
        "minLabel" => "10Chars",
        "maxLabel" => "800Chars"
      ),
      "default" => 200
    ),
    "show_avatar" => array(
      "label" => "Show Author Avatar",
      "type" => "radio",
      "datatype" => "boolean",
      "value" => array(
        "On" => true,
        "Off" => false
      ),
      "default" => true,
      "description" => "Show Author's avatar when available"
    ),
    "link_avatar" => array(
      "label" => "Link Avatar Name",
      "type" => "radio",
      "datatype" => "boolean",
      "value" => array(
        "On" => true,
        "Off" => false
      ),
      "default" => true,
      "description" => "If author URL available"
    ),
    "cache_duration" => array(
      "label" => "Cache Duration",
      "type" => "slider",
      "datatype" => "integer",
      "options" => array(
        "min" => 0,
        "max" => 2880,
        "suffix" => "minutes",
        "minLabel" => "No cache",
        "maxLabel" => "2Days"
      ),
      "default" => 0
    )
  ),

  
  // Slide Settings
  // -----------------------
  "Slide" => array(
    /*"circular" => array(
      "label" => "Circular",
      "type" => "radio",
      "datatype" => "boolean",
      "value" => array(
        "On" => true,
        "Off" => false
      ),
      "default" => false,
      "description" => "Slider will slide back to first slide when its reach end."
    ),
    "infinite" => array(
      "label" => "Infinite",
      "type" => "radio",
      "datatype" => "boolean",
      "value" => array(
        "On" => true,
        "Off" => false
      ),
      "default" => false,
      "description" => "Slider will continously sliding when its reach end."
    ),*/
    "continous_slide" => array(
      "label" => "Continous Slide",
      "type" => "radio",
      "datatype" => "boolean",
      "value" => array(
        "On" => true,
        "Off" => false
      ),
      "default" => false,
      "description" => "Slider will continously sliding when its reach end."
    ),
    "direction" => array(
      "label" => "Sliding Direction",
      "type" => "radio",
      "datatype" => "string",
      "value" => array(
        "Horizontal" => "left",
        "Vertical" => "up",
      ),
      "default" => "left",
      "description" => "The direction to scroll the slides, determines whether the slider scrolls horizontal or vertical."
    ),
    "starting_slide" => array(
      "label" => "Starting Slide",
      "type" => "slider",
      "datatype" => "integer",
      "options" => array(
        "min" => 1,
        "max" => 10
      ),
      "default" => 1
    ),
    "randomize" => array(
      "label" => "Randomize Slide",
      "type" => "radio",
      "datatype" => "boolean",
      "value" => array(
        "On" => true,
        "Off" => false
      ),
      "default" => false,
      "description" => "Randomize slide order"
    ),
    "autoplay_slide" => array(
      "label" => "Autoplay Slide",
      "type" => "radio",
      "datatype" => "boolean",
      "value" => array(
        "On" => true,
        "Off" => false
      ),
      "default" => false
    ),
    "autoplay_interval" => array(
      "label" => "Autoplay Interval",
      "type" => "slider",
      "datatype" => "integer",
      "options" => array(
        "min" => 1,
        "max" => 10,
        "minLabel" => "1 Sec",
        "maxLabel" => "10 Secs"
      ),
      "default" => 3
    ),
    "animation_speed" => array(
      "label" => "Animation Speed",
      "type" => "slider",
      "datatype" => "integer",
      "options" => array(
        "min" => 250,
        "max" => 2000,
        "step" => 250,
        "minLabel" => "250ms",
        "maxLabel" => "2Secs"
      ),
      "default" => 500
    ),
    "slide_transition" => array(
      "label" => "Slide Transition",
      "type" => "select",
      "datatype" => "string",
      "value" => array(
        "None" => "none",
        "Scroll" => "scroll",
        "Direct Scroll" => "directscroll",
        "Fade" => "fade",
        "Cross Fade" => "crossfade",
        "Cover" => "cover",
        "Cover Fade" => "cover-fade",
        "Uncover" => "uncover",
        "Uncover Fade" => "uncover-fade"
      ),
      "default" => "scroll"
    )
  ),


  // Slide Navigation
  // -----------------------
  "Navigation" => array(
    "show_slide_controls" => array(
      "label" => "Show Slide Controls",
      "type" => "select",
      "datatype" => "string",
      "value" => array(
        "Always" => "always",
        "On Hover" => "onhover",
        "Never" => "none"
      ),
      "default" => "always",
      "description" => "Select when slide controls appear"
    ),
    "navigation_type" => array(
      "label" => "Navigation Type",
      "type" => "select",
      "datatype" => "string",
      "value" => array(
        "Dots" => "dots",
        "None" => "none"
      ),
      "default" => "dots",
      "description" => "Choose navigation type"
    ),
    "navigation_position" => array(
      "label" => "Navigation position",
      "type" => "radio",
      "datatype" => "string",
      "value" => array(
        "Top" => "top",
        "Bottom" => "bottom"
      ),
      "default" => "bottom",
      "description" => "Select where the navigation will be shown"
    )
  )

);
