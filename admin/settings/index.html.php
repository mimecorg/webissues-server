<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<?php $form->renderFormOpen() ?>

<?php $this->insertComponent( 'Common_Tools_Locale', $form ) ?>

<?php $this->insertComponent( 'Common_Tools_PageSize', $form ) ?>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'Mobile Page Size' ) ?></legend>

<p><?php echo $this->tr( 'The maximum number of projects, issues and items in history which are displayed in the mobile version.' ) ?></p>

<?php $form->renderSelect( $this->tr( 'Projects tree:' ), 'projectPageMobile', $projectOptions, array( 'style' => 'width: 15em;' ) ) ?>
<?php $form->renderSelect( $this->tr( 'List of issues:' ), 'folderPageMobile', $folderOptions, array( 'style' => 'width: 15em;' ) ) ?>
<?php $form->renderSelect( $this->tr( 'Issue history:' ), 'historyPageMobile', $historyOptions, array( 'style' => 'width: 15em;' ) ) ?>

</fieldset>

<?php $this->insertComponent( 'Common_Tools_ViewSettings', $form ) ?>

<?php $this->insertComponent( 'Common_Tools_Editing', $form ) ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ) ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ) ?>
</div>

<?php $form->renderFormClose() ?>
