<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<h2><?php echo $projectName ?></h2>

<div class="toolbar">
<?php $toolBar->render() ?>
</div>

<?php if ( !empty( $descr ) ): ?>
<?php if ( $canEditDescr ): ?>
<div style="float: right">
<?php
    echo $this->imageAndTextLink( $this->mergeQueryString( '/client/projects/editdescription.php' ), '/common/images/edit-modify-16.png', $this->tr( 'Edit' ) );
    echo ' | ' . $this->imageAndTextLink( $this->mergeQueryString( '/client/projects/deletedescription.php' ), '/common/images/edit-delete-16.png', $this->tr( 'Delete' ) );
?>
</div>
<?php endif ?>

<h3>
<?php
    echo $this->tr( 'Description' );
    echo '&nbsp; <span class="edited">(' . $this->tr( 'Last Edited:' ) . ' ' . $descr[ 'modified_date' ] . ' &mdash; ' . $descr[ 'modified_by' ] . ')</span>';
?>
</h3>

<div class="comment-text"><?php echo $descr[ 'descr_text' ] ?></div>
<?php endif ?>
