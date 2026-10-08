<?php
/** /work/ — the full catalog, newest year first, grouped under year headings. */
get_header();
$by_year = [];
foreach ( $GLOBALS['wp_query']->posts as $p ) {
	$by_year[ jca_year( $p->ID ) ?: 'Undated' ][] = $p;
}
?>
<section class="catalog">
	<?php get_template_part( 'template-parts/catalog-head', null, [ 'title' => 'Work' ] ); ?>
	<?php if ( $by_year ) : ?>
		<?php foreach ( $by_year as $year => $posts ) : ?>
			<div class="group">
				<h2 class="jca-label group__title"><?php echo esc_html( $year ); ?></h2>
				<div class="grid">
					<?php foreach ( $posts as $p ) : get_template_part( 'template-parts/tile', null, [ 'id' => $p->ID ] ); endforeach; ?>
				</div>
			</div>
		<?php endforeach; ?>
	<?php else : ?>
		<p class="page-plain__empty">No paintings yet.</p>
	<?php endif; ?>
</section>
<?php
get_footer();
