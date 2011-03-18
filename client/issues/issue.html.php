<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<?php if ( $form->hasErrors() ): ?>
<p class="error">
<?php echo $this->tr( 'Some of the values you entered are incorrect.' ) ?>
</p>
<?php endif ?>

<?php if ( !empty( $folderName ) ): ?>
<p><?php echo $this->tr( 'Create a new issue in folder <strong>%1</strong>.', null, $folderName ) ?></p>
<?php elseif ( !empty( $oldIssueName ) ): ?>
<p><?php echo $this->tr( 'Edit attributes of issue <strong>%1</strong>.', null, $oldIssueName ) ?></p>
<?php endif ?>

<?php $form->renderFormOpen(); ?>

<?php $form->renderText( $this->tr( 'Name:' ), 'issueName', array( 'size' => 80 ) ); ?>

<?php if ( !empty( $attributes ) ): ?>
<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'Attributes' ) ?></legend>

<?php
    foreach ( $attributes as $attributeId => $attribute ):
        $label = $this->tr( '%1:', null, $attribute[ 'attr_name' ] );
        $key = 'value' . $attributeId;
        if ( isset( $items[ $attributeId ] ) ):
            $form->renderSelect( $label, $key, $items[ $attributeId ], array( 'style' => 'width: 30em;' ) );
        elseif ( !empty( $multiLine[ $attributeId ] ) ):
            $form->renderTextArea( $label, $key, array( 'style' => 'width: 30em;', 'rows' => 6 ) );
        else:
            $form->renderText( $label, $key, array( 'style' => 'width: 30em;' ) );
        endif;
    endforeach
?>

</fieldset>
<?php endif ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ); ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ); ?>
</div>

<?php $form->renderFormClose() ?>
