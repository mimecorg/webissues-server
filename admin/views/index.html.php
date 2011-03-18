<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<p><?php echo $this->tr( 'Edit public view settings for type <strong>%1</strong>.', null, $type[ 'type_name' ] ) ?></p>

<?php $form->renderFormOpen(); ?>

<?php if ( $order ): ?>
<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'Order of Attributes' ) ?></legend>

<div style="float: right">
<?php echo $this->imageAndTextLink( $this->mergeQueryString( '/admin/views/order.php' ), '/common/images/edit-modify-16.png', $this->tr( 'Modify' ) ) ?>
</div>

<p><?php echo $order ?></p>

</fieldset>
<?php endif ?>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'Default View' ) ?></legend>

<div style="float: right">
<?php echo $this->imageAndTextLink( $this->mergeQueryString( '/admin/views/default.php' ), '/common/images/edit-modify-16.png', $this->tr( 'Modify' ) ) ?>
</div>

<table class="info-list">
<tr>
<td><?php echo $this->tr( 'Columns:' ) ?></td>
<td><?php echo $defaultView[ 'columns' ] ?></td>
</tr>
<tr>
<td><?php echo $this->tr( 'Sort By:' ) ?></td>
<td><?php echo $defaultView[ 'sort' ]; ?></td>
</tr>
</table>

</fieldset>

<p style="float: right;">
<?php $toolBar->render() ?>
</p>

<table class="grid">
<tr>
<?php $grid->renderHeader( $this->tr( 'Name' ), 'name' ) ?>
<?php $grid->renderHeader( $this->tr( 'Columns' ) ) ?>
<?php $grid->renderHeader( $this->tr( 'Sort By' ) ) ?>
<?php $grid->renderHeader( $this->tr( 'Filter' ) ) ?>
</tr>

<?php foreach ( $views as $viewId => $view ): ?>
<?php $grid->renderRowOpen( $viewId ) ?>

<td><?php echo $this->imageAndTextLink( $this->mergeQueryString( '/client/views/index.php', array( 'id' => $viewId ) ),
        '/common/images/view-16.png', $view[ 'view_name' ] ) ?></td>
<td><?php echo $view[ 'columns' ] ?></td>
<td><?php echo $view[ 'sort' ] ?></td>
<td><?php echo $view[ 'conditions' ] ?></td>

<?php $grid->renderRowClose() ?>
<?php endforeach ?>

</table>

<?php $grid->renderPager() ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ); ?>
</div>

<?php $form->renderFormClose() ?>
