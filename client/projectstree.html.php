<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<div style="float: right">
<?php $toolBar->render() ?>
</div>

<h2><?php echo $this->tr( 'Projects' ) ?></h2>

<?php if ( !empty( $projects ) ): ?>

<table class="grid">
<tr>
<?php $grid->renderHeader( $this->tr( 'Name' ) ) ?>
<?php $grid->renderHeader( $this->tr( 'Type' ) ) ?>
</tr>

<?php foreach ( $projects as $projectId => $project ): ?>
<?php $grid->renderParentRowOpen( $projectId ) ?>

<td colspan="2">
<?php
    $grid->renderExpandButton( empty( $project[ 'folders' ] ) );
    if ( $project[ 'project_access' ] == System_Const::AdministratorAccess ):
        $image = '/common/images/project-admin-16.png';
    else:
        $image = '/common/images/project-16.png';
    endif;
    echo $this->imageAndTextLink( $this->appendQueryString( '/client/index.php', array( 'project' => $projectId ) ), $image, $project[ 'project_name' ] )
?>
</td>

<?php $grid->renderRowClose() ?>

<?php foreach ( $project[ 'folders' ] as $folderId => $folder ): ?>
<?php $grid->renderChildRowOpen( $folderId, $projectId ) ?>

<td class="first-column">
  <?php echo $this->imageAndTextLink( $this->appendQueryString( '/client/index.php', array( 'folder' => $folderId ) ), '/common/images/folder-16.png', $folder[ 'folder_name' ] ) ?>
</td>
<td><?php echo $folder[ 'type_name' ] ?></td>

<?php $grid->renderRowClose() ?>
<?php endforeach ?>

<?php endforeach ?>

</table>

<?php else: ?>

<p><?php echo $this->tr( 'There are no projects.' ) ?></p>

<?php endif ?>
