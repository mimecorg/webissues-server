<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<?php if ( $alert[ 'is_public' ] ): ?>
<p><?php echo $this->tr( 'Are you sure you want to delete public alert <strong>%1</strong>?', null, $alert[ 'view_name' ] ) ?></p>
<?php else: ?>
<p><?php echo $this->tr( 'Are you sure you want to delete your personal alert <strong>%1</strong>?', null, $alert[ 'view_name' ] ) ?></p>
<?php endif ?>

<?php $form->renderFormOpen(); ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ); ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ); ?>
</div>

<?php $form->renderFormClose() ?>
