<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<p>
<?php
    if ( $isPublic ):
        if ( !empty( $oldAlert ) ):
            echo $this->tr( 'Modify public alert <strong>%1</strong>.', null, $oldAlert[ 'view_name' ] );
        elseif ( !empty( $folderName ) ):
            echo $this->tr( 'Create a new public alert for folder <strong>%1</strong>.', null, $folderName );
        else:
            echo $this->tr( 'Create a new public alert for type <strong>%1</strong>.', null, $typeName );
        endif;
    else:
        if ( !empty( $oldAlert ) ):
            echo $this->tr( 'Modify your personal alert <strong>%1</strong>.', null, $oldAlert[ 'view_name' ] );
        elseif ( !empty( $folderName ) ):
            echo $this->tr( 'Create a new personal alert for folder <strong>%1</strong>.', null, $folderName );
        else:
            echo $this->tr( 'Create a new personal alert for type <strong>%1</strong>.', null, $typeName );
        endif;
    endif
?>
</p>

<?php $form->renderFormOpen(); ?>

<?php if ( empty( $noViews ) ): ?>

<?php if ( $oldAlert == null ): ?>
<?php $form->renderSelect( $this->tr( 'View:' ), 'viewId', $viewOptions, array( 'style' => 'width: 15em;' ) ) ?>
<?php endif ?>

<?php if ( $emailEngine ): ?>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'Email Type' ) ?></legend>

<p><?php echo $this->tr( 'Send the following type of emails for this alert:' ) ?></p>
<?php $form->renderRadioGroup( 'alertEmail', $emailTypes ) ?>

<?php if ( !empty( $noEmailAddress ) ): ?>
<p class="error"><?php echo $this->tr( 'Warning: You will not receive any emails until you enter an email address in your preferences.' ) ?></p>
<?php endif ?>

</fieldset>

<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'Summary Schedule' ) ?></legend>

<div style="float: right">
<?php echo $this->link( '#', $this->tr( 'Select All' ), array( 'id' => 'day-select', 'style' => 'display: none' ) ) ?>
|
<?php echo $this->link( '#', $this->tr( 'Unselect All' ), array( 'id' => 'day-unselect', 'style' => 'display: none' ) ) ?>
</div>
<div><?php echo $this->tr( 'Send summary emails on the following days:' ) ?></div>

<div id="day-choices" class="form-checkgroup">
<?php
    $order = array();
    for ( $i = 0; $i < 7 ; $i++ ) {
        $rank = ( 7 + $i - $firstDay ) % 7;
        $order[ $rank ] = $i;
    }
?>
<?php for ( $rank = 0; $rank < 7; $rank++ ): ?>
<div style="float: left; width: 12%">
<?php $form->renderCheckBox( $days[ $order[ $rank ] ], 'day' . $order[ $rank ] ) ?>
</div>
<?php endfor; ?>
<div style="clear: left"></div>
</div>

<?php $form->renderError( 'days' ) ?>

<div style="float: right">
<?php echo $this->link( '#', $this->tr( 'Select All' ), array( 'id' => 'hour-select', 'style' => 'display: none' ) ) ?>
|
<?php echo $this->link( '#', $this->tr( 'Unselect All' ), array( 'id' => 'hour-unselect', 'style' => 'display: none' ) ) ?>
</div>

<div><?php echo $this->tr( 'Send summary emails at the following hours:' ) ?></div>

<div id="hour-choices" class="form-checkgroup">
<?php foreach ( $hours as $numericHour => $textHour ): ?>
<div style="float: left; width: 12%">
<?php $form->renderCheckBox( $textHour, 'hour' . $numericHour ) ?>
</div>
<?php endforeach; ?>
<div style="clear: left"></div>
</div>

<?php $form->renderError( 'hours' ) ?>

</fieldset>

<?php endif ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ); ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ); ?>
</div>

<?php else: ?>

<p class="error"><?php echo $this->tr( 'There are no more available views to use.' ) ?></p>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'Close' ), 'close' ); ?>
</div>

<?php endif ?>

<?php $form->renderFormClose() ?>
