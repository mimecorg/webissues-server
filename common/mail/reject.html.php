<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<h1><?php echo $this->tr( 'WebIssues Server registration rejected' ) ?></h1>

<p><?php echo $this->tr( 'Sorry, your registration request at the WebIssues Server was rejected by the administrator.' ) ?></p>

<ul>
<li><?php echo $this->tr( 'User name: %1', null, $userName ) ?></li>
<li><?php echo $this->tr( 'Login: %1', null, $login ) ?></li>
<li><?php echo $this->tr( 'Email address: %1', null, $email ) ?></li>
</ul>
