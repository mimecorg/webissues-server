<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<div class="toolbar">
<?php $toolBar->render() ?>
</div>

<?php if ( !empty( $projects ) ): ?>

<table class="grid">
<tr>
<?php $grid->renderHeader( $this->tr( 'Name' ), 'name' ) ?>
</tr>

<?php foreach ( $projects as $projectId => $project ): ?>
<?php $grid->renderRowOpen( $projectId, $project[ 'classes' ] ) ?>

<td>
<?php echo $this->imageAndTextLink( $this->mergeQueryString( '/admin/archive/index.php', array( 'id' => $projectId ) ), '/common/images/project-archived-16.png', $project[ 'project_name' ] ); ?>
</td>    

<?php $grid->renderRowClose() ?>
<?php endforeach ?>

</table>

<?php $grid->renderPager() ?>

<?php else: ?>

<p class="noitems"><?php echo $this->tr( 'There are no archived projects.' ) ?></p>

<?php endif ?>
