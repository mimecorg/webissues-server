<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<?php $form->renderFormOpen() ?>

<table class="info-list">
<tr>
<td><?php echo $this->tr( 'Type:' ) ?></td>
<td><?php echo $event[ 'event_type' ] ?></td>
</tr>
<tr>
<td><?php echo $this->tr( 'Severity:' ) ?></td>
<td><?php echo $this->image( $event[ 'icon' ], $event[ 'severity' ] ) . ' ' . $event[ 'severity' ] ?></td>
</tr>
<tr>
<td><?php echo $this->tr( 'Date:' ) ?></td>
<td><?php echo $event[ 'date' ] ?></td>
</tr>
<tr>
<td><?php echo $this->tr( 'Message:' ) ?></td>
<td><?php echo nl2br( $event[ 'event_message' ] ) ?></td>
</tr>
<tr>
<td style="white-space: nowrap"><?php echo $this->tr( 'User name:' ) ?></td>
<td><?php echo $event[ 'user_name' ] ?></td>
</tr>
<tr>
<td style="white-space: nowrap"><?php echo $this->tr( 'Host name:' ) ?></td>
<td><?php echo $event[ 'host_name' ] ?></td>
</tr>
</table>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ) ?>
</div>

<?php $form->renderFormClose() ?>
