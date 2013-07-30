<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<h2><?php echo $folderName ?></h2>

<div class="toolbar">
<?php $toolBar->render() ?>
</div>

<div style="float: left">
<?php $viewForm->renderFormOpen( null, array( 'class' => 'form-inline' ) ) ?>
<?php $viewForm->renderSelect( $this->tr( 'View:' ), 'viewSelect', $viewOptions, array( 'style' => 'width: 15em;' ) ) ?>
<?php $viewForm->renderSubmit( $this->tr( 'Go' ), 'go' ) ?>
&nbsp; <?php $viewToolBar->render() ?>
<?php $viewForm->renderFormClose() ?>
</div>

<div style="float: right">
<?php $searchForm->renderFormOpen( null, array( 'class' => 'form-inline' ) ) ?>
<?php $searchForm->renderText( null, 'searchBox', array( 'style' => 'width: 15em;' ) ) ?>
<?php $searchForm->renderText( null, 'searchOption', array( 'type' => 'hidden' ) ) ?>
<?php $searchForm->renderSubmit( $this->tr( 'Search' ), 'search' ) ?>
<?php $searchForm->renderFormClose() ?>
</div>

<div style="clear: both"></div>

<?php if ( !empty( $issues ) ): ?>

<table class="grid">
<tr>
<?php
    foreach ( $columns as $column => $name ):
        $grid->renderHeader( $headers[ $column ], $name );
    endforeach
?>
</tr>
<?php foreach ( $issues as $issueId => $issue ): ?>
<?php $grid->renderRowOpen( $issueId, ( $issue[ 'read_id' ] < $issue[ 'stamp_id' ] ) ? array( 'unread' ) : array() ) ?>

<?php foreach ( $columns as $column => $name ): ?>
<td>
<?php
    if ( $column == System_Api_Column::Name ):
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
        echo $this->imageAndTextLink( $this->mergeQueryString( WI_SCRIPT_URL, array( 'issue' => $issueId, 'folder' => null, 'hpg' => null, 'hflt' => null, 'unread' => null ) ),
            $imageUrl, $issue[ $name ], array(), array(), $issue[ 'tip_name' ] );
    elseif ( $column == System_Api_Column::Location ):
        echo $issue[ 'project_name' ] . ' &mdash; ' . $issue[ 'folder_name' ];
    else:
        echo $issue[ $name ];
    endif
?>
</td>
<?php endforeach ?>

<?php $grid->renderRowClose() ?>
<?php endforeach ?>

</table>

<?php $grid->renderPager() ?>

<?php elseif ( !empty( $searchBox ) ): ?>

<p class="noitems"><?php echo $this->tr( 'There are no issues matching the search criteria.' ) ?></p>

<?php elseif ( !empty( $viewSelect ) ): ?>

<p class="noitems"><?php echo $this->tr( 'There are no issues matching the selected view.' ) ?></p>

<?php elseif ( !empty( $isType ) ): ?>

<p class="noitems"><?php echo $this->tr( 'There are no issues of this type.' ) ?></p>

<?php else: ?>

<p class="noitems"><?php echo $this->tr( 'There are no issues in this folder.' ) ?></p>

<?php endif ?>
