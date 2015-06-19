<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<?php $form->renderFormOpen() ?>

<h3><?php echo $this->tr( 'WebIssues %1', null, WI_VERSION ) ?></h3>

<p><?php echo $this->tr( 'Issue tracking and team collaboration system.' ) ?></p>

<p><?php echo $this->tr( "This program is free software: you can redistribute it and/or modify"
    . " it under the terms of the GNU Affero General Public License as published by"
    . " the Free Software Foundation, either version 3 of the License, or"
    . " (at your option) any later version." ) ?></p>

<p><?php echo $this->tr( 'Copyright &copy; 2006 Michał Męciński' ) ?><br>
<?php echo $this->tr( 'Copyright &copy; 2007-2015 WebIssues Team' ) ?></p>

<fieldset class="form-fieldset">
<legend><?php echo $this->imageAndText( '/common/images/help-16.png', $this->tr( 'Help' ) ) ?></legend>

<p><?php echo $this->tr( 'Open the WebIssues Manual for help.' ) ?></p>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'Manual' ), 'manual' ) ?>
</div>

</fieldset>

<fieldset class="form-fieldset">
<legend><?php echo $this->imageAndText( '/common/images/web-16.png', $this->tr( 'Website' ) ) ?></legend>

<p><?php echo $this->tr( 'Visit %1 for more information about WebIssues.', null, '<a href="http://webissues.mimec.org">webissues.mimec.org</a>' ) ?></p>

</fieldset>

<fieldset class="form-fieldset">
<legend><?php echo $this->imageAndText( '/common/images/donate-16.png', $this->tr( 'Donations' ) ) ?></legend>

<p><?php echo $this->tr( 'If you like this program, your donation will help us dedicate more time for it, support it and implement new features.' ) ?></p>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'Donate' ), 'donate' ) ?>
</div>

</fieldset>

<?php if ( $checkLastVersion ): ?>

<fieldset class="form-fieldset">
<legend><?php echo $this->imageAndText( '/common/images/status-info-16.png', $this->tr( 'Latest Version' ) ) ?></legend>

<?php
switch ( $checkState ):
case 'current':
?>

<p><?php echo $this->tr( 'Your version of WebIssues is up to date.' ) ?></p>

<?php
break;
case 'available':
?>

<p><?php echo $this->tr( 'The latest version of WebIssues is %1.', null, $updateVersion ) ?></p>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'Release Notes' ), 'notes' ) ?>
<?php $form->renderSubmit( $this->tr( 'Download' ), 'download' ) ?>
</div>

<?php
break;
case 'error':
?>

<p class="error"><?php echo $this->tr( 'Checking for latest version failed.' ) ?></p>

<?php
break;
default:
?>

<p><?php echo $this->tr( 'Check for latest version of WebIssues.' ) ?></p>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'Check' ), 'lastVersion' ) ?>
</div>

<?php
break;
endswitch
?>

</fieldset>

<?php endif ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ) ?>
</div>

<?php $form->renderFormClose() ?>
