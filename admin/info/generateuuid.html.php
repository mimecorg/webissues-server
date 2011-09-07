<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<p><?php echo $this->tr( 'A new, random unique identifier will be assigned to this server.' ) ?></p>

<?php $form->renderFormOpen(); ?>

<table class="info-list">
<tr>
<td><?php echo $this->tr( 'Current ID:' ) ?></td>
<td><?php echo $server[ 'server_uuid' ] ?></td>
</tr>
<tr>
<td><?php echo $this->tr( 'New ID:' ) ?></td>
<td><?php echo $newUuid ?></td>
</tr>
</table>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ); ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ); ?>
</div>

<?php $form->renderFormClose() ?>
