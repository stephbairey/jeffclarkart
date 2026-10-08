<?php
/** Fallback for anything without a dedicated template (blog index, search, etc.). */
get_header();
?>
<section class="page-plain">
	<h1 class="page-plain__title"><?php echo is_search() ? 'Search' : 'Jeff Clark Artworks'; ?></h1>
	<div class="page-plain__content">
		<?php if ( have_posts() ) : ?>
			<ul class="exhibitions">
				<?php while ( have_posts() ) : the_post(); ?>
					<li><a href="<?php the_permalink(); ?>"><span class="jca-caption__title"><?php the_title(); ?></span></a></li>
				<?php endwhile; ?>
			</ul>
		<?php else : ?>
			<p class="page-plain__empty">Nothing here. <a href="<?php echo esc_url( get_post_type_archive_link( 'artwork' ) ); ?>">See the work</a>.</p>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
