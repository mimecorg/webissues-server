<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<p><?php echo $this->tr( 'You are about to unsubscribe from issue <strong>%1</strong>.', null, $issue[ 'issue_name' ] ) ?></p>

<p><?php echo $this->tr( 'You will no longer receive email notifications for this issue.' ) ?></p>

<?php $form->renderFormOpen(); ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ); ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ); ?>
</div>

<?php $form->renderFormClose() ?>
