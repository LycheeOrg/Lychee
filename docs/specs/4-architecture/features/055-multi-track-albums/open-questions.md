# Open Questions – Feature 055

Open questions for [Feature 055](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-055-01~~ | 055 – Multi-Track Albums | High | Legacy v7 UI keeps single-track support (per user instruction) while v8 gets full multi-track CRUD — how does the shared backend serve both once tracks move to their own table? | Resolved (A — legacy `Album::track` endpoints keep working unchanged, operating on a "primary" track (oldest-by-id) in the new `tracks` table; v8 gets new `Album::tracks` endpoints and forks its own frontend service/components) | 2026-08-17 | 2026-08-17 |
| ~~Q-055-02~~ | 055 – Multi-Track Albums | Medium | Map view (v8) — how should multiple simultaneous tracks be rendered/distinguished (colors, legend, toggle)? | Resolved (A — all tracks rendered simultaneously, one color per track, Leaflet layer-control legend with per-track visibility checkboxes) | 2026-08-17 | 2026-08-17 |
| ~~Q-055-03~~ | 055 – Multi-Track Albums | Medium | Track upload UX in v8 — single-file-at-a-time vs. batch multi-file upload, and where/how the `name` field is set (auto from filename vs. required user input, rename support). | Resolved (A — batch multi-file upload, name defaults to filename, rename supported afterward) | 2026-08-17 | 2026-08-17 |
| ~~Q-055-04~~ | 055 – Multi-Track Albums | Medium | Scope of the `disk` field — data-model column only (all writes stay on the default local disk) vs. an active S3-offload capability mirroring `UploadSizeVariantToS3Job` in this same feature. | Resolved (B — also build a track-specific S3-offload job + migration console command as part of this feature) | 2026-08-17 | 2026-08-17 |
| ~~Q-055-05~~ | 055 – Multi-Track Albums | Medium | First draft proposed a brand-new standalone `TrackManager.vue` dialog for v8 track management, opened from the album header's "+" add menu. User corrected this while reviewing the spec. | Resolved (fold track management into the existing `AlbumEdit.vue` "Album Settings" modal as a new `tracks` section, alongside `share`/`move`/`danger`/etc. — no new dialog) | 2026-08-17 | 2026-08-17 |
| ~~Q-055-06~~ | 055 – Multi-Track Albums | Medium | `Track.disk`'s default value source — hardcoded `'images'` literal (mirroring `SizeVariant`'s independently-duplicated `DEFAULT` constant) vs. `StorageDiskType::LOCAL->value` vs. `config('filesystems.default')` — these three coincide today but aren't wired together. | Resolved (A — `Track` model's default-on-create reads `StorageDiskType::LOCAL->value`) | 2026-08-17 | 2026-08-17 |
| ~~Q-055-07~~ | 055 – Multi-Track Albums | Medium | FR-055-04's "otherwise a new primary track row is created" branch doesn't state the new row's `name` value; the sibling "update in place" branch says "reset to the new filename" (ambiguous re: extension) and FR-055-06 strips the extension for v8 batch uploads. Are all three name-derivation paths meant to be identical? | Resolved (A — identical extension-stripped-filename convention on every creation/rename-on-reupload path) | 2026-08-17 | 2026-08-17 |
| ~~Q-055-08~~ | 055 – Multi-Track Albums | High | Plan/tasks cite `AlbumTagsController` as the precedent for the new `AlbumTracksController`'s `store`/`update`/`destroy` convention, but `AlbumTagsController` actually has only one read-only `get()` action (computed tag list) — no CRUD sub-resource pattern exists there to mirror. | Resolved (A — convention spelled out explicitly in the spec instead of citing `AlbumTagsController`; kept as its own controller) | 2026-08-17 | 2026-08-17 |
| ~~Q-055-09~~ | 055 – Multi-Track Albums | Medium | No `.gpx` fixture file and no Feature/HTTP-level test exist anywhere in the repo for the legacy track endpoints today (only two `Unit` FormRequest tests with mocked Gates) — T-055-01/05/09 assume existing coverage to "extend," which doesn't exist. | Resolved (C — new Feature/HTTP tests created from scratch, using a repurposed non-GPX file with a `.gpx` extension as the fixture, since GPX content validation is a Non-Goal) | 2026-08-17 | 2026-08-17 |
| ~~Q-055-10~~ | 055 – Multi-Track Albums | Low | `oldestOfMany()`/`HasOne::ofMany()` has zero prior usage anywhere in this codebase (framework supports it, but it's an unproven pattern here) — plan/tasks frame T-055-04 as merely "confirming availability" rather than de-risking a genuinely novel pattern. | Resolved (C — dropped in favor of an explicit `tracks.is_primary` boolean column, transactionally maintained on create/delete) | 2026-08-17 | 2026-08-17 |
| ~~Q-055-11~~ | 055 – Multi-Track Albums | Low | Plan's risk section and FR-055-12 attribute `Schema::disableForeignKeyConstraints()` and the `album_size_statistics` dependents-cleanup block to `Delete.php`; both actually live in `AlbumsToBeDeletedDTO::executeDelete()` — wrong-file guidance risks I4 landing the new `tracks` cleanup line in the wrong class. | Resolved (A — plan.md/tasks.md corrected to name both files explicitly) | 2026-08-17 | 2026-08-17 |
| ~~Q-055-12~~ | 055 – Multi-Track Albums | Medium | v8's `AlbumEdit.vue` gates each existing section with its own condition (e.g. `Move` additionally requires `is_model_album`, the whole modal returns no sections unless `is_base_album`) — spec only says the new `tracks` section is gated on `can_edit`, without saying whether it also needs an `is_model_album`-style type restriction (hidden for smart/tag/person albums) like `Move`. | Resolved (A — gated like `Move`: `is_model_album && can_edit`) | 2026-08-17 | 2026-08-17 |
| ~~Q-055-13~~ | 055 – Multi-Track Albums | Medium | NFR-055-04's reversible migration `down()` only reconstructs `track_short_path` from each album's primary track — a rollback performed after the feature has been live (with albums holding multiple v8-added tracks) silently discards every non-primary track's file+row with no guard or warning. | Resolved (A — risk accepted and documented explicitly in NFR-055-04, no code guard added) | 2026-08-17 | 2026-08-17 |

## Question Details

### ~~Q-055-01~~: Legacy v7 backend compatibility strategy for multi-track albums ✅ RESOLVED

**Status:** Resolved — **Option A** (legacy endpoints act on a "primary" track)
**Feature:** 055 – Multi-Track Albums
**Priority:** High
**Opened:** 2026-08-17
**Resolved:** 2026-08-17

**Resolution:** `POST`/`DELETE Album::track` keep their existing route names, request shapes, and behaviour for v7, but internally operate on the album's "primary" track — the oldest (lowest-`id`) row in the new `tracks` table, exposed via `Album::primaryTrack(): HasOne` (`hasOne(Track::class)->oldestOfMany('id')`). `track_url` stays on `HeadAlbumResource`/`PositionDataResource` computed from the primary track only. v8 gets new, separate `Album::tracks` endpoints (plural) for full multi-track CRUD, and forks its own frontend service/components (new `resources/js/v8/services/track-service.ts`, a new `tracks` section inside the existing `AlbumEdit.vue` settings modal per Q-055-05, rewritten `Map.vue`) rather than editing the shared `album-service.ts`/`AlbumHeader.vue`/`contextMenuAlbumAdd.ts`/`Map.vue` used by v7 — consistent with the project's v8-migration convention of forking shared modules rather than editing them in place.

**Spec impact:** FR-055-04/05/06 below; DO-055-02/03; API-055-01..06.

**Context:** Today exactly one track exists per album: `albums.track_short_path` (nullable string) plus a computed `track_url` accessor (`app/Models/Album.php:525,541,566`). `Album::setTrack()` deletes any prior file before saving the new one, enforcing "at most one." This is exposed to both frontend trees (v7 legacy PrimeVue, v8 Nuxt UI) via the same shared `track_url` field on `HeadAlbumResource`/`PositionDataResource`, the same shared `album-service.ts` (`uploadTrack`/`deleteTrack`), and near-identical `AlbumHeader.vue`/`contextMenuAlbumAdd.ts`/`Map.vue` code in each tree. The user has instructed that v7 keeps single-track support unchanged while v8 gets full multi-track add/remove/list UI. Moving the backend to a genuine `tracks` child table (one-to-many on `album_id`) means the old single-row assumption no longer holds once a v8 user adds a second track.

**Question:** How should the shared REST API serve both frontends once multiple tracks can exist per album?
- **Option A (Recommended):** Keep the legacy endpoints (`POST`/`DELETE Album::track`) working exactly as today for v7, but re-implement them internally against the new `tracks` table by operating on a single designated "primary" track (e.g., the first track by insertion order/lowest `id`). `track_url` on `HeadAlbumResource`/`PositionDataResource` keeps being computed from that primary track only. `POST Album::track` when a primary already exists replaces just the primary (matching today's "delete old, save new" behaviour) without touching any other tracks added via v8; `DELETE Album::track` removes only the primary. v8 gets new dedicated endpoints (e.g. `GET/POST/PATCH/DELETE Album::tracks`) for full multi-track CRUD, and forks its own frontend service/components per the existing "fork shared modules rather than edit in place" v8-migration convention rather than editing `album-service.ts`/`AlbumHeader.vue` in place.
- **Option B:** Deprecate the legacy write endpoints; v7 can still display the primary track (read-only `track_url`, same accessor as Option A) but loses the ability to add/replace/delete a track from its UI (buttons removed or disabled in v7's `contextMenuAlbumAdd.ts`).
- **Option C:** Drop v7 track support entirely (no `track_url` in v7 API responses, no track rendering in v7's `Map.vue`, no upload/delete UI) — the feature becomes v8-exclusive end to end.

**Impact:** Determines whether `Album::setTrack()/deleteTrack()` are refactored to delegate to the new `Track` model or removed outright; whether `HeadAlbumResource`/`PositionDataResource`/`PositionData` actions keep a `track_url` field at all; whether v7's `AlbumHeader.vue`/`contextMenuAlbumAdd.ts`/`Map.vue` need any changes; and the shape of the new v8-only multi-track API/DTO/frontend surface. Also affects `app/Actions/Album/Delete.php`'s track cleanup (currently hardcoded to `StorageDiskType::LOCAL` for the single `track_short_path`).

---

### ~~Q-055-02~~: Multi-track rendering on the Map view (v8) ✅ RESOLVED

**Status:** Resolved — **Option A** (all tracks shown, colored, with legend toggle)
**Feature:** 055 – Multi-Track Albums
**Priority:** Medium
**Opened:** 2026-08-17
**Resolved:** 2026-08-17

**Resolution:** All of an album's tracks render simultaneously in v8's `Map.vue`, each as its own `L.GPX` layer colored from a fixed cycling palette. Leaflet's built-in `L.control.layers` overlay control (not a custom component) provides the legend — one entry per track (`name`), each with a native visibility checkbox. Toggle state is client-side only (not persisted). v7's `Map.vue` is untouched (still renders the single `track_url`).

**Spec impact:** FR-055-08 below; UI-055-02; ASCII mock-up.

**Context:** `Map.vue` (v8) currently loads a single `track_url` into one Leaflet `L.GPX` layer. With multiple tracks per album, several tracks may need to render simultaneously, and the spec's required UI mock-up needs a concrete design to sketch.

**Question:** How should v8's Map view present multiple tracks?
- **Option A (Recommended):** Render all of an album's tracks simultaneously, each as its own `L.GPX` layer in a distinct color (cycled from a fixed palette), with a small legend/layer-control listing each track's `name` and a checkbox to toggle its visibility.
- **Option B:** Render only one track at a time, selected via a dropdown/tab list next to the map; switching selection swaps the visible `L.GPX` layer.
- **Option C:** Always render all tracks together with no per-track toggle or legend (simplest, but does not surface each track's `name` anywhere on the map).

**Impact:** Drives the spec's required ASCII mock-up, the `DO`/`API` contract for how track color/visibility state is represented (client-only vs. persisted), and the `Map.vue` rewrite scope.

---

### ~~Q-055-03~~: Track upload UX and naming in v8 ✅ RESOLVED

**Status:** Resolved — **Option A** (batch multi-file upload + rename)
**Feature:** 055 – Multi-Track Albums
**Priority:** Medium
**Opened:** 2026-08-17
**Resolved:** 2026-08-17

**Resolution:** The v8 "Add tracks" action accepts multiple GPX files in one selection (`multiple` file input), uploaded in a single `POST Album::tracks` request creating one `Track` row per file. Each new track's `name` defaults to its uploaded filename with the extension stripped. A rename action (inline edit in the track list, `PATCH Album::tracks`) lets the user change the name afterward.

**Spec impact:** FR-055-06/07 below; API-055-02/03; DO-055-02.

**Context:** Today's upload flow is a single hidden `<input type="file">` in `AlbumHeader.vue` that immediately uploads whatever one file was picked (`app/Http/Requests/Album/SetAlbumTrackRequest.php` validates one `file`). The user's requested `Track` fields include a `name` distinct from `file_name`, implying the name is either user-supplied or derived and editable.

**Question:** For the new v8 add/remove-tracks UI:
- **Option A (Recommended):** Support multi-file selection in one upload action (batch-add several GPX files at once); each new track's `name` defaults to its uploaded filename (without extension) and can be renamed afterward via an inline rename action in the track list/menu.
- **Option B:** Keep upload strictly one file per action (repeat the action to add more tracks); `name` is always the filename, with no rename capability in this feature (deferred).

**Impact:** Shapes `SetAlbumTrackRequest`'s replacement validation rules (single vs. array of files), the new `API-055-*` upload contract, and whether a rename `API`/`FR` entry is in scope for this feature.

---

### ~~Q-055-04~~: Scope of the `disk` field — data model only vs. active S3 offload ✅ RESOLVED

**Status:** Resolved — **Option B** (also build the S3-offload job now)
**Feature:** 055 – Multi-Track Albums
**Priority:** Medium
**Opened:** 2026-08-17
**Resolved:** 2026-08-17

**Resolution:** In addition to the `tracks.disk` column (default local, cast to `StorageDiskType`, resolved via `Storage::disk($track->disk->value)`), this feature adds a track-specific offload job (`App\Jobs\UploadTrackToS3Job`, mirroring `UploadSizeVariantToS3Job`) auto-dispatched on new-track upload when `Features::active('use-s3')`, plus a new console command (mirroring `lychee:s3_migrate`/`MoveToS3`) to bulk-migrate existing local tracks to S3.

**Spec impact:** FR-055-10/11 below; DO-055-04; CLI-055-01.

---

### ~~Q-055-05~~: Standalone dialog vs. folding into the existing `AlbumEdit.vue` settings modal ✅ RESOLVED

**Status:** Resolved — fold into the existing modal, no new dialog
**Feature:** 055 – Multi-Track Albums
**Priority:** Medium
**Opened:** 2026-08-17
**Resolved:** 2026-08-17

**Context:** The first spec draft (reviewed by the user via the IDE) designed a new standalone `TrackManager.vue` dialog for v8, triggered by a "Manage tracks" entry in the album header's "+" add context menu (`contextMenuAlbumAdd.ts`). Research into the actual v8 codebase found that album-level management surfaces (sharing, transfer, move, danger-zone delete, purchasable/shop config) all already live as scroll-spy sections inside one existing modal, `resources/js/v8/components/drawers/AlbumEdit.vue` ("Album Settings," opened via the gear icon in `AlbumHeader.vue`, toggled through the shared `useTogglablesStateStore().is_album_edit_open`). Each section is a sub-component under `resources/js/v8/components/forms/album/` (`AlbumProperties.vue`, `AlbumVisibility.vue`, `AlbumMove.vue`, `AlbumShare.vue`, `AlbumPurchasable.vue`, `AlbumTransfer.vue`, `AlbumDelete.vue`), registered via a local `SectionId` union type and `sections` computed (`AlbumEdit.vue:129-134` at time of research) plus a `<section id="album-settings-<id>">` block per entry.

**Resolution:** Track management is a new section (`SectionId` value `"tracks"`) inside `AlbumEdit.vue`, backed by a new `AlbumTracks.vue` component under `resources/js/v8/components/forms/album/`, following the exact same registration pattern as the existing sections (gated the same way `share`/`move`/etc. are gated on rights). No new standalone dialog is built. The existing "+" add-menu entries for track upload/delete (`gallery.menus.upload_track`/`delete_track` in `contextMenuAlbumAdd.ts`, v8 only) are removed, since track management now lives in Album Settings like every other album-configuration concern — consistent with how sharing/transfer are *not* in the "+" menu either.

**Spec impact:** UI mock-ups section rewritten (album-settings section instead of a new dialog); FR-055-06/07/08 wording updated to reference `AlbumEdit.vue`'s `tracks` section instead of a "Track manager dialog"; UI-055-01 relabeled.

---

### ~~Q-055-06~~ · `Track.disk`'s default value — literal, enum, or config? ✅ RESOLVED

**Status:** Resolved — Option A  
**Feature:** 055 – Multi-Track Albums  
**Resolved:** 2026-08-17

**Resolution:** The `Track` model's default-on-create value for `disk` reads `StorageDiskType::LOCAL->value` directly (not a bare `'images'` literal, not `config('filesystems.default')`). The migration's column-level `default()` still takes a literal string (schema defaults can't reference PHP enums), but every place that *assigns* a disk at creation time in application code goes through the enum.

**Spec impact:** FR-055-01/FR-055-10 updated to state the enum reference explicitly.

**Preferred option:** 🅰️ (**recommended**) Option A – Reference the enum directly

**Question**  
FR-055-01 specifies the `tracks.disk` column as "default `images`, cast to `StorageDiskType`"; FR-055-10 separately says `Track.disk` "defaults to the app's configured default disk (`images`, i.e. `StorageDiskType::LOCAL`)". Verified against the actual codebase: `StorageDiskType::LOCAL = 'images'` (`app/Enum/StorageDiskType.php`), `config/filesystems.php`'s `default` key is also `'images'`, and the precedent this feature is told to mirror — `size_variants.storage_disk`'s default — is a **hardcoded PHP string literal** (`public const DEFAULT = 'images';` in `2024_04_26_201931_add_storate_disk_to_size_variants.php`), independently duplicated and not derived from either the enum or the filesystems config. All three values coincide today purely by chance, not by any code-level link. Should the new `tracks` migration column and `Track` model default follow the same fragile literal, reference the enum directly, or read the filesystems config?

**Blocking tasks:** T-055-02, T-055-03, T-055-18.

---

#### 🅰️ (**recommended**) Option A – Reference the enum directly

- **Idea:** Use `StorageDiskType::LOCAL->value` as both the migration column default and the `Track` model's default-on-create value, instead of a bare string literal.
- **Spec impact:** FR-055-01/FR-055-10 wording updated to cite the enum as the single source of truth; migration's `default()` call still needs a literal string at the schema level (Laravel migrations can't reference PHP enums in `$table->string(...)->default(...)`), but the `Track` model's create-time assignment reads `StorageDiskType::LOCAL->value` rather than duplicating `'images'`.
- **Pros:**
  - ✅ Single source of truth for "what disk is local" at the application layer — no risk of the model silently drifting from the enum.
  - ✅ Closes the exact kind of latent bug `SizeVariant`'s literal duplication already has, rather than reproducing it a second time.
- **Cons:**
  - ❌ Deviates stylistically from the `SizeVariant` precedent this feature is explicitly told to mirror — inconsistent with existing code even though arguably more correct.
  - ❌ The migration's column-level default still can't avoid a literal (schema defaults are static), so the fix is only partial (model-level, not DB-level).

---

#### 🅱️ Option B – Hardcode `'images'`, matching `SizeVariant` exactly

- **Idea:** Duplicate the exact pattern `size_variants` already uses — a literal `'images'` string at both the migration and model level, with no reference to the enum.
- **Spec impact:** No change from the spec's current wording; FR-055-01/FR-055-10 stay as-is.
- **Pros:**
  - ✅ Zero new pattern — perfectly consistent with the one precedent this feature is told to follow.
  - ✅ No ambiguity about "why is this different from `SizeVariant`."
- **Cons:**
  - ❌ Continues the fragile duplicated-literal risk this review surfaced — if `StorageDiskType::LOCAL`'s value is ever changed, both `size_variants` and `tracks` silently drift out of sync with the enum, with no compiler/test signal.

---

#### 🅲 Option C – Read from `config('filesystems.default')`

- **Idea:** Derive the default disk value from the Laravel filesystem config at write time, rather than a literal or the enum.
- **Spec impact:** FR-055-01/FR-055-10 rewritten to describe a config-driven default; adds a runtime dependency on `config('filesystems.default')` at track-creation time.
- **Pros:**
  - ✅ Reflects whatever disk is actually configured as default, even if it diverges from `'images'` in some future deployment.
- **Cons:**
  - ❌ Conflates two different concepts — "the disk representing local storage" (what `StorageDiskType::LOCAL` means) vs. "whatever `filesystems.default` currently is" — these could diverge without the enum ever changing.
  - ❌ More indirection than needed for a value that's supposed to specifically mean "local," not "whatever happens to be default."

---

**Next action**  
Decide before T-055-02/T-055-03. If 🅰️: update FR-055-01/FR-055-10 wording and use `StorageDiskType::LOCAL->value` in the `Track` model's create path. If 🅱️: no spec change needed, proceed as currently written.

---

### ~~Q-055-07~~ · Name convention for legacy-created primary tracks ✅ RESOLVED

**Status:** Resolved — Option A  
**Feature:** 055 – Multi-Track Albums  
**Resolved:** 2026-08-17

**Resolution:** Backfill, legacy create, legacy update, and v8 batch create all set `name` = filename with its extension stripped — one rule, no legacy-specific special case.

**Spec impact:** FR-055-04 reworded to state the create branch's `name` value explicitly and clarify the update branch strips the extension, matching FR-055-02/FR-055-06.

**Preferred option:** 🅰️ (**recommended**) Option A – One identical convention everywhere

**Question**  
FR-055-04 describes two sub-branches for legacy `POST /Album::track`: if a primary track already exists, its row is "updated in place... `name` reset to the new filename"; otherwise "a new primary track row is created" with no stated `name` value at all. Separately, FR-055-02's backfill sets `name` = "filename without extension," and FR-055-06's v8 batch upload sets `name` = "the uploaded filename with its extension stripped." Do all track-creation/rename-on-reupload paths use the identical extension-stripped convention, or does the legacy endpoint intentionally differ?

**Blocking tasks:** T-055-05, T-055-06.

---

#### 🅰️ (**recommended**) Option A – Identical extension-stripped convention everywhere

- **Idea:** Backfill, legacy create, legacy update, and v8 batch create all set `name` = filename with its extension stripped — one rule, no special case for the legacy endpoint.
- **Spec impact:** FR-055-04 reworded to explicitly state the create branch's `name` value and clarify the update branch means "extension stripped," matching FR-055-02/FR-055-06 verbatim.
- **Pros:**
  - ✅ One consistent, easily-testable rule across every code path that creates or renames-on-reupload a track.
  - ✅ No unexplained divergence for v8 users who later see a legacy-created primary track's name next to v8-created ones in the same list.
- **Cons:**
  - ❌ None of substance — this is the low-risk, low-cost option.

---

#### 🅱️ Option B – Legacy paths keep the extension; only v8 strips it

- **Idea:** Legacy-created/updated primary tracks get the full filename including `.gpx`; only v8's batch-upload path strips the extension.
- **Spec impact:** FR-055-04 explicitly diverges from FR-055-02/FR-055-06's convention.
- **Pros:**
  - ✅ Requires no further investigation into what, if anything, v7 currently does with the filename (though v7 never displays `name` today, so this benefit is moot).
- **Cons:**
  - ❌ Produces inconsistent-looking names between legacy- and v8-created tracks in v8's own track list, for no user-visible benefit today.
  - ❌ Adds a branch with no clear justification, purely because it wasn't explicitly ruled out.

---

#### 🅲 Option C – Leave `name` unset for legacy-created tracks

- **Idea:** Don't synthesize a name at all for legacy-created primary tracks; leave it blank until a user renames via v8.
- **Spec impact:** Would require changing FR-055-01's `name` column constraint from "required" to nullable, or defining an empty-string default.
- **Pros:**
  - ✅ Avoids guessing a convention nobody currently needs (v7 doesn't display `name`).
- **Cons:**
  - ❌ Conflicts with FR-055-01's existing "`name` (string, required)" constraint — not viable without a separate spec change, and strictly worse than Option A for no benefit.

---

**Next action**  
Decide before T-055-06 implementation. If 🅰️: update FR-055-04's wording and T-055-05's test assertions to match FR-055-02/FR-055-06 exactly.

---

### ~~Q-055-08~~ · `AlbumTracksController`'s cited precedent doesn't actually exist ✅ RESOLVED

**Status:** Resolved — Option A  
**Feature:** 055 – Multi-Track Albums  
**Resolved:** 2026-08-17

**Resolution:** The `AlbumTagsController` citation is dropped. `AlbumTracksController` is specified directly as its own controller with `store`/`update`/`destroy` methods (standard Laravel resource-controller naming), kept separate from `AlbumController` per the plan's original per-sub-resource design intent.

**Spec impact:** Plan I3 and T-055-11 updated with explicit method names/shape; no more reference to `AlbumTagsController` as precedent.

**Preferred option:** 🅰️ (**recommended**) Option A – Spell out the convention explicitly in the spec

**Question**  
The plan (I3) and T-055-11 both state the new `AlbumTracksController` "mirrors `AlbumTagsController`'s sub-resource-controller convention" with `store`/`update`/`destroy` methods. Verified against the actual code: `app/Http/Controllers/Gallery/AlbumTagsController.php` has exactly one method, `get()`, which computes a derived/aggregated list of tags found on an album's photos — read-only, no `store`/`update`/`destroy`, and it doesn't manage a persisted sub-resource at all. No existing controller in this codebase currently implements a `store`/`update`/`destroy` triad scoped to `album_id` + a child-row `id`. What should `AlbumTracksController` actually follow?

**Blocking tasks:** T-055-11.

---

#### 🅰️ (**recommended**) Option A – Spell out the convention explicitly; keep the separate controller

- **Idea:** Accept that no existing precedent matches, and have the spec state the new controller's shape directly (`store`/`update`/`destroy` methods, standard Laravel resource-controller naming) instead of deferring to a nonexistent reference. Keep it as its own `AlbumTracksController`, separate from `AlbumController`.
- **Spec impact:** FR-055-06/07/08 and I3/T-055-11 updated to state the method names/shape directly, with the `AlbumTagsController` citation removed.
- **Pros:**
  - ✅ Unblocks implementation immediately with no further research.
  - ✅ Matches standard Laravel resource-controller naming used broadly across the framework ecosystem, even though no sibling in this repo does it yet.
  - ✅ Preserves the plan's original design intent — one controller per sub-resource, keeping `AlbumController` from growing further.
- **Cons:**
  - ❌ Still technically a "new" pattern locally (no other controller in this app does 3-verb album sub-resource CRUD) — true of every option here, since no adequate precedent exists.

---

#### 🅱️ Option B – Search for a closer existing precedent first

- **Idea:** Before committing to a shape, look for any other sub-resource controller in the codebase (outside `AlbumTagsController`) that might be a better fit to mirror.
- **Spec impact:** Adds a research step before I3 can start.
- **Pros:**
  - ✅ If a better precedent exists, keeps stylistic consistency higher than inventing from scratch.
- **Cons:**
  - ❌ Unknown extra time with no guarantee of a better result — may still land on "invent new convention" after searching, at greater cost than just doing Option A now.

---

#### 🅲 Option C – Fold store/update/destroy into the existing `AlbumController`

- **Idea:** Add three new methods directly to `AlbumController` (which already owns `setTrack()`/`deleteTrack()`) instead of creating a separate `AlbumTracksController`.
- **Spec impact:** No new controller class; `AlbumController` grows by three methods.
- **Pros:**
  - ✅ Keeps all track-related logic in one file alongside the legacy methods it's most related to.
- **Cons:**
  - ❌ `AlbumController` already handles many concerns (visibility, sharing, etc.); the plan's explicit design goal was a separate controller per sub-resource to keep concerns isolated — this works directly against that.

---

**Next action**  
Decide before T-055-11. If 🅰️: update I3/T-055-11 wording with explicit method names and drop the `AlbumTagsController` reference.

---

### ~~Q-055-09~~ · No existing GPX fixture or Feature-level test coverage to extend ✅ RESOLVED

**Status:** Resolved — Option C  
**Feature:** 055 – Multi-Track Albums  
**Resolved:** 2026-08-17

**Resolution:** New Feature-level HTTP tests are still created from scratch for the legacy `Album::track` endpoints (`tests/Feature_v2/AlbumTrackControllerTest.php`, matching this repo's `Feature_v2` convention — the "nothing to extend" finding stands, so T-055-01/05/09 create rather than extend). The fixture itself, however, is a small repurposed non-GPX binary renamed to a `.gpx` extension (e.g. `tests/Fixtures/tracks/sample.gpx`), not authored GPX-formatted content — acceptable since the spec's Non-Goals explicitly exclude GPX content parsing/validation, matching today's actual validation level (extension/MIME only).

**Spec impact:** FX-055-01 resolved to `tests/Fixtures/tracks/sample.gpx` (repurposed binary, not real GPX content — noted in the fixture's own description so a future GPX-parsing feature doesn't mistake it for valid content); T-055-01/05/09 intent text changed from "extend" to "create."

**Preferred option:** 🅰️ (**recommended**) Option A – Create new Feature tests + fixture from scratch

**Question**  
T-055-01 and T-055-05 instruct extending/replacing "the existing `POST/DELETE Album::track` feature tests," and FX-055-01 in spec.md notes an existing GPX fixture "to be confirmed." Verified: no `.gpx` file exists anywhere in the repository, and the only track-related tests are `SetAlbumTrackRequestTest`/`DeleteTrackRequestTest` — `Unit`-level FormRequest tests against mocked `Gate` checks, no HTTP round-trip, no fixture file. The repo has no `tests/Feature` directory for this area at all (closest is `tests/Feature_v2`, which has nothing track-related). Since there's nothing to "extend," how should this be scoped?

**Blocking tasks:** T-055-01, T-055-05, T-055-09.

---

#### 🅰️ (**recommended**) Option A – Create new Feature/HTTP tests + a real `.gpx` fixture from scratch

- **Idea:** Explicitly scope T-055-01/05/09 as creating a new Feature-level HTTP test class (e.g. `tests/Feature_v2/AlbumTrackControllerTest.php`, matching the repo's `Feature_v2` convention) plus a minimal, genuinely-valid `.gpx` fixture (e.g. `tests/Fixtures/tracks/sample.gpx`).
- **Spec impact:** FX-055-01 resolved from "tbd" to a concrete path; tasks.md intent text changed from "extend" to "create."
- **Pros:**
  - ✅ Matches the actual repo state, unblocking I1/I2/I3's test-first steps immediately.
  - ✅ Establishes a reusable fixture other future track-related tests can build on.
  - ✅ Verifies the highest-risk part of this feature (legacy endpoint behavior preservation) at the HTTP level, closing a real coverage gap that predates this feature.
- **Cons:**
  - ❌ Slightly larger scope than the plan currently implies — but this work was always required, just mis-described as "extending."

---

#### 🅱️ Option B – Skip HTTP-level testing for legacy endpoints; rely on Unit tests only

- **Idea:** Leave the existing `Unit` FormRequest tests as the only coverage for legacy endpoints; only add Feature tests for the new v8 endpoints (I3).
- **Spec impact:** Test Strategy section trimmed; S-055-02/03 (legacy behavior-preservation scenarios) go unverified by automated tests.
- **Pros:**
  - ✅ Less new test-authoring work.
- **Cons:**
  - ❌ Directly contradicts the spec's own Test Strategy section, which requires feature tests for all 4 routes including legacy.
  - ❌ Leaves the riskiest refactor in the whole feature (preserving exact legacy behavior while changing the underlying schema) unverified end-to-end.

---

#### 🅲 Option C – Repurpose a non-GPX file with a `.gpx` extension as the fixture

- **Idea:** Rename an existing small binary test fixture to end in `.gpx` rather than authoring real GPX-formatted content, since Non-Goals state no GPX content parsing/validation is in scope.
- **Spec impact:** None formally — pure implementation shortcut.
- **Pros:**
  - ✅ Fast to produce.
- **Cons:**
  - ❌ Creates a fixture that looks real but isn't, misleading anyone who later adds real GPX parsing/validation and assumes the existing fixture is valid content.

---

**Next action**  
Decide before T-055-01. If 🅰️: create the fixture and Feature test scaffold as the first concrete step of I1, and update FX-055-01/T-055-01/05/09 wording accordingly.

---

### ~~Q-055-10~~ · `oldestOfMany()` is an unproven pattern in this codebase ✅ RESOLVED

**Status:** Resolved — Option C  
**Feature:** 055 – Multi-Track Albums  
**Resolved:** 2026-08-17

**Resolution:** `oldestOfMany` is dropped in favor of an explicit `tracks.is_primary` boolean column (`not null`, `default false`), transactionally maintained by application code:
- **Creation** (backfill, legacy create, v8 batch create — all paths, per Q-055-07): a newly created `Track` row is marked `is_primary = true` if and only if, at the moment of creation, the album currently has zero tracks. This preserves the original v7-compatibility guarantee — the first track added to an album via *either* v7 or v8 becomes primary, so `track_url` (FR-055-09) is populated regardless of which UI added it.
- **Legacy update** (FR-055-04, primary already exists): unchanged — the existing primary row's `is_primary` stays `true`.
- **Deletion** (FR-055-05/FR-055-08): deleting a track where `is_primary = true` now requires explicit bookkeeping (previously "none needed" under `oldestOfMany`) — in the same transaction as the delete, promote the next-oldest remaining track (`ORDER BY id ASC LIMIT 1`) to `is_primary = true`, if any remain. If none remain, the album simply has no primary track (`primaryTrack()` resolves to `null`).
- `Album::primaryTrack(): HasOne` becomes `hasOne(Track::class, 'album_id', 'id')->where('is_primary', true)` — a plain constrained relation, no `ofMany` subsystem involved.
- The invariant "at most one `is_primary = true` row per album" is enforced by the create/delete bookkeeping above (not a DB-level constraint, since a portable partial/conditional unique index isn't straightforward across this repo's supported DB engines) — covered by an explicit regression test asserting the invariant holds through S-055-04/S-055-08's delete-the-primary scenarios.

**Spec impact:** FR-055-01 gains the `is_primary` column; FR-055-03/DO-055-01/DO-055-03 relation definition changed from `oldestOfMany('id')` to `where('is_primary', true)`; FR-055-04 (legacy create) and FR-055-06 (v8 batch create) both specify the is-this-the-first-track check; FR-055-08 (v8 delete) rewritten to specify the promote-next-primary bookkeeping (replacing the "no extra bookkeeping needed" language, which no longer applies); FR-055-05 (legacy delete) gets the same promotion note.

**Preferred option:** 🅰️ (**recommended**) Option A – Keep `oldestOfMany`, expand test coverage

**Question**  
FR-055-03/T-055-04 rely on `hasOne(Track::class, ...)->oldestOfMany('id')`. `composer.json` confirms `laravel/framework: ^12.0` (well above the Laravel 8.42 minimum), but a repo-wide grep for `ofMany`/`oldestOfMany`/`latestOfMany` returns zero hits anywhere in `app/` — it is not a known-working pattern here today, despite T-055-04 framing it as merely "confirm availability." How should this be de-risked?

**Blocking tasks:** T-055-04.

---

#### 🅰️ (**recommended**) Option A – Keep `oldestOfMany`, with explicit expanded test coverage

- **Idea:** Keep the planned `oldestOfMany('id')` relation, but expand T-055-04's test list to explicitly cover: ordering after a mid-sequence delete (S-055-04), tie-breaking if two tracks share a `created_at`, and eager-loading (`with('primaryTrack')`) behavior — rather than treating it as a simple availability check.
- **Spec impact:** T-055-04's intent text expanded with these three explicit test cases.
- **Pros:**
  - ✅ Uses Laravel's idiomatic, purpose-built relation type for exactly this "canonical row among many" use case — no custom logic to maintain.
  - ✅ Self-documenting: a reader instantly knows "primary = oldest by id" from the relation name alone.
- **Cons:**
  - ❌ Still the first usage of this pattern in the codebase — no sibling implementation to sanity-check against, even with expanded tests.

---

#### 🅱️ Option B – Use the plan's stated fallback (`hasOne(...)->oldest('id')`) from the start

- **Idea:** Skip `ofMany` entirely; use a plain constrained `hasOne()->oldest('id')` relation, which produces a near-identical result via more universally-understood Eloquent machinery.
- **Spec impact:** DO-055-03/FR-055-03 wording changed to drop the `oldestOfMany` reference.
- **Pros:**
  - ✅ Simpler mental model — no dedicated "OfMany" subquery subsystem involved.
- **Cons:**
  - ❌ Less self-documenting than the purpose-built `ofMany` construct, and switching away from the more idiomatic option for an unconfirmed reason isn't clearly justified once availability is confirmed (which it is).

---

#### 🅲 Option C – Track "primary-ness" with an explicit `is_primary` boolean column

- **Idea:** Add a denormalized `tracks.is_primary` flag, maintained by application code on every create/delete, instead of relying on `MIN(id)`.
- **Spec impact:** New column; FR-055-01/03/08 rewritten to maintain `is_primary` transactionally.
- **Pros:**
  - ✅ Removes reliance on both `oldestOfMany` and implicit id-ordering — "primary" becomes an explicit, queryable fact.
- **Cons:**
  - ❌ Significant added complexity and a new failure mode (two rows accidentally both/never marked primary).
  - ❌ Directly undoes the design's stated simplicity win — FR-055-08 explicitly relies on "no extra bookkeeping needed given the `oldestOfMany` definition."

---

**Next action**  
No blocking decision required to start T-055-04 — Option A is the default path; escalate to 🅲 only if `oldestOfMany` testing surfaces a real correctness problem.

---

### ~~Q-055-11~~ · Delete-cleanup guidance cites the wrong class for the FK-disable/dependents-cleanup code ✅ RESOLVED

**Status:** Resolved — Option A  
**Feature:** 055 – Multi-Track Albums  
**Resolved:** 2026-08-17

**Resolution:** T-055-15's wording corrected: the new `DB::table('tracks')->whereIn('album_id', $chunk)->delete()` line is added inside `AlbumsToBeDeletedDTO::executeDelete()`'s existing chunked closure (alongside `album_size_statistics`); the per-disk `FileDeleterJob` dispatch change is made separately at `Actions/Album/Delete.php:96`'s call site. Task now names both files explicitly.

**Spec impact:** plan.md/tasks.md text only; no design change.

**Preferred option:** 🅰️ (**recommended**) Option A – Correct the file attribution in plan.md/tasks.md

**Question**  
Plan's Risks section correctly cites `Schema::disableForeignKeyConstraints()` as living in `AlbumsToBeDeletedDTO::executeDelete()`, but other FR-055-12/task wording refers to the dependents-cleanup block (where the new `tracks`-row deletion line belongs) as if it were in `Delete.php`. Verified: both `disableForeignKeyConstraints()` and the `live_metrics`/`access_permissions`/`statistics`/`album_size_statistics` chunked-delete block live in `app/DTO/Delete/AlbumsToBeDeletedDTO.php`, while `Delete.php:96` is only where the hardcoded `FileDeleterJob(StorageDiskType::LOCAL, ...)` dispatch happens — a different class entirely. How should this be corrected before I4?

**Blocking tasks:** T-055-14, T-055-15.

---

#### 🅰️ (**recommended**) Option A – Correct the plan/tasks wording to name both files explicitly

- **Idea:** Update T-055-15's intent text to state explicitly that the new `DB::table('tracks')->whereIn('album_id', $chunk)->delete()` line goes inside `AlbumsToBeDeletedDTO::executeDelete()`'s existing chunked closure, while the per-disk `FileDeleterJob` dispatch change is made separately at `Actions/Album/Delete.php:96`'s call site.
- **Spec impact:** Purely textual — no design change.
- **Pros:**
  - ✅ Prevents an implementer from landing both changes in the wrong file, which this review's confusion already demonstrates is an easy mistake to make.
  - ✅ Zero cost, zero risk.
- **Cons:**
  - ❌ None.

---

#### 🅱️ Option B – Leave the wording as-is; trust implementation-time code reading

- **Idea:** Don't correct the docs; rely on the implementer reading the actual code carefully before writing T-055-14/15.
- **Spec impact:** None.
- **Pros:**
  - ✅ Zero doc-editing effort now.
- **Cons:**
  - ❌ Defeats the purpose of a spec/task doc meant to guide implementation — this exact confusion already occurred once during this review.

---

#### 🅲 Option C – Restructure delete-cleanup code so all track logic lives in one place

- **Idea:** Move both the `FileDeleterJob` dispatch and the DB-row cleanup into a single method, even if that means relocating code between `Delete.php` and `AlbumsToBeDeletedDTO` beyond what this feature strictly needs.
- **Spec impact:** Broader refactor of existing, unrelated cleanup code (`live_metrics`/`access_permissions`/`statistics`).
- **Pros:**
  - ✅ Could make future track-cleanup-adjacent work easier to locate.
- **Cons:**
  - ❌ Unrelated scope creep — restructuring working code this feature doesn't need to touch adds regression risk for no required benefit.

---

**Next action**  
Apply Option A directly to plan.md/tasks.md before I4 starts; no decision-maker input needed, this is a factual correction.

---

### ~~Q-055-12~~ · Should the v8 `tracks` section be restricted to `is_model_album`, like `Move`? ✅ RESOLVED

**Status:** Resolved — Option A  
**Feature:** 055 – Multi-Track Albums  
**Resolved:** 2026-08-17

**Resolution:** The `tracks` section is gated exactly like `Move`: `albumStore.config?.is_model_album && albumStore.rights?.can_edit`. Hidden for smart/tag/person albums.

**Spec impact:** FR-055-13 wording gains the explicit condition.

**Preferred option:** 🅰️ (**recommended**) Option A – Gate like `Move` (`is_model_album && can_edit`)

**Question**  
FR-055-13 says the new `tracks` section is "gated the same way sibling sections are gated on album rights (`can_edit`...)". Verified against `AlbumEdit.vue`: the whole modal's `sections` computed returns `[]` unless `is_base_album`, and every individual section layers a further type-specific gate on top of a rights check — e.g. `Move` also requires `is_model_album`, `Share` also requires a user-count check. No existing section is gated on `can_edit` alone. Should `tracks` be restricted to model (regular photo) albums, or available on every base-album type?

**Blocking tasks:** T-055-21.

---

#### 🅰️ (**recommended**) Option A – Gate like `Move`: `is_model_album && can_edit`

- **Idea:** Hide the `tracks` section for smart/tag/person albums, showing it only for regular ("model") albums, mirroring `Move`'s exact condition.
- **Spec impact:** FR-055-13 wording gains the explicit condition; T-055-21 implements it directly.
- **Pros:**
  - ✅ GPS tracks conceptually belong to a real, physical trip/album of photos the same way Move/Transfer do — smart/tag/person albums are virtual views, not physical trips.
  - ✅ Reuses an existing, already-battle-tested gating condition rather than inventing a new one.
- **Cons:**
  - ❌ Forecloses a plausible future case (e.g. a person album for "Alice's hiking trip" with an associated track) without further work.

---

#### 🅱️ Option B – Gate on `can_edit` only (as spec currently literally states)

- **Idea:** No album-type restriction — any base album with edit rights shows the `tracks` section, including smart/tag/person albums.
- **Spec impact:** None — matches FR-055-13's current wording exactly.
- **Pros:**
  - ✅ Simplest gating logic, no special-casing.
  - ✅ Doesn't prematurely restrict a feature that (unlike Move) has no *structural* reason to be model-album-only — you genuinely cannot move a virtual smart album, but tracks don't have that constraint.
- **Cons:**
  - ❌ Risks a UI that "shows a Tracks section with an Add button" on an album type where it's unclear what a track conceptually attaches to (e.g. a smart album with a dynamically-changing photo set).

---

#### 🅲 Option C – New granular rule: hide only for smart albums, allow tag/person albums

- **Idea:** Split the difference — tag/person albums are still "real" albums with a fixed-ish photo set (unlike dynamically-queried smart albums), so allow tracks there but not on smart albums specifically.
- **Spec impact:** Introduces a new gating condition with no existing precedent in `AlbumEdit.vue` to mirror.
- **Pros:**
  - ✅ Conceptually threads the needle between "physical trip" (tag/person albums plausibly qualify) and "virtual view" (smart albums don't).
- **Cons:**
  - ❌ Bespoke rule with no precedent, adding implementation and testing surface for a distinction the spec never previously needed.

---

**Next action**  
Decide before T-055-21. If 🅰️: implement `is_model_album && can_edit` directly, matching `Move`'s condition verbatim.

---

### ~~Q-055-13~~ · Silent data loss on migration rollback once multi-track data exists ✅ RESOLVED

**Status:** Resolved — Option A  
**Feature:** 055 – Multi-Track Albums  
**Resolved:** 2026-08-17

**Resolution:** Risk accepted, matching this repo's general posture that migration `down()` is a development safety net, not a production data-preservation guarantee. No code guard added.

**Spec impact:** NFR-055-04 gains an explicit caveat stating `down()` is only intended/safe to run shortly after `up()`, before multi-track data accumulates, and that a later rollback silently discards non-primary tracks.

**Preferred option:** 🅰️ (**recommended**) Option A – Accept and document the risk

**Question**  
NFR-055-04 requires the `tracks` migration's `down()` to restore `albums.track_short_path` "from each album's primary track" and drop the `tracks` table — clean immediately after `up()` runs, but this feature's entire purpose is letting albums accumulate *multiple* tracks. Once that happens in production, a later rollback would silently discard every non-primary track's file+row with no warning, and this risk is never called out anywhere in the spec/plan/tasks today. Should this be accepted, guarded against, or mitigated?

**Blocking tasks:** T-055-02.

---

#### 🅰️ (**recommended**) Option A – Accept the risk; document it explicitly in NFR-055-04

- **Idea:** State plainly in NFR-055-04 that `down()` is only intended/safe to run shortly after `up()`, before multi-track data accumulates — matching this repo's general posture that migration rollbacks are a development safety net, not a production data-preservation guarantee.
- **Spec impact:** NFR-055-04 wording gains an explicit caveat; no code change.
- **Pros:**
  - ✅ Zero implementation cost.
  - ✅ Matches how every other migration in this codebase already behaves — verified, no existing migration defensively guards `down()` against post-adoption data loss.
- **Cons:**
  - ❌ An operator running `migrate:rollback` in a genuine emergency months later still loses secondary track data with no in-the-moment warning — only this doc says so.

---

#### 🅱️ Option B – Guard `down()`; abort if any album has more than one track

- **Idea:** Have `down()` check for albums with `>1` `tracks` row and abort/throw rather than proceeding destructively, forcing an operator to handle the extra data first.
- **Spec impact:** NFR-055-04 rewritten with the guard's exact behavior; new migration-test scenario for "rollback refused when multi-track data exists."
- **Pros:**
  - ✅ Makes the risk impossible to hit silently — forces a conscious decision at rollback time.
- **Cons:**
  - ❌ Adds real implementation/test complexity to a `down()` for a rare scenario; no other migration in the codebase does this, making it a one-off pattern.

---

#### 🅲 Option C – Guard with a warn-and-dump fallback

- **Idea:** `down()` doesn't abort but logs a warning and writes a serialized dump of all non-primary tracks to a fallback location before dropping them.
- **Spec impact:** NFR-055-04 rewritten with dump-and-warn behavior; new dependency on a dump format/location decision.
- **Pros:**
  - ✅ Rollback still proceeds unattended while leaving a recovery trail.
- **Cons:**
  - ❌ Most complex of the three, with no precedent anywhere in the codebase, and a dump nobody is likely to look for or restore from isn't meaningfully better than Option A's documented risk.

---

**Next action**  
Decide before T-055-02. If 🅰️: add the caveat sentence to NFR-055-04 and proceed with `down()` as currently planned.
