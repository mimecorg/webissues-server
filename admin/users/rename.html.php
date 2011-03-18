<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<p><?php echo $this->tr( 'Enter the new name of user <strong>%1</strong>.', null, $user[ 'user_name' ] ) ?></p>

<?php $form->renderFormOpen(); ?>

<?php $form->renderText( $this->tr( 'Name:' ), 'userName', array( 'size' => 40 ) ); ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ); ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ); ?>
</div>

<?php $form->renderFormClose() ?>
