# Open Questions – Feature 105

Open questions for [Feature 105](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-105-01~~ | 105 – Album edit select feedback | High | What an unset (`null`) select shows in the v8 album edit drawer (A explicit "Default" entry mapped to `null`, timelines reuse their `default` entry, header/cover show "Automatic" · B placeholder text only · C show the resolved gallery value) | Resolved (Option A; owner, 2026-11-01; spec FR-105-02 … 05; timelines: Q-105-02) | 2026-11-01 | 2026-11-01 |
| ~~Q-105-02~~ | 105 – Album edit select feedback | High | Timeline selects: a stored `default` turns the album's timeline on, `null` follows the gallery switch (A "Default" entry for `null` + the `default` value shown as "Enabled" · B "Default" entry for `null`, `default` keeps its label · C `null` shown as the `default` entry) | Resolved (Option A; owner, 2026-11-01; spec FR-105-06) | 2026-11-01 | 2026-11-01 |
| ~~Q-105-03~~ | 105 – Album edit select feedback | Medium | Same defects outside the album edit drawer: "Set as header" and the webshop buy button read size variants a struct-of-arrays tile lacks; the bulk album edit table and fields dialog show no "Default", label the timeline `default` value "default" and can store a sort column without its order (A fold all into 105 · B fold only the struct-of-arrays pair, bulk edit in its own feature · C log only) | Resolved (Option A; owner, 2026-11-01; spec FR-105-09, FR-105-10) | 2026-11-01 | 2026-11-01 |

## Question Details

### ~~Q-105-01~~ – What an unset select shows

**Context:** In the v8 album edit drawer, `null` means "use the gallery setting" for photo/album sort column and order, aspect ratio, photo layout and both timelines, and "automatic" for header (random photo) and cover. `selectedLabel()` renders `null` as an empty trigger and no entry is checked when the dropdown opens. A stored value gets the check icon and the highlight on open (checked on the scratch instance for all twelve selects). The date scrubber already maps `null` to a "Default" entry. The API requires an order whenever a sort column is sent (`required_with`), so picking a column while the order is unset answers 422 and nothing is saved.

- **Option A (recommended):** An explicit "Default" entry at the top of each select whose `null` means "gallery setting" (photo and album sort column, aspect ratio, photo layout), mapped to `null`. The timelines check their existing `default` entry for `null`. A sort order follows its column: column "Default" shows order "Default" and disables it; picking a column while the order is unset sets Ascending. Header and cover show "Automatic" in the trigger when unset (cover keeps its clear button).
  - ✅ Every select shows a value when the drawer opens and checks an entry when it opens, like the date scrubber.
  - ✅ A field can go back to the gallery setting, which v8 cannot do today for these fields.
  - ✅ Removes the silent 422 when a column is picked before its order.
  - ❌ Two labels ("Default", "Automatic") in 23 locales.
  - ❌ "Default" does not say what the gallery setting is.
- **Option B:** Placeholder text only: an unset select shows a muted "Default" (or "Automatic") in its trigger; lists unchanged.
  - ✅ Smallest change.
  - ❌ The dropdown still opens with nothing checked for an unset field.
  - ❌ No way back to the gallery setting once a value is picked.
- **Option C:** Show the resolved gallery value: an unset field displays e.g. "Taken date (default)" and checks that entry.
  - ✅ Tells the user the value that actually applies.
  - ❌ The drawer needs the gallery defaults (five settings) from the server.
  - ❌ A checked entry no longer tells whether the album pins the value or inherits it; picking that same entry pins it.

**Resolution:** Option A (owner, 2026-11-01) for the sort columns and orders, aspect ratio, photo layout, header and cover (FR-105-02 … 05). The timeline selects are decided by Q-105-02.

### ~~Q-105-02~~ – Timeline selects: `null` versus `default`

**Context:** `null` and `default` share the granularity (`HasTimelineData`, `PhotoBucketComputer`, `AlbumBucketComputer`: both resolve to `timeline_*_granularity`) but not the on/off switch: `AlbumConfig` turns the album's timeline on for any non-null value, so `default` means "on, gallery granularity" even when `timeline_albums_enabled`/`timeline_photos_enabled` is off, while `null` follows that switch. The drawer autosaves every field on any edit, so a select that shows `null` as the `default` entry writes `default` the next time the user edits anything. The option lists (`timelineAlbumGranularityOptions`, `timelinePhotoGranularityOptions`, label `gallery.timeline.default` = "default") are shared with v7 and both bulk-edit dialogs, so a drawer-only change uses drawer-local lists.

- **Option A (recommended):** Drawer-local timeline lists: a "Default" entry (`null`, follows the gallery switch and granularity) first, then the `default` value labelled "Enabled", then "disabled" and the SE granularities. Same three-way reading as the date scrubber (Default / Enabled / Disabled).
  - ✅ Shows the stored value without changing what is saved.
  - ✅ The two meanings get two distinct labels.
  - ❌ One more label ("Enabled") in 23 locales.
- **Option B:** "Default" entry for `null`; the `default` value keeps its label "default".
  - ✅ No extra label.
  - ❌ Two entries read "Default" and "default" with different effects.
- **Option C:** Show `null` as the existing `default` entry (Q-105-01 Option A as written).
  - ✅ No new entry.
  - ❌ The next autosave stores `default` and turns the timeline on for that album when the gallery switch is off.

**Resolution:** Option A (owner, 2026-11-01). Drawer-local timeline lists with a "Default" entry and the `default` value labelled "Enabled" (FR-105-06).

### ~~Q-105-03~~ – Same defects outside the album edit drawer

**Context:** Survey of the v8 forms after FR-105-08.
- Struct-of-arrays tiles carry no size variants until their details load. `AlbumPanel.vue` "Set as header" (selection menu) writes `selectedPhoto.size_variants.medium ?? small` into `preFormattedData.url`, so the hero gets `null` until a reload; `composables/album/buyMeActions.ts` `getPricesOptionsForPhoto()` drops the `medium`/`medium2x` prices of a tile whose details are not loaded. Found by reading the code, not reproduced. `AlbumPanel.vue` already loads details for the selection before the tag and license dialogs (Q-065-05).
- `BulkAlbumEdit.vue` inline sort cells: an inheriting album opens a select with nothing checked, there is no way back to the gallery setting, and picking a column sends `photo_sorting_col`/`album_sorting_col` alone (`sometimes` skips `required_with`), so an album without stored order keeps a column the model ignores (`getAlbumSortingAttribute()` needs both) while the table shows it.
- `BulkEditFieldsDialog.vue`: a checked field left empty resets it to the gallery setting without saying so; the timeline lists show the `default` value as "default"; sort column and order have separate checkboxes, so a column can be set without its order.
- Not affected: settings pages (config values are never `null`), photo edit and photo license dialog (a photo license is never `null`, "None" is an entry), the move-to album picker (`Thumb` under struct-of-arrays already), spotlight search, map, flow cards, landing, watermark preview and people pages (full photos from the v2 APIs), the other request parsers (`BulkAlbumPatchData` and `BulkAlbumController` already use `ColumnSortingAlbumType`), v7 (no struct-of-arrays album views).

- **Option A (recommended):** Fold all into Feature 105: load the selected photo's details before "Set as header" and before the buy-me price filter; bulk table and fields dialog get the "Default" entry, the "Enabled" timeline label and a sort column always sent with its order.
  - ✅ One change set for one class of defect.
  - ✅ Reuses `albumSelectOptions.ts` and the existing detail loading.
  - ❌ Feature 105 grows past the drawer (admin bulk edit, webshop).
- **Option B:** Fold only "Set as header" and buy-me into Feature 105; bulk album edit in its own feature.
  - ✅ Feature 105 stays on the album page.
  - ❌ The bulk edit half-pair bug waits.
- **Option C:** Log only; each item becomes its own feature later.
  - ✅ Nothing more in this change set.
  - ❌ Known defects stay.

**Resolution:** Option A (owner, 2026-11-01). Struct-of-arrays actions in FR-105-09, bulk album edit selects in FR-105-10.
