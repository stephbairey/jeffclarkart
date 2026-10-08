<?php
/**
 * Homepage: collage, statement, featured row. Everything comes from artwork meta and front-page fields.
 */
get_header();

$front_id = (int) get_option( 'page_on_front' );
$collage  = jca_home_collage();
$featured = jca_home_featured();
$bio      = (string) get_post_meta( $front_id, 'jca_bio', true );
$stmts    = [];
foreach ( [ 1, 2, 3 ] as $n ) {
	$lead = (string) get_post_meta( $front_id, "jca_statement_{$n}_lead", true );
	$body = (string) get_post_meta( $front_id, "jca_statement_{$n}_body", true );
	if ( $lead || $body ) {
		$stmts[] = [ $lead, $body ];
	}
}
?>
<section class="collage" aria-label="Collage">
	<div class="collage__circle" aria-hidden="true"></div>
	<?php for ( $slot = 1; $slot <= 7; $slot++ ) : ?>
		<div class="collage__frame collage__frame--<?php echo $slot; ?>">
			<?php if ( isset( $collage[ $slot ] ) ) :
				$p    = $collage[ $slot ];
				$attr = [ 'sizes' => '(max-width: 759px) 60vw, 25vw' ];
				if ( $slot === 2 ) {
					$attr += [ 'loading' => 'eager', 'fetchpriority' => 'high' ];
				} elseif ( $slot === 1 || $slot === 5 ) {
					// Eager but not prioritized; the explicit key stops WP adding its own fetchpriority="high".
					$attr += [ 'loading' => 'eager', 'fetchpriority' => 'auto' ];
				}
				?>
				<a href="<?php echo esc_url( get_permalink( $p ) ); ?>" aria-label="<?php echo esc_attr( get_the_title( $p ) ); ?>">
					<?php echo jca_image( $p->ID, 'jca-tile', $attr ); ?>
				</a>
			<?php endif; ?>
		</div>
	<?php endfor; ?>
	<h1 class="collage__headline">UGLY <span>beauty</span></h1>
</section>

<section class="statement" id="about" aria-label="Statement">
	<div class="statement__col">
		<span class="jca-label">About</span>
		<?php if ( $bio ) : ?>
			<p class="statement__bio"><?php echo wp_kses_post( nl2br( esc_html( $bio ) ) ); ?></p>
		<?php endif; ?>
		<?php if ( $stmts ) : ?>
			<div class="statement__list">
				<?php foreach ( $stmts as [ $lead, $body ] ) : ?>
					<p class="statement__item">
						<?php if ( $lead ) : ?><span class="statement__lead"><?php echo esc_html( $lead ); ?></span> <?php endif; ?>
						<?php echo esc_html( $body ); ?>
					</p>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<?php if ( ! $bio && ! $stmts ) : ?>
			<p class="statement__bio">Jeff Clark paints in acrylic on canvas in Portland, Oregon.</p>
		<?php endif; ?>
	</div>
</section>

<section class="featured" id="work" aria-label="Featured work">
	<span class="jca-label">Featured work</span>
	<?php if ( $featured ) : ?>
		<div class="featured__row">
			<?php foreach ( $featured as $p ) :
				[ $w, $h ] = array_map( 'floatval', explode( '/', jca_aspect( $p->ID ) ) );
				$ratio = ( $h > 0 ) ? $w / $h : 2 / 3;
				$grow  = round( $ratio, 3 );
				$basis = (int) round( 300 * $ratio );
				?>
				<figure class="featured__item" style="flex-grow:<?php echo $grow; ?>;flex-basis:<?php echo $basis; ?>px">
					<a href="<?php echo esc_url( get_permalink( $p ) ); ?>">
						<div class="featured__img" style="aspect-ratio:<?php echo esc_attr( jca_aspect( $p->ID ) ); ?>">
							<?php echo jca_image( $p->ID, 'jca-tile', [ 'sizes' => '(max-width: 759px) 100vw, 40vw' ] ); ?>
						</div>
						<figcaption class="jca-caption"><?php echo jca_caption( $p->ID ); ?></figcaption>
					</a>
				</figure>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
	<div class="featured__more">
		<a class="jca-label jca-label--rule" href="<?php echo esc_url( get_post_type_archive_link( 'artwork' ) ); ?>">View all work</a>
	</div>
</section>
<?php
get_footer();
