<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<?php if ( $isOwn ): ?>
<p><?php echo $this->tr( 'Configure your user preferences.' ) ?></p>
<?php else: ?>
<p><?php echo $this->tr( 'Configure preferences for user <strong>%1</strong>.', null, $user[ 'user_name' ] ) ?></p>
<?php endif ?>

<?php $form->renderFormOpen() ?>

<?php $this->insertComponent( 'Common_Tools_Locale', $form ) ?>

<?php if ( !$this->request->isRelativePathUnder( '/mobile' ) ): ?>
<?php $this->insertComponent( 'Common_Tools_PageSize', $form ) ?>
<?php endif ?>

<?php $this->insertComponent( 'Common_Tools_ViewSettings', $form ) ?>

<?php $this->insertComponent( 'Common_Tools_Editing', $form ) ?>

<?php if ( $emailEngine ): ?>
<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'Notifications' ) ?></legend>

<?php $form->renderText( $this->tr( 'Email address:' ), 'email', array( 'size' => 40 ) ) ?>

<p><?php echo $this->tr( 'This address will only be visible to the system administrator.'
 . ' You will not receive notifications if you leave this field empty.' ) ?></p>

<?php $form->renderCheckBox( $this->tr( 'Include issue details in notifications and summary reports' ), 'notifyDetails' ) ?>
<?php $form->renderCheckBox( $this->tr( 'Do not notify about issues that I have already read' ), 'notifyNoRead' ) ?>

</fieldset>
<?php endif ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ) ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ) ?>
</div>

<?php $form->renderFormClose() ?>
