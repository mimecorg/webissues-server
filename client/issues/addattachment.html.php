<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<p><?php echo $this->tr( 'Add an attachment to issue <strong>%1</strong>.', null, $issue[ 'issue_name' ] ) ?></p>

<?php $form->renderFormOpen( null, array( 'enctype' => 'multipart/form-data' ) ); ?>

<?php $form->renderFile( $this->tr( 'File:' ), 'file', array( 'size' => 60 ) ); ?>

<?php $form->renderText( $this->tr( 'Description:' ), 'description', array( 'size' => 80 ) ); ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ); ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ); ?>
</div>

<?php $form->renderFormClose() ?>
