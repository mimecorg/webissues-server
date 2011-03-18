<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<p><?php echo $this->tr( 'Create a new folder in project <strong>%1</strong>.', null, $project[ 'project_name' ] ) ?></p>

<?php $form->renderFormOpen(); ?>

<?php if ( !empty( $issueTypes ) ): ?>

<?php $form->renderText( $this->tr( 'Name:' ), 'folderName', array( 'size' => 40 ) ); ?>

<?php $form->renderSelect( $this->tr( 'Type:' ), 'issueType', $issueTypes, array( 'style' => 'width: 15em;' ) ) ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ); ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ); ?>
</div>

<?php else: ?>

<p class="error"><?php echo $this->tr( 'There are no available issue types to use.' ) ?></p>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'Close' ), 'close' ); ?>
</div>

<?php endif ?>
