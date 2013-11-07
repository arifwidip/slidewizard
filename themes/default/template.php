<?php $target = ( $target != 'same_window' ) ? '_blank' : ''; ?>

<div class="slide-image">
  <?php if( isset($image) && $image ) : ?>
    <img src="<?php echo $image; ?>">
  <?php endif; ?>
</div>

<div class="slide-description-wrapper">
  
  <div class="slide-title">
    <?php if( $slidewizard['options']['link_title'] ) : ?>
      <a href="<?php echo $permalink;?>" title="<?php echo $title; ?>" target="<?php echo $target; ?>"><?php echo $title; ?></a>
    <?php else : ?>
      <?php echo $title; ?>
    <?php endif; ?>
  </div>
  
  <div class="slide-meta">
    <?php echo $author_avatar; ?>
    
    <?php if( $slidewizard['options']['link_avatar'] && !empty($author_url) ) : ?>
      <a class="slide-author-name" href="<?php echo $author_url; ?>"><?php echo $author_name; ?></a>
    <?php else : ?>
      <span class="slide-author-name"><?php echo $author_name; ?></span>
    <?php endif; ?>
    
    <span class="slide-date"><?php echo $created_at; ?><span>
  </div>
  
  <?php if( isset($slidewizard['options']['author_avatar']) && $slidewizard['options']['author_avatar'] ) : ?>
    <div class="slide-meta">
      <div class="slide-author">
        <?php echo $author_avatar; ?>
        <?php if( $slidewizard['options']['link_avatar'] && $author_url ) : ?>
          <a href="<?php echo $author_url;?>"><?php echo $author_name; ?></a>
        <?php else : ?>
          <span><?php echo $author_name; ?></span>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
  
  <div class="slide-description">
    <div class="slide-text">
      <?php echo $content; ?>
      
      <?php if( $slidewizard['options']['show_readmore'] ) : ?>
        <a href="<?php echo $permalink;?>" title="<?php echo $title; ?>" target="<?php echo $target; ?>" class="slide-more-link"><?php _e('Read More', $this->namespace);?></a>
      <?php endif; ?>
    </div>
  </div>
  
</div>