<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<?php if ( $canPublish ): ?>
<p><?php echo $this->tr( 'Are you sure you want to convert your personal view <strong>%1</strong> to a public view?', null, $oldView[ 'view_name' ] ) ?></p>
<?php else: ?>
<p class="error"><?php echo $this->tr( 'View <strong>%1</strong> cannot be published.', null, $oldView[ 'view_name' ] ) ?></p>
<?php endif ?>

<?php $form->renderFormOpen(); ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ); ?>
<?php if ( $canPublish ): ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ); ?>
<?php endif ?>
</div>

<?php $form->renderFormClose() ?>
