<?php
/**
 * Catalog header: title, optional description, series + year filters.
 * Expects $args['title'], optional $args['description'].
 */
$current_term = is_tax( 'series' ) ? get_queried_object() : null;
$year         = isset( $_GET['year'] ) ? absint( $_GET['year'] ) : 0;
$terms        = get_terms( [ 'taxonomy' => 'series', 'hide_empty' => true ] );
$years        = jca_catalog_years();
$base         = $current_term ? get_term_link( $current_term ) : get_post_type_archive_link( 'artwork' );
?>
<div class="catalog__head">
	<div>
		<h1 class="catalog__title"><?php echo esc_html( $args['title'] ); ?></h1>
		<?php if ( ! empty( $args['description'] ) ) : ?>
			<p class="catalog__desc"><?php echo wp_kses_post( $args['description'] ); ?></p>
		<?php endif; ?>
	</div>
	<nav class="catalog__filters" aria-label="Filter by series">
		<?php echo jca_filter_link( get_post_type_archive_link( 'artwork' ), 'All', ! $current_term ); ?>
		<?php foreach ( $terms as $t ) : ?>
			<?php echo jca_filter_link( get_term_link( $t ), $t->name, $current_term && $current_term->term_id === $t->term_id ); ?>
		<?php endforeach; ?>
	</nav>
</div>
<?php if ( count( $years ) > 1 ) : ?>
	<nav class="catalog__filters" aria-label="Filter by year" style="margin:-16px 0 clamp(32px,3.4cqw,52px)">
		<?php echo jca_filter_link( $base, 'All years', ! $year ); ?>
		<?php foreach ( $years as $y ) : ?>
			<?php echo jca_filter_link( add_query_arg( 'year', $y, $base ), (string) $y, $year === $y ); ?>
		<?php endforeach; ?>
	</nav>
<?php endif; ?>
