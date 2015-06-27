<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<div class="pane-header">
<h2><?php echo $projectName ?> <span class="ellipsis">...</span></h2>
</div>

<div class="pane-body">

<?php if ( !empty( $descr ) ): ?>

<h3><?php echo $this->tr( 'Description' ) ?></h3>

<div class="edited"><?php echo $this->tr( 'Last Edited:' ) . ' ' . $descr[ 'modified_date' ] . ' &mdash; ' . $descr[ 'modified_by' ] ?></div>

<div class="comment-text"><?php echo $descr[ 'descr_text' ] ?></div>

<?php endif ?>

</div>
