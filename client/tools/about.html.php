<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<?php $form->renderFormOpen() ?>

<h3><?php echo $this->tr( 'WebIssues %1', null, WI_VERSION ) ?></h3>

<p><?php echo $this->tr( 'Issue tracking and team collaboration system.' ) ?></p>

<p><?php echo $this->tr( "This program is free software: you can redistribute it and/or modify"
    . " it under the terms of the GNU Affero General Public License as published by"
    . " the Free Software Foundation, either version 3 of the License, or"
    . " (at your option) any later version." ) ?></p>

<p><?php echo $this->tr( 'Copyright &copy; 2006 Michał Męciński' ) ?><br />
<?php echo $this->tr( 'Copyright &copy; 2007-2011 WebIssues Team' ) ?></p>

<fieldset class="form-fieldset">
<legend><?php echo $this->imageAndText( '/common/images/help-16.png', $this->tr( 'Help' ) ) ?></legend>

<p><?php echo $this->tr( 'Open the WebIssues Manual for help.' ) ?></p>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'Manual' ), 'manual' ) ?>
</div>

</fieldset>

<fieldset class="form-fieldset">
<legend><?php echo $this->imageAndText( '/common/images/web-16.png', $this->tr( 'Web Page' ) ) ?></legend>

<p><?php echo $this->tr( 'Visit %1 for more information about WebIssues.', null, '<a href="http://webissues.mimec.org">webissues.mimec.org</a>' ) ?></p>

</fieldset>

<fieldset class="form-fieldset">
<legend><?php echo $this->imageAndText( '/common/images/donate-16.png', $this->tr( 'Donations' ) ) ?></legend>

<p><?php echo $this->tr( 'If you like this program, your donation will help us dedicate more time for it, support it and implement new features.' ) ?></p>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'Donate' ), 'donate' ) ?>
</div>

</fieldset>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ) ?>
</div>

<?php $form->renderFormClose() ?>
