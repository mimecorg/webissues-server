<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<h3>
<?php
    echo $this->tr( 'Description' );
    echo '&nbsp; <span class="edited">(' . $this->tr( 'Last Edited:' ) . ' ' . $descr[ 'modified_date' ] . ' &mdash; ' . $descr[ 'modified_by' ] . ')</span>';
?>
</h3>

<div class="comment-text"><?php echo $descr[ 'descr_text' ] ?></div>

<?php $form->renderFormOpen(); ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ); ?>
</div>

<?php $form->renderFormClose() ?>
