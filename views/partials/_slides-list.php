<?php
/**
 * Template for listing all slides
 * 
 */
?>

<?php if( !empty( $slidewizards ) ): ?>
<ul class="slidewizard-list">
  <?php foreach ($slidewizards as $slidewizard) : ?>
    <li class="slidewizard-item" id="slide-<?php echo $slidewizard['id'];?>">
      
      <div class="slide-thumb">
        <img src="<?php echo slidewizard_get_source_icon_url( $slidewizard['source'][0] );?>">
      </div>
    
      <a href="<?php echo slidewizard_action('&action=edit&id=' . $slidewizard['id']);?>">
        <?php echo $slidewizard['title']; ?>
      </a>
      
      <div class="slidewizard-action">
        <div class="slidewizard-id">ID: <span><?php echo $slidewizard['id']; ?></span></div>
        <div class="slidewizard-delete tooltips slidewizard-button" title="<?php _e('Delete', $this->namespace);?>">
          <span class="nonce hidden"><?php echo wp_create_nonce( "{$this->namespace}_delete_slide" ); ?></span>
          <i class="icon-trash"></i>
        </div>
        <div class="slidewizard-use tooltips slidewizard-button" data-id="<?php echo $slidewizard['id'];?>" title="<?php _e('Use this', $this->namespace); ?> Slide">
          <i class="icon-share"></i>
        </div>
      </div>
    </li>
  <?php endforeach; ?>
</ul><!-- .slidewizard-list -->
<?php else : ?>
<h3 class="slidewizzard-default-text"> <?php _e('There is no slide here, you should create one, please click the "Create Slide" button', $this->namespace);?></h3>
<?php endif; ?>
