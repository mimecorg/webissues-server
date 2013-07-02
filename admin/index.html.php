<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<h3>Information</h3>

<dl>
<dt><?php echo $this->imageAndTextlink( '/admin/info/index.php', '/common/images/status-info-16.png', $this->tr( 'General Information' ) ) ?></dt>
<dd><?php echo $this->tr( 'Show information about this server.' ) ?></dd>
<dt><?php echo $this->imageAndTextlink( '/admin/events/index.php', '/common/images/view-log-16.png', $this->tr( 'Event Log' ) ) ?></dt>
<dd><?php echo $this->tr( 'Show recent entries from the event log.' ) ?></dd>
</dl>

<h3>Configuration</h3>

<dl>
<dt><?php echo $this->imageAndTextlink( '/admin/settings/index.php', '/common/images/configure-16.png', $this->tr( 'Server Settings' ) ) ?></dt>
<dd><?php echo $this->tr( 'Configure default language and other settings for this server.' ) ?></dd>
<dt><?php echo $this->imageAndTextlink( '/admin/settings/mail.php', '/common/images/alert-16.png', $this->tr( 'Email Settings' ) ) ?></dt>
<dd><?php echo $this->tr( 'Configure notification email settings.' ) ?></dd>
<dt><?php echo $this->imageAndTextlink( '/admin/settings/inbox.php', '/common/images/inbox-16.png', $this->tr( 'Inbox Settings' ) ) ?></dt>
<dd><?php echo $this->tr( 'Configure email inbox settings.' ) ?></dd>
</dl>

<h3>Management</h3>

<dl>
<dt><?php echo $this->imageAndTextlink( '/admin/users/index.php', '/common/images/view-users-16.png', $this->tr( 'User Accounts' ) ) ?></dt>
<dd><?php echo $this->tr( 'Create and edit user accounts.' ) ?></dd>
<?php if ( $canRegister ): ?>
<dt><?php echo $this->imageAndTextlink( '/admin/register/index.php', '/common/images/user-new-16.png', $this->tr( 'Registration Requests' ) ) ?></dt>
<dd><?php echo $this->tr( 'Approve and reject pending user registration requests.' ) ?></dd>
<?php endif ?>
<dt><?php echo $this->imageAndTextlink( '/admin/types/index.php', '/common/images/view-types-16.png', $this->tr( 'Issue Types' ) ) ?></dt>
<dd><?php echo $this->tr( 'Create and edit issue types.' ) ?></dd>
</dl>
