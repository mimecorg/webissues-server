<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<div class="pane-header">
<h2><?php echo !empty( $projectName ) ? $projectName : $this->tr( 'Projects' ) ?> <span class="ellipsis">...</span></h2>
</div>

<div class="pane-body">

<?php if ( !empty( $projects ) || !empty( $types ) ): ?>

<div class="grid">

<?php if ( !empty( $types ) ): ?>

<?php $grid->renderParentRowOpen( 'T', array( 'expandable' ), 'div' ) ?>
<?php echo $this->imageAndText( '/common/images/project-all-16.png', $this->tr( 'All Projects' ) ) ?> <span class="ellipsis">...</span>
<?php $grid->renderRowClose( 'div' ) ?>

<div class="children parent-T">

<?php foreach ( $types as $typeId => $type ): ?>
<a href="<?php echo $this->url( $this->filterQueryString( '/mobile/client/index.php', array( 'ps', 'po', 'ppg' ), array( 'type' => $typeId ) ) ) ?>">
<?php
    $grid->renderChildRowOpen( $typeId, 'T', array(), 'div' );
    echo $this->imageAndText( '/common/images/folder-type-16.png', $type[ 'type_name' ] );
    $grid->renderRowClose( 'div' );
?>
</a>
<?php endforeach ?>

</div>

<?php endif ?>

<?php foreach ( $projects as $projectId => $project ): ?>

<?php $grid->renderParentRowOpen( $projectId, !empty( $project[ 'folders' ] ) ? array( 'expandable' ) : array(), 'div' ) ?>

<?php
    $imageUrl = '/common/images/project';
    if ( $project[ 'is_public' ] ):
        $imageUrl .= '-public';
    endif;
    if ( $project[ 'project_access' ] == System_Const::AdministratorAccess ):
        $imageUrl .= '-admin';
    endif;
    echo $this->imageAndText( $imageUrl . '-16.png', $project[ 'project_name' ] );
?>
<?php if ( !empty( $project[ 'folders' ] ) ): ?>
 <span class="ellipsis">...</span>
<?php endif ?>
<?php if ( $project[ 'descr_id' ] ): ?>
<button class="hamburger">&equiv;</button>
<button class="cross">&times;</button>
<?php endif ?>
<?php $grid->renderRowClose( 'div' ) ?>

<?php if ( $project[ 'descr_id' ] ): ?>
<div class="menu">
  <ul>
<?php echo $this->imageAndTextLinkItem( $this->filterQueryString( '/mobile/client/index.php', array( 'ps', 'po', 'ppg' ), array( 'project' => $projectId ) ), '/common/images/view-description-16.png', $this->tr( 'View Description' ) ) ?>
  </ul>
</div>
<?php endif ?>

<div class="children parent-<?php echo $projectId ?>">

<?php foreach ( $project[ 'folders' ] as $folderId => $folder ): ?>
<a href="<?php echo $this->url( $this->filterQueryString( '/mobile/client/index.php', array( 'ps', 'po', 'ppg' ), array( 'folder' => $folderId ) ) ) ?>">
<?php
    $grid->renderChildRowOpen( $folderId, $projectId, array(), 'div' );
    echo $this->imageAndText( '/common/images/folder-16.png', $folder[ 'folder_name' ] );
    $grid->renderRowClose( 'div' );
?>
</a>
<?php endforeach ?>

</div>

<?php endforeach ?>

</div>

<?php $grid->renderMobilePager() ?>

<?php else: ?>

<p><?php echo $this->tr( 'There are no projects.' ) ?></p>

<?php endif ?>

</div>
