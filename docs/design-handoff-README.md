# Handoff: Jeff Clark Artworks — Homepage

## Overview
The homepage for Jeff Clark Artworks, a Portland, Oregon painter who sells original acrylic-on-canvas paintings. It's a single page, built desktop-first with a responsive mobile layout. The homepage is the "loud front door": a scattered, pinned-up collage of paintings with the huge headline **UGLY beauty** cutting across it. The rest of the site (catalog pages) will be quiet and systematic, modeled on an Artlogic gallery site (reference: Works pages at froelickgallery.com). The homepage's statement, featured-work and caption sections should already use that quieter style.

## About the Design Files
The files in this bundle are **design references created in HTML**: prototypes that show the intended look and behavior. They are **not production code to copy directly**. Your task is to **recreate this design in the target codebase's existing environment** (React, Astro, WordPress theme, etc.) using its established patterns. If no environment exists yet, choose the most appropriate framework (a static site generator such as Astro or Eleventy suits a small artist site well) and implement it there.

The prototype files use a custom runtime (`support.js`, `{{ }}` template holes, `<sc-if>`, `<sc-for>`, `<image-slot>`). Ignore that machinery. Read the inline styles and structure only.

## Fidelity
**High-fidelity.** The colors, typography, spacing, collage geometry and caption format are final. Recreate them exactly. All copy is placeholder (see "Content to replace" below).

---

## Design Tokens

### Colors (use exactly these six; nothing else)
| Token | Hex | Use |
|---|---|---|
| `--bg` | `#1F1E24` | Page background (cool charcoal). Never pure black. |
| `--surface` | `#2A2931` | Lifted surface: footer band, empty image placeholders |
| `--text` | `#D4D4D4` | Body text (Dust Grey) |
| `--heading` | `#C8C2DB` | Headings, wordmark, "UGLY", caption titles (Thistle) |
| `--accent` | `#B290C5` | "beauty", statement lead-ins, link hover, "View all work" underline (Amethyst Smoke) |
| `--link` | `#5191A8` | Links, nav, small section labels (Bondi Blue) |
| `--deco` | `#006593` | Large shapes and rules only, **never text** (Baltic Blue) |

Hard rules: no white, no pure black, no gradients, no drop shadows, no rounded corners on cards or images, no animated backgrounds, no parallax. The paintings supply the color.

`::selection` uses background `#006593` and text `#C8C2DB`.

### Typography
- **Display / nav / labels:** Jost, weight 500 (Google Fonts). Futura is an acceptable substitute if it's licensed.
- **Body:** Alegreya Sans, weight 400 (Google Fonts), a humanist sans.
- Google Fonts URL used: `https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600&family=Alegreya+Sans:ital,wght@0,400;0,500;1,400&display=swap`

| Role | Family | Size | Weight | Line-height | Tracking | Color |
|---|---|---|---|---|---|---|
| Headline "UGLY beauty" (desktop) | Jost | 16.5vw (238px @1440) | 500 | 0.8 | -0.045em | Thistle / Amethyst |
| Headline (mobile) | Jost | 30vw (117px @390) | 500 | 0.82 | -0.045em | Thistle / Amethyst |
| Wordmark | Jost | clamp(13px, 1.1vw, 16px) | 500 | normal | 0.28em | Thistle |
| Nav, section labels, "VIEW ALL WORK" | Jost, uppercase | 12px | 500 | normal | 0.24em | Bondi Blue |
| Bio paragraph | Alegreya Sans | clamp(20px, 1.6vw, 24px) | 400 | 1.5 | 0 | Dust Grey |
| Statement paragraphs | Alegreya Sans | clamp(17px, 1.3vw, 19px) | 400 | 1.6 | 0 | Dust Grey |
| Caption title | Jost, uppercase | 13px | 500 | 1.6 | 0.08em | Thistle |
| Caption body / footer | Alegreya Sans | 14px | 400 | 1.6 / 1.5 | 0 | Dust Grey |

The headline is set as `UGLY beauty`: "UGLY" in caps and "beauty" in lowercase, at the same size. Use `text-wrap: pretty` on paragraphs.

### Spacing
- Page side padding: `clamp(20px, 3vw, 48px)` (48px at 1440, 20px on mobile).
- All vertical rhythm uses `clamp()` (see the sections below). There is no fixed spacing scale.
- Border radius: **0 everywhere** except the decorative Baltic Blue circle (50%).
- Shadows: **none**.

### Units note
The prototype sizes the collage in container-query width units (`cqw`), so **1cqw = 1% of the page width**. In production, `vw` works if the page is full-width with no scrollbar offset issues. Otherwise use `container-type: inline-size` on the root and keep `cqw`. Pixel values below are given at the 1440px and 390px reference widths.

---

## Layout, top to bottom

### 1. Header
- Flex row, `justify-content: space-between`, `align-items: baseline`, wraps with gap `14px 32px`.
- Padding: `clamp(20px, 2.4vw, 36px)` vertical, page side padding horizontal.
- Left: wordmark link "JEFF CLARK ARTWORKS" (`white-space: nowrap`).
- Right: `<nav>` with WORK, ABOUT, CONTACT. Flex, gap `clamp(22px, 2.6vw, 40px)`.
- Background is the page charcoal. No border.
- **Mobile:** the same three text links stay in a row (they wrap below the wordmark). **No hamburger**, at any width.

### 2. Hero collage (desktop, ≥ 760px)
- `position: relative`, full width, height **58vw** (835px @1440). This fills the first viewport.
- Every painting frame is `position: absolute` with a slight rotation, so it feels pinned-up. Frames have `background: #2A2931` until the image loads. Images use `object-fit: cover`. No border, shadow or radius.
- Generous charcoal space between frames. Overlaps are intentional (hero-4 over hero-5's area, hero-6 between hero-2 and hero-4, hero-7 bottom-left).

| Slot | Aspect | left | top | width × height | rotate | z |
|---|---|---|---|---|---|---|
| Blue circle (decorative, `#006593`, 50% radius) | 1:1 | 58vw (835) | 6vw (86) | 30vw × 30vw (432) | 0 | 0 |
| hero-1 | 2:3 | 3vw (43) | 4vw (58) | 15 × 22.5vw (216×324) | -1.5° | 1 |
| hero-2 | 4:3 landscape | 21vw (302) | 9vw (130) | 24 × 18vw (346×259) | 1° | 1 |
| hero-3 | 2:3 | 51vw (734) | 3vw (43) | 14 × 21vw (202×302) | 1.2° | 1 |
| hero-4 | 1:2 tall | 63.5vw (914) | 21vw (302) | 11 × 22vw (158×317) | -1° | 2 |
| hero-5 | 2:3 | 80vw (1152) | 11vw (158) | 17 × 25.5vw (245×367) | 1.5° | 1 |
| hero-6 | 2:3 | 37vw (533) | 25vw (360) | 12 × 18vw (173×259) | -2° | 2 |
| hero-7 | 2:3 | 9vw (130) | 31vw (446) | 10 × 15vw (144×216) | 2° | 2 |

- **Headline** `<h1>`: `position: absolute; left: 2.4vw; bottom: 1.2vw; z-index: 3; white-space: nowrap; pointer-events: none`. It sits on **top** of the paintings, so the letters cut in front of them.
- The prototype has two optional toggles: the blue circle can be turned off, and the collage can drop to 5 paintings (hiding hero-6 and hero-7). The default is circle on, 7 paintings.

### 2b. Hero collage (mobile, < 760px)
- A loose two-column arrangement. Height **180vw** (702px @390). hero-7 is omitted.

| Slot | Aspect | left | top | width × height | rotate | z |
|---|---|---|---|---|---|---|
| Blue circle | 1:1 | 38vw | 54vw | 58 × 58vw | 0 | 0 |
| hero-1 | 2:3 | 5vw | 4vw | 42 × 63vw | -1.5° | 1 |
| hero-3 | 2:3 | 55vw | 14vw | 38 × 57vw | 1.5° | 1 |
| hero-2 | 4:3 | 22vw | 64vw | 52 × 39vw | 1° | 2 |
| hero-4 | 1:2 | 6vw | 106vw | 24 × 48vw | -1.5° | 1 |
| hero-5 | 2:3 | 62vw | 100vw | 32 × 48vw | 1.5° | 1 |
| hero-6 | 2:3 | 35vw | 113vw | 24 × 36vw | -1° | 2 |

- Headline: `left: 4vw; bottom: 3vw`, size 30vw, stacked on two lines ("UGLY" above "beauty") using a flex column. It still overlaps the bottom paintings.
- Make sure nothing overflows horizontally: `overflow-x: hidden` on the collage is a safe guard.

### 3. Statement (`#about`)
- Padding: `clamp(80px, 10vw, 160px)` top, page side padding, 0 bottom.
- Single left-aligned column, `max-width: 62ch`. This is a flex column with gap `clamp(36px, 3.4vw, 52px)`.
- Contents in order:
  1. Label "ABOUT" (small-caps label style, Bondi Blue).
  2. Bio paragraph (larger body size).
  3. Three statement paragraphs, stacked with gap `clamp(28px, 2.6vw, 40px)`. Each has `border-top: 1px solid #006593` and `padding-top: 22px`. Each opens with a short lead-in sentence in **Amethyst `#B290C5`**, followed by the rest in Dust Grey.
- No icons, no cards, no columns.

### 4. Featured work (`#work`)
- Padding: `clamp(96px, 11vw, 176px)` top, `clamp(80px, 9vw, 140px)` bottom.
- Label "FEATURED WORK", then margin-bottom `clamp(28px, 2.6vw, 40px)`.
- Three paintings in a row at **equal height**. The prototype achieves this with flex: each item's `flex-grow` and `flex-basis` are proportional to its aspect ratio (2:3 → grow 0.667, basis 200px; 4:3 → grow 1.333, basis 400px). Use `flex-wrap: wrap`, `align-items: flex-start`, and gap `clamp(40px, 3vw, 48px)` rows / `clamp(24px, 2.6vw, 40px)` columns. Each image box uses `aspect-ratio`. A CSS grid with `grid-template-columns: 2fr 4fr 2fr` gives the same result on desktop.
- Caption (`<figcaption>`, margin-top 16px), catalog format:
  - Line 1: **TITLE IN CAPS** (Jost, Thistle), followed by `, YEAR` in Dust Grey
  - Line 2: `Acrylic on canvas, 36 × 24 in.`
  - Line 3: price (`$2,400`) or `SOLD`
- Below the row (margin-top `clamp(48px, 4.4vw, 64px)`): the link "VIEW ALL WORK" in Bondi Blue label style with `border-bottom: 1px solid #B290C5` and `padding-bottom: 6px`. It links to the catalog.
- **Mobile:** the items stack vertically at full width (the flex-wrap handles this).

### 5. Footer (`#contact`)
- Background `#2A2931`. Padding 28px vertical, page side padding horizontal.
- One flex row (wraps on mobile), `align-items: baseline`, gap `10px 32px`, 14px Dust Grey:
  `Jeff Clark Artworks` (Thistle) · `Portland, Oregon` · email (mailto link) · `Instagram` (text link, new tab) · `© 2026 Jeff Clark` (pushed right with `margin-left: auto`).
- No newsletter box, no social icons, text links only.

---

## Interactions & Behavior
- Links: default `#5191A8`, hover `#B290C5`, `transition: color .15s`. No underline except the "View all work" rule.
- Nav links are anchor links in the prototype (`#work`, `#about`, `#contact`). In production, WORK should go to the catalog index.
- Breakpoint: **760px** (the collage switches between the desktop and mobile layouts). Everything else is fluid via `clamp()` and flex-wrap.
- The headline has `pointer-events: none`, so the paintings underneath remain clickable. Each collage painting should link to its catalog page.
- No animations, no parallax, no scroll effects.
- Images: lazy-load everything below the fold. For the collage, use `fetchpriority="high"` on the 2–3 largest frames.

## State Management
None required. It's a static page. Featured works and collage images should come from the CMS or catalog data:
`{ id, title, year, medium, dimensions, price | "SOLD", image, aspect }`.

## Content to replace (all placeholder)
- **Bio paragraph** and the **three statements** were drafted as stand-ins. Get the final copy from the artist.
- Featured titles, years, dimensions and prices are invented.
- Email `hello@jeffclarkartworks.com` and the Instagram URL are placeholders.
- All painting images are empty placeholders. Use real artwork photos at the stated aspect ratios: five 2:3, one 4:3, one 1:2 for the collage, plus three featured works.

## Accessibility
- Every painting needs alt text ("Title, year, acrylic on canvas").
- Thistle, Dust Grey, Amethyst and Bondi Blue on `#1F1E24` all pass 4.5:1 at their used sizes. Never use `#006593` for text.
- Keep `<h1>` as real text, not an image.

## Files
- `JeffClarkHomepage.dc.html`: the homepage prototype (all markup and inline styles; the logic class at the bottom handles the 760px switch and the featured data).
- `Homepage Views.dc.html`: canvas showing the page at 1440 and 390 side by side.
- `support.js`, `image-slot.js`: prototype runtime and image placeholder element. **Not for production.**

Open `Homepage Views.dc.html` in a browser (served over a local HTTP server, e.g. `npx serve`) to see both layouts.
