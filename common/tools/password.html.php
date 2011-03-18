<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<?php if ( $isSelf ): ?>
<p><?php echo $this->tr( 'Enter your new password.' ) ?></p>
<?php else: ?>
<p><?php echo $this->tr( 'Enter the new password for user <strong>%1</strong>.', null, $user[ 'user_name' ] ) ?></p>
<?php endif ?>

<?php $form->renderFormOpen() ?>

<?php if ( $isSelf ): ?>
<?php $form->renderPassword( $this->tr( 'Current password:' ), 'password', array( 'size' => 40 ) ) ?>
<?php endif ?>

<?php $form->renderPassword( $this->tr( 'New password:' ), 'newPassword', array( 'size' => 40 ) ) ?>
<?php $form->renderPassword( $this->tr( 'Confirm password:' ), 'newPasswordConfirm', array( 'size' => 40 ) ) ?>

<?php if ( !$isSelf ): ?>
<?php $form->renderCheckBox( $this->tr( 'User must change password at next logon.' ), 'isTemp' ); ?>
<?php endif ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ) ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ) ?>
</div>

<?php $form->renderFormOpen() ?>
