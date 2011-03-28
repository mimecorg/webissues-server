<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<div style="float: right;">
<?php $toolBar->render() ?>
</div>

<p><?php echo $this->tr( 'Members of project <strong>%1</strong>.', null, $project[ 'project_name' ] ) ?></p>

<?php $form->renderFormOpen(); ?>

<table class="grid">
<tr>
<?php $grid->renderHeader( $this->tr( 'Name' ), 'name' ) ?>
<?php $grid->renderHeader( $this->tr( 'Access Level' ), 'access' ) ?>
</tr>

<?php foreach ( $members as $userId => $member ): ?>
<?php $grid->renderRowOpen( $userId, $member[ 'classes' ] ) ?>

<td>
<?php
    if ( $member[ 'project_access' ] == System_Const::AdministratorAccess ):
        $image = '/common/images/user-admin-16.png';
    else:
        $image = '/common/images/user-16.png';
    endif;
    echo $this->imageAndTextLink( $this->mergeQueryString( '/client/projects/members.php', array( 'user' => $userId ) ), $image, $member[ 'user_name' ] ) ?>
</td>
<td><?php echo $member[ 'access_level' ] ?></td>

<?php $grid->renderRowClose() ?>
<?php endforeach ?>

</table>

<?php $grid->renderPager() ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ) ?>
</div>

<?php $form->renderFormClose() ?>