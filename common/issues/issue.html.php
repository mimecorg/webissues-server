<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<?php if ( $form->hasErrors() ): ?>
<p class="error">
<?php echo $this->tr( 'Some of the values you entered are incorrect.' ) ?>
</p>
<?php endif ?>

<?php if ( !empty( $clone ) ): ?>
<p><?php echo $this->tr( 'Clone issue <strong>%1</strong> as a new issue in the selected folder.', null, $oldIssueName ) ?></p>
<?php elseif ( !empty( $folderName ) ): ?>
<p><?php echo $this->tr( 'Create a new issue in folder <strong>%1</strong>.', null, $folderName ) ?></p>
<?php elseif ( !empty( $oldIssueName ) ): ?>
<p><?php echo $this->tr( 'Edit attributes of issue <strong>%1</strong>.', null, $oldIssueName ) ?></p>
<?php else: ?>
<p><?php echo $this->tr( 'Create a new issue in the selected folder.' ) ?></p>
<?php endif ?>

<?php $form->renderFormOpen(); ?>

<?php if ( empty( $noMembers ) && empty( $noFolders ) ): ?>

<?php $form->renderText( $this->tr( 'Name:' ), 'issueName', array( 'size' => 80 ) ); ?>

<?php if ( !empty( $folders ) ): ?>

<?php $form->renderSelect( $this->tr( 'Folder:' ), 'targetFolder', $folders, array( 'style' => 'width: 25em;' ) ) ?>

<?php endif ?>

<?php if ( !empty( $attributes ) ): ?>
<div class="form-fieldset">
<div class="form-legend"><?php echo $this->tr( 'Attributes' ) ?></div>

<?php
    foreach ( $attributes as $attributeId => $attribute ):
        $label = $this->tr( '%1:', null, $attribute[ 'attr_name' ] );
        $key = 'value' . $attributeId;
        if ( !empty( $multiLine[ $attributeId ] ) ):
            $form->renderTextArea( $label, $key, array( 'cols' => 60, 'rows' => 6 ) );
        else:
            $form->renderText( $label, $key, array( 'size' => 60 ) );
        endif;
    endforeach
?>

</div>
<?php endif ?>

<?php if ( !empty( $showDescription ) ): ?>
<?php $form->renderTextArea( $this->tr( 'Description:' ), 'descriptionText', array( 'cols' => 120, 'rows' => 20 ) ); ?>

<div class="comment-text preview" id="descriptionPreview" style="display: none;"></div>

<?php $form->renderSelect( $this->tr( 'Text format:' ), 'format', $formatOptions, array( 'style' => 'width: 25em;' ) ) ?>
<?php endif ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ); ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ); ?>
</div>

<?php else: ?>

<?php if ( !empty( $noFolders ) ): ?>
<p class="error"><?php echo $this->tr( 'There are no available folders of this type.' ) ?></p>
<?php else: ?>
<p class="error"><?php echo $this->tr( 'There are no available project members to assign to the issue.' ) ?></p>
<?php endif ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'Close' ), 'close' ); ?>
</div>

<?php endif ?>

<?php $form->renderFormClose() ?>
