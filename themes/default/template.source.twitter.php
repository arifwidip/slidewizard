<?php $target = ( $target != 'same_window' ) ? '_blank' : ''; ?>

<div class="slide-image">
  <?php if( $image ) : ?>
    <img src="<?php echo $image; ?>">
  <?php endif; ?>
</div>

<div class="slide-description-wrapper">
  
  <div class="slide-title">
    <?php echo $content; ?>
  </div>
  
  <div class="slide-meta">
    <img src="<?php echo $author_avatar; ?>">
    
    <?php if( $slidewizard['options']['link_avatar'] && !empty($author_url) ) : ?>
      <a class="slide-author-name" href="<?php echo $author_url; ?>"><?php echo $author_name; ?></a>
    <?php else : ?>
      <span class="slide-author-name"><?php echo $author_name; ?></span>
    <?php endif; ?>
    
    <a href="<?php $permalink; ?>" class="slide-date"><?php echo $created_at; ?></a>
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
    <p class="slide-text">
      
    </p>
  </div>
  
</div>