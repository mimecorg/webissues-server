<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title><?php echo $pageTitle ?> | <?php echo $siteName ?></title>
  <link rel="shortcut icon" href="<?php echo $this->url( $icon ) ?>" type="image/vnd.microsoft.icon" /> 
<?php foreach ( $cssFiles as $file ): ?>
  <link rel="stylesheet" href="<?php echo $this->url( $file )?>" type="text/css" />
<?php endforeach ?>
<?php foreach ( $cssConditional as $condition => $file ): ?>
  <!--[if <?php echo $condition ?>]><link rel="stylesheet" href="<?php echo $this->url( $file )?>" type="text/css" /><![endif]-->
<?php endforeach ?>
<?php foreach ( $scriptFiles as $file ): ?>
  <script type="text/javascript" src="<?php echo $this->url( $file )?>"></script>
<?php endforeach ?>
<?php if ( !empty( $inlineCode ) ): ?>
  <script type="text/javascript">
<?php echo $inlineCode ?>
  </script>
<?php endif ?>  
</head>
<body>

<div id="header">
  <img id="site-logo" src="<?php echo $this->url( '/common/images/webissues-logo.png' )?>" alt="WebIssues" />
  <div id="site-name"><?php echo $siteName ?></div>
  <div id="header-right">
<?php
    if ( $isAuthenticated ):
        if ( $isAdministrator ):
            echo $this->link( '/admin/index.php', $this->tr( 'Administration Panel' ) ) . ' | ';
        endif;
        echo $this->link( '/client/index.php', $this->tr( 'Web Client' ) ) . ' | ';
    endif;
    echo $this->tr( 'WebIssues %1', null, WI_VERSION ) . ' | ';
    echo $this->link( $manualUrl, $this->tr( 'Manual' ), array( 'target' => '_blank' ) );
?>
  </div>
  <div id="infobar-left">
<?php
    foreach ( $breadcrumbs as $breadcrumb ):
        echo $this->link( $breadcrumb[ 'url' ], $breadcrumb[ 'name' ] ) . ' &raquo; ';
    endforeach;
    echo $pageTitle;
?>
  </div>
  <div id="infobar-right">
<?php
    if ( $isAuthenticated ):
        echo $this->tr( 'Logged in as: %1', null, $userName ) . ' | ';
        echo $this->link( '/client/tools/index.php', $this->tr( 'Tools' ) ) . ' | ';
        echo $this->link( '/index.php', $this->tr( 'Log Out' ) );
    endif;
?>
  </div>
</div>

<div id="body">
<?php $this->insertContent() ?>
</div>

<?php if ( !empty( $errors ) ): ?>
<div class="debug">
<ul>
<?php foreach ( $errors as $error ): ?>
<li><?php echo nl2br( $error ) ?></li>
<?php endforeach ?>
</ul>
</div>
<?php endif ?>

</body>
</html>
