	</main>
	<footer class="site-footer" id="contact">
		<span class="site-footer__name">Jeff Clark Artworks</span>
		<span>Portland, Oregon</span>
		<a href="mailto:<?php echo esc_attr( jca_contact_email() ); ?>"><?php echo esc_html( jca_contact_email() ); ?></a>
		<?php if ( jca_instagram_url() ) : ?>
			<a href="<?php echo esc_url( jca_instagram_url() ); ?>" target="_blank" rel="noopener">Instagram</a>
		<?php endif; ?>
		<a href="<?php echo esc_url( get_post_type_archive_link( 'exhibition' ) ); ?>">Exhibitions</a>
		<?php if ( has_nav_menu( 'footer' ) ) : ?>
			<?php wp_nav_menu( [ 'theme_location' => 'footer', 'container' => false, 'items_wrap' => '%3$s', 'depth' => 1, 'walker' => new JCA_Bare_Walker() ] ); ?>
		<?php endif; ?>
		<span class="site-footer__copy">&copy; <?php echo esc_html( date_i18n( 'Y' ) ); ?> Jeff Clark</span>
	</footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
