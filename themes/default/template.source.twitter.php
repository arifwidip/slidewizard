<?php $target = ( $target != 'same_window' ) ? '_blank' : ''; ?>

<div class="slide-image">
  <?php if( isset($image) && $image ) : ?>
    <img src="<?php echo $image; ?>">
  <?php endif; ?>
</div>
  
<div class="slide-description-wrapper">
  
  <?php if( isset( $slidewizard['options']['show_avatar'] ) && $author_avatar ) : ?>
    <div class="twitter-avatar">
      <img src="<?php echo $author_avatar; ?>">
      <?php if( isset( $is_retweet ) && $is_retweet ) : ?>
        <img class="retweeter_avatar" src="<?php echo $retweeter_avatar; ?>">
      <?php endif; ?>
    </div>
  <?php endif; ?>
  
  <div class="slide-inner-wrapper">
    <div class="slide-author-name">
      <a class="slide-author-name" href="<?php echo $author_url; ?>" target="<?php echo $target; ?>"><?php echo $author_name; ?></a>
    </div>
  
    <div class="slide-title">
      <?php echo $content; ?>
    </div>

    <div class="slide-meta">
      <?php 
      if( isset( $in_reply_to_screen_name ) ) {
        $reply_status = sprintf('<a href="http://twitter.com/%s">in reply to %s</a>', $in_reply_to_screen_name, $in_reply_to_screen_name);
      } else 
        if( isset( $is_retweet ) && $is_retweet ) {
          $reply_status = sprintf('<a href="%s">retweeted by %s</a>', $retweeter_url, $retweeter_username);
        } else {
          $reply_status = '';
        }
      ?>
      <?php printf('<a href="%s" class="slide-date">%s</a> %s from %s', $permalink, $created_at, $reply_status, $source_app); ?>
    </div>
  </div>
  
</div>
