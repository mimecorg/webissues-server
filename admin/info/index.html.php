<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<?php $this->insertComponent( 'Admin_Info_Server' ) ?>

<?php $this->insertComponent( 'Admin_Info_Site', $form ) ?>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'Database' ) ?></legend>

<table class="info-list info-indent">
<tr>
<td><?php echo $this->tr( 'Database server:' ) ?></td>
<td><?php echo $dbServer . ' ' . $dbVersion ?></td>
</tr>
<tr>
<td><?php echo $this->tr( 'Host name:' ) ?></td>
<td><?php echo $dbHost ?></td>
</tr>
<tr>
<td><?php echo $this->tr( 'Database name:' ) ?></td>
<td><?php echo $dbDatabase ?></td>
</tr>
<tr>
<td><?php echo $this->tr( 'Table prefix:' ) ?></td>
<td><?php echo $dbPrefix ?></td>
</tr>
</table>

</fieldset>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'Cron Job' ) ?></legend>

<table class="info-list info-indent">
<tr>
<td><?php echo $this->tr( 'Cron is running:' ) ?></td>
<td><?php echo !empty( $cronCurrent ) ? $this->tr( 'yes' ) : $this->tr( 'no' ) ?></td>
</tr>
<tr>
<td><?php echo $this->tr( 'Cron last run:' ) ?></td>
<td><?php echo !empty( $cronLast ) ? $cronLast : $this->tr( 'never' ) ?></td>
</tr>
</table>

<?php $form->renderError( 'cron' ) ?>

</fieldset>
