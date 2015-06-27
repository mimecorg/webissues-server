<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<div class="pane-header">
<h2><?php echo $folderName ?> <span class="ellipsis">...</span></h2>
<?php if ( !$toolBar->isEmpty() ): ?>
<button class="hamburger">&equiv;</button>
<button class="cross">&times;</button>
<?php endif ?>
</div>

<?php if ( !$toolBar->isEmpty() ): ?>
<div class="menu">
  <ul>
<?php $toolBar->renderListItems() ?>
  </ul>
</div>
<?php endif ?>

<div class="pane-body">

<?php $viewForm->renderFormOpen( null, array( 'class' => 'form-inline' ) ) ?>
<?php $viewForm->renderSelect( $this->tr( 'View:' ), 'viewSelect', $viewOptions, array( 'style' => 'width: 15em;' ) ) ?>
<?php $viewForm->renderSubmit( $this->tr( 'Go' ), 'go' ) ?>
<?php $viewForm->renderFormClose() ?>

<?php $searchForm->renderFormOpen( null, array( 'class' => 'form-inline form-inline-right' ) ) ?>
<?php $searchForm->renderText( null, 'searchBox', array( 'style' => 'width: 15em;' ) ) ?>
<?php $searchForm->renderText( null, 'searchOption', array( 'type' => 'hidden' ) ) ?>
<?php $searchForm->renderSubmit( $this->tr( 'Search' ), 'search' ) ?>
<?php $searchForm->renderFormClose() ?>

<div style="clear: both"></div>

<?php if ( !empty( $issues ) ): ?>

<div class="grid">

<?php foreach ( $issues as $issueId => $issue ): ?>

<a href="<?php echo $this->url( $this->mergeQueryString( WI_SCRIPT_URL, array( 'issue' => $issueId, 'folder' => null, 'hpg' => null, 'hflt' => null, 'unread' => null ) ) ) ?>">

<?php $grid->renderRowOpen( $issueId, ( $issue[ 'read_id' ] < $issue[ 'stamp_id' ] ) ? array( 'row', 'unread' ) : array( 'row' ), 'div' ) ?>
<?php
    if ( $emailEngine && $issue[ 'subscription_id' ] != null ):
        if ( $issue[ 'read_id' ] == null ):
            $imageUrl = '/common/images/issue-unread-sub-16.png';
        elseif ( $issue[ 'read_id' ] < $issue[ 'stamp_id' ] ):
            $imageUrl = '/common/images/issue-modified-sub-16.png';
        else:
            $imageUrl = '/common/images/issue-subscribe-16.png';
        endif;
    else:
        if ( $issue[ 'read_id' ] == null ):
            $imageUrl = '/common/images/issue-unread-16.png';
        elseif ( $issue[ 'read_id' ] < $issue[ 'stamp_id' ] ):
            $imageUrl = '/common/images/issue-modified-16.png';
        else:
            $imageUrl = '/common/images/issue-16.png';
        endif;
    endif;
    echo $this->imageAndText( $imageUrl, $issue[ 'issue_name' ], array(), array(), $issue[ 'tip_name' ] );
?>
<?php $grid->renderRowClose( 'div' ) ?>

</a>

<?php endforeach ?>

</div>

<?php $grid->renderMobilePager() ?>

<?php elseif ( !empty( $searchBox ) ): ?>

<p class="noitems"><?php echo $this->tr( 'There are no issues matching the search criteria.' ) ?></p>

<?php elseif ( !empty( $viewSelect ) ): ?>

<p class="noitems"><?php echo $this->tr( 'There are no issues matching the selected view.' ) ?></p>

<?php elseif ( !empty( $isType ) ): ?>

<p class="noitems"><?php echo $this->tr( 'There are no issues of this type.' ) ?></p>

<?php else: ?>

<p class="noitems"><?php echo $this->tr( 'There are no issues in this folder.' ) ?></p>

<?php endif ?>

</div>
