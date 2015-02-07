<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<p><?php echo $this->tr( 'Are you sure you want to move project <strong>%1</strong> to the archive?', null, $project[ 'project_name' ] ) ?></p>

<p><?php echo $this->tr( 'You can unarchive the project later by going to the Projects Archive in the Administration Panel.' ) ?></p>

<?php $form->renderFormOpen(); ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ); ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ); ?>
</div>

<?php $form->renderFormClose() ?>
