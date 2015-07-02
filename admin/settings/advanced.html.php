<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<?php $form->renderFormOpen() ?>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'Limits' ) ?></legend>

<p><?php echo $this->tr( 'The maximum length (in characters) of comments and descriptions and the maximum size (in bytes) of attached files that are allowed.' ) ?></p>

<?php $form->renderSelect( $this->tr( 'Maximum text length:' ), 'commentMaxLength', $commentOptions, array( 'style' => 'width: 10em;' ) ) ?>
<?php $form->renderSelect( $this->tr( 'Maximum attachment size:' ), 'fileMaxSize', $fileOptions, array( 'style' => 'width: 10em;' ) ) ?>

</fieldset>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'Attachment Storage' ) ?></legend>

<p><?php echo $this->tr( 'The maximum size (in bytes) of files which will be stored in the database. Larger files will be stored in the file system.' ) ?></p>

<?php $form->renderSelect( $this->tr( 'Maximum database storage size:' ), 'fileDbMaxSize', $fileDbOptions, array( 'style' => 'width: 10em;' ) ) ?>

</fieldset>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'Maximum Lifetime' ) ?></legend>

<p><?php echo $this->tr( 'The maximum time after which inactive sessions and events in the event log are deleted.' ) ?></p>

<?php $form->renderSelect( $this->tr( 'Session lifetime:' ), 'sessionMaxLifetime', $sessionOptions, array( 'style' => 'width: 10em;' ) ) ?>
<?php $form->renderSelect( $this->tr( 'Event log lifetime:' ), 'logMaxLifetime', $logOptions, array( 'style' => 'width: 10em;' ) ) ?>
<?php $form->renderSelect( $this->tr( 'Inactive user registration request lifetime:' ), 'registerMaxLifetime', $registerOptions, array( 'style' => 'width: 10em;' ) ) ?>

</fieldset>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'Garbage Collection' ) ?></legend>

<p><?php echo $this->tr( 'The probability of garbage collection after each request. If the cron job is used for garbage collection, make sure that it is running.' ) ?></p>

<?php $form->renderSelect( $this->tr( 'GC probability:' ), 'gcDivisor', $gcOptions, array( 'style' => 'width: 20em;' ) ) ?>

</fieldset>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ) ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ) ?>
</div>

<?php $form->renderFormClose() ?>
