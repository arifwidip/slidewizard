<?php foreach( $themes as $theme ) : ?>
  <?php 
    $is_selected = ( $theme['slug'] == $slidewizard['themes'] ) ? 'active' : ''; 
    $is_checked = ( $theme['slug'] == $slidewizard['themes'] ) ? 'checked="checked"' : ''; 
  ?>
  
  <label class="themes <?php echo $is_selected; ?> tooltips" title="<?php echo $theme['meta']['description']; ?>">
    <span class="themes-thumb">
      <img src="<?php echo $theme['thumbnail'];?>" alt="<?php echo $theme['meta']['name']; ?>">
    </span>
    <span class="themes-title"><?php echo $theme['meta']['name']; ?></span>
    <input type="radio" name="themes" value="<?php echo $theme['slug'];?>" <?php echo $is_checked; ?>>
  </label>
<?php endforeach; ?>