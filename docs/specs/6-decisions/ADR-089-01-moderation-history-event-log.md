# ADR-089-01: Moderation History as an Append-Only Event Log

- **Status:** Accepted
- **Date:** 2026-10-13
- **Related features/specs:** Feature 089 (docs/specs/4-architecture/features/089-moderation-history-nsfw-review/spec.md), Feature 033 (docs/specs/4-architecture/features/033-upload-trust-level/spec.md), Feature 045 (docs/specs/4-architecture/features/045-nsfw-moderation/spec.md)
- **Related open questions:** Q-089-01, Q-089-02

## Context

A photo's moderation state is two columns, `photos.is_validated` (Feature 033) and `photos.nsfw_status` (Feature 045). They are written by the upload pipeline (`SetUploadValidated`, `AutoScanNsfwOnUpload`), by the NSFW callback (`NsfwActionService`) and by the admin (`ModerationController::approve`). The owner needs to know when a photo was approved for display. A photo can cycle: held at upload, approved, held again after a forced re-scan, approved again.

## Decision

1. New table `photo_moderation_events` (`id`, `photo_id` cascade, `event` held/approved, `source` upload/nsfw_scan/admin, `user_id` null on delete, `created_at`), append-only.
2. Every site that hides or shows a photo calls `App\Services\ModerationHistory::record()` explicitly, after the photo row exists. No Eloquent model events or observers.
3. `record()` asks a pure predicate whether the event is recorded, from three settings: `moderation_history_log_system` (source upload or nsfw_scan), `moderation_history_log_approvals` (event approved) and `moderation_history_log_nsfw_holds` (held from nsfw_scan). An event is written only when every applicable setting is on. One bulk insert per call.
4. Reads go through two admin endpoints: all events, paginated (`Moderation::history`), and one photo's events (`Moderation::photoHistory`), so the photo resources are unchanged.

## Consequences

### Positive
- Every hold/approve cycle is kept with its time, source and admin.
- The write sites stay readable: one call next to the state change.
- Admins can cut the volume (system events, approvals, NSFW holds) without code changes.

### Negative
- A new write path must call `record()`; nothing enforces it.
- The table grows with moderated uploads; with all settings on and hide-on-scan enabled, each scanned photo adds two rows.
- Deleting a photo deletes its events, so rejected photos leave no trace.

## Alternatives Considered

- **Approvals only (Q-089-01 B):** two write sites, but the time and reason of a hold are lost.
- **`validated_at` / `validated_by_id` on `photos` (Q-089-01 C):** no new table, but only the last approval and a wider `photos` table.
- **Model observer on `Photo::is_validated`:** one hook, but hides the write path and fires on every save; the project writes derived data explicitly at each site.

## Security / Privacy Impact

- Events name the acting admin; the read endpoints are admin only.
- Nothing is exposed to non-admins; the photo details section is rendered for admins only.

## Operational Impact

- Three settings in `Users Management`, all on by default; `moderation_history_log_nsfw_holds` is level 1 (SE) and requires NSFW detection and the system setting.
- One extra insert per moderation action; bulk approval inserts once per chunk of 100.

## Links

- Related spec sections: `docs/specs/4-architecture/features/089-moderation-history-nsfw-review/spec.md#moderation-history` (FR-089-01 … FR-089-08)
- Related ADRs: ADR-089-02
