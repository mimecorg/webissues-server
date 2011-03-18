<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<p><?php echo $this->tr( 'Move folder <strong>%1</strong> to another project.', null, $folder[ 'folder_name' ] ) ?></p>

<?php $form->renderFormOpen(); ?>

<?php $form->renderSelect( $this->tr( 'Project:' ), 'project', $projects, array( 'style' => 'width: 15em;' ) ) ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ); ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ); ?>
</div>

<?php $form->renderFormClose() ?>
