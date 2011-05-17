<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<div style="float: right">
<?php $toolBar->render() ?>
</div>

<h2><?php echo $folderName ?></h2>

<div style="float: right">
<?php $viewToolBar->render() ?>
</div>

<?php $viewForm->renderFormOpen( null, array( 'class' => 'form-inline' ) ) ?>
<?php $viewForm->renderSelect( $this->tr( 'Select view:' ), 'viewSelect', $viewOptions, array( 'style' => 'width: 15em;' ) ) ?>
<?php $viewForm->renderSubmit( $this->tr( 'Go' ), 'go' ) ?>
<?php $viewForm->renderFormClose() ?>

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
        if ( $issue[ 'read_id' ] == null ):
            $imageUrl = '/common/images/issue-unread-16.png';
        elseif ( $issue[ 'read_id' ] < $issue[ 'stamp_id' ] ):
            $imageUrl = '/common/images/issue-modified-16.png';
        else:
            $imageUrl = '/common/images/issue-16.png';
        endif;
        echo $this->imageAndTextLink( $this->mergeQueryString( WI_SCRIPT_URL, array( 'issue' => $issueId, 'folder' => null, 'hpg' => null, 'hflt' => null, 'unread' => null ) ),
            $imageUrl, $issue[ $name ], array(), array(), $issue[ 'tip_name' ] );
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
