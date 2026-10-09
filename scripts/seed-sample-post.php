<?php
/**
 * One sample News post to check the blog templates. Delete it before launch (or keep it as Jeff's first post).
 *   ~/bin/wp eval-file scripts/seed-sample-post.php
 */
if ( ! defined( 'WP_CLI' ) ) {
	exit( "Run via wp eval-file\n" );
}
if ( get_page_by_path( 'sample-post-new-work-in-the-studio', OBJECT, 'post' ) ) {
	WP_CLI::log( 'sample post exists' );
	return;
}
$content = <<<'HTML'
<!-- wp:paragraph -->
<p>This is a sample post so the News page has something to show. Jeff can delete it or write over it. It uses a normal paragraph, a heading, a quote and a list, so every kind of block the editor offers has a place to sit.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>What's on the easel</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Three canvases are in progress at once, which is unusual. One is a companion to "Happy Birthday, Mr. President" at the same scale. The other two are small, 20 by 16, and may end up as a new set in the Pop Elegies series.</p>
<!-- /wp:paragraph -->

<!-- wp:quote -->
<blockquote class="wp-block-quote"><p>Out of formless lines, shapes and color, the theme is allowed to develop and emerge, only fully revealing itself when undirected.</p></blockquote>
<!-- /wp:quote -->

<!-- wp:heading -->
<h2>Coming up</h2>
<!-- /wp:heading -->

<!-- wp:list -->
<ul><li>Studio visits by appointment through November.</li><li>Prints: still working out how to sell them. Phase two.</li><li>Questions about any painting go through the <a href="/contact/">contact page</a>.</li></ul>
<!-- /wp:list -->
HTML;

$id = wp_insert_post( [
	'post_type'    => 'post',
	'post_status'  => 'publish',
	'post_title'   => 'Sample post: new work in the studio',
	'post_name'    => 'sample-post-new-work-in-the-studio',
	'post_content' => $content,
	'post_excerpt' => 'A sample post so the News page has something to show. Three canvases on the go, a note on prints, and how to get in touch.',
] );
// Borrow a painting as the featured image.
$art = get_page_by_path( 'argument', OBJECT, 'artwork' );
if ( $art && has_post_thumbnail( $art ) ) {
	set_post_thumbnail( $id, get_post_thumbnail_id( $art ) );
}
WP_CLI::success( "sample post #{$id}" );
