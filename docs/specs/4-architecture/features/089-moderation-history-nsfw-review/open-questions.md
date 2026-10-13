# Open Questions – Feature 089

Open questions for [Feature 089](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-089-01~~ | 089 – Moderation history and NSFW review | High | What does the moderation history record? (A event log of holds and approvals with source and admin · B event log of approvals only · C `validated_at`/`validated_by` columns on `photos`) | Resolved (Option A with three logging settings; owner, 2026-10-13; spec FR-089-01 … FR-089-04, ADR-089-01) | 2026-10-10 | 2026-10-13 |
| ~~Q-089-02~~ | 089 – Moderation history and NSFW review | Medium | Where is the history shown? (A History tab on the Moderation page · B admin block in the photo details panel · C both) | Resolved (Option C; owner, 2026-10-13; spec FR-089-07, FR-089-08, UI-089-02, UI-089-03) | 2026-10-10 | 2026-10-13 |
| ~~Q-089-03~~ | 089 – Moderation history and NSFW review | High | What can be dismissed on the findings page? (A single findings and whole photos, with bulk · B whole photos only · C single findings only) | Resolved (Option A; owner, 2026-10-13; spec FR-089-10, FR-089-11, UI-089-01) | 2026-10-10 | 2026-10-13 |
| ~~Q-089-04~~ | 089 – Moderation history and NSFW review | High | What does a dismissal do? (A soft flag, photo state unchanged, Approve button on held rows · B soft flag, dismissing the last finding of a held photo approves it · C delete the detection rows) | Resolved (Option C; owner, 2026-10-13; spec FR-089-10, FR-089-11, ADR-089-02) | 2026-10-10 | 2026-10-13 |
| ~~Q-089-05~~ | 089 – Moderation history and NSFW review | Medium | Which frontend trees? (A v8 only · B v7 and v8) | Resolved (Option A; owner, 2026-10-13; spec Non-Goals, UI section) | 2026-10-10 | 2026-10-13 |
| ~~Q-089-06~~ | 089 – Moderation history and NSFW review | High | The no-album fallback of a sensitive finding (`ai_vision_nsfw_sensitive_no_album_action = moderate`) is undone by `applyActions()`, so no hold happens. (A fix the fallback here and record `held` · B leave it, drop the fallback from FR-089-02, fix in a separate change · C record `held` anyway) | Resolved (Option A; owner, 2026-10-13; spec FR-089-02, FR-089-15) | 2026-10-13 |

## Question Details

### ~~Q-089-01~~ – Content of the moderation history

**Context:** `photos.is_validated` is the only moderation state; nothing records when it changed, who changed it or why. It is written at these sites: `SetUploadValidated` (upload, `false` for a `check` uploader), `AutoScanNsfwOnUpload` (hide-on-scan, `false`), `NsfwActionService` (block/review/sensitive moderation and the no-album fallback set `false`; a clean result for a non-`check` uploader sets `true`) and `ModerationController::approve` (admin, `true`). Deleting a photo from the Moderation page goes through the normal photo delete.

- **Option A (chosen):** Append-only table `photo_moderation_events` (`id`, `photo_id` FK cascade, `event` = `held` | `approved`, `source` = `upload` | `nsfw_scan` | `admin`, `user_id` nullable = acting admin, `created_at`). A row is written explicitly at each site above when the value changes. Photos never held get no row. Rows are deleted with the photo, so a photo rejected (deleted) from the queue leaves no trace.
  - ✅ Answers when, by whom and why, for every hold/approve cycle (a forced re-scan can hold a photo again).
  - ✅ The same table can carry finding dismissals (Q-089-04).
  - ❌ New table and writes at about six sites.
- **Option B:** Same table, approvals only (admin approval and automatic approval after a clean scan).
  - ✅ Two write sites.
  - ❌ No record of when or why a photo entered the queue, so time spent in the queue is unknown.
- **Option C:** Columns `validated_at` and `validated_by_id` on `photos`, set on approval.
  - ✅ No new table; trivial to read.
  - ❌ Last approval only, no history; widens `photos`.

**Resolution:** Option A, with three settings that decide what is written: system events (no admin involved), approvals, and holds caused by NSFW findings. An event is written only when every setting that applies to it is on. Recorded in [spec.md](spec.md) FR-089-01 … FR-089-04 and [ADR-089-01](../../../6-decisions/ADR-089-01-moderation-history-event-log.md).

### ~~Q-089-02~~ – Where the history is shown

**Context:** The Moderation page (`/admin/moderation`) lists the photos still waiting (`is_validated = false`) and removes a row once approved. Nothing shows past decisions.

- **Option A (recommended):** A "History" tab on the Moderation page, next to the current list ("Pending"). Paginated, newest first, one row per event: thumbnail (opens the photo), title, owner, event (held / approved), source, by (admin username or "System"), date and time.
  - ✅ One place for moderation; admin-only like the rest of the page.
  - ✅ Overview across all photos.
  - ❌ No history while browsing a photo.
- **Option B:** An admin-only block in the photo details panel: "Approved on … by …" and the photo's events.
  - ✅ In context while browsing.
  - ❌ No overview; adds fields to the photo resource for one audience.
- **Option C (chosen):** Both.
  - ✅ Overview and context.
  - ❌ Twice the UI work.

**Resolution:** Option C. The photo details panel reads the photo's events from a dedicated endpoint, so the photo resource is unchanged. Recorded in [spec.md](spec.md) FR-089-07, FR-089-08, UI-089-02 and UI-089-03.

### ~~Q-089-03~~ – Dismissal granularity on the findings page

**Context:** The findings page has one row per photo (owner directive), each showing all its findings (label, confidence, tier) and all its albums. In a photo with several findings, some can be right and others wrong.

- **Option A (chosen):** Single findings and whole photos. Each finding chip has its own dismiss button, each row has "Dismiss all", and selected rows can be dismissed in bulk.
  - ✅ Keeps a correct finding while dismissing a wrong one in the same photo.
  - ✅ Fast for photos that are wholly false positives.
  - ❌ Two endpoints (or one taking either ids) and more UI.
- **Option B:** Whole photos only (row button and bulk).
  - ✅ Simplest UI and API.
  - ❌ A photo with one right and one wrong finding cannot be cleaned up.
- **Option C:** Single findings only.
  - ✅ Precise.
  - ❌ Tedious for photos with many wrong findings; no bulk.

**Resolution:** Option A. Recorded in [spec.md](spec.md) FR-089-10, FR-089-11 and UI-089-01.

### ~~Q-089-04~~ – Effect of a dismissal

**Context:** After the callback, detections are used for: the lightbox overlay (`GET /Photo/{id}/nsfw-detections`) and the album marking applied when an admin approves a held photo (`is_sensitive` detections in `ModerationController::approvePhotos`). The photo state (`nsfw_status`, `is_validated`) is set once at callback time. A `check` uploader's photo is held by its trust level whatever the findings say. An album marked NSFW cannot be traced back to the photo that caused it, so no option un-marks albums.

- **Option A (recommended):** Soft flag, photo state unchanged. New `is_dismissed` boolean on `nsfw_detections` (like `faces.is_dismissed`), recorded in the history with admin and time when Q-089-01 is A. Dismissed findings are hidden from the default listing and from the overlay, and ignored by approval-time album marking; "Show dismissed" lists them with a Restore button. A held photo shows an "Approve" button on its row, which does the same as Moderation approve.
  - ✅ Undo and audit trail.
  - ✅ No hidden coupling: approving stays an explicit action.
  - ❌ A false positive takes two clicks (dismiss, approve).
- **Option B:** Soft flag as in A, and dismissing the last active finding of a photo with `nsfw_status = review` approves it (`nsfw_status = visible`; `is_validated = true` unless the uploader's trust level is `check`, which still needs Moderation approval).
  - ✅ One click for an NSFW-only false positive.
  - ❌ Different outcome per trust level; restoring a finding does not hold the photo again.
- **Option C (chosen):** Delete the detection rows.
  - ✅ No schema change.
  - ❌ No undo and no audit; a forced re-scan recreates them.

**Resolution:** Option C. Dismissing deletes the `nsfw_detections` rows; the photo's `nsfw_status` and `is_validated` are unchanged and no history event is written. Recorded in [spec.md](spec.md) FR-089-10, FR-089-11 and [ADR-089-02](../../../6-decisions/ADR-089-02-nsfw-findings-dismissal-deletes.md).

### ~~Q-089-05~~ – Frontend trees

**Context:** The Moderation page and the NSFW presets page exist in both `resources/js/v7` and `resources/js/v8`. v8 is replacing v7.

- **Option A (chosen):** v8 only: new findings page and the history display. v7 pages unchanged; the backend records history whatever tree is used.
  - ✅ Half the frontend work, on the tree that stays.
  - ❌ v7 admins see neither history nor findings page.
- **Option B:** v7 and v8.
  - ✅ Parity.
  - ❌ Two implementations of two new views.

**Resolution:** Option A. Recorded in [spec.md](spec.md) Non-Goals and the UI section.

### ~~Q-089-06~~ – No-album fallback undone by `applyActions()`

**Context:** FR-089-02 lists the no-album fallback among the holds recorded by the NSFW callback. In `NsfwActionService`, a sensitive finding on a non-`check` photo with no album and `ai_vision_nsfw_sensitive_no_album_action = moderate` runs `handleNoAlbumFallback()`, which sets `nsfw_status = review`, `is_validated = false` and saves. `applySensitiveAction()` then returns `false`, so the end of `applyActions()` sees "not moderated", sets `nsfw_status = visible`, `is_validated = true` and saves again: the photo ends visible. `ModerationHistoryNsfwTest::testNoAlbumFallbackIsRecordedAsHeld` shows it (photo `visible`, expected `review`). No Feature 045 test covers the `moderate` value. This is a Feature 045 defect; fixing it changes NSFW behaviour on instances using that setting.

- **Option A (recommended):** Fix it in this feature. `handleNoAlbumFallback()` returns whether it moderated (and no longer saves), `applySensitiveAction()` returns that, so `applyActions()` keeps the hold and records `held` / `nsfw_scan`. Add a regression test to `NsfwActionServiceTest`.
  - ✅ The setting does what it says; FR-089-02 holds as written.
  - ✅ Small change, covered by the test already written.
  - ❌ Widens this feature into Feature 045 behaviour (photos without album start being held on such instances).
- **Option B:** Leave the defect, remove the fallback from FR-089-02 and the test, fix it in a separate change.
  - ✅ This feature stays history-only.
  - ❌ The setting stays broken; the history gains the event only after the separate fix.
- **Option C:** Record `held` when the fallback runs, without fixing it.
  - ✅ No behaviour change.
  - ❌ The history says "held" for a photo that is visible: wrong data.

**Resolution:** Option A. `handleNoAlbumFallback()` returns whether it moderated and no longer saves; `applySensitiveAction()` returns that result, so `applyActions()` keeps the hold and records `held` / `nsfw_scan`. Recorded in [spec.md](spec.md) FR-089-02 and FR-089-15; regression test `NsfwActionServiceTest::testSensitiveNoAlbumModerateHoldsPhoto`.
