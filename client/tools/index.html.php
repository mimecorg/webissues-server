<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<dl>
<dt><?php echo $this->imageAndTextLink( '/client/tools/gotoitem.php', '/common/images/edit-goto-16.png', $this->tr( 'Go To Item' ) ) ?></dt>
<dd><?php echo $this->tr( 'Go to item with given identifier.' ) ?></dd>
<?php if ( $isAuthenticated ): ?>
<dt><?php echo $this->imageAndTextLink( '/client/tools/password.php', '/common/images/edit-password-16.png', $this->tr( 'Change Password' ) ) ?></dt>
<dd><?php echo $this->tr( 'Change your password.' ) ?></dd>
<dt><?php echo $this->imageAndTextLink( '/client/tools/preferences.php', '/common/images/preferences-16.png', $this->tr( 'User Preferences' ) ) ?></dt>
<dd><?php echo $this->tr( 'Configure your user preferences.' ) ?></dd>
<?php endif ?>
<dt><?php echo $this->imageAndTextLink( '/client/tools/about.php', '/common/images/about-16.png', $this->tr( 'About WebIssues' ) ) ?></dt>
<dd><?php echo $this->tr( 'Show information about WebIssues.' ) ?></dd>
</dl>
