<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<?php if ( $form->hasErrors() ): ?>
<p class="error">
<?php echo $this->tr( 'Some of the values you entered are incorrect.' ) ?>
</p>
<?php endif ?>

<p>
<?php
    if ( $isDefault ):
        echo $this->tr( 'Modify the default view for type <strong>%1</strong>.', null, $type[ 'type_name' ] );
    elseif ( $isPublic ):
        if ( !empty( $clone ) ):
            echo $this->tr( 'Clone view <strong>%1</strong> as a new public view.', null, $oldView[ 'view_name' ] );
        elseif ( !empty( $oldView ) ):
            echo $this->tr( 'Modify the public view <strong>%1</strong>.', null, $oldView[ 'view_name' ] );
        else:
            echo $this->tr( 'Create a new public view for type <strong>%1</strong>.', null, $type[ 'type_name' ] );
        endif;
    else:
        if ( !empty( $clone ) ):
            echo $this->tr( 'Clone view <strong>%1</strong> as a new personal view.', null, $oldView[ 'view_name' ] );
        elseif ( !empty( $oldView ) ):
            echo $this->tr( 'Modify your personal view <strong>%1</strong>.', null, $oldView[ 'view_name' ] );
        else:
            echo $this->tr( 'Create a new personal view for type <strong>%1</strong>.', null, $type[ 'type_name' ] );
        endif;
    endif
?>
</p>

<?php $form->renderFormOpen(); ?>

<?php if ( $oldView == null && !$isDefault || $clone ): ?>
<?php $form->renderText( $this->tr( 'Name:' ), 'viewName', array( 'size' => 40 ) ) ?>
<?php endif ?>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'Columns' ) ?></legend>

<table class="form-inline">
<tr>
<td style="width: 200px;"><p><?php echo $this->tr( 'Selected columns:' ) ?></p></td>
<td><p><?php echo $this->tr( 'Order' ) ?></p></td>
</tr>
<?php foreach ( $fixedColumns as $column => $name ): ?>
<tr>
<td><?php $form->renderCheckBox( $name, "checkColumn$column", array ( 'disabled' => true ) ) ?></td>
<td><?php $form->renderSelect( null, "orderColumn$column", $columnOrder, array( 'style' => 'width: 70px', 'disabled' => true ) ) ?></td>
</tr>
<?php endforeach ?>
<?php foreach ( $selectedColumns as $column => $name ): ?>
<tr>
<td><?php $form->renderCheckBox( $name, "checkColumn$column" ) ?></td>
<td><?php $form->renderSelect( null, "orderColumn$column", $columnOrder, array( 'style' => 'width: 70px' ) ) ?></td>
</tr>
<?php endforeach ?>
<?php if ( !empty( $availableColumns ) ): ?>
<tr>
<td><p><?php echo $this->tr( 'Available columns:' ) ?></p></td>
<td></td>
</tr>
<?php endif ?>
<?php foreach ( $availableColumns as $column => $name ): ?>
<tr>
<td><?php $form->renderCheckBox( $name, "checkColumn$column" ) ?></td>
<td><?php $form->renderSelect( null, "orderColumn$column", $columnOrder, array( 'style' => 'width: 70px' ) ) ?></td>
</tr>
<?php endforeach ?>
</table>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'Update' ), 'updateColumns' ) ?>
</div>

</fieldset>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'Sort Order' ) ?></legend>

<?php $form->renderSelect( $this->tr( 'Column:' ), 'sortColumn', $columnOptions, array( 'style' => 'width: 200px' ) ) ?>
<div><?php echo $this->tr( 'Order:' ) ?></div>
<?php $form->renderRadioGroup( 'sortOrder', $orderOptions ) ?>

</fieldset>

<?php if ( !$isDefault ): ?>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'Filter' ) ?></legend>

<table class="form-inline">
<?php if ( !empty( $activeConditions ) ): ?>
<tr>
<td style="width: 200px;"><p><?php echo $this->tr( 'Active conditions:' ) ?></p></td>
<td style="width: 200px;"></td>
<td></td>
</tr>
<?php endif ?>
<?php foreach ( $activeConditions as $index => $condition ): ?>
<tr>
<td><?php $form->renderCheckBox( $condition[ 'name' ], "checkCondition$index" ) ?></td>
<td><?php $form->renderSelect( null, "operatorCondition$index", $condition[ 'operators' ], array( 'style' => 'width: 180px;' ) ) ?></td>
<td><?php $form->renderText( null, "valueCondition$index", array( 'style' => 'width: 250px;' ) ) ?></td>
</tr>
<?php endforeach ?>
<tr>
<td style="width: 200px;"><p><?php echo $this->tr( 'Available conditions:' ) ?></p></td>
<td style="width: 200px;"></td>
<td></td>
</tr>
<?php foreach ( $availableConditions as $column => $condition ): ?>
<tr>
<td><?php $form->renderCheckBox( $condition[ 'name' ], "checkAvailable$column" ) ?></td>
<td><?php $form->renderSelect( null, "operatorAvailable$column", $condition[ 'operators' ], array( 'style' => 'width: 180px;' ) ) ?></td>
<td><?php $form->renderText( null, "valueAvailable$column", array( 'style' => 'width: 250px;' ) ) ?></td>
</tr>
<?php endforeach ?>
</table>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'Update' ), 'updateFilter' ) ?>
</div>

</fieldset>

<?php endif ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ) ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ) ?>
</div>

<?php $form->renderFormClose() ?>
