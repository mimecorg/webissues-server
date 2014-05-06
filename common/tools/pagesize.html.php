<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'Page Size' ) ?></legend>

<p><?php echo $this->tr( 'The maximum number of projects, issues and items in history which are displayed on a single page.' ) ?></p>

<?php $form->renderSelect( $this->tr( 'Projects tree:' ), 'projectPageSize', $projectOptions, array( 'style' => 'width: 15em;' ) ) ?>
<?php $form->renderSelect( $this->tr( 'List of issues:' ), 'folderPageSize', $folderOptions, array( 'style' => 'width: 15em;' ) ) ?>
<?php $form->renderSelect( $this->tr( 'Issue history:' ), 'historyPageSize', $historyOptions, array( 'style' => 'width: 15em;' ) ) ?>

</fieldset>
