<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'Regional Options' ) ?></legend>

<?php $form->renderSelect( $this->tr( 'Language:' ), 'language', $languageOptions, array( 'style' => 'width: 20em;' ) ) ?>
<?php $form->renderSelect( $this->tr( 'Time zone:' ), 'timeZone', $zoneOptions, array( 'style' => 'width: 20em;' ) ) ?>

</fieldset>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'Formats' ) ?></legend>

<p><?php echo $this->tr( 'Customize the format of numbers, date and time. Default formats depend on the selected language.' ) ?></p>

<?php $form->renderSelect( $this->tr( 'Number format:' ), 'numberFormat', $numberOptions, array( 'style' => 'width: 10em;' ) ) ?>
<?php $form->renderSelect( $this->tr( 'Date format:' ), 'dateFormat', $dateOptions, array( 'style' => 'width: 10em;' ) ) ?>
<?php $form->renderSelect( $this->tr( 'Time format:' ), 'timeFormat', $timeOptions, array( 'style' => 'width: 10em;' ) ) ?>
<?php $form->renderSelect( $this->tr( 'First day of week:' ), 'firstDay', $dayOptions, array( 'style' => 'width: 10em;' ) ) ?>

</fieldset>
