<?php
/** /about/ — bio text left, photo of Jeff (the page's featured image) right, contact link. */
get_header();
the_post();
?>
<article class="page-plain">
	<h1 class="page-plain__title"><?php the_title(); ?></h1>
	<div class="page-plain__layout">
		<div class="page-plain__content">
			<?php the_content(); ?>
			<p><a class="jca-label jca-label--rule" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Get in touch</a></p>
		</div>
		<?php if ( has_post_thumbnail() ) : ?>
			<aside class="page-plain__aside">
				<?php the_post_thumbnail( 'jca-tile', [ 'alt' => 'Jeff Clark', 'loading' => 'lazy', 'sizes' => '(max-width: 899px) 100vw, 30vw' ] ); ?>
			</aside>
		<?php endif; ?>
	</div>
</article>
<?php
get_footer();
