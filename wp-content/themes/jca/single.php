<?php
/** Single blog post. */
get_header();
the_post();
$prev = get_previous_post();
$next = get_next_post();
?>
<article class="post">
	<header class="post__head">
		<span class="jca-label"><?php echo esc_html( get_the_date() ); ?></span>
		<h1 class="post__title"><?php the_title(); ?></h1>
	</header>
	<?php if ( has_post_thumbnail() ) : ?>
		<figure class="post__hero"><?php the_post_thumbnail( 'jca-detail', [ 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '(max-width: 899px) 100vw, 60vw' ] ); ?></figure>
	<?php endif; ?>
	<div class="post__body"><?php the_content(); ?></div>
	<nav class="post__nav" aria-label="Other posts">
		<span><?php if ( $prev ) : ?><a href="<?php echo esc_url( get_permalink( $prev ) ); ?>" rel="prev">← Older</a><?php endif; ?></span>
		<a href="<?php echo esc_url( get_permalink( (int) get_option( 'page_for_posts' ) ) ); ?>">All news</a>
		<span><?php if ( $next ) : ?><a href="<?php echo esc_url( get_permalink( $next ) ); ?>" rel="next">Newer →</a><?php endif; ?></span>
	</nav>
</article>
<?php
get_footer();
