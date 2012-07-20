<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<h1><?php echo $this->tr( 'WebIssues Server registration approved' ) ?></h1>

<p><?php echo $this->tr( 'Your registration request at the WebIssues Server was approved by the administrator.' ) ?></p>

<p><?php echo $this->tr( 'To log in to the WebIssues Server, please visit the following URL:' ) ?></p>

<p><?php echo $this->link( $loginUrl, $loginUrl ) ?></p>

<ul>
<li><?php echo $this->tr( 'User name: %1', null, $userName ) ?></li>
<li><?php echo $this->tr( 'Login: %1', null, $login ) ?></li>
<li><?php echo $this->tr( 'Email address: %1', null, $email ) ?></li>
</ul>
