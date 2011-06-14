<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<div style="float: right; max-width: 75%; text-align: right; line-height: 20px">
<?php $toolBar->render() ?>
</div>

<h2><?php echo $issue[ 'issue_name' ] ?></h2>

<div class="sub-pane-wrapper">

<table class="sub-pane-layout">
<tr>
<td class="top-sub-pane">

<h3><?php echo $this->tr( 'Properties' ) ?></h3>

<table class="info-list">
<tr>
<td><?php echo $this->tr( 'ID:' ) ?></td>
<td><?php echo $issue[ 'issue_id' ] ?></td>
</tr>
<tr>
<td><?php echo $this->tr( 'Type:' ) ?></td>
<td><?php echo $issue[ 'type_name' ] ?></td>
</tr>
<tr>
<td><?php echo $this->tr( 'Project:' ) ?></td>
<td><?php echo $issue[ 'project_name' ] ?></td>
</tr>
<tr>
<td><?php echo $this->tr( 'Folder:' ) ?></td>
<td><?php echo $issue[ 'folder_name' ] ?></td>
</tr>
<tr>
<td><?php echo $this->tr( 'Created date:' ) ?></td>
<td><?php echo $issue[ 'created_date' ] ?></td>
</tr>
<tr>
<td><?php echo $this->tr( 'Created by:' ) ?></td>
<td><?php echo $issue[ 'created_by' ] ?></td>
</tr>
<tr>
<td><?php echo $this->tr( 'Modified date:' ) ?></td>
<td><?php echo $issue[ 'modified_date' ] ?></td>
</tr>
<tr>
<td><?php echo $this->tr( 'Modified by:' ) ?></td>
<td><?php echo $issue[ 'modified_by' ] ?></td>
</tr>
</table>

</td>
<td class="top-sub-pane">

<h3><?php echo $this->tr( 'Attributes' ) ?></h3>

<table class="info-list">
<?php foreach( $attributeValues as $value ): ?>
<tr>
<td><?php echo $value[ 'attr_name' ] ?>:</td>
<td class="multi-line"><?php echo $value[ 'attr_value' ] ?></td>
</tr>
<?php endforeach ?>
</table>

</td>
</tr>
<tr>
<td colspan="2" class="bottom-sub-pane">

<div style="float: right">
<?php $filterBar->renderNoFilter( $this->tr( 'All History' ) ) ?>
<?php $filterBar->renderFilters( $filters ) ?>
</div>

<h3><?php echo $this->tr( 'Issue History' ) ?></h3>

<?php
    if ( !empty( $history ) ):

    foreach ( $history as $id => $item ):
?>

<h4>
<?php
    switch ( $item[ 'change_type' ] ):
    case System_Const::IssueCreated:
        echo $this->tr( 'Issue Created' );
        break;
    case System_Const::IssueRenamed:
    case System_Const::ValueChanged:
        echo $this->tr( 'Issue Modified' );
        break;
    case System_Const::CommentAdded:
        echo '<a class="anchor" name="item' . $id . '">' . $this->tr( 'Comment %1', null, $item[ 'change_id' ] ) . '</a>';
        break;
    case System_Const::FileAdded:
        echo '<a class="anchor" name="item' . $id . '">' . $this->tr( 'Attachment %1', null, $item[ 'change_id' ] ) . '</a>';
        break;
    case System_Const::IssueMoved:
        echo $this->tr( 'Issue Moved' );
        break;
    endswitch;
?>
</h4>

<?php if ( $item[ 'change_type' ] == System_Const::CommentAdded && $item[ 'can_edit' ] ): ?>

<div style="float: right">
<?php echo $this->imageAndTextLink( $this->mergeQueryString( '/client/issues/editcomment.php', array( 'id' => $id, 'issue' => null ) ),
    '/common/images/edit-modify-16.png', $this->tr( 'Edit' ) ) ?>
|
<?php echo $this->imageAndTextLink( $this->mergeQueryString( '/client/issues/deletecomment.php', array( 'id' => $id, 'issue' => null ) ),
    '/common/images/edit-delete-16.png', $this->tr( 'Delete' ) ) ?>
</div>

<?php elseif ( $item[ 'change_type' ] == System_Const::FileAdded && $item[ 'can_edit' ] ): ?>

<div style="float: right">
<?php echo $this->imageAndTextLink( $this->mergeQueryString( '/client/issues/editattachment.php', array( 'id' => $id, 'issue' => null ) ),
    '/common/images/edit-modify-16.png', $this->tr( 'Edit' ) ) ?>
|
<?php echo $this->imageAndTextLink( $this->mergeQueryString( '/client/issues/deleteattachment.php', array( 'id' => $id, 'issue' => null ) ),
    '/common/images/edit-delete-16.png', $this->tr( 'Delete' ) ) ?>
</div>

<?php endif ?>

<div class="history-info">
<?php
    echo $item[ 'created_date' ] . ' &mdash; ' . $item[ 'created_by' ];
    if ( $item[ 'stamp_id' ] != $id ):
        echo ' (' . $this->tr( "last edited:" ) .  ' ' . $item[ 'modified_date' ] . ' &mdash; ' . $item[ 'modified_by' ] . ')';
    endif;
?>
</div>

<?php
    switch ( $item[ 'change_type' ] ):
    case System_Const::IssueCreated:
    case System_Const::IssueRenamed:
    case System_Const::ValueChanged:
?>

<ul class="changes">
<?php foreach ( $item[ 'changes' ] as $change ): ?>
<li>
<?php
    switch ( $change[ 'change_type' ] ):
    case System_Const::IssueCreated:
        echo $this->tr( 'Name' ) . ': "' . $change[ 'value_new' ] . '"';
        break;
    case System_Const::IssueRenamed:
        echo $this->tr( 'Name' ) . ': "' . $change[ 'value_old' ] . '" &rarr; "' . $change[ 'value_new' ] . '"';
        break;
    case System_Const::ValueChanged:
        $from = ( $change[ 'value_old' ] == '' ) ? $this->tr( 'empty' ) : '"' . $change[ 'value_old' ] . '"';
        $to = ( $change[ 'value_new' ] == '' ) ? $this->tr( 'empty' ) : '"' . $change[ 'value_new' ] . '"';
        echo $change[ 'attr_name' ] . ': ' . $from . ' &rarr; ' . $to;
        break;
    endswitch;
?>
</li>
<?php endforeach ?>
</ul>

<?php
    break;
    case System_Const::CommentAdded:
?>

<div class="comment-text"><?php echo $item[ 'comment_text' ] ?></div>

<?php
    break;
    case System_Const::FileAdded:
?>

<div class="attachment">
<?php
    echo $this->link( $this->appendQueryString( '/client/issues/getattachment.php', array( 'id' => $id ) ), $item[ 'file_name' ] ) . ' (' . $item[ 'file_size' ] . ')';
    if ( $item[ 'file_descr' ] != '' ):
        echo ' &mdash; ' . $item[ 'file_descr' ];
    endif;
?>
</div>

<?php
    break;
    case System_Const::IssueMoved:
?>

<ul class="changes">
<li>
<?php
    $from = ( $item[ 'from_folder_name' ] == '' ) ? $this->tr( 'Unknown Folder' ) : '"' . $item[ 'from_folder_name' ] . '"';
    $to = ( $item[ 'to_folder_name' ] == '' ) ? $this->tr( 'Unknown Folder' ) : '"' . $item[ 'to_folder_name' ] . '"';
    echo $this->tr( 'Folder' ) . ': ' . $from . ' &rarr; ' . $to;
?>
</li>
</ul>

<?php
    break;
    endswitch;

    endforeach;

    else:
?>

<p>
<?php
    if ( $historyFilter == System_Const::CommentAdded ):
        echo $this->tr( 'There are no comments.' );
    else:
        echo $this->tr( 'There are no attachments.' );
    endif
?>
</p>

<?php endif ?>

<?php $pager->renderPager(); ?>

</td>
</tr>
</table>

</div>
