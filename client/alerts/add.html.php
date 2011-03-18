<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<p><?php echo $this->tr( 'Create a new alert for folder <strong>%1</strong>.', null, $folder[ 'folder_name' ] ) ?></p>

<?php $form->renderFormOpen(); ?>

<?php if ( !empty( $viewOptions ) ): ?>

<?php $form->renderSelect( $this->tr( 'View:' ), 'viewId', $viewOptions, array( 'style' => 'width: 15em;' ) ) ?>

<?php if ( $emailEngine ): ?>
<p><?php echo $this->tr( 'Send the following type of emails for this alert:' ) ?></p>
<?php $form->renderRadioGroup( 'alertEmail', $emailTypes ) ?>
<?php endif ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ); ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ); ?>
</div>

<?php else: ?>

<p class="error"><?php echo $this->tr( 'There are no more available views to use.' ) ?></p>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'Close' ), 'close' ); ?>
</div>

<?php endif ?>

<?php $form->renderFormClose() ?>
