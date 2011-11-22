<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<p><?php echo $this->tr( 'Move issue <strong>%1</strong> to another folder of the same type.', null, $issue[ 'issue_name' ] ) ?></p>

<?php $form->renderFormOpen(); ?>

<?php if ( $canMove ): ?>

<?php $form->renderSelect( $this->tr( 'Folder:' ), 'folder', $folders, array( 'style' => 'width: 15em;' ) ) ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ); ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ); ?>
</div>

<?php else: ?>

<p class="error"><?php echo $this->tr( 'There are no available destination folders.' ) ?></p>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ); ?>
</div>

<?php endif ?>

<?php $form->renderFormClose() ?>
