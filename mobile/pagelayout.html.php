<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo $pageTitle ?> | <?php echo $siteName ?></title>
  <link rel="shortcut icon" href="<?php echo $this->url( $icon ) ?>" type="image/vnd.microsoft.icon"> 
<?php foreach ( $cssFiles as $file ): ?>
  <link rel="stylesheet" href="<?php echo $this->url( $file ) ?>" type="text/css">
<?php endforeach ?>
<?php foreach ( $scriptFiles as $file ): ?>
  <script type="text/javascript" src="<?php echo $this->url( $file ) ?>"></script>
<?php endforeach ?>
<?php if ( !empty( $inlineCode ) ): ?>
  <script type="text/javascript">
<?php echo $inlineCode ?>
  </script>
<?php endif ?>  
</head>
<body>

<div id="header">
<?php if ( $isAuthenticated || $isAnonymous ): ?>
<a href="<?php echo $this->url( '/mobile/client/index.php' ) ?>">
<?php endif ?>
  <img id="site-logo" src="<?php echo $this->url( '/common/images/webissues-logo.png' )?>" alt="WebIssues">
  <div id="site-name"><?php echo $siteName ?></div>
<?php if ( $isAuthenticated || $isAnonymous ): ?>
</a>
<?php endif ?>
  <button class="hamburger">&equiv;</button>
  <button class="cross">&times;</button>
</div>
<div class="menu">
  <ul>
<?php
    if ( $isAuthenticated ):
        echo '<li>' . $this->tr( 'Logged in as: %1', null, $userName ) . '</li>';
    elseif ( $canLogIn ):
        echo $this->linkItem( $loginPageUrl, $this->tr( 'Log In' ) );
    endif;
    if ( $isAnonymous && !$canLogIn ):
        echo $this->imageAndTextLinkItem( '/mobile/client/index.php', '/common/images/user-disabled-16.png', $this->tr( 'Anonymous Access' ) );
    endif;
    if ( $canRegister ):
        echo $this->imageAndTextLinkItem( '/mobile/register.php', '/common/images/user-new-16.png', $this->tr( 'Register' ) );
    endif;
    if ( $isAuthenticated || $canLogIn ):
        echo $this->imageAndTextLinkItem( '/mobile/client/tools/gotoitem.php', '/common/images/edit-goto-16.png', $this->tr( 'Go To Item' ) );
    endif;
    if ( $isAuthenticated ):
        echo $this->imageAndTextLinkItem( '/mobile/client/tools/password.php', '/common/images/edit-password-16.png', $this->tr( 'Change Password' ) );
        echo $this->imageAndTextLinkItem( '/mobile/client/tools/preferences.php', '/common/images/preferences-16.png', $this->tr( 'User Preferences' ) );
        echo $this->linkItem( '/mobile/index.php', $this->tr( 'Log Out' ) );
    endif;
    echo '<li>' . $this->tr( 'WebIssues %1', null, WI_VERSION ) . '</li>';
    if ( $isAuthenticated || $canLogIn ):
        echo $this->imageAndTextLinkItem( '/mobile/client/tools/about.php', '/common/images/about-16.png', $this->tr( 'About WebIssues' ) );
    endif;
    echo $this->linkItem( $fullVersionUrl, $this->tr( 'Full Version' ), array( 'class' => 'switch-client' ) );
    echo $this->linkItem( $manualUrl, $this->tr( 'Manual' ) );
?>
  </ul>
</div>

<div id="body">
<?php $this->insertContent() ?>

<?php if ( !empty( $errors ) ): ?>
<div class="debug">
<ul>
<?php foreach ( $errors as $error ): ?>
<li><?php echo nl2br( $error ) ?></li>
<?php endforeach ?>
</ul>
</div>
<?php endif ?>
</div>

</body>
</html>
