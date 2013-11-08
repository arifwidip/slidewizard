<?php $target = ( $target != 'same_window' ) ? '_blank' : ''; ?>

<?php if( isset($image) && $image ) : ?>
  <div class="slide-image" style="background-image: url(<?php echo $image; ?>); width: <?php echo $dimensions['width']; ?>px; height: <?php echo $dimensions['height']; ?>px">
    <img src="<?php echo $image; ?>">
  </div>
<?php endif; ?>

<div class="slide-description-wrapper">

  <div class="slide-title">
    <?php if( $slidewizard['options']['link_title'] ) : ?>
      <a href="<?php echo $permalink;?>" title="<?php echo $title; ?>" target="<?php echo $target; ?>"><?php echo $title; ?></a>
    <?php else : ?>
      <?php echo $title; ?>
    <?php endif; ?>
  </div>

  <div class="slide-description">
    <div class="slide-text">
      <?php echo $content; ?>
      <span class="slide-date"><?php echo $created_at; ?><span>
      
      <?php if( $slidewizard['options']['show_readmore'] ) : ?>
        <a href="<?php echo $permalink;?>" title="<?php echo $title; ?>" target="<?php echo $target; ?>" class="slide-more-link"><?php _e('Read More', $this->namespace);?></a>
      <?php endif; ?>
    </div>
  </div>

</div>