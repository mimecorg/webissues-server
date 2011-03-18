<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<table class="pane-layout">
<tr>
<td class="top-pane">
<div class="pane-wrapper">

<?php if ( $this->hasSlot( 'float_links' ) ): ?>
<div style="float: right;">
<?php $this->insertSlot( 'float_links' ) ?>
</div>
<?php endif ?>

<h1<?php if ( $this->hasSlot( 'header_class' ) ): ?> class="<?php $this->insertSlot( 'header_class' ) ?>"<?php endif ?>><?php echo $header ?></h1>

<?php $this->insertContent() ?>

</div>
</td>
</tr>
</table>
