<?php
/** Default page: quiet single column. About uses page-about.php; Contact/Commissions/Exhibitions have their own. */
get_header();
the_post();
?>
<article class="page-plain">
	<h1 class="page-plain__title"><?php the_title(); ?></h1>
	<div class="page-plain__content"><?php the_content(); ?></div>
</article>
<?php
get_footer();
