<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<?php if ( !empty( $folderName ) ): ?>
<p><?php echo $this->tr( 'Edit alert settings for folder <strong>%1</strong>.', null, $folderName ) ?></p>
<?php else: ?>
<p><?php echo $this->tr( 'Edit alert settings for type <strong>%1</strong>.', null, $typeName ) ?></p>
<?php endif ?>

<?php $form->renderFormOpen(); ?>

<div class="toolbar">
<?php $toolBar->render() ?>
</div>

<?php if ( !empty( $alerts ) ): ?>

<table class="grid">
<tr>
<?php $grid->renderHeader( $this->tr( 'Name' ), 'name' ) ?>
<?php $grid->renderHeader( $this->tr( 'Total' ) ) ?>
<?php $grid->renderHeader( $this->tr( 'Unread' ) ) ?>
<?php $grid->renderHeader( $this->tr( 'Modified' ) ) ?>
<?php if ( $emailEngine ): ?>
<?php $grid->renderHeader( $this->tr( 'Email Type' ) ) ?>
<?php endif ?>
</tr>

<?php foreach ( $alerts as $alertId => $alert ): ?>
<?php $grid->renderRowOpen( $alertId ) ?>

<td>
<?php
    if ( $alert[ 'alert_unread' ] > 0 ):
        $image = '/common/images/alert-unread-16.png';
    elseif ( $alert[ 'alert_modified' ] > 0 ):
        $image = '/common/images/alert-modified-16.png';
    else:
        $image = '/common/images/alert-16.png';
    endif;
    echo $this->imageAndTextLink( $this->mergeQueryString( '/client/alerts/index.php', array( 'alert' => $alertId ) ),
        $image, $alert[ 'view_name' ] );
?>
</td>
<td<?php echo $alert[ 'alert_total' ] > 0 ? ' class="unread"' : '' ?>><?php echo $alert[ 'alert_total' ] ?></td>
<td<?php echo $alert[ 'alert_unread' ] > 0 ? ' class="unread"' : '' ?>><?php echo $alert[ 'alert_unread' ] ?></td>
<td<?php echo $alert[ 'alert_modified' ] > 0 ? ' class="unread"' : '' ?>><?php echo $alert[ 'alert_modified' ] ?></td>
<?php if ( $emailEngine ): ?>
<td><?php echo $alert[ 'alert_email' ] ?></td>
<?php endif ?>

<?php $grid->renderRowClose() ?>
<?php endforeach ?>

</table>

<?php $grid->renderPager() ?>

<?php else: ?>

<?php if ( !empty( $folderName ) ): ?>
<p class="noitems"><?php echo $this->tr( 'This folder has no alerts.' ) ?></p>
<?php else: ?>
<p class="noitems"><?php echo $this->tr( 'This type has no alerts.' ) ?></p>
<?php endif ?>

<?php endif ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ) ?>
</div>

<?php $form->renderFormClose() ?>
