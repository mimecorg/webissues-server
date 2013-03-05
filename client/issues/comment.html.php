<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<?php if ( !empty( $issueName ) ): ?>
<p><?php echo $this->tr( 'Add comment to issue <strong>%1</strong>.', null, $issueName ) ?></p>
<?php elseif ( !empty( $commentId ) ): ?>
<p><?php echo $this->tr( 'Edit comment <strong>%1</strong>.', null, $commentId ) ?></p>
<?php endif ?>

<?php $form->renderFormOpen(); ?>

<?php $form->renderTextArea( $this->tr( 'Comment:' ), 'commentText', array( 'cols' => 120, 'rows' => 20 ) ); ?>

<div class="comment-text preview" id="commentPreview" style="display: none;"></div>

<?php $form->renderSelect( $this->tr( 'Text format:' ), 'format', $formatOptions, array( 'style' => 'width: 25em;' ) ) ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ); ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ); ?>
</div>

<?php $form->renderFormClose() ?>
