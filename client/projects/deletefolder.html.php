<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<p><?php echo $this->tr( 'Are you sure you want to delete folder <strong>%1</strong>?', null, $folder[ 'folder_name' ] ) ?></p>

<?php if ( $warning ): ?>
<p class="warning"><?php echo $this->tr( '<strong>Warning:</strong> All issues in this folder will be permanently deleted.' ) ?></p>
<?php endif ?>

<?php $form->renderFormOpen(); ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ); ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ); ?>
</div>

<?php $form->renderFormClose() ?>
