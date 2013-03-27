<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<?php $form->renderFormOpen(); ?>

<?php $form->renderText( $this->tr( 'Name:' ), 'projectName', array( 'size' => 40 ) ); ?>

<?php $form->renderTextArea( $this->tr( 'Description:' ), 'descriptionText', array( 'cols' => 120, 'rows' => 20 ) ); ?>

<div class="comment-text preview" id="descriptionPreview" style="display: none;"></div>

<?php $form->renderSelect( $this->tr( 'Text format:' ), 'format', $formatOptions, array( 'style' => 'width: 25em;' ) ) ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ); ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ); ?>
</div>

<?php $form->renderFormClose() ?>
