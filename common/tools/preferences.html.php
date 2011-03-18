<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<?php if ( isset( $user ) ): ?>
<p><?php echo $this->tr( 'Configure preferences for user <strong>%1</strong>.', null, $user[ 'user_name' ] ) ?></p>
<?php endif ?>

<?php $form->renderFormOpen() ?>

<?php $this->insertComponent( 'Common_Tools_Locale', $form ) ?>

<?php if ( $emailEngine ): ?>
<fieldset class="form-fieldset">
<legend><?php echo $this->tr( 'Notifications' ) ?></legend>

<?php $form->renderText( $this->tr( 'Email address:' ), 'email', array( 'size' => 40 ) ) ?>

<p><?php echo $this->tr( 'This address will only be visible to the system administrator.'
 . ' You will not receive notifications if you leave this field empty.' ) ?></p>

<h4><?php echo $this->tr( 'Alert Notifications' ) ?></h4>

<?php $form->renderCheckBox( $this->tr( 'Do not include issues that I have already read' ), 'notifyNoRead' ) ?>

<h4><?php echo $this->tr( 'Summary Schedule' ) ?></h4>

<div style="float: right">
<?php echo $this->link( '#', $this->tr( 'Select All' ), array( 'id' => 'day-select', 'style' => 'display: none' ) ) ?>
|
<?php echo $this->link( '#', $this->tr( 'Unselect All' ), array( 'id' => 'day-unselect', 'style' => 'display: none' ) ) ?>
</div>
<div><?php echo $this->tr( 'Send on the following days:' ) ?></div>

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

<div style="float: right">
<?php echo $this->link( '#', $this->tr( 'Select All' ), array( 'id' => 'hour-select', 'style' => 'display: none' ) ) ?>
|
<?php echo $this->link( '#', $this->tr( 'Unselect All' ), array( 'id' => 'hour-unselect', 'style' => 'display: none' ) ) ?>
</div>

<div><?php echo $this->tr( 'Send at the following hours:' ) ?></div>

<div id="hour-choices" class="form-checkgroup">
<?php foreach ( $hours as $numericHour => $textHour ): ?>
<div style="float: left; width: 12%">
<?php $form->renderCheckBox( $textHour, 'hour' . $numericHour ) ?>
</div>
<?php endforeach; ?>
<div style="clear: left"></div>
</div>

<p><?php echo $this->tr( 'You will not receive summary emails if you do not select any day and hour.' ) ?></p>

</fieldset>
<?php endif ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ) ?>
<?php $form->renderSubmit( $this->tr( 'Cancel' ), 'cancel' ) ?>
</div>

<?php $form->renderFormClose() ?>
