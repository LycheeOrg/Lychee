# Open Questions – Feature 045

Open questions for [Feature 045](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-045-13~~ | 045 – NSFW Moderation | High | Photo-Album is many-to-many — spec assumes `album_id` FK on photos but photos use a `photo_album` pivot table. "Direct parent album" is ambiguous when a photo is in multiple albums. | Resolved (A – mark all associated albums) | 2026-06-22 | 2026-06-22 |
| ~~Q-045-14~~ | 045 – NSFW Moderation | High | Block action hard-delete mechanism — spec says "permanently removed" but doesn't specify whether to reuse the existing `Delete` action (handles pivot cleanup, purchasable cleanup, events) or use a simpler raw delete. | Resolved (A – reuse Delete with dedicated force-delete method) | 2026-06-22 | 2026-06-22 |
| ~~Q-045-15~~ | 045 – NSFW Moderation | Medium | CSRF exemption for `POST /api/v2/NsfwDetection/results` callback — face detection callback is listed in `VerifyCsrfToken::$except` but the spec/plan don't mention adding the NSFW callback. | Resolved (not an issue — follows face detection pattern, add to CSRF exclusions) | 2026-06-22 | 2026-06-22 |
| ~~Q-045-16~~ | 045 – NSFW Moderation | High | `ModerationController::approve()` is a bulk update — adding per-photo sensitive-detection checks and album-marking job dispatch requires loading detections per photo and iterating, breaking the current chunked `whereIn` pattern. | Resolved (B – hybrid: bulk update then post-process NSFW subset) | 2026-06-22 | 2026-06-22 |
| ~~Q-045-17~~ | 045 – NSFW Moderation | Medium | `NsfwBlockFindingAction` enum inconsistency — DO-045-03 and the matrix list `APPROVE` as a valid value, but the spec DSL `values` array only lists `[BLOCK, MODERATE]`. The enum needs `APPROVE` to support `nsfw_trust_block_action`. | Resolved (fix spec DSL to include APPROVE) | 2026-06-22 | 2026-06-22 |
| ~~Q-045-18~~ | 045 – NSFW Moderation | Medium | Pipe execution ordering — `AutoScanFacesOnUpload` calls `$next()` first (photo is persisted), then dispatches the scan job. Spec doesn't clarify whether `AutoScanNsfwOnUpload` should follow the same pattern or run before `$next()`. Hide-on-scan `is_validated = false` must be set before persistence. | Resolved (B – set values before `$next()`, dispatch after) | 2026-06-22 | 2026-06-22 |
| ~~Q-045-19~~ | 045 – NSFW Moderation | Medium | `nsfw_detections` FK cascade policy not specified — the `faces` table uses `cascadeOnDelete` on `photo_id`. Should `nsfw_detections` follow the same pattern? Spec says "cascade-deleted with the photo" but only in prose, not in migration requirements. | Resolved (update PhotosToBeDeletedDTO + cascadeOnDelete FK) | 2026-06-22 | 2026-06-22 |
| ~~Q-045-01~~ | 045 – NSFW Moderation | High | Callback stores `owner_id` on detection — how does the controller resolve the uploading user from the callback payload? | Resolved (B – snapshot trust level on photo) | 2026-06-21 | 2026-06-21 |
| ~~Q-045-02~~ | 045 – NSFW Moderation | High | "Block" action semantics — should blocked photos be soft-deleted, hidden via `is_validated`, or use a dedicated visibility flag? | Resolved (Custom – single `nsfw_status` enum + `is_validated`; no `blocked` value, block = hard-delete) | 2026-06-21 | 2026-06-22 |
| ~~Q-045-03~~ | 045 – NSFW Moderation | High | Sensitive action on unsorted photos (no album) — should it create a "Sensitive" album, mark as moderation instead, or skip? | Resolved (Custom – configurable: skip or fall back to moderate) | 2026-06-21 | 2026-06-21 |
| ~~Q-045-04~~ | 045 – NSFW Moderation | High | User context in upload pipe — `StandaloneDTO` may not carry the uploading user's trust level; how to resolve it? | Resolved (subsumed by Q-045-01 → B; pipe reads `$state->upload_trust_level` from DTO — uploader's level, not owner's) | 2026-06-21 | 2026-06-22 |
| ~~Q-045-05~~ | 045 – NSFW Moderation | Medium | Should the NSFW service share the same base URL as the face detection service, or use a completely separate URL? | Resolved (A – separate URL + API key) | 2026-06-21 | 2026-06-21 |
| ~~Q-045-06~~ | 045 – NSFW Moderation | Medium | Detection log granularity — store every individual detection, or only summary flags on the photo? | Resolved (A modified – block/review/sensitive only, not all_detected) | 2026-06-21 | 2026-06-21 |
| ~~Q-045-07~~ | 045 – NSFW Moderation | Medium | Per-album or per-user preset override — should this be supported in v1, or deferred? | Resolved (A – deferred) | 2026-06-21 | 2026-06-21 |
| ~~Q-045-08~~ | 045 – NSFW Moderation | Medium | Multiple albums — if a photo belongs to multiple albums and `sensitive` action fires, which albums get `is_nsfw = true`? | Resolved (A – direct album only) | 2026-06-21 | 2026-06-21 |
| ~~Q-045-09~~ | 045 – NSFW Moderation | Medium | Bulk scan scope — should bulk scan re-scan photos that previously completed, or only `nsfw_status IS NULL`? | Resolved (B – NULL+failed default, force for all) | 2026-06-21 | 2026-06-22 |
| ~~Q-045-10~~ | 045 – NSFW Moderation | Medium | Moderation page integration — should NSFW-blocked photos appear in the existing Moderation view, or require a separate filter/tab? | Resolved (B – add NSFW badge in Moderation view) | 2026-06-21 | 2026-06-21 |
| ~~Q-045-11~~ | 045 – NSFW Moderation | Low | Config category naming — should NSFW config keys use existing `mod-nsfw` category or a new one? | Resolved (Custom – use `ai` category) | 2026-06-21 | 2026-06-21 |
| ~~Q-045-12~~ | 045 – NSFW Moderation | Medium | SE (Supporter Edition) gating — should NSFW detection endpoints require SE license? | Resolved (Custom – simple SE, not pro) | 2026-06-21 | 2026-06-21 |

## Question Details

### ~~Q-045-13~~ · Photo-Album many-to-many breaks "direct parent album" assumption in sensitive action ✅ RESOLVED

**Status:** Resolved — **Option A** (mark all associated albums)  
**Feature:** 045 – NSFW Detection & Moderation  
**Priority:** High  
**Opened:** 2026-06-22  
**Resolved:** 2026-06-22

**Resolution:** `ApplyNsfwAlbumSensitivityJob` iterates all of the photo's associated albums (`$photo->albums`) and, for each album, checks `is_recursive_nsfw` before marking. The "no album" fallback fires when `$photo->albums->isEmpty()`. The spec language "direct parent album" and `album_id` FK references must be updated to reflect the many-to-many model. Q-045-08 resolution is superseded — all associated albums are now marked, not just one.

**Spec impact:** FR-045-06, FR-045-17, Appendix "Album marking rules" updated: replace "direct parent album" / `album_id` with "all associated albums" / `$photo->albums`. FR-045-15 "unsorted" condition updated from `album_id IS NULL` to `$photo->albums->isEmpty()`.

---

### ~~Q-045-14~~ · Block action hard-delete mechanism ✅ RESOLVED

**Status:** Resolved — **Option A** (reuse Delete with dedicated force-delete method)  
**Feature:** 045 – NSFW Detection & Moderation  
**Priority:** High  
**Opened:** 2026-06-22  
**Resolved:** 2026-06-22

**Resolution:** Add a `forceDeletePhoto(string $photo_id)` method to `Actions\Photo\Delete` (or a new dedicated action) that: (1) removes all `photo_album` pivot entries, (2) cleans up purchasables, (3) fires `PhotoWillBeDeleted`/`PhotoDeleted` events, (4) delegates to `PhotosToBeDeletedDTO` for size variant file cleanup and record deletion — bypassing the album-context requirement of `Delete::do()`. This reuses the existing file cleanup, event dispatch, and purchasable cleanup infrastructure. Block-delete removes the photo from **all** albums unconditionally.

**Spec impact:** FR-045-04 updated to note block-delete removes photo from all albums. I2/I3 plan steps reference the new method.

---

### ~~Q-045-15~~ · CSRF exemption for NSFW callback endpoint ✅ RESOLVED

**Status:** Resolved — not an issue (follows face detection pattern)  
**Feature:** 045 – NSFW Detection & Moderation  
**Priority:** Medium  
**Opened:** 2026-06-22  
**Resolved:** 2026-06-22

**Resolution:** Add `'/api/v2/NsfwDetection/results'` to `VerifyCsrfToken::$except`, matching the existing face detection callback pattern. This is a standard implementation detail — not an architectural question — and should be captured as a step in I5 tasks.

---

### ~~Q-045-16~~ · `ModerationController::approve()` needs per-photo logic for NSFW album marking ✅ RESOLVED

**Status:** Resolved — **Option B** (hybrid: bulk update then post-process NSFW subset)  
**Feature:** 045 – NSFW Detection & Moderation  
**Priority:** High  
**Opened:** 2026-06-22  
**Resolved:** 2026-06-22

**Resolution:** Keep the existing bulk `Photo::whereIn('id', $chunk)->update(['is_validated' => true])` pattern for the common case. After the bulk update, separately query for photos among the approved IDs that had `nsfw_status = review`: update their `nsfw_status` to `visible`, and for those with sensitive detections (`is_sensitive = true`) where `nsfw_sensitive_album_action = mark_album`, dispatch `ApplyNsfwAlbumSensitivityJob`. This two-pass approach preserves performance for non-NSFW approvals while correctly handling the NSFW subset.

**Spec impact:** I5 step 5 updated to describe the two-pass pattern.

---

### ~~Q-045-17~~ · `NsfwBlockFindingAction` enum — `APPROVE` case ✅ RESOLVED

**Status:** Resolved — no action needed (spec is already consistent)  
**Feature:** 045 – NSFW Detection & Moderation  
**Priority:** Medium  
**Opened:** 2026-06-22  
**Resolved:** 2026-06-22

**Resolution:** On re-inspection, the spec DSL already lists `values: [BLOCK, MODERATE, APPROVE]` for `NsfwBlockFindingAction` (line 415), matching DO-045-03 and the config key definition. No fix needed — the initial analysis was incorrect.

---

### ~~Q-045-18~~ · `AutoScanNsfwOnUpload` pipe execution ordering vs hide-on-scan ✅ RESOLVED

**Status:** Resolved — **Option B** (set values before `$next()`, dispatch after)  
**Feature:** 045 – NSFW Detection & Moderation  
**Priority:** Medium  
**Opened:** 2026-06-22  
**Resolved:** 2026-06-22

**Resolution:** The pipe sets `$state->photo->upload_trust_level` and conditionally `$state->photo->is_validated = false` (for hide-on-scan) on the in-memory model **before** calling `$next()`. The pipeline persists the photo with these values already set — no visibility gap and no extra `save()` call. After `$next()` returns (photo persisted with ID), dispatch the scan job. This diverges from the `AutoScanFacesOnUpload` pattern (which calls `$next()` first) but is correct: face detection doesn't need to modify model attributes before persistence.

**Spec impact:** I4 plan step 1 updated to specify the before-`$next()` pattern for attribute setting.

---

### ~~Q-045-19~~ · `nsfw_detections` FK cascade policy ✅ RESOLVED

**Status:** Resolved — update `PhotosToBeDeletedDTO` + `cascadeOnDelete` FK  
**Feature:** 045 – NSFW Detection & Moderation  
**Priority:** Medium  
**Opened:** 2026-06-22  
**Resolved:** 2026-06-22

**Resolution:** Two-pronged approach matching how existing related tables are handled: (1) The migration uses `$table->foreign('photo_id')->references('id')->on('photos')->cascadeOnDelete()` on `nsfw_detections`, matching the `faces` table pattern. (2) `PhotosToBeDeletedDTO::forceDelete()` is updated to explicitly `DB::table('nsfw_detections')->whereIn('photo_id', $chunk->all())->delete()` alongside the existing `size_variants`, `statistics`, `palettes`, and `photo_album` cleanup — matching how that method handles related data rather than relying solely on DB cascades.

**Spec impact:** I1 migration task notes updated to specify `cascadeOnDelete`. I2/I3 notes to include `PhotosToBeDeletedDTO` update.

---

### ~~Q-045-01~~ · How does the callback controller resolve the uploading user from the callback payload? ✅ RESOLVED

**Status:** Resolved — **Option B** (snapshot trust level on photo at upload time)  
**Feature:** 045 – NSFW Detection & Moderation  
**Resolution:** New `upload_trust_level` column on `photos` table, populated by the `AutoScanNsfwOnUpload` pipe at upload time from `$state->upload_trust_level` (the **uploader's** trust level from the pipeline DTO, not the photo owner's). This distinction matters because the uploader and owner may differ — when User A uploads to User B's album, `photo.owner_id` = B but the trust level should be A's. Callback reads the snapshotted value from the photo instead of querying the user's current trust level. Encoded in FR-045-07, FR-045-08, FR-045-14, T-045-05, T-045-13.

**Question**  
The NSFW callback endpoint receives `photo_id` but no user context. To apply trust-level-aware actions (FR-045-08: auto-approve moderation for trusted users), the controller needs to know who uploaded the photo. The `photos` table has an `owner_id` column (FK to `users.id`), but `owner_id` reflects the **album owner** (via `SetOwnership` pipe), not necessarily the uploader. Should the controller query `Photo::owner_id` to resolve the user's trust level, or should the uploading user's trust level be stored on the photo at upload time?

---

#### 🅰️ Option A – Look up `owner_id` at callback time _(rejected)_

- **Idea:** When processing the callback, load `Photo` with `owner` relation, read `$photo->owner->upload_trust_level`. This is a single extra JOIN.
- **Spec impact:** No new columns. T-045-15 controller logic includes `$photo->load('owner')`.
- **Pros:**
  - ✅ No schema changes — `owner_id` already exists on `photos`.
  - ✅ Always reflects the user's current trust level.
  - ✅ Simplest implementation.
- **Cons:**
  - ❌ **Critical flaw:** `owner_id` is the album owner, not the uploader. When User A uploads to User B's album, `owner_id = B` — this would apply B's trust level instead of A's.
  - ❌ If trust level changed between upload and scan, the "current" level may not match intent at upload time.

---

#### 🅱️ Option B – Snapshot trust level on photo at upload time

- **Idea:** Add `upload_trust_level` column to `photos` table, populated by the upload pipe. Callback reads this snapshot.
- **Spec impact:** New column, new migration, pipe update.
- **Pros:**
  - ✅ Captures the trust level that was in effect when the upload occurred.
- **Cons:**
  - ❌ Extra column and migration.
  - ❌ Stale data — if admin upgrades a user to trusted, already-pending scans still use the old level.

---

**Next action:** Resolved → Option B. Option A is flawed because `owner_id` is the album owner, not the uploader. The DTO already carries `$state->upload_trust_level` (the authenticated uploader's trust level), so no extra query is needed.

---

### ~~Q-045-02~~ · "Block" action semantics — what happens to blocked photos? ✅ RESOLVED

**Status:** Resolved — **Custom** (single `nsfw_status` enum + `is_validated` combination)  
**Feature:** 045 – NSFW Detection & Moderation  
**Resolution:** Single `nsfw_status` enum column on `photos` (nullable string; values: `pending`, `failed`, `review`, `visible`). Merges both scan tracking and visibility into one column. Used in combination with `is_validated`:
- `null` = not yet scanned; `pending` = scan dispatched; `failed` = scan errored; `review` = held for moderation (`is_validated = false`); `visible` = scan completed with no action or admin-approved.
- When `nsfw_status` is set to `review`, `is_validated` is also set to `false`.
- Admin approval sets `nsfw_status = visible` and `is_validated = true`.
- No `blocked` value — block actions hard-delete the photo (row, files, thumbnails permanently removed).
This replaces both the originally proposed `nsfw_blocked` boolean and the separate `nsfw_scan_status` / `nsfw_visibility` columns. Encoded in FR-045-13, DO-045-06, T-045-05, T-045-07.

---

### ~~Q-045-03~~ · Sensitive action on unsorted photos (no album) ✅ RESOLVED

**Status:** Resolved — **Custom** (configurable: skip or fall back to moderate)  
**Feature:** 045 – NSFW Detection & Moderation  
**Resolution:** New config key `nsfw_sensitive_no_album_action` (string, category `AI Vision`) with two values:
- `skip` — log warning ("Cannot mark album as sensitive: photo {id} has no album"), do not apply the sensitive action. Other actions (block, moderation) still apply if their conditions are met.
- `moderate` — fall back to setting `nsfw_status = review` and `is_validated = false`. Ensures the photo is held for admin review even without an album to mark.

Default value: `skip`. This gives the admin explicit control over the trade-off between silent inaction and implicit escalation. Encoded in FR-045-06, FR-045-15 (new), T-045-07 (config migration gains 8th key), T-045-10, S-045-18, S-045-23 (new).

---

### ~~Q-045-04~~ · User context in the upload pipe — how to resolve the uploader's trust level? ✅ RESOLVED

**Status:** Resolved — subsumed by Q-045-01 → Option B  
**Feature:** 045 – NSFW Detection & Moderation  
**Resolution:** Since Q-045-01 resolved to snapshot `upload_trust_level` on the photo at upload time, the pipe reads `$state->upload_trust_level` from the pipeline DTO — this is the **uploader's** trust level, pre-resolved at dispatch time from the authenticated user. It does NOT use `$state->photo->owner` (which is the album owner, not the uploader). No extra query needed — the DTO already carries the uploader's trust level. Encoded in T-045-15.

---

### ~~Q-045-05~~ · NSFW service URL — same host as face detection or separate? ✅ RESOLVED

**Status:** Resolved — **Option A** (separate URL + API key)  
**Feature:** 045 – NSFW Detection & Moderation  
**Resolution:** Two new env vars: `AI_VISION_NSFW_URL` and `AI_VISION_NSFW_API_KEY`, independent of face detection. Operators can run each service on different hosts/ports. Encoded in NFR-045-02, T-045-08.

---

### ~~Q-045-06~~ · Detection log granularity — every detection or summary only? ✅ RESOLVED

**Status:** Resolved — **Option A (modified)** — store individual detections from `block_detected`, `review_detected`, `sensitive_detected` only; `all_detected` NOT persisted  
**Feature:** 045 – NSFW Detection & Moderation  
**Resolution:** Each entry in `block_detected`, `review_detected`, `sensitive_detected` creates a row in `nsfw_detections` with label, confidence, bbox, area, and tier. Items from `all_detected` that do not appear in any action array are not stored — they represent detections below all configured thresholds and are not actionable. The `NsfwDetectionTier` enum drops the `DETECTED` case (3 values: `BLOCK`, `REVIEW`, `SENSITIVE`). Encoded in FR-045-09, DO-045-07, T-045-03, T-045-06, T-045-10.

---

### ~~Q-045-07~~ · Per-album or per-user preset override — v1 or deferred? ✅ RESOLVED

**Status:** Resolved — **Option A** (deferred to follow-up)  
**Feature:** 045 – NSFW Detection & Moderation  
**Resolution:** Global preset only in v1. Per-album/user overrides tracked in plan.md Follow-ups/Backlog. Already reflected in spec Non-Goals.

---

### ~~Q-045-08~~ · Multiple albums — which get marked sensitive? ✅ RESOLVED

**Status:** Resolved — **Option A** (direct album only)  
**Feature:** 045 – NSFW Detection & Moderation  
**Resolution:** Only the album referenced by `photo.album_id` is marked `is_nsfw = true`. Parent/ancestor albums are unaffected. Already reflected in FR-045-06 ("direct album").

---

### ~~Q-045-09~~ · Bulk scan scope — re-scan completed photos? ✅ RESOLVED

**Status:** Resolved — **Option B** (NULL + failed by default; `force` for completed)  
**Feature:** 045 – NSFW Detection & Moderation  
**Resolution:** Default bulk scan targets `nsfw_scan_status IS NULL` or `failed`. Optional `force` boolean parameter re-scans `completed` photos as well. Enables preset changes to be applied retroactively. Encoded in FR-045-11, T-045-16, S-045-17, S-045-22.

---

### ~~Q-045-10~~ · Moderation page integration — existing view or separate? ✅ RESOLVED

**Status:** Resolved — **Option B** (add NSFW badge/tag in Moderation view)  
**Feature:** 045 – NSFW Detection & Moderation  
**Resolution:** NSFW-blocked/review photos appear in the existing Moderation view (via `is_validated = false`) with a visible NSFW badge/tag showing the `nsfw_visibility` state (`blocked` or `review`). This lets admins immediately distinguish NSFW holds from manual moderation. Both `nsfw_visibility` and `is_validated` are exposed in the Moderation API response. Encoded in FR-045-13, new T-045-22.

---

### ~~Q-045-11~~ · Config category naming ✅ RESOLVED

**Status:** Resolved — **Custom** (use `ai` category)  
**Feature:** 045 – NSFW Detection & Moderation  
**Resolution:** All new NSFW detection config keys use category `AI Vision`, grouping them with other AI-powered features. The existing NSFW display/visibility keys (e.g., `hide_nsfw_in_smart_albums`) remain under `mod-nsfw`. Encoded in T-045-07, T-045-19.

---

### ~~Q-045-12~~ · SE (Supporter Edition) gating for NSFW detection ✅ RESOLVED

**Status:** Resolved — **Custom** (simple SE gating, not pro tier)  
**Feature:** 045 – NSFW Detection & Moderation  
**Resolution:** All NSFW endpoints use the `support` middleware (basic SE), not `support:pro`. This makes NSFW detection available to all SE supporters, not just pro-tier. Encoded in NFR-045-05, T-045-17.
