<?php if ( !defined( 'WI_VERSION' ) ) die( -1 ); ?>

<?php
    if ( !empty( $bottomPaneClass ) ):
        $this->beginSlot( 'bottom_pane' );
        $this->insertComponent( $bottomPaneClass );
        $this->endSlot();
    endif;
?>

<?php
    if ( !empty( $topPaneClass ) ):
        $this->beginSlot( 'top_pane' );
        $this->insertComponent( $topPaneClass );
        $this->endSlot();
    endif;
?>

<?php
    if ( !empty( $leftPaneClass ) ):
        $this->beginSlot( 'left_pane' );
        $this->insertComponent( $leftPaneClass );
        $this->endSlot();
    endif;
?>
