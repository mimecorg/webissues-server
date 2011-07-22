<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<?php if ( $form->hasErrors() ): ?>
<p class="error">
<?php echo $this->tr( 'Some of the values you entered are incorrect.' ) ?>
</p>
<?php endif ?>

<?php if ( !empty( $typeName ) ): ?>
<p><?php echo $this->tr( 'Create a new attribute in type <strong>%1</strong>.', null, $typeName ) ?></p>
<?php elseif ( !empty( $oldAttributeName ) ): ?>
<p><?php echo $this->tr( 'Modify attribute <strong>%1</strong>.', null, $oldAttributeName ) ?></p>
<?php endif ?>

<?php $form->renderFormOpen() ?>

<?php switch ( $page ):
case 'type': ?>

<?php $form->renderSelect( $this->tr( 'Type:' ), 'attributeType', $typeOptions ) ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'Continue' ), 'next' ) ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ) ?>
</div>

<?php break;
case 'details': ?>

<?php if ( !empty( $typeName ) ): ?>
<?php $form->renderText( $this->tr( 'Name:' ), 'attributeName', array( 'size' => 40 ) ) ?>
<?php endif ?>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'Attribute Details' ) ?></legend>

<div style="float: right">
<?php $form->renderSubmit( $this->tr( 'Change Type' ), 'changeType', array( 'disabled' => empty( $canChangeType ) ) ) ?>
</div>

<p><?php echo $this->tr( 'Specify details of <strong>%1</strong> attribute.', null, $typeOptions[ $type ] ) ?></p>

<?php switch ( $type ):
case 'TEXT': ?>

<?php $form->renderCheckBox( $this->tr( 'Allow entering multiple lines of text' ), 'multiLine' ) ?>

<?php $form->renderText( $this->tr( 'Minimum length:' ), 'minimumLength', array( 'size' => 40 ) ) ?>
<?php $form->renderText( $this->tr( 'Maximum length:' ), 'maximumLength', array( 'size' => 40 ) ) ?>

<?php break;
case 'ENUM': ?>

<?php $form->renderCheckBox( $this->tr( 'Allow entering custom values' ), 'editable' ) ?>

<?php $form->renderCheckBox( $this->tr( 'Allow selecting multiple items' ), 'multiSelect' ) ?>

<p><?php echo $this->tr( 'Enter one dropdown list item per line.' ) ?></p>

<?php $form->renderTextArea( $this->tr( 'Dropdown list items:' ), 'items', array( 'cols' => 40, 'rows' => 10 ) ) ?>

<p><?php echo $this->tr( 'The following settings can only be used when entering custom values is allowed and selecting multiple items is not allowed.' ) ?></p>

<?php $form->renderText( $this->tr( 'Minimum length:' ), 'minimumLength', array( 'size' => 40 ) ) ?>
<?php $form->renderText( $this->tr( 'Maximum length:' ), 'maximumLength', array( 'size' => 40 ) ) ?>

<?php break;
case 'NUMERIC': ?>

<?php $form->renderText( $this->tr( 'Decimal places:' ), 'decimalPlaces', array( 'size' => 40 ) ) ?>
<?php $form->renderText( $this->tr( 'Minimum value:' ), 'minimumValue', array( 'size' => 40 ) ) ?>
<?php $form->renderText( $this->tr( 'Maximum value:' ), 'maximumValue', array( 'size' => 40 ) ) ?>
<?php $form->renderCheckBox( $this->tr( 'Strip trailing decimal zeros' ), 'stripZeros' ) ?>

<?php break;
case 'DATETIME': ?>

<?php $form->renderRadioGroup( 'time', $timeOptions ) ?>

<?php break;
case 'USER': ?>

<?php $form->renderCheckBox( $this->tr( 'Allow selecting only project members' ), 'members' ) ?>

<?php $form->renderCheckBox( $this->tr( 'Allow selecting multiple items' ), 'multiSelect' ) ?>

<?php break;
endswitch ?>

<?php $form->renderError( 'definitionError' ) ?>

</fieldset>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'Common Settings' ) ?></legend>

<?php $form->renderCheckBox( $this->tr( 'Attribute is required' ), 'required' ) ?>
<?php $form->renderText( $this->tr( 'Default value:' ), 'defaultValue', array( 'size' => 40 ) ) ?>

</fieldset>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ) ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ) ?>
</div>

<?php break;
endswitch ?>

<?php $form->renderFormClose() ?>
