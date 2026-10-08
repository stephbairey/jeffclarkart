<?php
/** /work/ — the full catalog grid. */
get_header();
?>
<section class="catalog">
	<?php get_template_part( 'template-parts/catalog-head', null, [ 'title' => 'Work' ] ); ?>
	<?php if ( have_posts() ) : ?>
		<div class="grid">
			<?php while ( have_posts() ) : the_post(); get_template_part( 'template-parts/tile', null, [ 'id' => get_the_ID() ] ); endwhile; ?>
		</div>
	<?php else : ?>
		<p class="page-plain__empty">No paintings match that filter.</p>
	<?php endif; ?>
</section>
<?php
get_footer();
