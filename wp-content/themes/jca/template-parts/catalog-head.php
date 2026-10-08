<?php
/**
 * Catalog header: title, optional description, series filter.
 * Expects $args['title'], optional $args['description'].
 */
$current_term = is_tax( 'series' ) ? get_queried_object() : null;
$terms        = get_terms( [ 'taxonomy' => 'series', 'hide_empty' => true ] );
?>
<div class="catalog__head">
	<div>
		<h1 class="catalog__title"><?php echo esc_html( $args['title'] ); ?></h1>
		<?php if ( ! empty( $args['description'] ) ) : ?>
			<p class="catalog__desc"><?php echo wp_kses_post( $args['description'] ); ?></p>
		<?php endif; ?>
	</div>
	<nav class="catalog__filters" aria-label="Series">
		<?php echo jca_filter_link( get_post_type_archive_link( 'artwork' ), 'All', ! $current_term ); ?>
		<?php foreach ( $terms as $t ) : ?>
			<?php echo jca_filter_link( get_term_link( $t ), $t->name, $current_term && $current_term->term_id === $t->term_id ); ?>
		<?php endforeach; ?>
	</nav>
</div>
