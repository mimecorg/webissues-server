<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<p><?php echo $this->tr( 'Edit permissions of user <strong>%1</strong>.', null, $user[ 'user_name' ] ) ?></p>

<?php $form->renderFormOpen(); ?>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'Global Access' ) ?></legend>

<?php if ( !empty( $canChangeAccess ) ): ?>
<div style="float: right">
<?php echo $this->imageAndTextLink( $this->mergeQueryString( '/admin/users/access.php' ), '/common/images/edit-modify-16.png', $this->tr( 'Change' ) ) ?>
</div>
<?php endif ?>

<p><?php echo $systemLevel ?></p>

</fieldset>

<div class="toolbar">
<?php $toolBar->render() ?>
</div>

<?php if ( !empty( $projects ) ): ?>

<table class="grid">
<tr>
<?php $grid->renderHeader( $this->tr( 'Name' ), 'name' ) ?>
<?php $grid->renderHeader( $this->tr( 'Access' ), 'access' ) ?>
</tr>

<?php foreach ( $projects as $projectId => $project ): ?>
<?php $grid->renderRowOpen( $projectId ) ?>

<td>
<?php
    if ( $project[ 'project_access' ] == System_Const::AdministratorAccess ):
        $image = '/common/images/project-admin-16.png';
    else:
        $image = '/common/images/project-16.png';
    endif;
    echo $this->imageAndTextLink( $this->mergeQueryString( '/admin/users/projects.php', array( 'project' => $projectId ) ), $image, $project[ 'project_name' ] ) ?>
</td>
<td><?php echo $project[ 'access_level' ] ?></td>

<?php $grid->renderRowClose() ?>
<?php endforeach ?>

</table>

<?php $grid->renderPager() ?>

<?php else: ?>

<p class="noitems"><?php echo $this->tr( 'This user is not a member of any project.' ) ?></p>

<?php endif ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ) ?>
</div>

<?php $form->renderFormClose() ?>
