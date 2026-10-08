<?php
/** /contact/ — inquiry form. ?inquire=<id> pre-fills the hidden field (resolved server-side). */
get_header();
the_post();
$artwork = jca_contact_prefill_artwork();
?>
<article class="page-plain">
	<h1 class="page-plain__title"><?php the_title(); ?></h1>
	<div class="page-plain__layout">
		<div class="page-plain__content">
			<?php if ( $artwork ) : ?>
				<p>About <a href="<?php echo esc_url( get_permalink( $artwork ) ); ?>"><span class="jca-caption__title"><?php echo esc_html( get_the_title( $artwork ) ); ?></span></a><?php if ( jca_year( $artwork->ID ) ) : ?>, <?php echo esc_html( jca_year( $artwork->ID ) ); ?><?php endif; ?></p>
			<?php endif; ?>
			<?php the_content(); ?>
			<?php jca_render_form( 'contact' ); ?>
		</div>
		<?php if ( $artwork ) : ?>
			<aside class="page-plain__aside">
				<?php echo jca_image( $artwork->ID, 'jca-tile', [ 'sizes' => '(max-width: 899px) 100vw, 30vw' ] ); ?>
				<figcaption class="jca-caption"><?php echo jca_caption( $artwork->ID ); ?></figcaption>
			</aside>
		<?php endif; ?>
	</div>
</article>
<?php
get_footer();
