# Decisions Log — Jeff Clark Artworks

Format: `Dxxx — Title` · status · date · context · options · choice · rationale · revisit conditions. New entries append to the bottom.

---

## D001 — Traditional WordPress on Lingua Ink Media hosting

- **Status**: Decided
- **Date**: 2026-10-07
- **Context**: Squarespace trial, no API, half the site template fiction. Jeff wants one point of entry per painting.
- **Options**: stay on Squarespace; static site generator; headless WP (as on lilaanwell.com); traditional themed WP.
- **Choice**: Traditional themed WordPress on NixiHost.
- **Rationale**: Jeff needs an admin form, not a page builder or a repo. One system to maintain; Steph already hosts it. Headless adds a second deploy pipeline for no gain on a 25-item catalog.
- **Revisit if**: traffic or a prints store outgrows shared hosting.

## D002 — Catalog as a custom post type, series as a taxonomy

- **Status**: Decided
- **Date**: 2026-10-07
- **Choice**: `artwork` CPT + `series` taxonomy; all listing pages derived.
- **Rationale**: Jeff agreed in writing that series pages should "come free from the catalog instead of being hand-built."

## D003 — "Featured" is a flag, not a series

- **Status**: Decided
- **Date**: 2026-10-08
- **Context**: The old site had a "Featured" series that functioned as a homepage selection.
- **Choice**: `jca_featured_on_home` checkbox + `jca_home_order` / `jca_featured_order` ints. Four real series only.
- **Rationale**: A homepage selection is a view, not a body of work. A fifth series would create a public `/series/featured/` page nobody asked for.

## D004 — Pop Elegies variants grouped by meta, not parent posts

- **Status**: Decided
- **Date**: 2026-10-08
- **Context**: 12 "Self-Portrait 0X - Variant 0Y" works are one series of three sets. First draft proposed hierarchical posts with synthetic parents.
- **Choice**: Flat CPT. `jca_variant_group` text meta ("Self-Portrait 01"). Series template groups tiles by that value.
- **Rationale** (Steph): synthetic parents aren't paintings; Self-Portrait 01/02 already exist as real works in Fragmented Identity and 03 has no original. Parents would leak into the grid, prev/next, sitemap and counts. Meta keeps URLs flat and nothing phantom.

## D005 — Homepage statement in explicit fields

- **Status**: Decided
- **Date**: 2026-10-08
- **Choice**: CMB2 box on the front page: `bio`, `statement_1..3`. The About page is independent.
- **Rationale** (Steph): parsing the About page's editor content couples the template to how Jeff formats a page. Four fields, zero parsing.

## D006 — Pre-resize masters before upload

- **Status**: Decided
- **Date**: 2026-10-08
- **Context**: Masters are 9000px, 30–66 MB JPEGs. WP `big_image_size_threshold` is 2560; Imagick on shared hosting risks memory errors; `upload_max_filesize` on this host is 256M.
- **Choice**: `scripts/resize-masters.py` writes 3000px-long-edge sRGB JPEGs locally; those are what WP ingests. True masters stay in Jeff's Drive and Steph's Downloads.
- **Revisit if**: a print portal (Phase 2) needs higher-res derivatives, in which case store them outside the media library.

## D007 — Contact pre-fill by post ID

- **Status**: Decided
- **Date**: 2026-10-08
- **Choice**: "Contact me about this painting" links to `/contact/?inquire=<id>` (`artwork` is the CPT query var and would 404); the template resolves the ID to a title server-side and injects it into the Ninja Forms hidden field.
- **Rationale** (Steph): titles in query strings mangle commas and apostrophes and allow arbitrary text into the field.

## D008 — Ninja Forms

- **Status**: Decided
- **Date**: 2026-10-08
- **Options**: Contact Form 7 (on linguainkmedia.com), Ninja Forms, custom form in the plugin.
- **Choice**: Ninja Forms. Submissions table built in; Steph's usual choice.

## D009 — Exhibitions: two structures for now, one later

- **Status**: Decided, with a planned refactor
- **Date**: 2026-10-08
- **Context**: Froelick's detail page has a per-work Exhibitions block; Jeff also wants an Exhibitions page. Phase 1 has an `exhibition` CPT and a per-artwork `jca_exhibition_history` repeater. These overlap.
- **Choice**: Keep both in Phase 1 because both are empty.
- **Refactor**: once there is real exhibition content, replace the repeater with a relationship from artworks to `exhibition` posts and render the per-work block from that. Don't let the repeater calcify.

## D010 — Coming Soon is a static file

- **Status**: Decided
- **Date**: 2026-10-08
- **Choice**: `coming-soon/index.html` in the live docroot, `noindex`. No WordPress on the live domain until launch.
- **Rationale**: nothing to patch for a two-week placeholder; the finished site replaces it in one rsync.

## D011 — Deploy by rsync from Steph's machine

- **Status**: Decided
- **Date**: 2026-10-08
- **Options**: git pull on server; rsync; edit in place.
- **Choice**: `scripts/deploy.sh` rsyncs theme + plugin over the `nixihost` SSH alias. Same path for staging and prod.
