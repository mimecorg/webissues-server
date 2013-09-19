<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<?php $form->renderFormOpen(); ?>

<div class="toolbar">
<?php $toolBar->render() ?>
</div>

<?php if ( !empty( $projects ) ): ?>

<table class="grid">
<tr>
<?php $grid->renderHeader( $this->tr( 'Name' ), 'name' ) ?>
<?php $grid->renderHeader( $this->tr( 'Type' ) ) ?>
<?php $grid->renderHeader( $this->tr( 'Access' ) ) ?>
</tr>

<?php foreach ( $projects as $projectId => $project ): ?>
<?php $grid->renderParentRowOpen( $projectId ) ?>

<td colspan="2">
  <?php $grid->renderExpandButton( empty( $project[ 'folders' ] ) ) ?>
  <?php
    $imageUrl = '/common/images/project';
    if ( $project[ 'is_public' ] ):
        $imageUrl .= '-public';
    endif;
    echo $this->imageAndTextLink( $this->appendQueryString( '/client/projects/index.php', array( 'project' => $projectId ) ), $imageUrl . '-admin-16.png', $project[ 'project_name' ] )
  ?>
</td>
<td><?php echo $project[ 'project_access' ] ?></td>

<?php $grid->renderRowClose() ?>

<?php foreach ( $project[ 'folders' ] as $folderId => $folder ): ?>
<?php $grid->renderChildRowOpen( $folderId, $projectId ) ?>

<td class="first-column">
  <?php echo $this->imageAndTextLink( $this->appendQueryString( '/client/projects/index.php', array( 'folder' => $folderId ) ), '/common/images/folder-16.png', $folder[ 'folder_name' ] ) ?>
</td>
<td><?php echo $folder[ 'type_name' ] ?></td>
<td></td>

<?php $grid->renderRowClose() ?>
<?php endforeach ?>

<?php endforeach ?>

</table>

<?php $grid->renderPager() ?>

<?php elseif ( $isAdministrator ): ?>

<p class="noitems"><?php echo $this->tr( 'There are no projects.' ) ?></p>

<?php else: ?>

<p class="noitems"><?php echo $this->tr( 'There are no projects that you can manage.' ) ?></p>

<?php endif ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ) ?>
</div>

<?php $form->renderFormClose() ?>
