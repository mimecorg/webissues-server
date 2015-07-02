<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<?php $form->renderFormOpen() ?>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'Anonymous Access' ) ?></legend>

<p><?php echo $this->tr( 'This option enables read-only access to public projects without logging in.' ) ?></p>

<?php $form->renderCheckBox( $this->tr( 'Enable anonymous access' ), 'anonymousAccess' ) ?>

</fieldset>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'User Registration' ) ?></legend>

<p><?php echo $this->tr( 'This option allows users to self-register. Sending emails needs to be enabled.' ) ?></p>

<?php $form->renderCheckBox( $this->tr( 'Enable user registration' ), 'selfRegister' ) ?>

<p><?php echo $this->tr( 'This option allows users to register without administrator\'s approval. Registered users only have access to public projects by default.' ) ?></p>

<?php $form->renderCheckBox( $this->tr( 'Enable automatic approval' ), 'registerAutoApprove' ) ?>

<p><?php echo $this->tr( 'Optionally send email notifications about pending registration requests to this address. Requires the cron job to be running.' ) ?></p>

<?php $form->renderText( $this->tr( 'Email address:' ), 'registerNotifyEmail', array( 'size' => 40 ) ) ?>

</fieldset>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ) ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ) ?>
</div>

<?php $form->renderFormClose() ?>
