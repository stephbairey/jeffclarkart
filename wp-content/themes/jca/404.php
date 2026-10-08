<?php
get_header();
?>
<section class="not-found">
	<h1 class="page-plain__title">Not found</h1>
	<p class="page-plain__content">That page isn't here. <a href="<?php echo esc_url( get_post_type_archive_link( 'artwork' ) ); ?>">See all work</a> or go <a href="<?php echo esc_url( home_url( '/' ) ); ?>">home</a>.</p>
</section>
<?php
get_footer();
