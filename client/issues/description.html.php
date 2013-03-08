<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<?php if ( empty( $exists ) ): ?>
<p><?php echo $this->tr( 'Add description to issue <strong>%1</strong>.', null, $issueName ) ?></p>
<?php else: ?>
<p><?php echo $this->tr( 'Edit description of issue <strong>%1</strong>.', null, $issueName ) ?></p>
<?php endif ?>

<?php $form->renderFormOpen(); ?>

<?php $form->renderTextArea( $this->tr( 'Description:' ), 'descriptionText', array( 'cols' => 120, 'rows' => 20 ) ); ?>

<div class="comment-text preview" id="descriptionPreview" style="display: none;"></div>

<?php $form->renderSelect( $this->tr( 'Text format:' ), 'format', $formatOptions, array( 'style' => 'width: 25em;' ) ) ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ); ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ); ?>
</div>

<?php $form->renderFormClose() ?>
