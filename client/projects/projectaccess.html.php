<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<p><?php echo $this->tr( 'Change global access for project <strong>%1</strong>.', null, $project[ 'project_name' ] ) ?></p>

<?php $form->renderFormOpen(); ?>

<?php $form->renderRadioGroup( 'isPublic', $accessLevels ) ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ); ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ); ?>
</div>

<?php $form->renderFormClose() ?>
