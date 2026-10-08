<?php
/** /exhibitions/ — list, empty-state ready. */
get_header();
$page = get_page_by_path( 'exhibitions' );
?>
<article class="page-plain">
	<h1 class="page-plain__title">Exhibitions</h1>
	<div class="page-plain__content">
		<?php if ( $page && $page->post_content ) : ?>
			<?php echo apply_filters( 'the_content', $page->post_content ); ?>
		<?php endif; ?>
		<?php if ( have_posts() ) : ?>
			<ul class="exhibitions">
				<?php while ( have_posts() ) : the_post();
					$id    = get_the_ID();
					$venue = jca_meta( $id, 'venue' );
					$city  = jca_meta( $id, 'city' );
					$start = jca_meta( $id, 'start_date' );
					$end   = jca_meta( $id, 'end_date' );
					$dates = $start ? date_i18n( 'F j, Y', strtotime( $start ) ) . ( $end ? ' – ' . date_i18n( 'F j, Y', strtotime( $end ) ) : '' ) : '';
					?>
					<li>
						<div><span class="jca-caption__title"><?php the_title(); ?></span></div>
						<div><?php echo esc_html( implode( ', ', array_filter( [ $venue, $city ] ) ) ); ?></div>
						<?php if ( $dates ) : ?><div><?php echo esc_html( $dates ); ?></div><?php endif; ?>
						<?php if ( jca_meta( $id, 'note' ) ) : ?><div><?php echo esc_html( jca_meta( $id, 'note' ) ); ?></div><?php endif; ?>
						<?php if ( jca_meta( $id, 'link' ) ) : ?><div><a href="<?php echo esc_url( jca_meta( $id, 'link' ) ); ?>" target="_blank" rel="noopener">Details</a></div><?php endif; ?>
					</li>
				<?php endwhile; ?>
			</ul>
		<?php else : ?>
			<p class="page-plain__empty">No exhibitions listed yet. Check back, or <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">get in touch</a> about showing the work.</p>
		<?php endif; ?>
	</div>
</article>
<?php
get_footer();
