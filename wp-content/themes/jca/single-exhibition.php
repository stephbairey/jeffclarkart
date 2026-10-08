<?php
/** Single exhibition: redirect to the list; there's nothing more to say per item in Phase 1. */
wp_safe_redirect( get_post_type_archive_link( 'exhibition' ), 302 );
exit;
