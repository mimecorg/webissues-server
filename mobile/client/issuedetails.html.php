<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<div class="pane-header">
<h2><?php echo $issue[ 'issue_name' ] ?> <span class="ellipsis">...</span></h2>
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
<td><?php echo $this->tr( 'Location:' ) ?></td>
<td><?php echo $issue[ 'project_name' ] . ' &mdash; ' . $issue[ 'folder_name' ] ?></td>
</tr>
<tr>
<td><?php echo $this->tr( 'Created:' ) ?></td>
<td><?php echo $issue[ 'created_date' ] . ' &mdash; ' . $issue[ 'created_by' ] ?></td>
</tr>
<tr>
<td><?php echo $this->tr( 'Last Modified:' ) ?></td>
<td><?php echo $issue[ 'modified_date' ] . ' &mdash; ' . $issue[ 'modified_by' ] ?></td>
</tr>
<?php foreach( $attributeValues as $value ): ?>
<tr>
<td><?php echo $value[ 'attr_name' ] ?>:</td>
<td class="multi-line"><?php echo $value[ 'attr_value' ] ?></td>
</tr>
<?php endforeach ?>
</table>

<?php if ( !empty( $descr ) ): ?>
<div class="description">

<h3>
<?php echo $this->tr( 'Description' ) ?>
<?php if ( $canReply || $canEditDescr ): ?>
<button class="hamburger">&equiv;</button>
<button class="cross">&times;</button>
<?php endif ?>
</h3>

<?php if ( $canReply || $canEditDescr ): ?>
<div class="menu">
  <ul>
<?php
    if ( $canReply ):
        echo $this->imageAndTextLinkItem( $this->mergeQueryString( '/mobile/client/issues/addcomment.php', array( 'reply' => 'descr' ) ), '/common/images/reply-16.png', $this->tr( 'Reply' ) );
    endif;
    if ( $canEditDescr ):
        echo $this->imageAndTextLinkItem( $this->mergeQueryString( '/mobile/client/issues/editdescription.php' ), '/common/images/edit-modify-16.png', $this->tr( 'Edit' ) );
        echo $this->imageAndTextLinkItem( $this->mergeQueryString( '/mobile/client/issues/deletedescription.php' ), '/common/images/edit-delete-16.png', $this->tr( 'Delete' ) );
    endif;
?>
  </ul>
</div>
<?php endif ?>

<?php if ( $descr[ 'is_modified' ] ): ?>
<div class="edited"><?php echo $this->tr( 'Last Edited:' ) . ' ' . $descr[ 'modified_date' ] . ' &mdash; ' . $descr[ 'modified_by' ] ?></div>
<?php endif ?>

<div class="comment-text"><?php echo $descr[ 'descr_text' ] ?></div>

</div>
<?php endif ?>

<div class="history">

<h3>
<?php echo $this->tr( 'Issue History' ) ?>
<button class="hamburger">&equiv;</button>
<button class="cross">&times;</button>
</h3>

<div class="menu">
  <ul>
<?php $filterBar->renderListItems( $filters, $defaultFilter ) ?>
  </ul>
</div>

<?php
    if ( !empty( $history ) ):

    foreach ( $history as $id => $item ):
?>

<div class="history-item">

<h4><?php echo $item[ 'created_date' ] . ' &mdash; ' . $item[ 'created_by' ] ?></h4>
<?php if ( $item[ 'is_modified' ] ): ?>
<div class="edited"><?php echo $this->tr( 'Last Edited:' ) . ' ' . $item[ 'modified_date' ] . ' &mdash; ' . $item[ 'modified_by' ] ?></div>
<?php endif ?>

<?php if ( $item[ 'change_type' ] == System_Const::CommentAdded ): ?>

<div class="history-links">
<?php echo '<a class="anchor" name="item' . $id . '">' . $this->imageAndText( '/common/images/comment-16.png', $this->tr( 'Comment %1', null, $item[ 'change_id' ] ) ) . '</a>' ?>
<?php if ( $canReply || $item[ 'can_edit' ] ): ?>
<button class="hamburger">&equiv;</button>
<button class="cross">&times;</button>
<?php endif ?>
</div>

<?php if ( $canReply || $item[ 'can_edit' ] ): ?>
<div class="menu">
  <ul>
<?php  
    if ( $canReply ):
        echo $this->imageAndTextLinkItem( $this->mergeQueryString( '/mobile/client/issues/addcomment.php', array( 'reply' => $id ) ), '/common/images/reply-16.png', $this->tr( 'Reply' ) );
    endif;
    if ( $item[ 'can_edit' ] ):
        echo $this->imageAndTextLinkItem( $this->mergeQueryString( '/mobile/client/issues/editcomment.php', array( 'id' => $id, 'issue' => null ) ),
            '/common/images/edit-modify-16.png', $this->tr( 'Edit' ) );
        echo $this->imageAndTextLinkItem( $this->mergeQueryString( '/mobile/client/issues/deletecomment.php', array( 'id' => $id, 'issue' => null ) ),
            '/common/images/edit-delete-16.png', $this->tr( 'Delete' ) );
    endif
?>
  </ul>
</div>
<?php endif ?>

<?php elseif ( $item[ 'change_type' ] == System_Const::FileAdded ): ?>

<div class="history-links">
<?php echo '<a class="anchor" name="item' . $id . '">' . $this->imageAndText( '/common/images/file-attach-16.png', $this->tr( 'Attachment %1', null, $item[ 'change_id' ] ) ) . '</a>' ?>
<?php if ( $item[ 'can_edit' ] ): ?>
<button class="hamburger">&equiv;</button>
<button class="cross">&times;</button>
<?php endif ?>
</div>

<?php if ( $item[ 'can_edit' ] ): ?>
<div class="menu">
  <ul>
<?php
    echo $this->imageAndTextLinkItem( $this->mergeQueryString( '/mobile/client/issues/editattachment.php', array( 'id' => $id, 'issue' => null ) ),
        '/common/images/edit-modify-16.png', $this->tr( 'Edit' ) );
    echo $this->imageAndTextLinkItem( $this->mergeQueryString( '/mobile/client/issues/deleteattachment.php', array( 'id' => $id, 'issue' => null ) ),
        '/common/images/edit-delete-16.png', $this->tr( 'Delete' ) );
?>
  </ul>
</div>
<?php endif ?>

<?php endif ?>

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
        echo $this->tr( 'Name' ) . ': "' . $change[ 'value_old' ] . '" &raquo; "' . $change[ 'value_new' ] . '"';
        break;
    case System_Const::ValueChanged:
        $from = ( $change[ 'value_old' ] == '' ) ? $this->tr( 'empty' ) : '"' . $change[ 'value_old' ] . '"';
        $to = ( $change[ 'value_new' ] == '' ) ? $this->tr( 'empty' ) : '"' . $change[ 'value_new' ] . '"';
        echo $change[ 'attr_name' ] . ': ' . $from . ' &raquo; ' . $to;
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
    echo $this->link( $this->appendQueryString( '/mobile/client/issues/getattachment.php', array( 'id' => $id ) ), $item[ 'file_name' ] ) . ' (' . $item[ 'file_size' ] . ')';
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
    echo $this->tr( 'Issue moved from %1 to %2', null, $from, $to );
?>
</li>
</ul>

<?php
    break;
    endswitch;
?>

</div>

<?php
    endforeach;

    else:
?>

<p class="noitems">
<?php
    if ( $historyFilter == System_Api_HistoryProvider::Comments ):
        echo $this->tr( 'There are no comments.' );
    elseif ( $historyFilter == System_Api_HistoryProvider::Files ):
        echo $this->tr( 'There are no attachments.' );
    else:
        echo $this->tr( 'There are no comments or attachments.' );
    endif
?>
</p>

<?php endif ?>

<?php $pager->renderMobilePager(); ?>

</div>

</div>
