<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<p><?php echo $this->tr( 'Modify the order of attributes for type <strong>%1</strong>.', null, $type[ 'type_name' ] ) ?></p>

<?php $form->renderFormOpen(); ?>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'Attributes' ) ?></legend>

<table class="form-inline">
<tr>
<td style="width: 200px;"><p><?php echo $this->tr( 'Name' ) ?></p></td>
<td><p><?php echo $this->tr( 'Order' ) ?></p></td>
</tr>
<?php foreach ( $order as $id => $name ): ?>
<tr>
<td><?php echo $name ?></td>
<td><?php $form->renderSelect( null, "order$id", $orderOptions, array( 'style' => 'width: 70px' ) ) ?></td>
</tr>
<?php endforeach ?>
</table>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'Update' ), 'update' ) ?>
</div>

</fieldset>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ); ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ); ?>
</div>

<?php $form->renderFormClose() ?>
