<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<?php if ( !empty( $allProjects ) ): ?>

<div style="float: right">
<?php echo $this->link( '#', $this->tr( 'Select All' ), array( 'id' => 'project-select', 'style' => 'display: none' ) ) ?>
|
<?php echo $this->link( '#', $this->tr( 'Unselect All' ), array( 'id' => 'project-unselect', 'style' => 'display: none' ) ) ?>
</div>

<?php endif ?>

<?php if ( !empty( $allProjects ) ): ?>
<p><?php echo $this->tr( 'Approve registration request for user <strong>%1</strong> and add the user to the selected projects.', null, $register[ 'user_name' ] ) ?></p>
<?php else: ?>
<p><?php echo $this->tr( 'Approve registration request for user <strong>%1</strong>.', null, $register[ 'user_name' ] ) ?></p>
<?php endif ?>

<?php $form->renderFormOpen(); ?>

<?php if ( !empty( $allProjects ) ): ?>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'Projects' ) ?></legend>

<div id="project-choices" class="form-checkgroup">
<?php foreach ( $allProjects as $projectId => $project ): ?>
<div style="float: left; width: 25%">
<?php $form->renderCheckBox( $project[ 'project_name' ], 'project' . $projectId ) ?>
</div>
<?php endforeach; ?>
<div style="clear: left"></div>
</div>

</fieldset>

<?php endif ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ); ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ); ?>
</div>

<?php $form->renderFormClose() ?>
