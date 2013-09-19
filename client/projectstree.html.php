<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<div style="float: right">
<?php $toolBar->render() ?>
</div>

<h2><?php echo $this->tr( 'Projects' ) ?></h2>

<?php if ( !empty( $projects ) || !empty( $types ) ): ?>

<table class="grid">
<tr>
<?php $grid->renderHeader( $this->tr( 'Name' ), 'name' ) ?>
<?php $grid->renderHeader( $this->tr( 'Type' ) ) ?>
</tr>

<?php if ( !empty( $types ) ): ?>

<?php $grid->renderParentRowOpen( 'T' ) ?>

<td colspan="2">
  <?php $grid->renderExpandButton( empty( $types ) ) ?>
  <?php echo $this->imageAndText( '/common/images/project-all-16.png', $this->tr( 'All Projects' ) ) ?>
</td>

<?php $grid->renderRowClose() ?>

<?php foreach ( $types as $typeId => $type ): ?>
<?php $grid->renderChildRowOpen( $typeId, 'T' ) ?>

<td class="first-column">
  <?php echo $this->imageAndTextLink( $this->filterQueryString( '/client/index.php', array( 'ps', 'po', 'ppg' ), array( 'type' => $typeId ) ), '/common/images/folder-type-16.png', $type[ 'type_name' ] ) ?>
</td>
<td></td>

<?php $grid->renderRowClose() ?>
<?php endforeach ?>

<?php endif ?>

<?php foreach ( $projects as $projectId => $project ): ?>
<?php $grid->renderParentRowOpen( $projectId ) ?>

<td colspan="2">
  <?php $grid->renderExpandButton( empty( $project[ 'folders' ] ) ) ?>
  <?php
    $imageUrl = '/common/images/project';
    if ( $project[ 'is_public' ] ):
        $imageUrl .= '-public';
    endif;
    if ( $project[ 'project_access' ] == System_Const::AdministratorAccess ):
        $imageUrl .= '-admin';
    endif;
    echo $this->imageAndTextLink( $this->filterQueryString( '/client/index.php', array( 'ps', 'po', 'ppg' ), array( 'project' => $projectId ) ), $imageUrl . '-16.png', $project[ 'project_name' ] )
  ?>
</td>

<?php $grid->renderRowClose() ?>

<?php foreach ( $project[ 'folders' ] as $folderId => $folder ): ?>
<?php $grid->renderChildRowOpen( $folderId, $projectId ) ?>

<td class="first-column">
  <?php echo $this->imageAndTextLink( $this->filterQueryString( '/client/index.php', array( 'ps', 'po', 'ppg' ), array( 'folder' => $folderId ) ), '/common/images/folder-16.png', $folder[ 'folder_name' ] ) ?>
</td>
<td><?php echo $folder[ 'type_name' ] ?></td>

<?php $grid->renderRowClose() ?>
<?php endforeach ?>

<?php endforeach ?>

</table>

<?php $grid->renderPager() ?>

<?php else: ?>

<p><?php echo $this->tr( 'There are no projects.' ) ?></p>

<?php endif ?>
