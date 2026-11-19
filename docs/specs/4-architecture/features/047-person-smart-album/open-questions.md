# Open Questions – Feature 047

Open questions for [Feature 047](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-047-01~~ | 047 – Person Smart Album | High | `AlbumPhotosController` + `GetAlbumPhotosRequest` + `AlbumHeadController` have hardcoded `TagAlbum` branches — PersonAlbum falls through to wrong code path or throws | Resolved (A – add PersonAlbum branches) | 2026-06-28 | 2026-06-28 |
| ~~Q-047-02~~ | 047 – Person Smart Album | High | `Delete::do()` only checks `tag_albums` table for non-regular albums — PersonAlbums silently skipped then treated as regular Album (tree validation fails) | Resolved (A – add `deletePersonAlbums()`) | 2026-06-28 | 2026-06-28 |
| ~~Q-047-03~~ | 047 – Person Smart Album | High | `EditableBaseAlbumResource` constructor and `fromModel()` accept `Album\|TagAlbum` only — PersonAlbum passed to it will cause a type error | Resolved (A – widen to `Album\|TagAlbum\|PersonAlbum`) | 2026-06-28 | 2026-06-28 |
| ~~Q-047-04~~ | 047 – Person Smart Album | Medium | Frontend `smartAlbums` getter merges `tagAlbums` into smart albums section — spec assumes a separate "Person Albums" section but tag albums have no separate section either | Resolved (A – merge into `smartAlbums` getter) | 2026-06-28 | 2026-06-28 |
| ~~Q-047-05~~ | 047 – Person Smart Album | Medium | Person ID visibility validation — non-admin user could reference non-searchable person IDs in create/update, leaking person names via `HeadPersonAlbumResource.show_persons` | Resolved (B – validate existence only; filter in head resource) | 2026-06-28 | 2026-06-28 |
| ~~Q-047-06~~ | 047 – Person Smart Album | Medium | Person deletion leaves orphaned PersonAlbum with zero persons — album shows zero photos with no indication of why | Resolved (B modified – cleanup job triggered in delete code path + listener) | 2026-06-28 | 2026-06-28 |

## Question Details

### ~~Q-047-01~~ · `AlbumPhotosController`, `GetAlbumPhotosRequest`, and `AlbumHeadController` don't handle PersonAlbum ✅ RESOLVED

**Status:** Resolved — **Option A** (add PersonAlbum branches alongside TagAlbum in all affected classes)  
**Feature:** 047 – Person Smart Album  
**Priority:** High  
**Opened:** 2026-06-28  
**Resolved:** 2026-06-28

**Resolution:** Add `PersonAlbum` handling to: (a) `AlbumFactory::findBaseAlbumOrFail` — third query for `PersonAlbum`; (b) `AlbumFactory::findBaseAlbumsOrFail` — same; (c) `GetAlbumPhotosRequest::processValidatedValues` — `PersonAlbum::find()` fallback after `TagAlbum::find()`; (d) `AlbumPhotosController::get()` — `elseif ($album instanceof PersonAlbum)` branch identical to the TagAlbum branch; (e) `AlbumHeadController::get()` — PersonAlbum case in `match(true)`.

**Spec impact:** I2/T-047-04 expanded to include `AlbumFactory::findBaseAlbumsOrFail`. I6/T-047-12 expanded to include `AlbumHeadController`. New tasks T-047-04b (`GetAlbumPhotosRequest`) and T-047-04c (`AlbumPhotosController`) added to I2.

---

### ~~Q-047-02~~ · `Delete::do()` only checks `tag_albums` table — PersonAlbums silently mishandled ✅ RESOLVED

**Status:** Resolved — **Option A** (add `deletePersonAlbums()` mirroring `deleteTagAlbums()`)  
**Feature:** 047 – Person Smart Album  
**Priority:** High  
**Opened:** 2026-06-28  
**Resolved:** 2026-06-28

**Resolution:** Mirror the `deleteTagAlbums()` pattern: at the start of `Delete::do()`, query `DB::table('person_albums')` to find person album IDs, call a new `deletePersonAlbums()` method (cleans up purchasables, live_metrics, access_permissions, statistics, person_albums, base_albums), then remove those IDs from the remaining set before processing regular albums.

**Spec impact:** FR-047-06 updated to note Delete action modification. New task T-047-09b added for `Delete::do()` update.

---

### ~~Q-047-03~~ · `EditableBaseAlbumResource` constructor accepts `Album|TagAlbum` only — PersonAlbum causes type error ✅ RESOLVED

**Status:** Resolved — **Option A** (widen to `Album|TagAlbum|PersonAlbum`, add `persons` field)  
**Feature:** 047 – Person Smart Album  
**Priority:** High  
**Opened:** 2026-06-28  
**Resolved:** 2026-06-28

**Resolution:** Change the constructor and `fromModel()` signature to `Album|TagAlbum|PersonAlbum`. Add an `if ($album instanceof PersonAlbum)` branch that sets `$this->persons` (new field: array of `{id, name}`) and `$this->is_and`. Keep the existing `tags` field for TagAlbum backward compatibility.

**Spec impact:** DO-047-01 updated: `EditableBaseAlbumResource` gains `persons` field. I6/T-047-11 expanded. TypeScript types updated in I11/T-047-28.

---

### ~~Q-047-04~~ · Frontend: tag albums are merged into smart albums section — spec assumes a separate "Person Albums" section ✅ RESOLVED

**Status:** Resolved — **Option A** (merge person albums into `smartAlbums` getter, same as tag albums)  
**Feature:** 047 – Person Smart Album  
**Priority:** Medium  
**Opened:** 2026-06-28  
**Resolved:** 2026-06-28

**Resolution:** Add `personAlbums` to `AlbumsState`, concatenate `baseSmartAlbums`, `tagAlbums`, and `personAlbums` in the `smartAlbums` getter. Person Albums appear in the "Smart Albums" section alongside smart and tag albums, consistent with how tag albums are currently displayed.

**Spec impact:** FR-047-04 updated: "Person Albums appear in the root album listing within the Smart Albums section" (not a dedicated section). UI-047-04 updated. I12/T-047-30 simplified.

---

### ~~Q-047-05~~ · Person ID visibility validation — non-admin may reference non-searchable persons ✅ RESOLVED

**Status:** Resolved — **Option B** (validate existence only; filter persons in head resource)  
**Feature:** 047 – Person Smart Album  
**Priority:** Medium  
**Opened:** 2026-06-28  
**Resolved:** 2026-06-28

**Resolution:** Keep `exists:persons,id` in the request validation. In `HeadPersonAlbumResource` and `EditableBaseAlbumResource`, filter `show_persons` / `persons` to only include persons visible to the current user (using the `Person::searchable()` scope). The album still functions — photo resolution via faces is unaffected — but invisible person names are redacted from API responses. This also handles the case where a person becomes non-searchable after album creation.

**Spec impact:** FR-047-12 updated: `show_persons` is filtered by visibility. I6/T-047-11 notes expanded for visibility filtering in both resources.

---

### ~~Q-047-06~~ · Person deletion leaves orphaned PersonAlbum with zero persons ✅ RESOLVED

**Status:** Resolved — **Option B modified** (cleanup job triggered in delete code path + listener)  
**Feature:** 047 – Person Smart Album  
**Priority:** Medium  
**Opened:** 2026-06-28  
**Resolved:** 2026-06-28

**Resolution:** When a Person is deleted, dispatch a `CleanupOrphanedPersonAlbumsJob` that queries `person_albums` whose ID no longer appears in `person_albums_persons` (zero persons remaining) and deletes them via the existing `Delete` action. The job is triggered in two ways: (1) explicitly dispatched in the `PeopleController::destroy()` code path (not via observer), and (2) registered as an event listener so other deletion paths (merge, CLI) also trigger cleanup. This avoids orphaned albums accumulating while keeping the cleanup logic centralized in a job rather than inline in the controller.

**Spec impact:** New FR-047-13 for orphan cleanup. New DO-047-05 for `CleanupOrphanedPersonAlbumsJob`. New task T-047-09c in I4. Test added in I9.
