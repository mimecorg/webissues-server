<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<p><?php echo $this->tr( 'Change access to project <strong>%1</strong> for user <strong>%2</strong>.',
    null, $project[ 'project_name' ], $user[ 'user_name' ] ); ?></p>

<?php $form->renderFormOpen(); ?>

<?php $form->renderRadioGroup( 'accessLevel', $accessLevels ) ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ); ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ); ?>
</div>

<?php $form->renderFormClose() ?>
