<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<?php $form->renderFormOpen() ?>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'General' ) ?></legend>

<p><?php echo $this->tr( 'Method of sending emails:' ) ?></p>
<?php $form->renderRadioGroup( 'emailEngine', $engineOptions ) ?>

<?php $form->renderText( $this->tr( 'Email address of the server:' ), 'emailFrom', array( 'size' => 40 )  ) ?>

<p><?php echo $this->tr( 'If sending emails is enabled, make sure that the cron job is running.' ) ?></p>

</fieldset>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'SMTP Server' ) ?></legend>

<p><?php echo $this->tr( 'Fill the information below when using a custom SMTP server.' ) ?></p>

<?php $form->renderText( $this->tr( 'Server name:' ), 'smtpServer', array( 'size' => 40 )  ) ?>
<?php $form->renderText( $this->tr( 'Port number:' ), 'smtpPort', array( 'size' => 40 )  ) ?>
<?php $form->renderSelect( $this->tr( 'Encryption mode:' ), 'smtpEncryption', $encryptionOptions, array( 'style' => 'width: 10em;' ) ) ?>
<?php $form->renderText( $this->tr( 'User name:' ), 'smtpUser', array( 'size' => 40 )  ) ?>
<?php $form->renderPassword( $this->tr( 'Password:' ), 'smtpPassword', array( 'size' => 40 )  ) ?>

<?php $form->renderError( 'testConnection' ) ?>

<?php if ( !empty( $testSuccessful ) ): ?>
<p><?php echo $this->tr( 'Connection to the SMTP server was successful.' ) ?></p>
<?php endif ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'Test Connection' ), 'test' ) ?>
</div>

</fieldset>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'Hyperlinks' ) ?></legend>

<p><?php echo $this->tr( 'Enter the base URL of the server, with the trailing slash, to enable hyperlinks in emails.' ) ?></p>

<?php $form->renderText( $this->tr( 'Server URL:' ), 'baseUrl', array( 'size' => 60 )  ) ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'Detect' ), 'detect' ) ?>
</div>

</fieldset>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ) ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ) ?>
</div>

<?php $form->renderFormClose() ?>
