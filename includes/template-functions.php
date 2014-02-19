<?php

/**
 * Get the URL for the specified plugin action
 *
 * @param object $str [optional] Expects the handle passed in the menu
 * definition
 *
 */
if( !function_exists( 'slidewizard_action' ) ) {
  function slidewizard_action( $str = "" ) {
    global $SlideWizard;
    
    $action = $SlideWizard->action( $str );
    
    return $action;
  }
}


/**
 * Get the classname from a file name
 * 
 * Creates a string of the name of a class based off the name of a file.
 * All "-" characters in a file name will be treated as spaces, which will
 * then be eliminated to return a Pascal case class name. An optional class
 * prefix can be passed in as the second parameter.
 * 
 * @param string $filename The name of the file to get the class name from
 * @param string $prefix The optional prefix to use for the class name
 */
if( !function_exists( 'slidewizard_get_classname_from_filename' ) ) {
  function slidewizard_get_classname_from_filename( $filename = "", $prefix = "" ) {
    $classname = $prefix . str_replace( " ", "", ucwords( preg_replace( array( '/\.php$/', '/\-/' ), array( "", " " ), basename( $filename ) ) ) );
    
    return $classname;
  }
}


/**
 * Get the icon URL for a source
 * 
 * @param mixed $source Either the source's slug or the source object itself (the slug will be extracted from the "name" property)
 * 
 * @return string
 */
if( !function_exists( 'slidewizard_get_source_icon_url' ) ) {
  function slidewizard_get_source_icon_url( $source ) {
    global $SlideWizard;
    
    // Get the slug from the Source object if that was passed in
    if( is_object( $source ) ) {
      $source = (string) $source->name;
    }
    
    $url = "";
    
    $sources = $SlideWizard->get_sources();
    if( isset( $sources[$source] ) ) {
      $file_data = $sources[$source]->get_source_file( "/images/icon.png" );
      $url = $file_data['url'];
    }
    
    return $url;
  }
}


/**
 * Truncate text to a specified length
 * 
 * Returns a substring of the text passed in truncated down to the specified length.
 * Does not take into account proper closing of HTML tags.
 * 
 * @param string $str The string to truncate
 * @param integer $length Length to truncate to in characters
 * @param string $suffix The text to append to the end of a truncated string
 */
if( !function_exists( 'slidewizard_truncate_text' ) ) {
  function slidewizard_truncate_text( $str, $length = 55, $suffix = "&hellip;" ) {
    $truncated = trim( mb_substr( strip_tags( $str ), 0, (int) $length ) );
    $str_length = function_exists( 'mb_strlen' ) ? mb_strlen( $str ) : strlen( $str );
      
    if( $str_length > $length ) {
      $truncated .= $suffix;
    }
    
    return $truncated;
  }
}


/**
 * Sets a WordPress Transient. Returns a boolean value of the success of the write.
 * 
 * @param string $name The name (key) for the file cache
 * @param mixed $content The content to store for the file cache
 * @param string $time_from_now time in minutes from now when the cache should expire
 * 
 * @return boolean
 */
if( !function_exists( 'slidewizard_cache_write' ) ) {
  function slidewizard_cache_write( $name = "", $content = "", $time_from_now = 30 ) {
    $duration = $time_from_now * 60;
      $name = md5( $name . SLIDEWIZARD_VERSION . SLIDEWIZARD_PLUGIN_DIR );
      return set_transient( $name, $content, $duration );
  }
}


/**
 * Reads a file cache value and returns the content stored, 
 * or returns boolean(false)
 * 
 * @param string $name The name (key) for the transient
 * 
 * @return mixed
 */
if( !function_exists( 'slidewizard_cache_read' ) ) {
  function slidewizard_cache_read( $name = "" ) {
    $name = md5( $name . SLIDEWIZARD_VERSION . SLIDEWIZARD_PLUGIN_DIR );
    return get_transient( $name );
  }
}


/**
 * Deletes a WordPress Transient Cache
 * 
 * @param string $name The name (key) for the file cache
 */
if( !function_exists( 'slidewizard_cache_clear' ) ) {
  function slidewizard_cache_clear( $name = "" ) {
    delete_transient( $name );
  }
}


/**
 * Function for build options html structure
 *
 * @param array $options Array containing options
 * @param array $stored_options Stored options value
 *
 * @return string
 */
if( !function_exists( 'slidewizard_options_builder' ) ) {
  function slidewizard_options_builder( $options, $stored_options = array() ) {
    $output = '';
    $counter = 1; // Counter for tab pane

    foreach( $options as $key => $value ) {
      // Build options group
      $options_count = count( $value );

      $output .= '<div id="'. strtolower($key) .'" class="tab-pane fade">';
      $output .= '<ul class="options-list form-horizontal">';

      $output .= slidewizard_render_input_html( $value, $stored_options );

      $output .= '</ul>';
      $output .= '</div>';

      $counter++;
    }
    
    return $output;
  }
}


/**
 * Render options input type
 *
 * @param array $value Options array value
 * @param array $stored_options Stored options value
 *
 * @return string
 */
if( !function_exists( 'slidewizard_render_input_html' ) ) {
  function slidewizard_render_input_html( $value, $stored_options = array() ) {
    // Loop each options
    // -------------------
    $options_counter = 1; // Counter for options list
    $output = '';

    foreach( $value as $id => $option ) {
      $output .= slidewizard_render_input_single( $id, $option, $stored_options );
      $options_counter++;
    }

    return $output;
  }
}


/**
 * Render single options input
 *
 * @param array $option Options value
 * @param array $stored_options Stored options
 */
if( !function_exists( 'slidewizard_render_input_single' ) ) {
  function slidewizard_render_input_single( $id, $option, $stored_options = array(), $alt_desc = false ) {
    $is_hidden = ( isset($option['hidden']) && $option['hidden'] ) ? ' hidden ' : '';
    $type = $option['type'];
    $option_name = 'options['. $id .']';
    $attr = '';
    $class = '';

    // $stored_value = ( !empty($stored_options[$id]) ) ? $stored_options[$id] : $option['default'];
    $stored_value = $stored_options[$id];

    // If attributes defined
    if( isset($option['attr']) && $option['attr'] ) {
      foreach( $option['attr'] as $attr_key => $attr_value ) {
        $attr .= $attr_key . '=' . $attr_value . " ";
      }
    }

    // Options control group
    $output = '<li class="control-group control-'. $id . $is_hidden .'">';

    // Options Label
    $output .= '<label class="control-label" for="options-'. $id .'">';
    $output .= '<span class="label-text">'. $option['label'] .'</span>';

    // If Options has description
    if( isset($option['description']) && $option['description'] && !$alt_desc ) {
      $output .= '<span class="tooltip-help tooltips badge" data-placement="right" title="'. $option['description'] .'">?</span>';
    }
    $output .= '</label>';


    // Render options
    $output .= '<div class="control-option control-option-'. $type .'">';
    // Check options type
    switch ( $type ) {

      // Text
      // --------------------------
      case "text":
        $input_type = ( isset( $option['hide_field'] ) ) ? 'password' : 'text';
        $output .= '<input value="'. $stored_value .'" name="'. $option_name .'" type="'. $input_type .'" '. $attr .'>';
        break;

      // Radio
      // --------------------------
      case "radio":
        // Bootstrap Radio Button
        $output .= '<div class="input-radio btn-group" class="btn-group" data-toggle="buttons-radio">';
        $data_attr = "";
        if( isset($option['data']) && $option['data'] ) {
          foreach( $option['data'] as $data_key => $data_val ) {
            $data_attr .= "data-{$data_key}=\"$data_val\" ";
          }
        }

        if( isset( $option['value'] ) ) {
          foreach( $option['value'] as $radio_key => $radio_val ) {
            if( 'boolean' == $option['datatype'] ) {
              if( $radio_val === false ) {
                $radio_val = 0;
              }
            }

            $is_current = ( $radio_val == $stored_value ) ? 'active' : '';
            $output .= '<button type="button" '. $data_attr .' data-value="'. $radio_val .'" class="btn '. $is_current .'">'. $radio_key .'</button>';
          }
        }
        $output .= '</div>';

        // Regular radio input
        if( isset( $option['value'] ) ) {
          foreach( $option['value'] as $radio_key => $radio_val ) {
            if( 'boolean' == $option['datatype'] ) {
              if( $radio_val === false ) {
                $radio_val = 0;
              }
            }

            $is_current = ( $radio_val == $stored_value ) ? 'checked' : '';
            $output .= '<label class="radio inline">';
            $output .= $radio_key;
            $output .= '<input type="radio" name="'. $option_name .'" value="'. $radio_val .'" '. $is_current .'>';
            $output .= '</label>';
          }
        }
        break;

      // Slider
      // --------------------------
      case "slider":
        $input_slider_opts = '';
        $minLabel = ( isset($option['options']['minLabel']) && $option['options']['minLabel'] ) ? $option['options']['minLabel'] : $option['options']['min'];
        $maxLabel = ( isset($option['options']['maxLabel']) && $option['options']['maxLabel'] ) ? $option['options']['maxLabel'] : $option['options']['max'];
        foreach( $option['options'] as $input_option_key => $input_option_val ) {
          $input_slider_opts .= "data-$input_option_key=\"$input_option_val\" ";
        }

        $output .= '<div class="input-slider" '. $input_slider_opts .'>';
        $output .= '<span class="min">'. $minLabel .'</span>';
        $output .= '<span class="max">'. $maxLabel .'</span>';
        $output .= '</div>';
        $output .= '<input readonly="readonly" type="text" name="'. $option_name .'" value="'. $stored_value .'">';

        if( isset( $option['options']['suffix'] ) ) {
          $output .= '<span class="suffix">';
          $output .= $option['options']['suffix'];
          $output .= '</span>';
        }

        break;

      // Select
      // --------------------------
      case "select":
        $output .= '<select name="'. $option_name .'">';
        foreach( $option['value'] as $select_key => $select_val ) {
          $is_current = ( $select_val == $stored_value ) ? 'selected' : '';
          $output .= '<option value="'. $select_val .'" '. $is_current .'>'. $select_key .'</option>';
        }
        $output .= "</select>";
        break;

    }

    // Alternative description
    if( $alt_desc && isset( $option['description'] ) ) {
      $output .= '<div class="alt-desc">'. $option['description'] .'</div>';
    }

    $output .= '</div>';

    $output .= '</li>';
    return $output;
  }
}


/*-----------------------------------------------------------------------------------*/
/* vt_resize - Resize images dynamically using wp built in functions
/*-----------------------------------------------------------------------------------*/
/*
 * Resize images dynamically using wp built in functions
 * Victor Teixeira
 *
 * php 5.2+
 *
 * Exemplo de uso:
 *
 * <?php
 * $thumb = get_post_thumbnail_id();
 * $image = vt_resize( $thumb, '', 140, 110, true );
 * ?>
 * <img src="<?php echo $image[url]; ?>" width="<?php echo $image[width]; ?>" height="<?php echo $image[height]; ?>" />
 *
 * @param int $attach_id
 * @param string $img_url
 * @param int $width
 * @param int $height
 * @param bool $crop
 * @return array
 */
if ( ! function_exists( 'vt_resize' ) ) {
  function vt_resize( $attach_id = null, $img_url = null, $width, $height, $crop = false ) {

    // Cast $width and $height to integer
    $width = intval( $width );
    $height = intval( $height );

    // this is an attachment, so we have the ID
    if ( $attach_id ) {
      $image_src = wp_get_attachment_image_src( $attach_id, 'full' );
      $file_path = get_attached_file( $attach_id );
    // this is not an attachment, let's use the image url
    } else if ( $img_url ) {
      $file_path = parse_url( esc_url( $img_url ) );
      $file_path = $_SERVER['DOCUMENT_ROOT'] . $file_path['path'];

      //$file_path = ltrim( $file_path['path'], '/' );
      //$file_path = rtrim( ABSPATH, '/' ).$file_path['path'];

      $orig_size = getimagesize( $file_path );

      $image_src[0] = $img_url;
      $image_src[1] = $orig_size[0];
      $image_src[2] = $orig_size[1];
    }

    $file_info = pathinfo( $file_path );
                
    // check if file exists
    if ( !isset( $file_info['dirname'] ) && !isset( $file_info['filename'] ) && !isset( $file_info['extension'] )  )
      return;
    if ( !isset( $file_info['dirname'] ) && !isset( $file_info['extension'] )  )
      return;
                
    $base_file = $file_info['dirname'].'/'.$file_info['filename'].'.'.$file_info['extension'];
    if ( !file_exists($base_file) )
      return;

    $extension = '.'. $file_info['extension'];

    // the image path without the extension
    $no_ext_path = $file_info['dirname'].'/'.$file_info['filename'];

    $cropped_img_path = $no_ext_path.'-'.$width.'x'.$height.$extension;

    // checking if the file size is larger than the target size
    // if it is smaller or the same size, stop right here and return
    if ( $image_src[1] > $width ) {
      // the file is larger, check if the resized version already exists (for $crop = true but will also work for $crop = false if the sizes match)
      if ( file_exists( $cropped_img_path ) ) {
        $cropped_img_url = str_replace( basename( $image_src[0] ), basename( $cropped_img_path ), $image_src[0] );

        $vt_image = array (
          'url' => $cropped_img_url,
          'width' => $width,
          'height' => $height
        );
        return $vt_image;
      }

      // $crop = false or no height set
      if ( $crop == false OR !$height ) {
        // calculate the size proportionaly
        $proportional_size = wp_constrain_dimensions( $image_src[1], $image_src[2], $width, $height );
        $resized_img_path = $no_ext_path.'-'.$proportional_size[0].'x'.$proportional_size[1].$extension;

        // checking if the file already exists
        if ( file_exists( $resized_img_path ) ) {
          $resized_img_url = str_replace( basename( $image_src[0] ), basename( $resized_img_path ), $image_src[0] );

          $vt_image = array (
            'url' => $resized_img_url,
            'width' => $proportional_size[0],
            'height' => $proportional_size[1]
          );
          return $vt_image;
        }
      }

      // check if image width is smaller than set width
      $img_size = getimagesize( $file_path );
      if ( $img_size[0] <= $width ) $width = $img_size[0];
      
      // Check if GD Library installed
      if ( ! function_exists ( 'imagecreatetruecolor' ) ) {
          echo 'GD Library Error: imagecreatetruecolor does not exist - please contact your webhost and ask them to install the GD library';
          return;
      }

      // no cache files - let's finally resize it
      if ( function_exists( 'wp_get_image_editor' ) ) {
        $image = wp_get_image_editor( $file_path );
        if ( ! is_wp_error( $image ) ) {
          $image->resize( $width, $height, $crop );
          $save_data = $image->save();
          if ( isset( $save_data['path'] ) ) $new_img_path = $save_data['path'];
        }
      } else {
        $new_img_path = image_resize( $file_path, $width, $height, $crop );
      }   
      
      $new_img_size = getimagesize( $new_img_path );
      $new_img = str_replace( basename( $image_src[0] ), basename( $new_img_path ), $image_src[0] );

      // resized output
      $vt_image = array (
        'url' => $new_img,
        'width' => $new_img_size[0],
        'height' => $new_img_size[1]
      );

      return $vt_image;
    }

    // default output - without resizing
    $vt_image = array (
      'url' => $image_src[0],
      'width' => $width,
      'height' => $height
    );

    return $vt_image;
  }
}

function slidewizard_get_tweets_bearer_token( $consumer_key, $consumer_secret ){
		$consumer_key = rawurlencode( $consumer_key );
		$consumer_secret = rawurlencode( $consumer_secret );

		$token = maybe_unserialize( get_option( 'slidewizard_twitter_token' ) );

		if( ! is_array($token) || empty($token) || $token['consumer_key'] != $consumer_key || empty($token['access_token']) ) {
			$authorization = base64_encode( $consumer_key . ':' . $consumer_secret );

			$options = array(
				'http' => array(
					'method' => 'POST',
					'header' => 'Authorization: Basic '.$authorization,
					'content' => 'grant_type=client_credentials'
				)
			);
			$context = stream_context_create($options);
			$result = json_decode( @file_get_contents('https://api.twitter.com/oauth2/token', false, $context) );
			$token = serialize( array(
				'consumer_key'      => $consumer_key,
				'access_token'      => $result->access_token
			) );
			update_option( 'slidewizard_twitter_token', $token );
		}
	}

	function slidewizard_get_tweets( $instance = array() ){
		if(empty($instance)) return false;
		extract($instance);
		$token = maybe_unserialize( get_option( 'slidewizard_twitter_token' ) );
			if('' == $token){
				slidewizard_get_tweets_bearer_token($consumer_key,$consumer_secret);
				return slidewizard_get_tweets();
			}
		if( strpos($query, 'from:') === 0  ) {
			$query_type = 'user_timeline';
			$query = substr($query, 5);
			$url = 'https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name='.rawurlencode($query).'&count='.$number;
		} else {
			$query_type = 'search';
			$url =  'https://api.twitter.com/1.1/search/tweets.json?q='.rawurlencode($query).'&count='.$number;
		}

		$options = array(
			'http' => array(
				'method' => 'GET',
				'header' => 'Authorization: Bearer '.$token['access_token']
			)
		);
		$context = stream_context_create($options);
		$result = json_decode( @file_get_contents( $url, false, $context) );

		if( isset( $result->errors ) && $result->code == 89 ) {
			delete_option( 'slidewizard_twitter_token' );
			slidewizard_get_tweets_bearer_token();
			return slidewizard_get_tweets();
		} 

		$tweets = array();
		if( 'user_timeline' == $query_type ) {
			if( !empty($result) ) {
				$tweets = $result;
			}
		} else {
			if( !empty($result->statuses) ) {
				$tweets = $result->statuses;
			}

		}

		$follow_button = '<a href="https://twitter.com/__name__" class="twitter-follow-button" data-show-count="false" data-lang="en">Follow @__name__</a><script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>';
			
			$before_item = '<div class="tweet-item '.$query_type.'">';
			$after_item = '</div>';
			
			if($list_before)$before_item = $list_before;
			if($list_after)$after_item = $list_after;
			
		if( !empty($tweets) ) {
			foreach ($tweets as $tweet ) {
				$text = slidewizard_update_tweet_urls( $tweet->text );
				$time = human_time_diff( strtotime($tweet->created_at), time() );
				$url = 'http://twitter.com/'.$tweet->user->id.'/status/'.$tweet->id_str;
				$screen_name = $tweet->user->screen_name;
				$name = $tweet->user->name;
				$profile_image_url = $tweet->user->profile_image_url;

				echo $before_item;
				if( 'search' == $query_type ) {
					echo '<div class="twitter-user">';
					if( $show_account == 'true' ) {
						echo '<a href="https://twitter.com/'.$screen_name.'" class="user">';
						if( $show_avatar && $profile_image_url ) {
							echo '<img src="'.$profile_image_url.'" width="16px" height="16px" >';
						}
						echo '&nbsp;<strong class="name">'.$name.'</strong>&nbsp;<span class="screen_name">@'.$screen_name.'</span></a>';
					}
					echo '</div>';
				}

				echo    '<div class="tweet-content">'.$text.'</div><span class="time"><a target="_blank" title="" href="'.$url.'"> about '.$time.' ago</a></span>';
				
				if( 'search' == $query_type ) {
					if( $show_follow == 'true' ) {
						echo str_replace('__name__', $screen_name, $follow_button);
					}
				}
				echo $after_item;
			}

			if( 'user_timeline' == $query_type ) {
						if(( $show_account == 'true' )||( $show_follow == 'true')) {
				echo    '<div class="twitter-user">';
				if( $show_account == 'true' ) {
					echo '<a href="https://twitter.com/'.$screen_name.'" class="user">';
					if( $show_avatar && $profile_image_url ) {
						echo '<img src="'.$profile_image_url.'" width="16px" height="16px" >';
					}
					echo '&nbsp;<strong class="name">'.$name.'</strong>&nbsp;<span class="screen_name">@'.$screen_name.'</span></a>';
				}
				if( $show_follow == 'true') {
					echo str_replace('__name__', $screen_name, $follow_button);
				}
				echo    '</div>';
						}	
			}

			
		}
	}
	function slidewizard_update_tweet_urls($content) {
		$maxLen = 16;
		//split long words
		$pattern = '/[^\s\t]{'.$maxLen.'}[^\s\.\,\+\-\_]+/';
		$content = preg_replace($pattern, '$0 ', $content);

		//
		$pattern = '/\w{2,4}\:\/\/[^\s\"]+/';
		$content = preg_replace($pattern, '<a href="$0" title="" target="_blank">$0</a>', $content);

		//search
		$pattern = '/\#([a-zA-Z0-9_-]+)/';
		$content = preg_replace($pattern, '<a href="https://twitter.com/#%21/search/%23$1" title="" target="_blank">$0</a>', $content);

		//user
		$pattern = '/\@([a-zA-Z0-9_-]+)/';
		$content = preg_replace($pattern, '<a href="https://twitter.com/#!/$1" title="" target="_blank">$0</a>', $content);

		return $content;
	}
