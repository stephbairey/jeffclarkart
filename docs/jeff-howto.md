# Jeff Clark Artworks — how to run the site

Log in at **https://jeffclarkart.com/wp-admin/**. Everything about a painting lives in one place: the painting's own entry. Change it there and every page that shows it updates.

## Add a painting

1. In the left menu click **Artworks → Add Painting**.
2. **Title**: the painting's name, exactly as you want it shown (no quotes; the site adds the catalog format).
3. **Painting image** (right column, "Set painting image"): upload one photo. Use the 3000-pixel web version, not the 9000-pixel master.
4. **Painting details** (below the title):
   - Year
   - Medium (already says Acrylic on Canvas)
   - Height and Width in inches, **height first**
   - Price in whole dollars
   - Status: Available, Sold, Reserved, or Inquire (Inquire hides the price)
   - Sold note, if sold (for example "Private Collection")
   - Commissioned work: tick it for pieces made to order; they show as examples on the Commissions page.
   - Variant group: only for sets like the Pop Elegies. Type the set name ("Self-Portrait 04") and all paintings with that name show together.
5. **Series** (right column): tick one.
6. **Homepage** (right column): tick "Show in the homepage collage" and give it a slot number 1–7 if you want it on the front page. "Featured row 1–3" puts it in the three-painting row under your statement.
7. Click **Publish**.

The painting now appears in Work, in its series page, and anywhere else it should.

## Mark a painting sold

1. **Artworks → All Paintings**, click the title.
2. Change **Status** to Sold. Add a Sold note if you like.
3. **Update**.

The price disappears and "SOLD" shows in its place everywhere.

## Change many prices at once

1. Make a spreadsheet with two columns in the first row: `title` and `price`. One painting per row. Optional third column `status` (available, sold, reserved, inquire).
2. Save it as .csv or .xlsx.
3. In WordPress go to **Tools → Price Sheet**, choose the file, click **Preview changes**.
4. Read the preview. Rows it couldn't match are listed in red with the reason (usually a title typo). Nothing has changed yet.
5. Click **Apply these changes**.

Titles don't have to match capitalization or spacing, but they do have to be the same words.

## Your homepage text

**Pages → Home** has four boxes: a bio paragraph and three short statements (each with an opening line in purple and the rest in grey). Edit, then Update.

## Your About page, Commissions and Contact text

**Pages → About** (set a photo of yourself with "Set featured image"), **Pages → Commissions**, **Pages → Contact**. Ordinary page text.

## News posts

**Posts → Add New** for studio news, shows, or anything else. Give it a title, write in the editor (paragraphs, headings, images and quotes all work), and set a featured image if you want a picture beside it on the News page. Click **Publish**. The newest post appears first at **News** in the top menu.

There is a sample post there now. Edit it into your first real post or delete it under **Posts → All Posts**.

## Exhibitions

**Exhibitions → Add New** when you have a show: title, venue, city, dates, a note, a link. The Exhibitions page lists them newest first and says "No exhibitions listed yet" until you add one.

## Email and Instagram

**Settings → General**, near the bottom: Public contact email, Instagram URL, and the two form IDs (leave those alone).

## Inquiries

Form messages email you at the contact address and are also kept under **Ninja Forms → Submissions** in case one goes astray.

## Things not to do

- Don't install plugins or change the theme. Ask Steph.
- Don't upload the giant master files; they'll slow the site.
- Don't delete paintings that have sold. Mark them Sold so the record stays.
