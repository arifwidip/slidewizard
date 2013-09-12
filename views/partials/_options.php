<ul id="slidewizard-option-tabs" class="nav nav-tabs">

  <li class="active"><a href="#themes" data-toggle="tab">Themes</a></li>
  <li><a href="#setup" data-toggle="tab">Setup</a></li>
  
  <?php 
  $i=0; 
  foreach( $options as $key => $value ) : 
    if( $key !== "Setup" ) : ?>
      <li>
        <a href="#<?php echo strtolower($key)?>" data-toggle="tab"><?php echo $key; ?></a>
      </li>
  <?php endif; $i++; endforeach; ?>
</ul>

<div class="tab-content">
  <div id="themes" class="tab-pane fade themes-list clearfix in active">
    <?php include ( SLIDEWIZARD_PLUGIN_DIR . '/views/partials/_options-themes.php' ); ?>
  </div>
  
  <?php include( SLIDEWIZARD_PLUGIN_DIR . '/views/partials/_options-setup.php' ); ?>

  <?php
    unset( $options['Setup'] );
    echo slidewizard_options_builder( $options, $slidewizard['options'] );
  ?>
</div>