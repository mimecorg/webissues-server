<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<?php $this->beginSlot( 'float_links' ) ?>
<?php echo $this->imageAndTextLink( '/client/tools/about.php', '/common/images/about-16.png', $this->tr( 'About WebIssues' ) ) ?>
<?php $this->endSlot() ?>

<?php $this->insertComponent( 'Admin_Info_Server' ) ?>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'Server Configuration' ) ?></legend>

<table class="info-list info-align">
<tr>
<td><?php echo $this->tr( 'Anonymous access:' ) ?></td>
<td><?php echo $anonymous ?></td>
</tr>
<tr>
<td><?php echo $this->tr( 'User registration:' ) ?></td>
<td><?php echo $register ?></td>
</tr>
<tr><td></td></tr>
<tr>
<td><?php echo $this->tr( 'Sending emails:' ) ?></td>
<td><?php echo $email ?></td>
</tr>
<?php if ( !empty( $emailFrom ) ): ?>
<tr>
<td><?php echo $this->tr( 'Outgoing address:' ) ?></td>
<td><?php echo $emailFrom ?></td>
</tr>
<?php endif ?>
<?php if ( !empty( $emailServer ) ): ?>
<tr>
<td><?php echo $this->tr( 'Outgoing server:' ) ?></td>
<td><?php echo $emailServer ?></td>
</tr>
<?php endif ?>
<tr><td></td></tr>
<tr>
<td><?php echo $this->tr( 'Email inbox:' ) ?></td>
<td><?php echo $inbox ?></td>
</tr>
<?php if ( !empty( $inboxEmail ) ): ?>
<tr>
<td><?php echo $this->tr( 'Inbox address:' ) ?></td>
<td><?php echo $inboxEmail ?></td>
</tr>
<?php endif ?>
<?php if ( !empty( $inboxServer ) ): ?>
<tr>
<td><?php echo $this->tr( 'Inbox server:' ) ?></td>
<td><?php echo $inboxServer ?></td>
</tr>
<?php endif ?>
<tr><td></td></tr>
<tr>
<td><?php echo $this->tr( 'Cron last started:' ) ?></td>
<td><?php echo $cron ?></td>
</tr>
</table>

<?php $form->renderError( 'register' ) ?>
<?php $form->renderError( 'cron' ) ?>

</fieldset>

<?php $this->insertComponent( 'Admin_Info_Database' ) ?>

<?php $this->insertComponent( 'Admin_Info_Site', $form ) ?>
