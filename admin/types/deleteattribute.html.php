<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<p><?php echo $this->tr( 'Are you sure you want to delete attribute <strong>%1</strong>?', null, $attribute[ 'attr_name' ] ) ?></p>

<?php if ( $warning ): ?>
<p class="warning"><?php echo $this->tr( '<strong>Warning:</strong> All current and historical values of this attribute will be deleted.' ) ?></p>
<?php endif ?>

<?php $form->renderFormOpen(); ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ); ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ); ?>
</div>

<?php $form->renderFormClose() ?>
