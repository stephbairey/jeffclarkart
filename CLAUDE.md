# Jeff Clark Artworks — Project Context

## What this is

Portfolio and catalog site for Jeff Clark, a Portland, Oregon painter who sells original acrylic-on-canvas work. Replaces a Squarespace trial that expires 2026-10-16. Built and hosted by Lingua Ink Media (Steph Bairey). Jeff is the client; his partner Andrea Mia handles money.

The one principle: **Jeff uploads a painting once, fills in a short form, and everything else derives from that.** Series pages, grids, filters, the homepage collage and featured row, and the detail page are all views over one catalog. If a feature makes Jeff edit two places to change one fact, it's wrong.

## Stack

- **WordPress** (traditional, themed) on NixiHost shared cPanel hosting, LiteSpeed, PHP 8.4
- **Live:** https://jeffclarkart.com → `/home/baireyco/jeffclarkart.com/`
- **Staging:** https://staging.jeffclarkart.com → `/home/baireyco/staging.jeffclarkart.com/`
- **Fields:** CMB2. **Forms:** Ninja Forms. **Cache/images:** LiteSpeed Cache. **Spam:** Antispam Bee.
- **Deploy:** `scripts/deploy.sh staging|prod` rsyncs the theme and plugin over the `nixihost` SSH alias. WP core, uploads and `wp-config.php` are never in this repo.
- **wp-cli** lives at `~/bin/wp` on the server.

## Repo layout

```
coming-soon/index.html             static placeholder served on the live domain until launch
wp-content/themes/jca/             the theme (classic PHP; the homepage needs exact absolute-positioned CSS)
wp-content/plugins/jca-catalog/    artwork + exhibition CPTs, series taxonomy, CMB2 fields, price sheet, helpers
data/artworks.json                 seed catalog (25 works)
scripts/                           deploy, resize-masters, import-artworks, attach-images
docs/                              design handoff README, Jeff's how-to
```

## Design

Source of truth is the Claude Design package (`docs/design-handoff-README.md`). Six colors only, no white, no black, no gradients, no shadows, no radius, no hamburger, no animations. Jost 500 for display/nav/labels, Alegreya Sans 400 for body. Breakpoint 760px. The homepage is the loud front door (collage + "UGLY beauty"); every other page is quiet and systematic like an Artlogic gallery site (froelickgallery.com Works pages).

Caption format follows the Portland Art Museum: title, then medium and dimensions with **height before width**.

## Data model (short)

- `artwork` CPT, flat, slug `/work/`. Meta prefix `jca_`: year, medium, height_in, width_in, framed_*, price, status (available|sold|reserved|inquire), sold_note, inventory_code, exhibition_history (group), variant_group, featured_on_home, home_order, featured_order.
- `series` taxonomy (flat): Beautiful Oddities, Fragmented Identity, Human Stories, Pop Elegies: Death of the 80's. "Featured" is a flag, not a series.
- `exhibition` CPT, empty at launch.
- Front page CMB2 fields: bio, statement_1..3. The homepage reads these, never the About page.
- Pop Elegies variants are grouped by `jca_variant_group` text ("Self-Portrait 01"), no parent posts.

Display rules: available → `$10,800`; sold → SOLD + sold_note; reserved → RESERVED; inquire → Inquire (price hidden).

## Decisions

See `DECISIONS.md`. Don't re-litigate without a reason.

## Working preferences

Steph has 15 years of enterprise web ops and deep WordPress/PHP. Implementation-first, exact settings and code, skip basics. Verify as you go. When something breaks, say what broke, why, and the fix. Phase 2 (prints portal, fuller commissions, exhibitions content) is out of scope beyond keeping the data model open. Credentials are never committed.
