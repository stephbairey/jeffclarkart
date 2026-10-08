<?php
/**
 * Tools → Price Sheet: upload a CSV or XLSX of title / price / status, preview the diff, apply on confirm.
 *
 * Matching is by normalized title (lowercase, collapsed whitespace, straight quotes, no surrounding quotes).
 * Unmatched rows are reported, never silently skipped.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', function () {
	add_management_page( 'Price Sheet', 'Price Sheet', 'manage_options', 'jca-price-sheet', 'jca_price_sheet_page' );
} );

function jca_normalize_title( string $t ): string {
	$t = html_entity_decode( $t, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$t = str_replace( [ '’', '‘', '“', '”', '–', '—' ], [ "'", "'", '"', '"', '-', '-' ], $t );
	$t = trim( $t, " \t\n\r\0\x0B\"'" );
	$t = preg_replace( '/\s+/u', ' ', $t );
	return mb_strtolower( $t );
}

/** Map normalized title → post ID for all artworks (any status except trash). */
function jca_title_index(): array {
	$posts = get_posts( [ 'post_type' => 'artwork', 'post_status' => [ 'publish', 'draft', 'pending', 'private' ], 'posts_per_page' => -1, 'fields' => 'ids', 'no_found_rows' => true ] );
	$idx   = [];
	foreach ( $posts as $id ) {
		$idx[ jca_normalize_title( get_the_title( $id ) ) ] = $id;
	}
	return $idx;
}

/** Parse an uploaded file into rows of ['title','price','status']. Throws on bad input. */
function jca_parse_sheet( string $path, string $name ): array {
	$ext = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );
	if ( $ext === 'xlsx' ) {
		$table = jca_read_xlsx( $path );
	} elseif ( in_array( $ext, [ 'csv', 'txt' ], true ) ) {
		$table = jca_read_csv( $path );
	} else {
		throw new RuntimeException( 'Upload a .csv or .xlsx file.' );
	}
	if ( count( $table ) < 2 ) {
		throw new RuntimeException( 'The sheet has no data rows.' );
	}
	$header = array_map( fn( $h ) => strtolower( trim( (string) $h ) ), array_shift( $table ) );
	$ti = jca_find_col( $header, [ 'title', 'painting', 'name', 'artwork' ] );
	$pi = jca_find_col( $header, [ 'price', 'price (usd)', 'usd', 'price usd' ] );
	$si = jca_find_col( $header, [ 'status' ] );
	if ( $ti === null || $pi === null ) {
		throw new RuntimeException( 'Need a "title" column and a "price" column in the first row. Found: ' . implode( ', ', $header ) );
	}
	$rows = [];
	foreach ( $table as $n => $r ) {
		$title = trim( (string) ( $r[ $ti ] ?? '' ) );
		if ( $title === '' ) {
			continue;
		}
		$rows[] = [
			'line'   => $n + 2,
			'title'  => $title,
			'price'  => trim( (string) ( $r[ $pi ] ?? '' ) ),
			'status' => $si !== null ? strtolower( trim( (string) ( $r[ $si ] ?? '' ) ) ) : '',
		];
	}
	return $rows;
}

function jca_find_col( array $header, array $names ): ?int {
	foreach ( $header as $i => $h ) {
		if ( in_array( $h, $names, true ) ) {
			return $i;
		}
	}
	return null;
}

function jca_read_csv( string $path ): array {
	$fh = fopen( $path, 'r' );
	if ( ! $fh ) {
		throw new RuntimeException( 'Could not read the file.' );
	}
	$first = fread( $fh, 3 );
	if ( $first !== "\xEF\xBB\xBF" ) {
		rewind( $fh );
	}
	$rows = [];
	while ( ( $r = fgetcsv( $fh, 0, ',', '"', '\\' ) ) !== false ) {
		if ( count( $r ) === 1 && $r[0] === null ) {
			continue;
		}
		$rows[] = $r;
	}
	fclose( $fh );
	return $rows;
}

/** Minimal XLSX reader: first worksheet, shared strings and inline strings, no formulas. */
function jca_read_xlsx( string $path ): array {
	if ( ! class_exists( 'ZipArchive' ) ) {
		throw new RuntimeException( 'XLSX needs the PHP zip extension; export the sheet as CSV instead.' );
	}
	$zip = new ZipArchive();
	if ( $zip->open( $path ) !== true ) {
		throw new RuntimeException( 'Could not open the .xlsx file.' );
	}
	$shared = [];
	if ( ( $ss = $zip->getFromName( 'xl/sharedStrings.xml' ) ) !== false ) {
		$x = simplexml_load_string( $ss );
		foreach ( $x->si as $si ) {
			$shared[] = isset( $si->t ) ? (string) $si->t : implode( '', array_map( fn( $r ) => (string) $r->t, iterator_to_array( $si->r, false ) ) );
		}
	}
	// First sheet by workbook order.
	$sheetFile = 'xl/worksheets/sheet1.xml';
	if ( ( $wb = $zip->getFromName( 'xl/workbook.xml' ) ) !== false && ( $rels = $zip->getFromName( 'xl/_rels/workbook.xml.rels' ) ) !== false ) {
		$wbx = simplexml_load_string( $wb );
		$wbx->registerXPathNamespace( 'm', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main' );
		$first = $wbx->xpath( '//m:sheets/m:sheet' )[0] ?? null;
		if ( $first ) {
			$rid  = (string) $first->attributes( 'http://schemas.openxmlformats.org/officeDocument/2006/relationships' )->id;
			$relx = simplexml_load_string( $rels );
			foreach ( $relx->Relationship as $rel ) {
				if ( (string) $rel['Id'] === $rid ) {
					$sheetFile = 'xl/' . ltrim( (string) $rel['Target'], '/' );
					$sheetFile = str_replace( 'xl/xl/', 'xl/', $sheetFile );
				}
			}
		}
	}
	$xml = $zip->getFromName( $sheetFile );
	$zip->close();
	if ( $xml === false ) {
		throw new RuntimeException( 'No worksheet found in the .xlsx file.' );
	}
	$sheet = simplexml_load_string( $xml );
	$rows  = [];
	foreach ( $sheet->sheetData->row as $row ) {
		$cells = [];
		foreach ( $row->c as $c ) {
			$ref = (string) $c['r'];
			$col = jca_col_index( preg_replace( '/\d+/', '', $ref ) );
			$t   = (string) $c['t'];
			if ( $t === 's' ) {
				$v = $shared[ (int) $c->v ] ?? '';
			} elseif ( $t === 'inlineStr' ) {
				$v = (string) $c->is->t;
			} else {
				$v = isset( $c->v ) ? (string) $c->v : '';
			}
			$cells[ $col ] = $v;
		}
		if ( $cells ) {
			$max = max( array_keys( $cells ) );
			$r   = array_fill( 0, $max + 1, '' );
			foreach ( $cells as $i => $v ) {
				$r[ $i ] = $v;
			}
			$rows[] = $r;
		}
	}
	return $rows;
}

function jca_col_index( string $letters ): int {
	$n = 0;
	foreach ( str_split( strtoupper( $letters ) ) as $ch ) {
		$n = $n * 26 + ( ord( $ch ) - 64 );
	}
	return max( 0, $n - 1 );
}

/** Build the diff: matched rows with current/new values, plus unmatched rows. */
function jca_price_sheet_diff( array $rows ): array {
	$idx       = jca_title_index();
	$matched   = [];
	$unmatched = [];
	$seen      = [];
	foreach ( $rows as $r ) {
		$key = jca_normalize_title( $r['title'] );
		if ( ! isset( $idx[ $key ] ) ) {
			$unmatched[] = $r;
			continue;
		}
		$id = $idx[ $key ];
		if ( isset( $seen[ $id ] ) ) {
			$r['error'] = 'Duplicate of line ' . $seen[ $id ];
			$unmatched[] = $r;
			continue;
		}
		$seen[ $id ] = $r['line'];

		$new_price = jca_sanitize_decimal( $r['price'] );
		$cur_price = (string) jca_meta( $id, 'price' );
		$new_status = $r['status'];
		if ( $new_status !== '' && ! isset( JCA_STATUSES[ $new_status ] ) ) {
			$r['error'] = 'Unknown status "' . $r['status'] . '" (use available, sold, reserved, inquire)';
			$unmatched[] = $r;
			continue;
		}
		$cur_status = jca_status( $id );
		$changes    = [];
		if ( $r['price'] !== '' && (float) $new_price !== (float) $cur_price ) {
			$changes['price'] = [ $cur_price, $new_price ];
		}
		if ( $new_status !== '' && $new_status !== $cur_status ) {
			$changes['status'] = [ $cur_status, $new_status ];
		}
		$matched[] = [ 'id' => $id, 'line' => $r['line'], 'title' => get_the_title( $id ), 'changes' => $changes ];
	}
	return [ 'matched' => $matched, 'unmatched' => $unmatched ];
}

function jca_price_sheet_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Not allowed.' );
	}
	$transient_key = 'jca_price_sheet_' . get_current_user_id();
	$notice = '';
	$diff   = null;

	// Step 2: apply.
	if ( isset( $_POST['jca_apply'] ) && check_admin_referer( 'jca_price_sheet_apply' ) ) {
		$pending = get_transient( $transient_key );
		if ( ! $pending ) {
			$notice = '<div class="notice notice-error"><p>The preview expired. Upload the sheet again.</p></div>';
		} else {
			$applied = 0;
			foreach ( $pending['matched'] as $m ) {
				foreach ( $m['changes'] as $field => [ $old, $new ] ) {
					update_post_meta( $m['id'], JCA_META . $field, $new );
					$applied++;
				}
			}
			delete_transient( $transient_key );
			$notice = '<div class="notice notice-success"><p>Applied ' . (int) $applied . ' change' . ( $applied === 1 ? '' : 's' ) . ' across ' . count( array_filter( $pending['matched'], fn( $m ) => $m['changes'] ) ) . ' painting(s).</p></div>';
		}
	}

	// Step 1: upload + preview.
	if ( isset( $_POST['jca_preview'] ) && check_admin_referer( 'jca_price_sheet_upload' ) ) {
		if ( empty( $_FILES['sheet']['tmp_name'] ) || ! is_uploaded_file( $_FILES['sheet']['tmp_name'] ) ) {
			$notice = '<div class="notice notice-error"><p>Choose a file first.</p></div>';
		} else {
			try {
				$rows = jca_parse_sheet( $_FILES['sheet']['tmp_name'], $_FILES['sheet']['name'] );
				$diff = jca_price_sheet_diff( $rows );
				set_transient( $transient_key, $diff, 30 * MINUTE_IN_SECONDS );
			} catch ( Throwable $e ) {
				$notice = '<div class="notice notice-error"><p>' . esc_html( $e->getMessage() ) . '</p></div>';
			}
		}
	}
	?>
	<div class="wrap">
		<h1>Price Sheet</h1>
		<?php echo $notice; // phpcs:ignore ?>
		<p>Upload a spreadsheet with a <code>title</code> column and a <code>price</code> column (optional <code>status</code>: available, sold, reserved, inquire). Titles are matched to paintings ignoring case, spacing and quote style. Nothing changes until you confirm the preview.</p>

		<form method="post" enctype="multipart/form-data" style="margin:1em 0 2em">
			<?php wp_nonce_field( 'jca_price_sheet_upload' ); ?>
			<input type="file" name="sheet" accept=".csv,.xlsx,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
			<?php submit_button( 'Preview changes', 'primary', 'jca_preview', false ); ?>
		</form>

		<?php if ( $diff ) : ?>
			<?php $changing = array_filter( $diff['matched'], fn( $m ) => $m['changes'] ); ?>
			<h2>Preview</h2>
			<p><?php echo count( $diff['matched'] ); ?> row(s) matched a painting, <?php echo count( $changing ); ?> with changes, <?php echo count( $diff['unmatched'] ); ?> unmatched.</p>

			<?php if ( $diff['unmatched'] ) : ?>
				<h3 style="color:#b32d2e">Unmatched rows (will be skipped)</h3>
				<table class="widefat striped" style="max-width:900px"><thead><tr><th>Line</th><th>Title in sheet</th><th>Reason</th></tr></thead><tbody>
				<?php foreach ( $diff['unmatched'] as $u ) : ?>
					<tr><td><?php echo (int) $u['line']; ?></td><td><?php echo esc_html( $u['title'] ); ?></td><td><?php echo esc_html( $u['error'] ?? 'No painting with this title' ); ?></td></tr>
				<?php endforeach; ?>
				</tbody></table>
			<?php endif; ?>

			<h3>Changes</h3>
			<?php if ( ! $changing ) : ?>
				<p>Nothing to change. Every matched row already has these values.</p>
			<?php else : ?>
				<table class="widefat striped" style="max-width:900px"><thead><tr><th>Painting</th><th>Price</th><th>Status</th></tr></thead><tbody>
				<?php foreach ( $changing as $m ) : ?>
					<tr>
						<td><a href="<?php echo esc_url( get_edit_post_link( $m['id'] ) ); ?>"><?php echo esc_html( $m['title'] ); ?></a></td>
						<td><?php echo isset( $m['changes']['price'] ) ? esc_html( ( $m['changes']['price'][0] === '' ? '—' : jca_price_fmt( $m['changes']['price'][0] ) ) . ' → ' . ( $m['changes']['price'][1] === '' ? '—' : jca_price_fmt( $m['changes']['price'][1] ) ) ) : '<span style="color:#888">no change</span>'; ?></td>
						<td><?php echo isset( $m['changes']['status'] ) ? esc_html( $m['changes']['status'][0] . ' → ' . $m['changes']['status'][1] ) : '<span style="color:#888">no change</span>'; ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody></table>
				<form method="post" style="margin-top:1em">
					<?php wp_nonce_field( 'jca_price_sheet_apply' ); ?>
					<?php submit_button( 'Apply these changes', 'primary', 'jca_apply', false ); ?>
				</form>
			<?php endif; ?>
		<?php endif; ?>
	</div>
	<?php
}
