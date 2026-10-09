<?php
/** /news/ — blog index (the page set as "Posts page"). */
get_header();
$page = get_post( (int) get_option( 'page_for_posts' ) );
?>
<section class="page-plain">
	<h1 class="page-plain__title"><?php echo esc_html( $page ? $page->post_title : 'News' ); ?></h1>
	<?php if ( $page && $page->post_content ) : ?>
		<div class="page-plain__content" style="margin-bottom:clamp(32px,3.4cqw,52px)"><?php echo apply_filters( 'the_content', $page->post_content ); ?></div>
	<?php endif; ?>
	<?php if ( have_posts() ) : ?>
		<ul class="news">
			<?php while ( have_posts() ) : the_post(); ?>
				<li class="news__item">
					<div>
						<span class="jca-label news__date"><?php echo esc_html( get_the_date() ); ?></span>
						<h2 class="news__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<p class="news__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 45, '…' ) ); ?></p>
						<a class="jca-label jca-label--rule news__more" href="<?php the_permalink(); ?>">Read more</a>
					</div>
					<?php if ( has_post_thumbnail() ) : ?>
						<a class="news__thumb" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php the_post_thumbnail( 'jca-tile', [ 'loading' => 'lazy', 'sizes' => '(max-width: 899px) 100vw, 30vw' ] ); ?></a>
					<?php endif; ?>
				</li>
			<?php endwhile; ?>
		</ul>
		<?php the_posts_pagination( [ 'prev_text' => '← Newer', 'next_text' => 'Older →', 'class' => 'pagination', 'screen_reader_text' => ' ' ] ); ?>
	<?php else : ?>
		<p class="page-plain__empty">Nothing posted yet.</p>
	<?php endif; ?>
</section>
<?php
get_footer();
