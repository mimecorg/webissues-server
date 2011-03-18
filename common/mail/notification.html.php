<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<h2><?php echo $projectName . ' - ' . $folderName . ' - ' . $viewName ?></h2>

<table class="grid">
<tr>
<?php foreach ( $columns as $column => $name ): ?>
<th><?php echo $headers[ $column ] ?></th>
<?php endforeach ?>
</tr>
<?php foreach ( $issues as $issue ): ?>
<tr>
<?php foreach ( $columns as $name ): ?>
<td><?php echo $issue[ $name ] ?></td>
<?php endforeach ?>
</tr>
<?php endforeach ?>
</table>

<p><?php echo $this->tr( 'This is an alert email from the WebIssues Server.' ) ?></p>
