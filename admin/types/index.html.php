<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<div class="toolbar">
<?php $toolBar->render() ?>
</div>

<?php if ( !empty( $types ) ): ?>

<table class="grid">
<tr>
<?php $grid->renderHeader( $this->tr( 'Name' ), 'name' ) ?>
<?php $grid->renderHeader( $this->tr( 'Type' ) ) ?>
<?php $grid->renderHeader( $this->tr( 'Default Value' ) ) ?>
<?php $grid->renderHeader( $this->tr( 'Required' ) ) ?>
<?php $grid->renderHeader( $this->tr( 'Details' ) ) ?>
</tr>

<?php foreach ( $types as $typeId => $type ): ?>
<?php $grid->renderParentRowOpen( $typeId ) ?>

<td colspan="5">
  <?php $grid->renderExpandButton( empty( $type[ 'attributes' ] ) ) ?>
  <?php echo $this->imageAndTextLink( $this->mergeQueryString( '/admin/types/index.php', array( 'type' => $typeId, 'attribute' => null ) ),
    '/common/images/type-16.png', $type[ 'type_name' ] ) ?>
</td>

<?php $grid->renderRowClose() ?>

<?php foreach ( $type[ 'attributes' ] as $attributeId => $attribute ): ?>
<?php $grid->renderChildRowOpen( $attributeId, $typeId ) ?>

<td class="first-column" style="white-space: nowrap">
  <?php echo $this->imageAndTextLink( $this->mergeQueryString( '/admin/types/index.php', array( 'attribute' => $attributeId, 'type' => null ) ),
    '/common/images/attribute-16.png', $attribute[ 'attr_name' ] ) ?>
</td>
<td style="white-space: nowrap"><?php echo $attribute[ 'type' ] ?></td>
<td><?php echo $attribute[ 'default_value' ] ?></td>
<td><?php echo $attribute[ 'required' ] ?></td>
<td><?php echo $attribute[ 'details' ] ?></td>

<?php $grid->renderRowClose() ?>
<?php endforeach ?>

<?php endforeach ?>

</table>

<?php $grid->renderPager() ?>

<?php else: ?>

<p class="noitems"><?php echo $this->tr( 'There are no issue types.' ) ?></p>

<?php endif ?>
