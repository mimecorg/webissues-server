<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<p><?php echo $this->tr( 'Enter the new name of type <strong>%1</strong>.', null, $type[ 'type_name' ] ) ?></p>

<?php $form->renderFormOpen(); ?>

<?php $form->renderText( $this->tr( 'Name:' ), 'typeName', array( 'size' => 40 ) ); ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ); ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ); ?>
</div>

<?php $form->renderFormClose() ?>
