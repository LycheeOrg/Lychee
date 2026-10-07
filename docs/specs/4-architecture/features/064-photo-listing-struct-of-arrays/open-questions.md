# Open Questions – Feature 064

Open questions for [Feature 064](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-064-01~~ | 064 – Photo Listing Struct-of-Arrays | High | `details` tier scoping mechanism for large albums — required `bucket_id` only vs. dual-mode `bucket_id`/`photo_ids[]` vs. plain pagination | Resolved (Option A — dual-mode confirmed; see Q-064-04 for the final, asymmetric cap design) | 2026-09-05 | 2026-09-05 |
| ~~Q-064-02~~ | 064 – Photo Listing Struct-of-Arrays | Medium | Should per-photo permission signals be folded into the `details`/`ratios` tiers (`owner_id` only) or served by a dedicated fourth `/rights` endpoint, mirroring Feature 061's separate album rights tier? | Resolved (Option A — fold into `ratios`/`details` as `owner_id` only, no dedicated endpoint; user confirmed 2026-09-05, "Q64-2: A") | 2026-09-05 | 2026-09-05 |
| ~~Q-064-03~~ | 064 – Photo Listing Struct-of-Arrays | Medium | Does `ratios`' "group by `bucket_id`, reproduce `buckets`' counts" correlation contract hold when `bucketable: false` (`OWNER_ID`-sorted albums)? | Resolved (Option A — the correlation guarantee is explicitly scoped to `bucketable: true` only; not about bucketing by ratio at all, just the one excluded `OWNER_ID` sort column's edge-case behavior) | 2026-09-05 | 2026-09-05 |
| ~~Q-064-04~~ | 064 – Photo Listing Struct-of-Arrays | High | User follow-up: "what happens if we ask for more than 500 photos id? How do we ask for the next 500?" | Resolved (asymmetric: `bucket_id` mode uncapped, no truncation, returns everything; `photo_ids[]` mode capped at 300 as input, 422 above — user: "in bucket mode there is no truncating we return everything. in photoId mode we limit to 300 as input") | 2026-09-05 | 2026-09-05 |
| ~~Q-064-05~~ | 064 – Photo Listing Struct-of-Arrays | Low | Should `ratios` keep an always-present `titles[]` field separate from the conditional `thumb_infos` (title-or-description)? | Resolved (Option B — `titles[]` always present; user: "make title always present") | 2026-09-05 | 2026-09-05 |
| ~~Q-064-06~~ | 064 – Photo Listing Struct-of-Arrays | High | How far should `details`' parity with v2's full `PhotoResource` go? | Resolved (Option B, broader — user: "the details endpoint combined with the ratio endpoint should allow to reconstruct the PhotoResource object, meaning, statistics, palette, ratings etc should be present too") | 2026-09-05 | 2026-09-05 |
| ~~Q-064-07~~ | 064 – Photo Listing Struct-of-Arrays | Medium | Should `ratios[i]`'s SQL `COALESCE` reproduce `Photo::getAspectRatioAttribute()`'s video special-case (force `1` when a video has no small/medium, even if an `ORIGINAL` row with a real ratio exists), or always surface the real ratio when available? | Resolved (deliberate divergence — always use the real ratio; user: "we want to use the video aspect ratio also here and not just default to 1") | 2026-09-05 | 2026-09-05 |

## Question Details

### ~~Q-064-01~~ · `details` tier scoping mechanism for large albums ✅ RESOLVED

**Status:** Resolved — **Option A**  
**Feature:** 064 – Photo Listing Struct-of-Arrays  
**Resolved:** 2026-09-05

**Resolution:** Dual-mode confirmed — the client supplies exactly one of `bucket_id` or `photo_ids[]`. See ~~Q-064-04~~ immediately below for the final, asymmetric cap design (`bucket_id` uncapped; `photo_ids[]` capped at 300 as input) that resulted from the user's own follow-up question about fetching more than one batch. Encoded in FR-064-09, DO-064-04, NFR-064-04.

---

### ~~Q-064-02~~ · Fold per-photo rights into `details`/`ratios`, or a dedicated fourth `/rights` endpoint? ✅ RESOLVED

**Status:** Resolved — **Option A**  
**Feature:** 064 – Photo Listing Struct-of-Arrays  
**Resolved:** 2026-09-05

**Resolution:** User confirmed Option A directly ("Q64-2: A"). `owner_id` (present in both `ratios` and `details`) is the only rights-adjacent field this feature exposes — no fourth dedicated `/rights` endpoint. Every other photo-menu action is already sourced client-side from the containing album's own whole-album rights object; only ownership-gated actions (Feature 059's owner-only rating rule) genuinely vary photo-by-photo, and `owner_id` alone is sufficient for the client to combine with its own already-known identity. Encoded in FR-064-11, NG9.

---

### ~~Q-064-03~~ · Does the `ratios`↔`buckets` bucket-correlation contract hold when `bucketable: false`? ✅ RESOLVED

**Status:** Resolved — **Option A**  
**Feature:** 064 – Photo Listing Struct-of-Arrays  
**Resolved:** 2026-09-05

**Clarification (this question is not about bucketing by the `ratio` field):** `ratios` is just this tier's *name* (its main payload is aspect ratio, needed for grid layout) — nothing buckets by ratio, ever. This question is about `bucket_id`, the field every tier carries reflecting whichever dimension the album is actually sorted/bucketed by (date or title, per user direction — `OWNER_ID` is the one excluded column, NG11). The question was: for that one excluded case, `ratios` still labels every row's `bucket_id` as `"unknown"` while `buckets` reports zero buckets at all — do the two responses need to agree?

**Resolution:** Option A — no code change. The byte-for-byte "group `ratios` by `bucket_id`, reproduce `buckets`' counts" correlation (FR-064-07/S-064-09) is explicitly scoped to apply only when `bucketable: true`. For the one `OWNER_ID`-sorted, `bucketable: false` case, a client must check `bucketable` first — which it already has to do before deciding whether to render sticky bucket headers at all, so this adds no new client-side logic. Encoded in FR-064-07, S-064-09.

---

### ~~Q-064-04~~ · `details` overflow handling for large requests ✅ RESOLVED

**Status:** Resolved — **Custom** (asymmetric caps, neither of the originally-drafted options)  
**Feature:** 064 – Photo Listing Struct-of-Arrays  
**Resolved:** 2026-09-05

**Resolution:** User confirmed directly, deliberately asymmetric: *"in bucket mode there is no truncating we return everything. in photoId mode we limit to 300 as input."* `bucket_id` mode is **uncapped** — it always resolves and returns every photo in the requested bucket, however large, with no 422 and no truncation (the recommended "cap both modes, 422 on overflow" option was **not** chosen for this mode). `photo_ids[]` mode **is** capped, at **300** entries (not the originally-drafted 500), validated as input at the request layer (422 above). A client wanting more than 300 arbitrary/non-contiguous photos' details self-chunks via repeated `photo_ids[]` requests, sliced from the id list it already holds from `ratios`; a client using `bucket_id` mode has no equivalent limit to work around, since bucket sizes are expected to stay reasonable in practice and are already knowable in advance from `buckets`' own `counts[]`. Encoded in FR-064-09, NFR-064-04, DO-064-04, S-064-16, S-064-27.

---

### ~~Q-064-05~~ · Should `ratios` keep an always-present `titles[]`, separate from the conditional `thumb_infos`? ✅ RESOLVED

**Status:** Resolved — **Option B**  
**Feature:** 064 – Photo Listing Struct-of-Arrays  
**Resolved:** 2026-09-05

**Resolution:** User confirmed directly ("make title always present"). `ratios` now always includes `titles: string[]` (guest-blanked per the existing `file_name_hidden` rule). `thumb_infos` narrows to **description-mode only** — populated (Markdown-rendered) only when `photo_thumb_info = description` and the overlay is on; when `photo_thumb_info = title`, the client renders the always-present `titles[]` directly, no separate overlay field needed. Encoded in FR-064-07/FR-064-18, DO-064-02.

---

### ~~Q-064-06~~ · How far should `details`' "informations for opening a photo" parity go? ✅ RESOLVED

**Status:** Resolved — **Option B** (broader than either originally-drafted option)  
**Feature:** 064 – Photo Listing Struct-of-Arrays  
**Resolved:** 2026-09-05

**Resolution:** User confirmed directly, going beyond Option B's original framing: *"the details endpoint combined with the ratio endpoint should allow to reconstruct the PhotoResource object, meaning, statistics, palette, ratings etc should be present too."* This settles all three open sub-questions at once:
1. **Scope** — `checksum`/`original_checksum`, `palette`, `statistics`, `updated_at`, live-photo fields, and `face_count` all move into `details` (FR-064-10/20/21). Only `next_photo_id`/`previous_photo_id` stay excluded — not by choice, but because v2's own `PhotoResource` always nulls both in this exact album-listing context too, so there is nothing real to reconstruct.
2. **Size-variant subset (FR-064-19)** — all 9, full `SizeVariantsResouce` parity, not a narrowed lightbox-relevant subset — required by "reconstruct the PhotoResource object" taken literally.
3. **Field shape** — nested objects (`size_variants`, `palette`, `statistics`, each `(ResourceClass|null)[]`, index-aligned to `ids`), reusing the existing typed resource classes directly rather than flattening into dozens of parallel SoA arrays — a deliberate, scoped exception to this feature's SoA convention, justified because `details` is already the bounded, richer-payload tier (unlike `buckets`/`ratios`, where SoA's payload-size argument actually matters).

**Correctness finding surfaced by this resolution:** `PhotoPolicy::canReadMetrics()`'s `metrics_access=owner` mode is genuinely **per-photo** (compares that specific photo's `owner_id` to the caller), not per-request like every other gate in this feature — a `details` response spanning two different owners can show `statistics: null` for one and real values for the other, in the same response. Encoded in FR-064-21, NFR/S-064-29.

---

### ~~Q-064-07~~ · Should `ratios`' aspect-ratio SQL reproduce `getAspectRatioAttribute()`'s video-forces-1 special case? ✅ RESOLVED

**Status:** Resolved — real ratio wins, the accessor's special case is deliberately **not** reproduced  
**Feature:** 064 – Photo Listing Struct-of-Arrays  
**Resolved:** 2026-09-05

**Context:** `Photo::getAspectRatioAttribute()` (`app/Models/Photo.php:480-491`) has a video-specific short-circuit — if the photo `isVideo()` and neither a `MEDIUM` nor `SMALL` size variant exists, it returns `1` (square) unconditionally, without even checking whether an `ORIGINAL` row with a real, meaningful ratio exists. FR-064-08's SQL `COALESCE(sv_original.ratio, sv_medium.ratio, sv_small.ratio, 1)` does not reproduce this branch — it would use `sv_original.ratio` first if present, disagreeing with the accessor for a video that currently has only an `ORIGINAL` row (e.g. before thumbnail/preview generation has run).

**Resolution:** User confirmed directly: *"we want to use the video aspect ratio also here and not just default to 1."* `ratios[i]`'s `COALESCE` stays exactly as drafted — `sv_original.ratio` first, falling through to medium/small, `1` only when none of the three exist — and this is a **deliberate, intentional divergence** from `getAspectRatioAttribute()`'s video branch, not a bug to reconcile. `ratios` is the feature's grid-rendering-geometry field, and a real captured aspect ratio (when known) always beats a forced square fallback for layout purposes, even for videos. Encoded in FR-064-08.
