<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<?php $this->beginSlot( 'float_links' ) ?>
<?php $filterBar->renderNoFilter( $this->tr( 'All Events' ) ) ?>
<?php $filterBar->renderFilters( $eventTypes ) ?>
<?php $this->endSlot() ?>

<?php if ( !empty( $events ) ): ?>

<table class="grid">
<tr>
<?php $grid->renderHeader( $this->tr( 'Type' ) ) ?>
<?php $grid->renderHeader( $this->tr( 'Date' ), 'date' ) ?>
<?php $grid->renderHeader( $this->tr( 'Message' ) ) ?>
</tr>

<?php foreach ( $events as $eventId => $event ): ?>
<?php $grid->renderRowOpen( $eventId ) ?>

<td><?php echo $this->image( $event[ 'icon' ], $event[ 'severity' ] ) . ' ' . $event[ 'type' ] ?></td>
<td><?php echo $event[ 'date' ] ?></td>
<td><?php echo $this->link( $this->mergeQueryString( '/admin/events/event.php', array( 'id' => $eventId ) ), $event[ 'message' ] ) ?></td>

<?php $grid->renderRowClose() ?>
<?php endforeach ?>

</table>

<?php $grid->renderPager() ?>

<?php else: ?>

<p class="noitems"><?php echo $this->tr( 'There are no events of the selected type.' ) ?></p>

<?php endif ?>
