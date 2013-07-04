<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<p><?php echo $this->tr( 'Your email was successfully registered in the WebIssues system as the following issue:' ) ?></p>

<ul>
<li><?php echo $this->tr( 'ID: %1', null, '#' . $issueId ) ?></li>
<li><?php echo $this->tr( 'Name: %1', null, $issueName ) ?></li>
</ul>

<p><?php echo $this->tr( 'You can add comments and attachments to this issue by responding to this email. Include %1 in the subject when sending further emails regarding this issue.',
    null, '[#' . $issueId . ']' ) ?></p>

<?php if ( !empty( $issueUrl ) ): ?>
<p><?php echo $this->tr( 'To view the status of this issue in the WebIssues Server, visit the following URL:' ) ?></p>

<p><?php echo $this->link( $issueUrl, $issueUrl ) ?></p>
<?php endif ?>
