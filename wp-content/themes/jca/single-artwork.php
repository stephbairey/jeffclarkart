<?php
/** /work/{slug}/ — one painting. */
get_header();
the_post();
$id      = get_the_ID();
$nav     = jca_prev_next( $id );
$terms   = get_the_terms( $id, 'series' );
$framed  = jca_dimensions( $id, true );
$history = jca_exhibition_history( $id );
$group   = trim( (string) jca_meta( $id, 'variant_group' ) );
?>
<article class="artwork">
	<div class="artwork__layout">
		<div class="artwork__img" style="aspect-ratio:<?php echo esc_attr( jca_aspect( $id ) ); ?>">
			<?php echo jca_image( $id, 'jca-detail', [ 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '(max-width: 899px) 100vw, 60vw' ] ); ?>
		</div>
		<div class="artwork__meta">
			<div class="jca-label artwork__artist">Jeff Clark</div>
			<h1 class="artwork__title"><?php the_title(); ?><?php if ( jca_year( $id ) ) : ?><span>, <?php echo esc_html( jca_year( $id ) ); ?></span><?php endif; ?></h1>
			<ul class="artwork__rows">
				<li><?php echo esc_html( jca_medium_line( $id ) ); ?></li>
				<?php if ( $framed ) : ?><li>Framed: <?php echo esc_html( $framed ); ?></li><?php endif; ?>
				<?php if ( $terms && ! is_wp_error( $terms ) ) : ?>
					<li>Series: <?php echo implode( ', ', array_map( fn( $t ) => '<a href="' . esc_url( get_term_link( $t ) ) . '">' . esc_html( $t->name ) . '</a>', $terms ) ); ?></li>
				<?php endif; ?>
				<?php if ( $group ) : ?><li>Set: <?php echo esc_html( $group ); ?></li><?php endif; ?>
				<?php if ( jca_meta( $id, 'inventory_code' ) ) : ?><li>No. <?php echo esc_html( jca_meta( $id, 'inventory_code' ) ); ?></li><?php endif; ?>
			</ul>
			<div class="artwork__price"><?php echo esc_html( jca_price_line( $id ) ); ?></div>
			<?php if ( jca_status( $id ) !== 'sold' ) : ?>
				<div class="artwork__inquire"><a class="jca-label jca-label--rule" href="<?php echo esc_url( jca_contact_url( $id ) ); ?>">Contact me about this painting</a></div>
			<?php endif; ?>
			<?php if ( get_the_content() ) : ?>
				<div class="artwork__desc page-plain__content"><?php the_content(); ?></div>
			<?php endif; ?>
			<?php if ( $history ) : ?>
				<div class="artwork__section">
					<span class="jca-label">Exhibitions</span>
					<ul>
						<?php foreach ( $history as $h ) : ?>
							<li><?php echo esc_html( implode( ', ', array_filter( [ $h['venue'] ?? '', $h['city'] ?? '', $h['year'] ?? '' ] ) ) ); ?><?php if ( ! empty( $h['note'] ) ) : ?> — <?php echo esc_html( $h['note'] ); ?><?php endif; ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
			<div class="artwork__section" style="font-size:13px">&copy; <?php echo esc_html( jca_year( $id ) ?: date_i18n( 'Y' ) ); ?> Jeff Clark</div>
		</div>
	</div>
	<nav class="artwork__nav" aria-label="Previous and next painting">
		<span><?php if ( $nav['prev'] ) : ?><a href="<?php echo esc_url( get_permalink( $nav['prev'] ) ); ?>" rel="prev">← Previous</a><?php endif; ?></span>
		<a href="<?php echo esc_url( $nav['term'] ? get_term_link( $nav['term'] ) : get_post_type_archive_link( 'artwork' ) ); ?>"><?php echo $nav['term'] ? esc_html( $nav['term']->name ) : 'All work'; ?></a>
		<span><?php if ( $nav['next'] ) : ?><a href="<?php echo esc_url( get_permalink( $nav['next'] ) ); ?>" rel="next">Next →</a><?php endif; ?></span>
	</nav>
</article>
<?php
get_footer();
