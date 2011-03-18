<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<p>
<?php
if ( $isPublic ):
    echo $this->tr( 'Are you sure you want to delete public view <strong>%1</strong>?', null, $oldView[ 'view_name' ] );
else:
    echo $this->tr( 'Are you sure you want to delete your personal view <strong>%1</strong>?', null, $oldView[ 'view_name' ] );
endif;
?>
</p>

<?php $form->renderFormOpen(); ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ); ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ); ?>
</div>

<?php $form->renderFormClose() ?>
