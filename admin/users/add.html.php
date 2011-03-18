<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<?php $form->renderFormOpen(); ?>

<?php $form->renderText( $this->tr( 'Name:' ), 'userName', array( 'size' => 40 ) ); ?>
<?php $form->renderText( $this->tr( 'Login:' ), 'login', array( 'size' => 40 ) ); ?>
<?php $form->renderPassword( $this->tr( 'Password:' ), 'password', array( 'size' => 40 ) ); ?>
<?php $form->renderPassword( $this->tr( 'Confirm password:' ), 'passwordConfirm', array( 'size' => 40 ) ); ?>
<?php $form->renderCheckBox( $this->tr( 'User must change password at next logon.' ), 'isTemp' ); ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ); ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ); ?>
</div>

<?php $form->renderFormClose() ?>
