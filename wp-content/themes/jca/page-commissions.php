<?php
/** /commissions/ — short description, examples of commissioned work, inquiry form. Phase 1. */
get_header();
the_post();
$examples = jca_commissioned_works();
?>
<article class="page-plain">
	<h1 class="page-plain__title"><?php the_title(); ?></h1>
	<div class="page-plain__content">
		<?php the_content(); ?>
	</div>
	<?php if ( $examples ) : ?>
		<div class="group" style="margin-top:clamp(40px,4cqw,64px)">
			<h2 class="jca-label group__title">Commissioned work</h2>
			<div class="grid">
				<?php foreach ( $examples as $p ) : get_template_part( 'template-parts/tile', null, [ 'id' => $p->ID ] ); endforeach; ?>
			</div>
		</div>
	<?php endif; ?>
	<div class="page-plain__content" style="margin-top:clamp(40px,4cqw,64px)">
		<h2>Inquire about a commission</h2>
		<?php jca_render_form( 'commissions' ); ?>
	</div>
</article>
<?php
get_footer();
