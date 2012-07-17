<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<?php if ( !empty( $memberProjects ) ): ?>

<div style="float: right">
<?php echo $this->link( '#', $this->tr( 'Select All' ), array( 'id' => 'project-select', 'style' => 'display: none' ) ) ?>
|
<?php echo $this->link( '#', $this->tr( 'Unselect All' ), array( 'id' => 'project-unselect', 'style' => 'display: none' ) ) ?>
</div>

<?php endif ?>

<p><?php echo $this->tr( 'Add user <strong>%1</strong> to the selected projects.', null, $user[ 'user_name' ] ) ?></p>

<?php $form->renderFormOpen(); ?>

<?php if ( !empty( $memberProjects ) ): ?>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'Projects' ) ?></legend>

<div id="project-choices" class="form-checkgroup">
<?php foreach ( $memberProjects as $projectId => $project ): ?>
<div style="float: left; width: 25%">
<?php $form->renderCheckBox( $project[ 'project_name' ], 'project' . $projectId ) ?>
</div>
<?php endforeach; ?>
<div style="clear: left"></div>
</div>

<?php $form->renderError( 'memberProjects' ) ?>

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

<p class="error"><?php echo $this->tr( 'There are no more available projects to add.' ) ?></p>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'Close' ), 'close' ); ?>
</div>

<?php endif ?>

<?php $form->renderFormClose() ?>
