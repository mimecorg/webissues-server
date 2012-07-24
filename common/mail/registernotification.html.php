<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<h1>
<?php
    if ( $baseUrl != '' ):
        echo $this->link( $baseUrl . 'admin/register/index.php', $this->tr( 'Registration Requests' ) );
    else:
        echo $this->tr( 'Registration Requests' );
    endif;
?>
</h1>

<p><?php echo $this->tr( 'You have the following new registration requests pending for approval:' ) ?></p>

<table class="grid">
<tr>
<th><?php echo $this->tr( 'Name' ) ?></th>
<th><?php echo $this->tr( 'Login' ) ?></th>
<th><?php echo $this->tr( 'Email' ) ?></th>
<th><?php echo $this->tr( 'Date' ) ?></th>
</tr>
<?php foreach ( $requests as $requestId => $request ): ?>
<tr>
<td><?php echo $request[ 'user_name' ] ?></td>
<td><?php echo $request[ 'user_login' ] ?></td>
<td><?php echo $request[ 'user_email' ] ?></td>
<td><?php echo $request[ 'date' ] ?></td>
</tr>
<?php endforeach ?>
</table>

<p><?php echo $this->tr( 'This is an alert email from the WebIssues Server.' ) ?></p>
