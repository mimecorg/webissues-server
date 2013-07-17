<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<?php $form->renderFormOpen() ?>

<?php if ( !empty( $noImap ) ): ?>

<p class="error"><?php echo $this->tr( 'Inbox settings are not avaiable because the \'IMAP\' extension is missing or disabled.' ) ?></p>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ) ?>
</div>

<?php else: ?>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'General' ) ?></legend>

<p><?php echo $this->tr( 'Method of receiving emails:' ) ?></p>
<?php $form->renderRadioGroup( 'inboxEngine', $engineOptions ) ?>

<?php $form->renderText( $this->tr( 'Inbox email address:' ), 'inboxEmail', array( 'size' => 40 )  ) ?>

<p><?php echo $this->tr( 'If receiving emails is enabled, make sure that the cron job is running.' ) ?></p>

</fieldset>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'Inbox Server' ) ?></legend>

<p><?php echo $this->tr( 'Fill the information below to configure the IMAP or POP3 server.' ) ?></p>

<?php $form->renderText( $this->tr( 'Server name:' ), 'inboxServer', array( 'size' => 40 )  ) ?>
<?php $form->renderText( $this->tr( 'Port number:' ), 'inboxPort', array( 'size' => 40 )  ) ?>
<?php $form->renderSelect( $this->tr( 'Encryption mode:' ), 'inboxEncryption', $encryptionOptions, array( 'style' => 'width: 10em;' ) ) ?>
<?php $form->renderText( $this->tr( 'User name:' ), 'inboxUser', array( 'size' => 40 )  ) ?>
<?php $form->renderPassword( $this->tr( 'Password:' ), 'inboxPassword', array( 'size' => 40 )  ) ?>
<?php $form->renderText( $this->tr( 'Mailbox name:' ), 'inboxMailbox', array( 'size' => 40 )  ) ?>

<?php $form->renderCheckBox( $this->tr( 'Do not validate server certificate' ), 'inboxNoValidate' ) ?>
<?php $form->renderCheckBox( $this->tr( 'Leave processed messages on the server' ), 'inboxLeaveMessages' ) ?>

<?php $form->renderError( 'testConnection' ) ?>

<?php if ( !empty( $testSuccessful ) ): ?>
<p><?php echo $this->tr( 'Connection to the inbox server was successful.' ) ?></p>
<?php endif ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'Test Connection' ), 'test' ) ?>
</div>

</fieldset>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'External Users' ) ?></legend>

<p><?php echo $this->tr( 'This option enables receiving emails from addresses which are not assigned to any user in the system.' ) ?></p>

<?php $form->renderCheckBox( $this->tr( 'Accept messages from external users' ), 'inboxAllowExternal' ) ?>
<?php $form->renderSelect( $this->tr( 'Robot user account:' ), 'inboxRobot', $users, array( 'style' => 'width: 40em;' ) ) ?>

</fieldset>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'Folder Mapping' ) ?></legend>

<p><?php echo $this->tr( 'This option enables mapping the inbox email address extensions to project and folder name.' ) ?></p>

<?php $form->renderCheckBox( $this->tr( 'Map address extensions to project and folder' ), 'inboxMapFolder' ) ?>
<?php $form->renderSelect( $this->tr( 'Default folder:' ), 'inboxDefaultFolder', $folders, array( 'style' => 'width: 40em;' ) ) ?>

</fieldset>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'Sending Emails' ) ?></legend>

<p><?php echo $this->tr( 'These options enable responses and automatic subscriptions for issues created through the inbox. Sending emails needs to be enabled.' ) ?></p>

<?php $form->renderCheckBox( $this->tr( 'Send responses when issues are created' ), 'inboxRespond' ) ?>
<?php $form->renderCheckBox( $this->tr( 'Subscribe senders to created issues' ), 'inboxSubscribe' ) ?>

</fieldset>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ) ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ) ?>
</div>

<?php endif ?>

<?php $form->renderFormClose() ?>
