<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<?php if ( !empty( $members ) ): ?>

<div style="float: right">
<?php echo $this->link( '#', $this->tr( 'Select All' ), array( 'id' => 'user-select', 'style' => 'display: none' ) ) ?>
|
<?php echo $this->link( '#', $this->tr( 'Unselect All' ), array( 'id' => 'user-unselect', 'style' => 'display: none' ) ) ?>
</div>

<?php endif ?>

<p><?php echo $this->tr( 'Add new members to project <strong>%1</strong>.', null, $project[ 'project_name' ] ) ?></p>

<?php $form->renderFormOpen(); ?>

<?php if ( !empty( $members ) ): ?>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'Users' ) ?></legend>

<div id="user-choices" class="form-checkgroup">
<?php foreach ( $members as $userId => $user ): ?>
<div style="float: left; width: 25%">
<?php $form->renderCheckBox( $user[ 'user_name' ], 'member' . $userId ) ?>
</div>
<?php endforeach; ?>
<div style="clear: left"></div>
</div>

<?php $form->renderError( 'members' ) ?>

</fieldset>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'Access' ) ?></legend>
<?php $form->renderRadioGroup( 'accessLevel', $accessLevels ) ?>
</fieldset>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ); ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ); ?>
</div>

<?php else: ?>

<p class="error"><?php echo $this->tr( 'There are no more available users to add.' ) ?></p>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'Close' ), 'close' ); ?>
</div>

<?php endif ?>

<?php $form->renderFormClose() ?>
