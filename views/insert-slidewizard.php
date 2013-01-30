<?php
/**
 * PHP Template for showing list of create SlideWizard
 * 
 */
?>

<!doctype html>
<html <?php language_attributes(); ?>>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title><?php echo __('Insert your SlideWizard', $namespace); ?></title>
    
    <link rel="stylesheet" type="text/css" href="<?php echo SLIDEWIZARD_PLUGIN_URL ?>/css/bootstrap.dev.css">
    <link rel="stylesheet" type="text/css" href="<?php echo SLIDEWIZARD_PLUGIN_URL ?>/css/slidewizard-admin-style.css">
    
    <?php
      foreach( $scripts as $script ) {
        $src = $wp_scripts->registered[$script]->src;
        if ( !preg_match( '|^https?://|', $src ) && !( $content_url && 0 === strpos( $src, $content_url ) ) ) {
          $src = $base_url . $src;
        }
          
        echo '<script type="text/javascript" src="' . $src . ( strpos( $src, "?" ) !== false ? "&" : "?" ) . "v=" . $wp_scripts->registered[$script]->ver . '"></script>';
      }
    ?>
    <style>
    body {
      margin: 0;
      padding: 51px 0 0 0;
      font-size: 14px;
      line-height: 20px;
      color: #333;
      font-family: sans-serif;
      background: #fafafa;
    }
    .hidden {
      display: none
    }
    .navbar-inner {
      -webkit-border-radius: 0;
      -moz-border-radius: 0;
      border-radius: 0;
    }
    .container {
      width: auto !important;
      padding: 5px 15px;
    }
    a {
      text-decoration: none
    }
    .modal-footer {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
    }
    ul {
      list-style: none
    }
    .slidewizard-main {
      background: none;
      border: none;
    }
    .slidewizard-action {
      display: none !important
    }
    </style>  
  </head>
  
  <body>
    
    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <h4><?php echo __('Choose a SlideWizard to insert:', $namespace); ?></h4>
        </div>
      </div>
    </div>
    
    <div class="slidewizard-main">
      <?php include ( SLIDEWIZARD_PLUGIN_DIR ) . '/views/partials/_slides-list.php'; ?>
    </div>
    
    <!-- <div class="modal-footer">
      <a href="#insert" class="btn btn-primary"><?php echo __('Insert', $namespace); ?></a>
    </div> -->
  </body>
</html>