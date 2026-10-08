<?php
/** /series/{slug}/ — one series. Variant groups render as sets under a heading. */
get_header();
$term   = get_queried_object();
$groups = jca_group_variants( $GLOBALS['wp_query']->posts );
?>
<section class="catalog">
	<?php get_template_part( 'template-parts/catalog-head', null, [ 'title' => $term->name, 'description' => $term->description ] ); ?>
	<?php if ( $groups ) : ?>
		<?php foreach ( $groups as $g ) : ?>
			<div class="group">
				<?php if ( $g['heading'] !== '' ) : ?>
					<h2 class="jca-label group__title"><?php echo esc_html( $g['heading'] ); ?></h2>
				<?php endif; ?>
				<div class="grid">
					<?php foreach ( $g['posts'] as $p ) : get_template_part( 'template-parts/tile', null, [ 'id' => $p->ID ] ); endforeach; ?>
				</div>
			</div>
		<?php endforeach; ?>
	<?php else : ?>
		<p class="page-plain__empty">No paintings in this series yet.</p>
	<?php endif; ?>
</section>
<?php
get_footer();
