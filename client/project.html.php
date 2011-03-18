<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<?php if ( !empty( $toolBar ) ): ?>
<div class="nested-float" style="float: right">
<?php $toolBar->render() ?>
</div>
<?php endif ?>

<h2><?php echo $projectName ?></h2>
