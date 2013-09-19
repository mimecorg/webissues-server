<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<p><?php echo $this->tr( 'Edit permissions of project <strong>%1</strong>.', null, $project[ 'project_name' ] ) ?></p>

<?php $form->renderFormOpen(); ?>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'Global Access' ) ?></legend>

<div style="float: right">
<?php echo $this->imageAndTextLink( $this->mergeQueryString( '/client/projects/projectaccess.php' ), '/common/images/edit-modify-16.png', $this->tr( 'Change' ) ) ?>
</div>

<p><?php echo $systemLevel ?></p>

</fieldset>

<div class="toolbar">
<?php $toolBar->render() ?>
</div>

<?php if ( !empty( $members ) ): ?>

<table class="grid">
<tr>
<?php $grid->renderHeader( $this->tr( 'Name' ), 'name' ) ?>
<?php $grid->renderHeader( $this->tr( 'Access' ), 'access' ) ?>
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

<?php else: ?>

<p class="noitems"><?php echo $this->tr( 'This project has no members.' ) ?></p>

<?php endif ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ) ?>
</div>

<?php $form->renderFormClose() ?>
