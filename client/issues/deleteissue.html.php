<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<p><?php echo $this->tr( 'Are you sure you want to delete issue <strong>%1</strong>?', null, $issue[ 'issue_name' ] ) ?></p>

<p class="warning"><?php echo $this->tr( '<strong>Warning:</strong> The entire issue history will be permanently deleted.' ) ?></p>

<?php $form->renderFormOpen(); ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ); ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ); ?>
</div>

<?php $form->renderFormClose() ?>
