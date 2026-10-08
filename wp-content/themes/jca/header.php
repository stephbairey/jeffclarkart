<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page">
	<a class="skip-link screen-reader-text" href="#main">Skip to content</a>
	<header class="site-header">
		<a class="site-header__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">JEFF CLARK ARTWORKS</a>
		<nav class="site-nav" aria-label="Primary"><?php jca_header_nav(); ?></nav>
	</header>
	<main id="main">
