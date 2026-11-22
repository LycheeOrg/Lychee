# Open Questions – Feature 117

Open questions for [Feature 117](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-117-01~~ | 117 | High | What "only expose the root" lists: top-level albums, or the gallery root URL alone | Resolved (A, owner 2026-11-22, spec FR-117-02) | 2026-11-22 | 2026-11-22 |
| ~~Q-117-02~~ | 117 | High | Sitemap behaviour while `login_required` (full or root only) or the gallery password is on | Resolved (A, owner 2026-11-22, spec FR-117-09, ADR-117-01) | 2026-11-22 | 2026-11-22 |
| ~~Q-117-03~~ | 117 | Medium | Whether sensitive (NSFW) albums and their descendants are listed | Resolved (B, owner 2026-11-22, spec FR-117-10; setting in the Sensitive category) | 2026-11-22 | 2026-11-22 |
| ~~Q-117-04~~ | 117 | Medium | Whether public tag albums, smart albums and other public pages are listed | Resolved (B, owner 2026-11-22, spec FR-117-11) | 2026-11-22 | 2026-11-22 |
| ~~Q-117-05~~ | 117 | Medium | Source of `<lastmod>`, or no `<lastmod>` | Resolved (B amended by the owner: `updated_at` and `max_taken_at`, 2026-11-22, spec FR-117-12) | 2026-11-22 | 2026-11-22 |
| ~~Q-117-06~~ | 117 | Medium | Server-side caching of the generated sitemap | Resolved (A, owner 2026-11-22, spec NFR-117-02, ADR-117-01; own cache tag, see ADR) | 2026-11-22 | 2026-11-22 |
| ~~Q-117-07~~ | 117 | Medium | How crawlers discover the sitemap (`robots.txt` is a static file) | Resolved (A, owner 2026-11-22, spec NFR-117-03) | 2026-11-22 | 2026-11-22 |

## Question Details

### ~~Q-117-01~~ — What "only expose the root" lists

**Context.** The owner asked for a second setting, off by default, that "only exposes the root". Lychee's root has two meanings: the gallery root page (`/gallery`) and the top-level albums (`albums.parent_id IS NULL`) that a guest sees on that page.

**Option A (recommended): gallery root plus top-level albums.** With the setting on, the sitemap lists `/gallery` and the browsable albums whose `parent_id` is `NULL`. Nested albums are left out.
- Pros: the sitemap still points crawlers at real content. It hides the depth of the album tree, which seems to be the point of the setting.
- Cons: crawlers can still follow links from top-level albums into sub-albums. This is expected, since those albums are public.

**Option B: the gallery root URL only.** With the setting on, the sitemap holds one `<url>`: `/gallery`.
- Pros: the smallest possible exposure.
- Cons: little value over no sitemap, because crawlers find the home page on their own.

Owner selected A on 2026-11-22. Normative outcome: spec FR-117-02.

### ~~Q-117-02~~ — Gallery-wide locks

**Context.** Three settings stop a guest from browsing the gallery root: `login_required` (everything), `login_required` with `login_required_root_only` (albums open by direct link, the root needs a login), and `gallery_password` (`GalleryLockState`). In all three cases nothing is browsable from the root as a guest. The RSS feed and embeds are switched off while the gallery password is set (`GalleryLockState::isRssEnabled()`, `isEmbedEnabled()`).

**Option A (recommended): answer 404, as if the sitemap were disabled.** Add `GalleryLockState::isSitemapEnabled()` (or an equivalent helper): `sitemap_enable` is on, `login_required` is off, and no gallery password is set.
- Pros: consistent with RSS and embeds. Crawlers get no URL that would only lead to a login page.
- Cons: an admin who enables the sitemap and also requires login gets a 404 without being told why. The setting's `details` text has to say so.

**Option B: answer an empty `<urlset>`.**
- Pros: the URL always answers 200 while the setting is on.
- Cons: Search Console reports an empty sitemap, which is more confusing than a 404.

Owner selected A on 2026-11-22. Normative outcome: spec FR-117-09, ADR-117-01.

### ~~Q-117-03~~ — Sensitive (NSFW) albums

**Context.** Albums marked sensitive (`base_albums.is_nsfw`), and everything below them, are served to guests behind a warning or blur. Hiding them is a frontend choice (`nsfw_visible`). Every other public listing has its own switch (`hide_nsfw_in_rss`, `hide_nsfw_in_search`, `hide_nsfw_in_flow` …). `AlbumQueryPolicy::appendRecursiveSensitiveAlbumsCondition()` already finds albums with a sensitive ancestor.

**Option A (recommended): always exclude them.** Sensitive albums and their descendants are never listed.
- Pros: search engines do not index content the owner flagged as sensitive. No extra setting.
- Cons: an owner who wants sensitive albums indexed cannot do it.

**Option B: a `hide_nsfw_in_sitemap` setting, on by default.** Same pattern as RSS, and it requires `sitemap_enable`.
- Pros: the owner can choose. Matches the other modules.
- Cons: one more setting and one more test branch.

**Option C: include them.**
- Pros: simplest query.
- Cons: sensitive content ends up in search results by default.

Owner selected B on 2026-11-22, with `hide_nsfw_in_sitemap` in the Sensitive (`Mod NSFW`) category. The two other toggles go into an existing category, not a new Sitemap category (owner directive). Planning note: the filter reads the stored `albums.is_recursive_nsfw` flag, as ADR-103-01 requires for checks without an origin, instead of `appendRecursiveSensitiveAlbumsCondition()`. Normative outcome: spec FR-117-01, FR-117-02, FR-117-10.

### ~~Q-117-04~~ — Tag albums, smart albums and other public pages

**Context.** Besides regular albums, a guest can see on the root page public tag albums (`TagAlbum`, not part of the tree, so browsability means visible and without a password) and public smart albums (Recent, Highlighted, On This Day, Unsorted, each made public by its own setting). Guest-facing pages such as `/flow`, `/timeline` and `/map` also exist when their public settings are on. Photos are not listed (owner directive).

**Option A (recommended): regular albums plus public tag albums.** Tag albums count when the guest can see them on the root page and they have no password.
- Pros: the sitemap matches what a guest can browse among albums. Tag albums are curated and stable.
- Cons: a tag album's content changes with tagging, so its URL may show different photos over time.

**Option B: regular albums only.**
- Pros: smallest scope. One query on `albums` with the tree-based browsability filter.
- Cons: public tag albums are left out even though a guest sees them at the root.

**Option C: A plus public smart albums and the enabled public pages.**
- Pros: the most complete picture of what a guest can reach.
- Cons: smart albums and the flow, timeline and map pages change constantly, so they are poor entries to index. More settings to check.

Owner selected B on 2026-11-22. Normative outcome: spec FR-117-11.

### ~~Q-117-05~~ — Source of `<lastmod>`

**Context.** Google uses `<lastmod>` only when it is consistently accurate, and ignores `<changefreq>` and `<priority>`. `base_albums.updated_at` moves when the album's own metadata changes. As far as I can tell, adding photos only updates `albums` columns (counts, `max_taken_at`, covers) through `RecomputeAlbumStatsJob`, so `base_albums.updated_at` probably stays the same. `max_taken_at` is the capture date, not the upload date.

**Option A (recommended): latest of `base_albums.updated_at` and the newest photo added.** `GREATEST(base_albums.updated_at, MAX(photos.created_at))` over `photo_album`, computed in the same `toBase()` query.
- Pros: accurate for the change that matters most in a gallery (new photos).
- Cons: one aggregate join over `photo_album`, which costs more on large galleries.

**Option B: `base_albums.updated_at` only.**
- Pros: no join.
- Cons: probably misses photo additions, and Google may then stop trusting the value.

**Option C: no `<lastmod>`.**
- Pros: cheapest. Never wrong.
- Cons: crawlers cannot prioritise changed albums.

Owner selected B on 2026-11-22, amended: `albums.max_taken_at` also counts, since it moves when photos are added. `<lastmod>` is the later of `base_albums.updated_at` and `albums.max_taken_at`. A `max_taken_at` later than generation time (wrong EXIF clock) is ignored (local tidy-up). Normative outcome: spec FR-117-12.

### ~~Q-117-06~~ — Server-side caching

**Context.** The browsability filter runs a `NOT EXISTS` over the ancestor path for every album, O(n²) in the worst case (see `AlbumQueryPolicy::applyBrowsabilityFilter()`). The URL is public and unauthenticated, so anyone can trigger the query repeatedly. `ManagedCacheService` already caches the root listing with tags that album changes flush (`CacheKeyProvider::albumListingGlobalTag()`).

**Option A (recommended): managed cache plus HTTP caching.** Cache the generated XML through `ManagedCacheService` under one guest key tagged with the album-listing global tag, and send `Cache-Control: public, max-age=3600`.
- Pros: the expensive query runs once per change. It reuses existing invalidation.
- Cons: the XML can be wrong until the next flush if some change does not flush that tag (to be checked during planning).

**Option B: HTTP caching only.** `Cache-Control: public, max-age=3600`, no server cache.
- Pros: no invalidation to get right.
- Cons: every request that misses a proxy or crawler cache runs the full query.

**Option C: no caching.**
- Pros: always fresh.
- Cons: an unauthenticated endpoint with an O(n²) worst case.

Owner selected A on 2026-11-22. Planning check: `albumListingGlobalTag()` is only flushed by `AlbumListingCacheFlushRequested`, not by album saves, moves, deletions or permission changes. The cached entry therefore carries a dedicated `sitemapTag()` as well, flushed by the same `ManagedCacheAlbumListingInvalidator` handlers. The cache holds rows, not XML. Normative outcome: spec NFR-117-02, ADR-117-01.

### ~~Q-117-07~~ — How crawlers find the sitemap

**Context.** `public/robots.txt` is a static file. A `Sitemap:` line there needs an absolute URL, which a static file in the repository cannot know. Without that line, the admin has to submit the URL in Google Search Console, Bing Webmaster Tools and so on.

**Option A (recommended): leave `robots.txt` alone and document the URL.** The setting's `details` text gives the URL to submit (`<APP_URL>/sitemap.xml`).
- Pros: no change to how static files are served (FrankenPHP, nginx, Apache). Most owners who care about SEO use Search Console anyway.
- Cons: crawlers that rely on `robots.txt` alone do not find the sitemap.

**Option B: serve `robots.txt` from Laravel.** Remove the static file and add a route that adds `Sitemap: <APP_URL>/sitemap.xml` when the sitemap is on.
- Pros: automatic discovery by every crawler.
- Cons: deletes a file that deployments may have customised. Some web server configurations serve `.txt` files directly and would never reach the route.

Owner selected A on 2026-11-22. Normative outcome: spec NFR-117-03, FR-117-01 (`details` text).
