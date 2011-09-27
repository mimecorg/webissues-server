<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<?php $this->beginSlot( 'float_links' ) ?>
<?php $filterBar->renderNoFilter( $this->tr( 'All Users' ) ) ?>
<?php $filterBar->renderFilters( $userTypes ) ?>
<?php $this->endSlot() ?>

<div class="toolbar">
<?php $toolBar->render() ?>
</div>

<table class="grid">
<tr>
<?php $grid->renderHeader( $this->tr( 'Name' ), 'name' ) ?>
<?php $grid->renderHeader( $this->tr( 'Login' ), 'login' ) ?>
<?php $grid->renderHeader( $this->tr( 'Access Level' ), 'access' ) ?>
</tr>

<?php foreach ( $users as $userId => $user ): ?>
<?php $grid->renderRowOpen( $userId, $user[ 'classes' ] ) ?>

<td>
<?php
    if ( $user[ 'user_access' ] == System_Const::AdministratorAccess ):
        $image = '/common/images/user-admin-16.png';
    elseif ( $user[ 'user_access' ] == System_Const::NoAccess ):
        $image = '/common/images/user-disabled-16.png';
    else:
        $image = '/common/images/user-16.png';
    endif;
    echo $this->imageAndTextLink( $this->mergeQueryString( '/admin/users/index.php', array( 'id' => $userId ) ), $image, $user[ 'user_name' ] );
?>
</td>    
<td><?php echo $user[ 'user_login' ] ?></td>
<td><?php echo $user[ 'access_level' ] ?></td>

<?php $grid->renderRowClose() ?>
<?php endforeach ?>

</table>

<?php $grid->renderPager() ?>
