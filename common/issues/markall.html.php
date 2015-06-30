<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<?php if ( !empty( $folder ) ): ?>
<?php if ( $isRead ): ?>
<p><?php echo $this->tr( 'Are you sure you want to mark all issues in folder <strong>%1</strong> as read?', null, $folder[ 'folder_name' ] ) ?></p>
<?php else: ?>
<p><?php echo $this->tr( 'Are you sure you want to mark all issues in folder <strong>%1</strong> as unread?', null, $folder[ 'folder_name' ] ) ?></p>
<?php endif ?>
<?php else: ?>
<?php if ( $isRead ): ?>
<p><?php echo $this->tr( 'Are you sure you want to mark all issues of type <strong>%1</strong> as read?', null, $type[ 'type_name' ] ) ?></p>
<?php else: ?>
<p><?php echo $this->tr( 'Are you sure you want to mark all issues of type <strong>%1</strong> as unread?', null, $type[ 'type_name' ] ) ?></p>
<?php endif ?>
<?php endif ?>

<?php $form->renderFormOpen(); ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ); ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ); ?>
</div>

<?php $form->renderFormClose() ?>
