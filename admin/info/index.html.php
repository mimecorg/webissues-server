<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'Server Information' ) ?></legend>

<div style="float: right">
<?php $toolBar->render() ?>
</div>

<table class="info-list">
<tr>
<td><?php echo $this->tr( 'Version:' ) ?></td>
<td><?php echo $server[ 'db_version' ] ?></td>
</tr>
<tr>
<td><?php echo $this->tr( 'Name:' ) ?></td>
<td><?php echo $server[ 'server_name' ] ?></td>
</tr>
<tr>
<td><?php echo $this->tr( 'Unique ID:' ) ?></td>
<td><?php echo $server[ 'server_uuid' ] ?></td>
</tr>
</table>

</fieldset>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'Cron Job' ) ?></legend>

<p><?php echo $this->tr( 'The cron job is required for the email notifications to work.' ) ?></p>

<?php if ( empty( $cronLast ) && empty( $cronCurrent ) ): ?>
<p class="error"><?php echo $this->tr( 'The cron job was never started.' ) ?></p>
<?php elseif ( !empty( $cronOld ) ): ?>
<p class="error"><?php echo $this->tr( 'The cron job was not started within the last 24 hours.' ) ?></p>
<?php else: ?>
<p>
<?php if ( !empty( $cronCurrent ) ) echo $this->tr( 'The cron job is currently running.' ) . ' ' ?>
<?php if ( !empty( $cronLast ) ) echo $this->tr( 'The last cron job was started on %1.', null, $cronLast ) ?>
</p>
<?php endif ?>

</fieldset>
