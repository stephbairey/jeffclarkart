<?php
/** /commissions/ — short description + inquiry form. Phase 1. */
get_header();
the_post();
?>
<article class="page-plain">
	<h1 class="page-plain__title"><?php the_title(); ?></h1>
	<div class="page-plain__content">
		<?php the_content(); ?>
		<?php jca_render_form( 'commissions' ); ?>
	</div>
</article>
<?php
get_footer();
