<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'View Settings' ) ?></legend>

<?php if ( $settingsMode ): ?>
<p><?php echo $this->tr( 'Global view settings that affect all users and issue types.' ) ?></p>

<?php $form->renderCheckBox( $this->tr( 'Hide the ID column' ), 'hideIdColumn' ) ?>
<?php $form->renderCheckBox( $this->tr( 'Hide attributes with empty values' ), 'hideEmptyValues' ) ?>
<?php else: ?>
<p><?php echo $this->tr( 'Global view settings that affect all issue types.' ) ?></p>
<?php endif ?>

<?php $form->renderSelect( $this->tr( 'Order of issue history:' ), 'historyOrder', $orderOptions, array( 'style' => 'width: 25em;' ) ) ?>
<?php $form->renderSelect( $this->tr( 'Default filter in issue history:' ), 'historyFilter', $filterOptions, array( 'style' => 'width: 25em;' ) ) ?>

</fieldset>
