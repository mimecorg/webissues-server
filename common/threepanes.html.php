<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<table class="pane-layout">
<tr>
<td <?php if ( $this->hasSlot( 'bottom_pane' ) ) echo 'rowspan="2" ' ?>class="left-pane">
<div class="pane-wrapper">
<?php $this->insertSlot( 'left_pane' ) ?>
</div>
</td>
<td class="top-pane">
<div class="pane-wrapper">
<?php $this->insertSlot( 'top_pane' ) ?>
</div>
</td>
</tr>
<?php if ( $this->hasSlot( 'bottom_pane' ) ): ?>
<tr>
<td class="bottom-pane">
<div class="pane-wrapper">
<?php $this->insertSlot( 'bottom_pane' ) ?>
</div>
</td>
</tr>
<?php endif ?>
</table>
