<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<p><?php echo $this->tr( 'Modify alert <strong>%1</strong>.', null, $alert[ 'view_name' ] ) ?></p>

<?php $form->renderFormOpen(); ?>

<p><?php echo $this->tr( 'Send the following type of emails for this alert:' ) ?></p>
<?php $form->renderRadioGroup( 'alertEmail', $emailTypes ) ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ); ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ); ?>
</div>

<?php $form->renderFormClose() ?>
