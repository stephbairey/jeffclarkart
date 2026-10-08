<?php
/** Catalog tile. Expects $args['id']. */
$id = (int) ( $args['id'] ?? get_the_ID() );
?>
<figure class="tile">
	<a href="<?php echo esc_url( get_permalink( $id ) ); ?>">
		<div class="tile__img"><?php echo jca_image( $id, 'jca-tile', [ 'sizes' => '(max-width: 759px) 100vw, (max-width: 1023px) 50vw, 33vw' ] ); ?></div>
		<figcaption class="jca-caption"><?php echo jca_caption( $id ); ?></figcaption>
	</a>
</figure>
