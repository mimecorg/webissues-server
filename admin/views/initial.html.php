<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<p><?php echo $this->tr( 'Select the initial view for type <strong>%1</strong>.', null, $type[ 'type_name' ] ) ?></p>

<?php $form->renderFormOpen(); ?>

<?php $form->renderSelect( $this->tr( 'Initial view:' ), 'initialView', $views, array( 'style' => 'width: 200px' ) ) ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ); ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ); ?>
</div>

<?php $form->renderFormClose() ?>
