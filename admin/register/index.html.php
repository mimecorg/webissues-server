<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<div class="toolbar">
<?php $toolBar->render() ?>
</div>

<?php if ( !empty( $requests ) ): ?>

<table class="grid">
<tr>
<?php $grid->renderHeader( $this->tr( 'Name' ), 'name' ) ?>
<?php $grid->renderHeader( $this->tr( 'Login' ), 'login' ) ?>
<?php $grid->renderHeader( $this->tr( 'Email' ), 'email' ) ?>
<?php $grid->renderHeader( $this->tr( 'Date' ), 'date' ) ?>
</tr>

<?php foreach ( $requests as $requestId => $request ): ?>
<?php $grid->renderRowOpen( $requestId ) ?>

<td>
<?php
    echo $this->imageAndTextLink( $this->mergeQueryString( '/admin/register/index.php', array( 'id' => $requestId ) ), '/common/images/user-new-16.png', $request[ 'user_name' ] );
?>
</td>    
<td><?php echo $request[ 'user_login' ] ?></td>
<td><?php echo $request[ 'user_email' ] ?></td>
<td><?php echo $request[ 'date' ] ?></td>

<?php $grid->renderRowClose() ?>
<?php endforeach ?>

</table>

<?php $grid->renderPager() ?>

<?php else: ?>

<p class="noitems"><?php echo $this->tr( 'There are no pending registration requests.' ) ?></p>

<?php endif ?>
