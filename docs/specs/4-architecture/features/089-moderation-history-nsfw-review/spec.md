# Feature 089 – Moderation History and NSFW Review

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-10-13 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #089 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
Moderation (Feature 033) and NSFW detection (Feature 045) keep a single state per photo: `photos.is_validated` and `photos.nsfw_status`. This feature records the moderation history of each photo (when it was held, when it was approved for display, by whom, from which source) and adds an admin page listing the NSFW findings, one row per photo with all its findings, all its albums and links to the photo, where wrong findings are dismissed.

Affected layers: a new table and model, the photo creation pipeline, `NsfwActionService`, `ModerationController`, three settings, REST API v2 (five endpoints), v8 admin frontend (new findings page, History tab on the Moderation page, admin section in the photo details panel). See [ADR-089-01](../../../6-decisions/ADR-089-01-moderation-history-event-log.md) and [ADR-089-02](../../../6-decisions/ADR-089-02-nsfw-findings-dismissal-deletes.md).

## Goals
- G1: every hold and every approval of a photo is recorded with its time, its source and the acting admin, subject to three settings.
- G2: admins see the history across all photos and for one photo.
- G3: admins list every photo that has NSFW findings, one row per photo, with its findings, its albums and a link to the photo in each album.
- G4: admins dismiss wrong findings, one finding at a time, a photo at a time, or for a selection of photos.

## Non-Goals
- v7 frontend: unchanged (Q-089-05 → A). The backend records the history whichever frontend is used.
- Changing the trust-tier × finding-tier action matrix (Feature 045).
- Undoing a dismissal: dismissed findings are deleted (Q-089-04 → C).
- Changing the photo state on dismissal, or recording dismissals in the history.
- Un-marking albums that were marked NSFW by a sensitive finding.
- Filters on the findings page; re-scanning from the findings page (bulk scan stays on the Maintenance page).
- Recording the deletion of a photo: its events are deleted with it.
- Deduplicating the detection rows created again by a forced re-scan (`NsfwActionService::logDetections()` appends).

## Functional Requirements

### Moderation history

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-089-01 | New append-only table `photo_moderation_events`: `id` (big increments), `photo_id` (FK `photos.id`, cascade on delete), `event` (`held` \| `approved`), `source` (`upload` \| `nsfw_scan` \| `admin`), `user_id` (nullable unsigned int, FK `users.id`, null on delete: the admin who acted, null for the system), `created_at`. Index on `photo_id` (listings order by `id`). Rows are never updated. | One row per recorded event. | `event` and `source` cast to the enums `ModerationEvent` and `ModerationSource`. | Deleting a photo deletes its events; deleting an admin keeps the events with `user_id = null`. | — | Q-089-01 → A |
| FR-089-02 | `held` events are written where a photo is hidden: (1) at the end of the standalone photo creation pipelines (new photo, and photo joining an already uploaded live-photo video), when the new photo has `is_validated = false` (uploader trust level `check`, or hide-on-scan), source `upload`; (2) in `NsfwActionService::applyActions()`, when the callback moderates the photo (`nsfw_status = review`: block finding with action `moderate`, review finding, sensitive finding for `check`, no-album fallback), source `nsfw_scan`, whether or not the photo was already hidden. Each site calls the recorder explicitly; no model events. | Each hold yields one row. | — | A block finding with action `block` deletes the photo: no event survives. | — | Q-089-01 → A |
| FR-089-03 | `approved` events are written where a hidden photo becomes visible (`is_validated` from `false` to `true`): (1) `POST /Moderation::approve`, source `admin`, `user_id` = the admin, one row per photo that was hidden; (2) `NsfwActionService::applyActions()`, when a non-`check` photo hidden by hide-on-scan gets no moderating result, source `nsfw_scan`. | One row per photo whose `is_validated` changed. | Ids passed to `Moderation::approve` for photos already visible get no row. | — | — | Q-089-01 → A |
| FR-089-04 | Three settings decide which events are written; an event is written only when every setting that applies to it is on. `moderation_history_log_system` applies to events whose source is `upload` or `nsfw_scan`. `moderation_history_log_approvals` applies to `approved` events. `moderation_history_log_nsfw_holds` applies to `held` events whose source is `nsfw_scan`. All default to `1` (see Settings). | Setting off → the matching events are not written; other events are. | Booleans `0\|1`. | — | — | Q-089-01 → A (owner) |
| FR-089-05 | `GET /api/v2/Moderation::history` returns the events of all photos, newest first (`id` descending), paginated. Each entry: event id, photo id, photo title, thumbnail URL, owner username, `event`, `source`, acting admin username (`null` for the system or a deleted admin), `created_at` (ISO 8601). | Admin gets `PaginatedModerationHistoryResource`. | `page` (integer ≥ 1) and `per_page` (integer 1–100) required. | Non-admin → 403, guest → 401, invalid paging → 422. | — | Q-089-02 → C |
| FR-089-06 | `GET /api/v2/Moderation::photoHistory` returns the events of one photo, newest first, unpaginated, as a list of the FR-089-05 entries. | Admin gets `ModerationEventResource[]` (empty when the photo has none). | `photo_id` required (random id). | Non-admin → 403, guest → 401, unknown photo → 404. | — | Q-089-02 → C |
| FR-089-07 | The v8 Moderation page has two tabs: "Pending" (the current list) and "History" (FR-089-05, infinite scroll, 30 per page). History rows: thumbnail (opens the photo in place, as on the Pending tab), title, owner, event badge, by (admin username for source `admin`, "Upload" or "NSFW scan" for the system, "—" for a deleted admin), date and time. | UI-089-02. | — | Load failure → error toast, as on the Pending tab. | — | Q-089-02 → C |
| FR-089-08 | The v8 photo details panel shows a "Moderation" section to admins: the photo's events (FR-089-06), newest first, each with event, source or admin, date and time. Fetched when the panel is open and the user is admin, once per photo. Hidden when the user is not admin or the photo has no event. | UI-089-03. | — | Fetch failure → section hidden. | — | Q-089-02 → C |

### NSFW findings review

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-089-09 | `GET /api/v2/NsfwDetection/findings` returns one entry per photo that has at least one row in `nsfw_detections`, latest detection (highest detection id) first, paginated by photo. Each entry: photo id, title, thumbnail URL, owner username, `nsfw_status`, `is_validated`, every detection of the photo (`NsfwDetectionResource`, highest confidence first), every album holding the photo (`PhotoAlbumResource`, by title), `scanned_at` (latest detection `created_at`, ISO 8601). | Admin gets `PaginatedNsfwFindingsResource`. | `page` (integer ≥ 1) and `per_page` (integer 1–100) required. | Non-admin → 403, guest → 401, no SE licence → 402 (`support:se`). | — | Owner directive (one row per photo) |
| FR-089-10 | `DELETE /api/v2/NsfwDetection/findings` deletes the `nsfw_detections` rows whose ids are given. The photos' `nsfw_status` and `is_validated` are unchanged and no history event is written. | 204; the other findings of the same photos stay. | `detection_ids` required, array of 1–500 integers ≥ 1. | Non-admin → 403, guest → 401, no SE → 402, invalid body → 422. | — | Q-089-03 → A, Q-089-04 → C |
| FR-089-11 | `DELETE /api/v2/NsfwDetection/findings/photos` deletes every `nsfw_detections` row of the given photos, with the same effects as FR-089-10. | 204; the photos leave the findings listing. | `photo_ids` required, array of 1–500 random ids. | Non-admin → 403, guest → 401, no SE → 402, invalid body → 422. | — | Q-089-03 → A, Q-089-04 → C |
| FR-089-12 | New v8 page `/admin/nsfw-findings` (route name `nsfw-findings`) lists FR-089-09 with infinite scroll (30 per page). Row: checkbox, thumbnail (opens the photo in place with `Moderation::photo` and `PhotoBox`, as on the Moderation page), title, owner and scan date, status badges ("Hidden" when `is_validated = false`, "NSFW review" when `nsfw_status = review`), findings as chips (label, confidence in %, colour of the most severe tier: block, then review, then sensitive) each with a dismiss button, albums as links to `/gallery/{albumId}/{photoId}` ("—" when none), and a "Dismiss all" row button. A bulk bar dismisses the findings of the selected rows. A chip dismiss calls FR-089-10 at once; "Dismiss all" and the bulk action ask for confirmation, then call FR-089-11. A row whose last finding is dismissed leaves the list. Dismissed photos are dropped from the overlay cache (`usePhotoNsfwDetectionsStore`). | UI-089-01. | — | Request failure → error toast, list unchanged. | — | Owner directive, Q-089-03 → A |
| FR-089-13 | The admin dashboard shows an "NSFW findings" tile (group `extensions`, next to the NSFW config tile) linking to `/admin/nsfw-findings`, visible when `settings.can_edit` and `modules.is_nsfw_classifier_enabled`. `GET /admin/nsfw-findings` serves the Vue app (`migration:complete`, `login_required:always`). | Tile visible to admins with NSFW detection enabled. | — | — | — | Owner directive |
| FR-089-14 | Approval-time album marking (`ModerationController::approvePhotos`) and the lightbox overlay read the remaining `nsfw_detections` rows, so a dismissed sensitive finding no longer marks albums when the photo is approved. | Existing behaviour on fewer rows. | — | — | — | Q-089-04 → C |

### Decided without open question
- Photo links: one link per album, `/gallery/{albumId}/{photoId}`. The thumbnail opens the photo in place; this is the only link for a photo without album, since `/gallery/unsorted/{photoId}` shows only the admin's own photos when `enable_smart_album_per_owner` is on.
- Gating: the findings endpoints carry `support:se` and sit in the `feature:ai-vision`, `feature:v8` route group with the other NSFW endpoints. Every NSFW setting is level 1 (SE), so findings exist only on SE instances; this matches `NsfwDetection/bulk-scan` and `NsfwDetection/config`. The history endpoints follow the Moderation page (Feature 033): admin only, no SE gate.
- A chip dismiss acts at once; whole-photo and bulk dismissals ask for confirmation, since nothing can be restored.

## Non-Functional Requirements
| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-089-01 | Findings and history endpoints are admin only. | Detection details and moderation decisions are sensitive (NFR-045-03). | Non-admin → 403, guest → 401. | `may_administrate` | Feature 045 NFR-045-03 |
| NFR-089-02 | The findings and history listings run a constant number of queries per page, whatever the number of findings, albums or events per photo. | Large libraries. | Query count asserted in tests. | — | Owner directive (one row per photo) |
| NFR-089-03 | Recording events costs at most one insert per call: `Moderation::approve` writes the events of a chunk (100 photos) with one bulk insert. Nothing is written when the settings exclude the event. | Bulk approval of 500 photos. | Query count asserted in tests. | — | Q-089-01 → A |

## Settings
Category `Users Management`, after the trust-level settings. `required_keys` per [settings_dependencies.md](../../../3-reference/settings_dependencies.md).

| Key | Type | Default | Level | Requires | Description |
|-----|------|---------|-------|----------|-------------|
| `moderation_history_log_system` | `0\|1` | `1` | 0 | — | Record moderation events made by the system (holds at upload, NSFW scan results). |
| `moderation_history_log_approvals` | `0\|1` | `1` | 0 | — | Record approvals, by an admin or by the system. |
| `moderation_history_log_nsfw_holds` | `0\|1` | `1` | 1 | `ai_vision_enabled,ai_vision_nsfw_enabled,moderation_history_log_system` | Record holds caused by NSFW findings. Level 1: NSFW scanning needs SE. |

## UI / Interaction Mock-ups

### UI-089-01 – NSFW findings page (`/admin/nsfw-findings`)

```
┌──────────────────────────────────────────────────────────────────────────────────┐
│ [☰]                              NSFW findings                                   │
├──────────────────────────────────────────────────────────────────────────────────┤
│        Review the NSFW findings and dismiss the wrong ones. Dismissed            │
│        findings are deleted; a new scan can find them again.                     │
│                                                                                  │
│ 2 selected   [✕ Dismiss selected]                                                │
│ ┌──┬───────┬──────────────┬─────────────┬──────────────────────┬────────┬──────┐ │
│ │☐ │ Photo │ Title/owner  │ Status      │ Findings             │ Albums │      │ │
│ ├──┼───────┼──────────────┼─────────────┼──────────────────────┼────────┼──────┤ │
│ │☑ │ [img] │ IMG_0042.jpg │ [Hidden]    │ (BUTTOCKS_EXPOSED    │ Beach ↗│ [✕]  │ │
│ │  │       │ alice        │ [NSFW rev.] │  82 % ✕) (BELLY_     │ 2024 ↗ │      │ │
│ │  │       │ 2026-10-09   │             │  EXPOSED 41 % ✕)     │        │      │ │
│ ├──┼───────┼──────────────┼─────────────┼──────────────────────┼────────┼──────┤ │
│ │☑ │ [img] │ DSC_1201.jpg │             │ (FEET_EXPOSED 63 % ✕)│ —      │ [✕]  │ │
│ │  │       │ bob          │             │                      │        │      │ │
│ └──┴───────┴──────────────┴─────────────┴──────────────────────┴────────┴──────┘ │
│ chip colour: red = block, orange = review, yellow = sensitive                    │
│ (✕) on a chip dismisses that finding at once; [✕] on a row and                   │
│ [Dismiss selected] ask: "Dismiss all findings of N photos? They are deleted."    │
│                                                                                  │
│ Empty: (✓) No NSFW findings.                                                     │
└──────────────────────────────────────────────────────────────────────────────────┘
```

### UI-089-02 – Moderation page, History tab

```
┌──────────────────────────────────────────────────────────────────────────┐
│ [☰]                          Moderation                                  │
├──────────────────────────────────────────────────────────────────────────┤
│   [ Pending ]  [ History ]                                               │
│ ┌───────┬──────────────┬───────┬────────────┬───────────┬──────────────┐ │
│ │ Photo │ Title        │ Owner │ Event      │ By        │ Date         │ │
│ ├───────┼──────────────┼───────┼────────────┼───────────┼──────────────┤ │
│ │ [img] │ IMG_0042.jpg │ alice │ [Approved] │ admin     │ 10/13 14:02  │ │
│ │ [img] │ IMG_0042.jpg │ alice │ [Held]     │ NSFW scan │ 10/09 09:12  │ │
│ │ [img] │ IMG_0042.jpg │ alice │ [Held]     │ Upload    │ 10/09 09:11  │ │
│ └───────┴──────────────┴───────┴────────────┴───────────┴──────────────┘ │
│ Empty: (✓) No moderation history.                                        │
└──────────────────────────────────────────────────────────────────────────┘
```

### UI-089-03 – Photo details panel, admin section

```
┌───────────────────────────────┐
│ About                         │
│ …                             │
│ Albums                        │
│   Beach                       │
│ Moderation                    │
│   ✓ Approved · admin          │
│     2026-10-13 14:02          │
│   ⏸ Held · NSFW scan          │
│     2026-10-09 09:12          │
│   ⏸ Held · Upload             │
│     2026-10-09 09:11          │
│ …                             │
└───────────────────────────────┘
(admins only; hidden when the photo has no event)
```

## Branch & Scenario Matrix
| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-089-01 | `check` uploader uploads a photo → one `held` / `upload` event, `user_id` null. |
| S-089-02 | `monitor` uploader with `ai_vision_nsfw_monitor_hide_on_scan = 1` uploads a photo → one `held` / `upload` event. |
| S-089-03 | `trusted` uploader uploads a photo, no hide-on-scan → no event. |
| S-089-04 | NSFW callback, review finding, `monitor` photo → `held` / `nsfw_scan` event. |
| S-089-05 | NSFW callback, review finding, `check` photo (already hidden) → `held` / `nsfw_scan` event. |
| S-089-06 | NSFW callback, no finding, `monitor` photo hidden by hide-on-scan → `approved` / `nsfw_scan` event. |
| S-089-07 | NSFW callback, no finding, `check` photo → no event; the photo stays hidden. |
| S-089-08 | NSFW callback, block finding with action `block` → photo deleted, no event left. |
| S-089-09 | Admin approves three photos, one already visible → two `approved` / `admin` events with the admin's id, one insert. |
| S-089-10 | `moderation_history_log_system = 0` → upload and NSFW events not written; admin approvals written. |
| S-089-11 | `moderation_history_log_approvals = 0` → no `approved` event (admin or system); holds written. |
| S-089-12 | `moderation_history_log_nsfw_holds = 0` → `held` / `nsfw_scan` not written; `held` / `upload` and approvals written. |
| S-089-13 | Admin lists the history → events of all photos, newest first, with photo, owner and admin username; paginated. |
| S-089-14 | Non-admin lists the history or a photo's history → 403; guest → 401. |
| S-089-15 | Admin lists a photo's history → only that photo's events, newest first; none → empty list. |
| S-089-16 | A photo is deleted → its events are deleted. An admin is deleted → events kept with `user_id` null. |
| S-089-17 | Admin lists the findings → one entry per photo with detections, with all its detections and all its albums, latest detection first; photos without detections absent. |
| S-089-18 | Non-admin lists or deletes findings → 403; guest → 401. |
| S-089-19 | Admin deletes one finding of a photo with two → that row deleted, the other kept, photo state unchanged, no history event. |
| S-089-20 | Admin deletes the findings of a photo → all its rows deleted; the photo leaves the listing; photo state unchanged. |
| S-089-21 | Invalid delete bodies (empty array, more than 500 ids, non-integer detection id, malformed photo id) → 422. |
| S-089-22 | Photo held by a sensitive finding, finding deleted, admin approves → no album marking. |
| S-089-23 | UI: chip dismiss removes the chip; the last chip removes the row; "Dismiss all" and bulk ask for confirmation. |
| S-089-24 | UI: Moderation History tab lists events with infinite scroll; thumbnail opens the photo. |
| S-089-25 | UI: photo details panel shows the Moderation section to admins for a photo with events; hidden otherwise. |

## Test Strategy
- **Unit:** the pure predicate deciding whether an event is recorded (every event × source × setting combination), `tests/Unit`.
- **Feature (Feature_v3):** recording at upload (S-089-01 … S-089-03), at NSFW callback (S-089-04 … S-089-08), at approval with query count (S-089-09), settings (S-089-10 … S-089-12), history endpoints (S-089-13 … S-089-16), findings listing with query count (S-089-17, S-089-18), findings deletion (S-089-18 … S-089-22).
- **Regression:** the existing moderation, NSFW callback/action, upload trust level and settings test classes.
- **Frontend:** `npm run check`, `npm run format`, eslint on touched files; Playwright on a scratch instance for UI-089-01 … UI-089-03.

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-089-01 | `App\Models\PhotoModerationEvent` — table `photo_moderation_events`, `UPDATED_AT = null`, relations `photo`, `user`. | models |
| DO-089-02 | `App\Enum\ModerationEvent` — `HELD = 'held'`, `APPROVED = 'approved'`. | models, actions |
| DO-089-03 | `App\Enum\ModerationSource` — `UPLOAD = 'upload'`, `NSFW_SCAN = 'nsfw_scan'`, `ADMIN = 'admin'`. | models, actions |
| DO-089-04 | `App\Services\ModerationHistory` — `record(string[] $photo_ids, ModerationEvent, ModerationSource, ?int $user_id)`: checks the settings with a pure predicate, then one bulk insert. | actions, services |
| DO-089-05 | `App\Actions\Photo\Pipes\Standalone\RecordUploadHold` — last pipe of both standalone creation pipelines (`handleStandalone`, `handlePhotoLivePartner`); records `held` / `upload` when the photo is hidden. | actions |
| DO-089-06 | `App\Http\Resources\Models\ModerationEventResource` — `id`, `photo_id`, `photo_title`, `thumb_url`, `owner_username`, `event`, `source`, `username`, `created_at`. | REST |
| DO-089-07 | `App\Http\Resources\Collections\PaginatedModerationHistoryResource` — `events`, `current_page`, `last_page`, `per_page`, `total`. | REST |
| DO-089-08 | `App\Http\Resources\Models\NsfwFindingResource` — `photo_id`, `title`, `thumb_url`, `owner_username`, `nsfw_status`, `is_validated`, `detections`, `albums`, `scanned_at`. | REST |
| DO-089-09 | `App\Http\Resources\Collections\PaginatedNsfwFindingsResource` — `photos`, `current_page`, `last_page`, `per_page`, `total`. | REST |

### API Routes / Services
| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-089-01 | REST GET /api/v2/Moderation::history | All events, paginated (FR-089-05). | `ModerationController::history`, `ListModerationHistoryRequest` |
| API-089-02 | REST GET /api/v2/Moderation::photoHistory | One photo's events (FR-089-06). | `ModerationController::photoHistory`, `GetModerationPhotoHistoryRequest` |
| API-089-03 | REST GET /api/v2/NsfwDetection/findings | Findings per photo, paginated (FR-089-09). | `NsfwFindingsController::index`, `ListNsfwFindingsRequest`, `support:se` |
| API-089-04 | REST DELETE /api/v2/NsfwDetection/findings | Delete findings by id (FR-089-10). | `NsfwFindingsController::destroy`, `DeleteNsfwFindingsRequest`, `support:se` |
| API-089-05 | REST DELETE /api/v2/NsfwDetection/findings/photos | Delete findings by photo (FR-089-11). | `NsfwFindingsController::destroyByPhotos`, `DeleteNsfwFindingsByPhotosRequest`, `support:se` |
| API-089-06 | Web GET /admin/nsfw-findings | Vue app shell (FR-089-13). | `VueController` |

### Telemetry Events
None. Lychee emits no telemetry; the history table is the record.

### UI States
| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-089-01 | NSFW findings page | Admin opens `/admin/nsfw-findings` → rows per photo; chip, row and bulk dismissal; empty state when no finding. |
| UI-089-02 | Moderation History tab | Admin selects "History" on `/admin/moderation` → events, newest first; empty state when none. |
| UI-089-03 | Photo details Moderation section | Admin opens the details panel of a photo with events → section listed; hidden otherwise. |

## Documentation Deliverables
- Roadmap row #089.
- Knowledge map: moderation history and the findings listing in the Feature 045 section.
- Feature 033 and Feature 045 specs: point to this feature where the Moderation page and `nsfw_detections` are described.
- [settings_dependencies.md](../../../3-reference/settings_dependencies.md): row for `moderation_history_log_nsfw_holds`.
- ADR-089-01 (history event log), ADR-089-02 (dismissal deletes findings).

## Spec DSL

```
domain_objects:
  - id: DO-089-01
    name: PhotoModerationEvent
    fields:
      - name: photo_id
        type: string
        constraints: "FK photos.id, cascade on delete"
      - name: event
        type: ModerationEvent
      - name: source
        type: ModerationSource
      - name: user_id
        type: integer|null
        constraints: "FK users.id, null on delete"
      - name: created_at
        type: timestamp
  - id: DO-089-02
    name: ModerationEvent
    values: [HELD, APPROVED]
  - id: DO-089-03
    name: ModerationSource
    values: [UPLOAD, NSFW_SCAN, ADMIN]
routes:
  - id: API-089-01
    method: GET
    path: /api/v2/Moderation::history
  - id: API-089-02
    method: GET
    path: /api/v2/Moderation::photoHistory
  - id: API-089-03
    method: GET
    path: /api/v2/NsfwDetection/findings
  - id: API-089-04
    method: DELETE
    path: /api/v2/NsfwDetection/findings
  - id: API-089-05
    method: DELETE
    path: /api/v2/NsfwDetection/findings/photos
config_keys:
  - key: moderation_history_log_system
    type: bool
    default: "1"
  - key: moderation_history_log_approvals
    type: bool
    default: "1"
  - key: moderation_history_log_nsfw_holds
    type: bool
    default: "1"
    level: 1
ui_states:
  - id: UI-089-01
    description: NSFW findings page
  - id: UI-089-02
    description: Moderation History tab
  - id: UI-089-03
    description: Photo details Moderation section
```
