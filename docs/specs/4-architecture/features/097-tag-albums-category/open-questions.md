# Open Questions – Feature 097

Open questions for [Feature 097](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-097-01~~ | 097 | Medium | Position and header of the Tag Albums panel on the root gallery | Resolved (A: below Smart Albums, header "Tag Albums"; spec FR-097-04) | 2026-10-17 | 2026-10-17 |
| ~~Q-097-02~~ | 097 | Medium | Do person albums move with tag albums or stay in Smart Albums? | Resolved (C: second setting, own Person Albums panel; spec FR-097-01, FR-097-05) | 2026-10-17 | 2026-10-17 |
| ~~Q-097-03~~ | 097 | Medium | Which frontends get the split (v7 and v8, or v8 only)? | Resolved (A: v7 and v8; spec Overview, FR-097-06) | 2026-10-17 | 2026-10-17 |

## Question Details

### ~~Q-097-01~~ – Position and header of the Tag Albums panel

**Resolution (2026-10-17):** Option A.

**Context:** Today the root gallery shows, in order: Smart Albums (built-in smart albums + tag albums + person albums, merged in `AlbumsState.smartAlbums`), then Featured (pinned) Albums, then Albums, then shared albums (tabs or inline). With the new setting on, tag albums leave the Smart Albums panel and need their own panel.

- **Option A (recommended):** Directly below Smart Albums, above the tabs and Featured Albums, header "Tag Albums" (new key `gallery.tag_albums`). Always visible like Smart Albums (outside the tabs).
  - Pros: smallest visual move from today; tag albums stay where users already look; no interaction with the tabbed shared-albums layout.
  - Cons: still above the user's own albums.
- **Option B:** Below Featured Albums, just above Albums (inside the "My albums" tab when tabs are shown).
  - Pros: groups tag albums with the user's own curated content.
  - Cons: tags are cross-owner collections, placing them in "My albums" tab is misleading; two template branches to touch.
- **Option C:** At the bottom of the page, after Albums and shared albums.
  - Pros: keeps the top of the page compact.
  - Cons: hard to find on large galleries; struct-of-array mode uses a virtual scroller for Albums, so a panel after it is reached only after scrolling everything.

### ~~Q-097-02~~ – Person albums

**Resolution (2026-10-17):** Option C. Settings follow the `TA_`/`PA_` key prefixes (`TA_separate_category`, `PA_separate_category`); the Person Albums panel sits directly below the Tag Albums panel (same placement rule as Q-097-01 A).

**Context:** `AlbumsState.smartAlbums` concatenates `baseSmartAlbums`, `tagAlbums` and `personAlbums`. The request names only tag albums.

- **Option A (recommended):** Person albums stay in Smart Albums; only tag albums move.
  - Pros: matches the request literally; smallest change.
  - Cons: person albums remain mixed with smart albums.
- **Option B:** Person albums move to the Tag Albums panel too (renamed e.g. "Collections").
  - Pros: all user-defined virtual albums together.
  - Cons: blurs the meaning of the panel and the setting name.
- **Option C:** A second setting gives person albums their own panel as well.
  - Pros: symmetric and flexible.
  - Cons: scope creep; one more setting.

### ~~Q-097-03~~ – Frontend scope

**Resolution (2026-10-17):** Option A.

**Context:** Both `resources/js/v7/views/gallery-panels/Albums.vue` and `resources/js/v8/views/gallery-panels/Albums.vue` render the Smart Albums panel from the shared `AlbumsState` store.

- **Option A (recommended):** Both v7 and v8.
  - Pros: the setting behaves the same whichever UI the admin runs; the store change is shared anyway, so v7 costs one template block.
  - Cons: slightly more UI to verify.
- **Option B:** v8 only; v7 keeps the merged panel regardless of the setting.
  - Pros: smaller verification surface.
  - Cons: a setting that silently does nothing on v7.
