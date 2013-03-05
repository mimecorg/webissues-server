<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'Editing' ) ?></legend>

<p><?php echo $this->tr( 'The default format used for new comments and descriptions.' ) ?></p>

<?php $form->renderSelect( $this->tr( 'Default text format:' ), 'defaultFormat', $formatOptions, array( 'style' => 'width: 25em;' ) ) ?>

</fieldset>
