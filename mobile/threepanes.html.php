<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<div class="pane-wrapper">
<?php $this->insertSlot( 'left_pane' ) ?>
</div>

<?php if ( $this->hasSlot( 'top_pane' ) ): ?>
<div class="pane-wrapper">
<?php $this->insertSlot( 'top_pane' ) ?>
</div>
<?php endif ?>

<?php if ( $this->hasSlot( 'bottom_pane' ) ): ?>
<tr>
<div class="pane-wrapper">
<?php $this->insertSlot( 'bottom_pane' ) ?>
</div>
<?php endif ?>
