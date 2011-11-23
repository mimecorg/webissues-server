<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<p><?php echo $this->tr( 'Edit personal view settings for type <strong>%1</strong>.', null, $type[ 'type_name' ] ) ?></p>

<div class="toolbar">
<?php $toolBar->render() ?>
</div>

<?php $form->renderFormOpen(); ?>

<?php if ( !empty( $views ) ): ?>

<table class="grid">
<tr>
<?php $grid->renderHeader( $this->tr( 'Name' ), 'name' ) ?>
<?php $grid->renderHeader( $this->tr( 'Columns' ) ) ?>
<?php $grid->renderHeader( $this->tr( 'Sort By' ) ) ?>
<?php $grid->renderHeader( $this->tr( 'Filter' ) ) ?>
</tr>

<?php foreach ( $views as $viewId => $view ): ?>
<?php $grid->renderRowOpen( $viewId ) ?>

<td><?php echo $this->imageAndTextLink( $this->mergeQueryString( '/client/views/index.php', array( 'id' => $viewId ) ),
        '/common/images/view-16.png', $view[ 'view_name' ] ) ?></td>
<td><?php echo $view[ 'columns' ] ?></td>
<td><?php echo $view[ 'sort' ] ?></td>
<td><?php echo $view[ 'conditions' ] ?></td>

<?php $grid->renderRowClose() ?>
<?php endforeach ?>

</table>

<?php $grid->renderPager() ?>

<?php else: ?>

<p class="noitems"><?php echo $this->tr( 'This type has no personal views.' ) ?></p>

<?php endif ?>

<div class="form-submit">
<?php $form->renderSubmit( $this->tr( 'OK' ), 'ok' ) ?>
</div>

<?php $form->renderFormClose() ?>
