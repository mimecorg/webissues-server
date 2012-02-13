<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<h2>
<?php
    if ( $baseUrl != '' ):
        echo $this->link( $this->appendQueryString( $baseUrl . 'client/index.php', array( 'folder' => $folderId, 'view' => $viewId != 0 ? $viewId : null ) ),
            $projectName . ' - ' . $folderName . ' - ' . $viewName );
    else:
        echo $projectName . ' - ' . $folderName . ' - ' . $viewName;
    endif;
?>
</h2>

<table class="grid">
<tr>
<?php foreach ( $columns as $column => $name ): ?>
<th><?php echo $headers[ $column ] ?></th>
<?php endforeach ?>
</tr>
<?php foreach ( $issues as $issueId => $issue ): ?>
<tr>
<?php foreach ( $columns as $column => $name ): ?>
<td>
<?php
    if ( $column == System_Api_Column::Name && $baseUrl != '' ):
        echo $this->link( $this->appendQueryString( $baseUrl . 'client/index.php', array( 'issue' => $issueId ) ), $issue[ $name ] );
    else:
        echo $issue[ $name ];
    endif;
?>
</td>
<?php endforeach ?>
</tr>
<?php endforeach ?>
</table>

<p><?php echo $this->tr( 'This is an alert email from the WebIssues Server.' ) ?></p>
