# Feature 093 – Face Quick Naming

| Field | Value |
|-------|-------|
| Status | In progress |
| Last updated | 2026-10-14 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md)  |
| Linked tasks | [tasks.md](tasks.md)  |
| Roadmap entry | #093 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
Naming faces today means opening clusters one by one (`/people/clusters`), or opening the assignment modal face by face from a photo or from the admin maintenance table. This feature adds a full-screen, keyboard-first "quick naming" view: one photo at a time, the current face highlighted, a single name box that already has focus. Enter names the face and moves on; an arrow key skips; going back lets the user correct a name; Backspace or Space on an empty box discards the face. The view lives in the v8 frontend only, because every AI Vision route is behind the `feature:v8` middleware. It builds on the face/person model of [Feature 030](../030-ai-vision-service/spec.md).

## Goals
- Name a face with as few keystrokes as possible: type, Enter, next face.
- Keep focus in the name box at all times, including after every action.
- Allow skipping a face and coming back to any face handled in the session to change its name or undo a discard.
- Discard a face (blurry, not a face, stranger) with Backspace or Space on an empty box.
- Reach the view from the People page and from the face review (clusters) page.

## Non-Goals
- No v7 (PrimeVue) version.
- No change to detection, embeddings or clustering.
- No merge of persons or editing of person details from this view.
- No mobile swipe gestures in this iteration (buttons are enough on touch devices). Mobile virtual keyboards often do not report Backspace/Space on an empty input reliably, so on touch devices discarding goes through the Discard button.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-093-01 | The view shows one face at a time: the whole photo with the current face highlighted (bounding box from `x/y/width/height`), the face crop, a progress indicator and the name box. The photo is loaded from the face-scoped route `GET /api/v3/Faces/{face_id}/photo/{variant}` (API-093-05, ADR-093-01; `medium` with the usual fallback chain). | The next queued face is shown and the box is focused. | Empty queue shows the "All faces named" state. | Load error shows an error state with a retry button. | None. | Owner request. |
| FR-093-02 | The queue comes from `GET /api/v3/Faces/queue` (API-093-03), a Struct-of-Arrays endpoint built on one `toBase()` query (ADR-0009). It lists unassigned (`person_id` null), non-dismissed faces: clustered faces first, grouped by `cluster_label` (largest cluster first, size counted over the same filtered set of faces) so similar faces come back to back, then unclustered faces (`cluster_label` null or -1); within a group by confidence descending. An optional `cluster_label` parameter limits the queue to one cluster (entry from a cluster card). Admins get faces on every photo; other users get faces on photos they own (`photos.owner_id`). Every queued face can therefore be dismissed by the viewer (owner or admin), and its photo is served by API-093-05 (ADR-093-01); the queue carries no album id. Bounding: capped with truncation (ADR-069-01, strategy 3), cap from the new admin setting `ai_vision_face_queue_limit` (default 500); the response carries `is_truncated`, and the view refetches when it reaches the end of a truncated queue and drops ids already handled in the session (named, skipped or discarded), so a skipped face is not shown twice. | Arrays returned, view starts on index 0. | `cluster_label` must be an integer ≥ 0, else 422 (unclustered faces cannot be selected on their own). Guest → 401. Struct-of-array flag off or no batch face right → 403. | Server error → error state with retry. | None. | Q-093-01 (A, SoA); Q-093-05 (A); Q-093-09 (A); ADR-0009; ADR-069-01; ADR-093-01. |
| FR-093-03 | The people list comes once per session from `GET /api/v3/People` (API-093-04), a new Struct-of-Arrays endpoint on one `toBase()` query (no existing v3 endpoint lists persons). Typing filters it client-side, case-insensitive; matches are ranked exact matches first, then prefix matches, then substring matches; at most 8 persons are listed, and the list only appears once the box has text. Person names are not unique (homonyms allowed), so every row shows the person's representative crop and face count. Within the exact matches, the person of the face's suggestion (FR-093-12) comes first, then by face count descending; other groups are alphabetical, then by face count. The list always ends with a create entry: "Create «text»", or "Create another «text»" when a person with that name already exists. The first row is highlighted; ↑/↓ move the highlight. Enter acts on the highlighted row: a person row assigns that person (`person_id`); the create entry creates a new person from the trimmed text (`new_person_name`). Shift+Enter always creates a new person from the typed text, even when the name already exists. A person created this way is added to the local list so the next face can pick it. When the box is empty, the face is not a revisited named face, and it has a suggestion (FR-093-12), the suggested name is shown greyed out in the box with a "Suggested" hint, and Enter accepts it (assigns that person). Then the next face is shown with an empty, focused box. | Face assigned, view advances. | Empty box + Enter with no suggestion does nothing, on a new face or a revisited discarded face (revisited named faces: FR-093-05). Names are trimmed; length ≤ 255 as enforced by `AssignFaceRequest`. | API error: see FR-093-09. | None. | Q-093-03 (A); Q-093-10 (A); Q-093-11 (A, Shift+Enter refined by Q-093-13); Q-093-13 (A). |
| FR-093-04 | While the name box is empty, or still holds exactly the pre-filled name of a revisited face, → (← in RTL) skips to the next face without changing it and ← (→ in RTL) goes back to the previous face of the session. Once the text differs, arrows move the caret as usual. On a revisited face whose box still holds the unchanged pre-filled name, Backspace clears the whole box in one press (it does not discard; see FR-093-06). On-screen Previous/Skip buttons do the same in any state. Skipped faces stay unnamed. | View moves, box focused. | No previous face: back is a no-op. | — | None. | Q-093-04 (A); Q-093-06 (A + Backspace clear). |
| FR-093-05 | Going back to a face named in this session pre-fills the box with its name; Enter with a different name re-assigns it; clearing the box and pressing Enter un-assigns it. | Face re-assigned / un-assigned. | — | See FR-093-09. | None. | Owner request. |
| FR-093-06 | Backspace or Space while the box is empty discards the face by setting `is_dismissed` through the existing toggle `PATCH /api/v2/Face/{id}`, then moves to the next face. Only a key press made while the box is already empty discards: the keystroke that empties the box does not, and auto-repeated key events (`KeyboardEvent.repeat`) never discard, so holding Backspace to clear a pre-filled name cannot discard the face. IME composition events are ignored. Going back to a discarded face shows a "discarded" badge; naming it first toggles `is_dismissed` back, then assigns it. Dismissed faces are purged later by the existing admin "Destroy dismissed faces" job. | Face discarded, view advances. | Keys behave normally when the box has text. | See FR-093-09. | None. | Q-093-02 (A). |
| FR-093-07 | The People page and the face review (clusters) page each get a "Quick naming" button opening the view (`/people/quick-naming`); each cluster card on the review page gets one too, opening the view scoped to that cluster (`?cluster=<label>`). Buttons are shown only when the new global right `modules.can_quick_name_faces` is true: AI Vision and face recognition enabled, the struct-of-array flag on, and `AlbumPolicy::CAN_BATCH_FACE_OPS` granted with a null album (admins in every mode, logged-in users in `public`/`private` mode). | Button opens the view. | Hidden otherwise. A direct URL without rights shows the forbidden state. | — | None. | Owner request. |
| FR-093-08 | Focus returns to the name box after every action, after a toast, and when the window regains focus. Navigation keys are handled on the input's own `keydown`, not through `definePanelShortcuts` (which ignores keystrokes typed into inputs). | — | — | — | None. | Owner request. |
| FR-093-09 | Actions are optimistic: the view advances before the request returns. If an assign, un-assign, un-dismiss or discard request fails, a toast shows the error with a "Go back" action that jumps to that face; the face is marked failed in the session history (red badge when revisited) and its typed name is kept in the box. Discard and un-dismiss always send the target value (`is_dismissed: true|false`, API-093-02), so a retry after a request that actually succeeded is harmless. | Request succeeds silently. | — | Toast + failed badge; going back and pressing Enter retries. | None. | Q-093-07 (A); Q-093-12 (A). |
| FR-093-10 | A "?" button at the top right of the view opens the keybindings help, showing the quick naming keys (Enter, Shift+Enter = always a new person, arrows, Backspace/Space, ↑/↓). The "?" key itself is not a shortcut in this view, since it is a printable character in the name box. | Help opens; on close, focus returns to the name box. | — | — | None. | Owner request. |
| FR-093-11 | The session is the in-memory history of faces shown since the view was opened; it is lost on reload or when leaving the view. Dismissing a face that was a person's last face deletes that person on the server (existing `recountOrDeletePersons`), so after any dismiss of a previously named face the view reloads the people list (API-093-04). | — | — | — | None. | Existing Feature 030 behaviour. |
| FR-093-12 | Each queued face may carry a suggested person: `suggested_person_ids` / `suggested_person_names` in API-093-03, computed in the same SELECT (correlated subquery). It is the person of the highest-confidence `face_suggestions` row whose suggested face is assigned and not dismissed, limited to persons the caller may see (admins: all; others: searchable persons and their own person, as in API-093-04), else null. Client side, when the previous face in the session has the same non-null `cluster_label` and was named in this session, that person overrides the server suggestion. The suggestion is never applied without Enter. Revisited named faces show their name pre-filled and no suggestion, so clearing the box and pressing Enter un-assigns (FR-093-05). | Suggestion shown greyed out; Enter accepts. | Typing replaces it; arrows and Backspace/Space treat the box as empty. | No suggestion → empty box as usual. | None. | Q-093-10 (A). |
| FR-093-13 | Request ordering under the optimistic flow: requests for the same face are sent one after another (a re-name waits for the previous assign of that face). A person created in this session is added to the local list immediately, even before the server returns its id. Picking it again (its row, or Enter on it as the highlighted match) while its creation is still pending waits for that creation and assigns with its `person_id` once known, so the view never creates two persons by accident. Only the create entry or Shift+Enter makes a second person with the same name. | No duplicate persons, final state matches the last action. | — | A failed creation fails every face waiting on it (FR-093-09). | None. | Consequence of Q-093-07 (A). |
| FR-093-14 | Leaving the view: "Back" returns to the page the view was opened from (People or face review), which reloads its data. The done state offers the same button. Opened from a cluster card, the queue only holds that cluster's faces on photos the caller may name (FR-093-02), so it can be shorter than the card's count, which counts every visible face. | — | — | — | None. | Consequence of Q-093-05 (A). |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-093-01 | The next face is visible without waiting on the network after an action (optimistic advance, next photos preloaded). | "Tinder-like" speed. | Next face renders before the assign request returns; the next 3 images are preloaded. | Image preloading. | Owner request. |
| NFR-093-02 | Arrow semantics follow reading direction (`useLtRorRtL()`). | RTL locales (ar, fa). | Manual check with `dir="rtl"`. | `resources/js/utils/Helpers.ts`. | Coding conventions. |
| NFR-093-03 | All new strings are in `lang/en/people.php` and every other locale file. | i18n parity. | Key-parity check across `lang/*`. | Translation files. | Repo convention. |
| NFR-093-04 | The keyboard shortcuts are listed in the keybindings help modal. | Discoverability. | Entry visible in `KeybindingsHelp.vue`. | v8 help modal. | Repo convention. |
| NFR-093-05 | The two Struct-of-Arrays endpoints (API-093-03, API-093-04) each run one SELECT through `toBase()`: no model hydration, eager loads, URL signing or Carbon on the request path; they work on SQLite, MySQL and PostgreSQL. The binary route API-093-05 follows `PhotoAssetController` instead. | v3 conventions. | Query count asserted with `DB::listen` in the AssistedVision tests. | ADR-0009, Spec 079 NFRs. | ADR-0009. |
| NFR-093-06 | The new setting `ai_vision_face_queue_limit` has a translated title and description in all `lang/*/all_settings.php` files. | Repo convention. | Key-parity check. | Settings migration. | ADR-069-01 (cap is admin-configurable). |
| NFR-093-07 | The person list for non-admins is the same set as `GET /api/v2/People` (searchable persons and the user's own person). An exact-name match against a person the user cannot see is not detected, so a duplicate person may be created; this is accepted and resolved by the existing merge feature. | Privacy (no leak of non-searchable names). | Test: non-searchable person absent from the list for a regular user. | `Person::scopeSearchable`. | Q-093-03. |

## UI / Interaction Mock-ups

Main state (LTR):

```
+--------------------------------------------------------------------------+
| ← Back to People            Quick naming          37 / 412 faces   [?] |
|   (when `is_truncated`: "37 / 500+" — no extra COUNT query, NFR-093-05) |
|--------------------------------------------------------------------------|
|                                                                          |
|        +------------------------------------------------------+          |
|        |                                                      |          |
|        |           .-----------.                              |          |
|        |           |  ▓▓▓▓▓▓▓  |   ← current face, highlighted  |          |
|        |           |  ▓▓▓▓▓▓▓  |     (rest of photo dimmed)     |          |
|        |           '-----------'                              |          |
|        |                                                      |          |
|        +------------------------------------------------------+          |
|                                                                          |
|   [crop]   ┌──────────────────────────────────────────────┐              |
|            │ Ali|                                          │ ← focused   |
|            └──────────────────────────────────────────────┘              |
|            │ ▸ (◕) Alice Martin        42 faces            │ highlighted  |
|            │   (◕) Alina Petrova        7 faces            │              |
|            │   + Create “Ali”              Shift+Enter     │              |
|                                                                          |
|   [ ‹ Previous ]      [ Discard ⌫ ]      [ Skip › ]                     |
|   Enter: name · Shift+Enter: new person · → skip · ← back · ⌫/Space: discard |
+--------------------------------------------------------------------------+
```

Suggested name (empty box):

```
|   [crop]   ┌──────────────────────────────────────────────┐              |
|            │ |Alice Martin                  Suggested     │ ← greyed out |
|            └──────────────────────────────────────────────┘              |
|   Enter accepts · type to replace                                        |
```

Revisiting a face (named earlier in the session):

```
|   [crop]   ┌──────────────────────────────────────────────┐              |
|            │ Alice Martin                                 │ ✓ named      |
|            └──────────────────────────────────────────────┘              |
|   Edit the name and press Enter to re-assign, clear + Enter to un-assign |
```

Revisiting a discarded face:

```
|   [crop]   ┌──────────────────────────────────────────────┐              |
|            │                                              │ ⚠ discarded  |
|            └──────────────────────────────────────────────┘              |
|   Type a name and press Enter to restore and name this face              |
```

Empty queue:

```
+--------------------------------------------------------------------------+
|                         ✓ All faces are named                            |
|                       [ Back to People ]                                 |
+--------------------------------------------------------------------------+
```

In RTL the arrows swap: ← skips, → goes back; the button row is mirrored.

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-093-01 | Type an existing name, Enter → face assigned to that person, next face shown, box focused. |
| S-093-02 | Type a name with no match, Enter → new person created, face assigned; the person is selectable for the next face. Typing an existing name in another case reuses that person. |
| S-093-03 | Skip with → (← in RTL) → face unchanged, next face shown. |
| S-093-04 | Go back to a named face, change the name, Enter → face re-assigned. |
| S-093-05 | Backspace or Space on an empty box → face dismissed (`is_dismissed`), next face shown. |
| S-093-06 | Go back to a discarded face, type a name, Enter → face restored and assigned. |
| S-093-07 | Queue exhausted → "All faces are named" state. |
| S-093-08 | User without assign rights → entry buttons hidden; direct URL shows the forbidden state. |
| S-093-09 | Queue fails to load → error state with retry. |
| S-093-10 | Opened from a cluster card on the review page → only faces of that cluster are queued. |
| S-093-11 | Queue capped (`is_truncated`) → at the end of the list the view refetches; already handled faces no longer appear. |
| S-093-12 | Edited text in the box + arrow keys → caret moves, no navigation. |
| S-093-13 | Revisit a named face, box unchanged → arrows navigate; Backspace clears the whole box in one press; a second Backspace discards. |
| S-093-14 | Assign fails after the optimistic advance → toast with "Go back"; the face shows a failed badge and keeps its typed name. |
| S-093-15 | Click "?" → keybindings help lists the quick naming keys; closing it returns focus to the name box. |
| S-093-16 | Photo of a queued NSFW face that is in no album, or another user's unsorted photo for an admin → image displays through API-093-05. |
| S-093-17 | Next face has a suggestion → greyed-out name, Enter accepts; a run of faces of the same cluster is named with Enter after the first one. |
| S-093-18 | Type "Ali" while "Alice" exists → Enter assigns Alice; ↓ to "Create «Ali»" + Enter, or Shift+Enter, creates "Ali". |
| S-093-19 | Discard times out but the server applied it; retry → face stays dismissed. |
| S-093-20 | Two persons named "John Smith" exist; type "John Smith" → both listed with crop and face count, the suggested one first; Enter assigns the highlighted one; "Create another «John Smith»" or Shift+Enter creates a third. |

## Test Strategy
- **REST API (v3):** in the `AssistedVision` suite, using `Tests\Feature_v3\Base\BaseApiWithDataTest::getJsonV3()`. `tests/AssistedVision/Face/FaceQueueTest.php`: guest 401, struct-of-array flag off 403, AI Vision off 403, `restricted` user 403, ordering (clusters grouped, largest first, confidence desc, unclustered last), `cluster_label` filter and 422, assigned and dismissed faces excluded, cap and `is_truncated`, visibility (admin all, user own photos), suggestions (highest confidence, only assigned and visible persons, non-searchable person hidden from a regular user, null when none), no `album_ids` field, one query (`DB::listen`). `tests/AssistedVision/People/PeopleListV3Test.php`: flag off 403, `privacy-preserving` user 403, guest allowed in `public` mode, admin all, user searchable + own, name order, one query.
- **REST API (v2):** `tests/AssistedVision/Face/FaceDismissTest.php` gains cases for the explicit `is_dismissed` value: `true` on a dismissed face stays dismissed, `false` restores, absent still toggles, non-boolean → 422. Assign is reused unchanged.
- **REST API (v3, binary):** `tests/AssistedVision/Face/FacePhotoAssetTest.php`: owner 200, admin 200, other user 403, guest 401, flag off 403, unknown face 404, variant fallback, temporary-link MAC required when enabled; `PhotoAssetTest` regression after the helper extraction.
- **Frontend (v8):** `npm run check`; manual run on a scratch instance in LTR and RTL.
- **Docs/Contracts:** regenerate TypeScript types (`php artisan typescript:transform`) for `FaceQueueResource`, `PeopleListResource` and the new `ModulesRightsResource` field.

## Interface & Contract Catalogue

### API Routes / Services
| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-093-01 | REST POST /api/v2/Face/{id}/assign | Assign / create / un-assign (existing). | `AssignFaceRequest`. |
| API-093-02 | REST PATCH /api/v2/Face/{id} | Toggle `is_dismissed` (existing). New optional boolean `is_dismissed`: when given, the face is set to that value (idempotent); when absent, the endpoint keeps toggling. Setting `true` also clears `person_id`, as the toggle does. | `ToggleDismissedRequest`; the view always sends the value. |
| API-093-03 | REST GET /api/v3/Faces/queue[?cluster_label=N] | SoA queue of faces to name: `ids`, `photo_ids`, `xs`, `ys`, `widths`, `heights`, `crop_urls` (nullable), `cluster_labels` (nullable), `suggested_person_ids` (nullable), `suggested_person_names` (nullable), `is_truncated`. | New `FaceQueueController`, `GetFaceQueueRequest`, `QueryFaceQueue` action, `FaceQueueResource`. |
| API-093-04 | REST GET /api/v3/People | SoA list of persons the user may pick: `ids`, `names`, `face_counts`, `crop_urls` (nullable, representative face). Whole-scope, unpaginated (ADR-069-01 strategy 1): persons are few and the payload is small. Authorised like `GET /api/v2/People` (`AiVisionPolicy::CAN_VIEW_PEOPLE`, so guests may read it in `public` mode) plus the struct-of-array flag. Non-admins see searchable persons and their own person, as `GET /api/v2/People` does. Ordered by name. | New `PeopleListController`, `GetPeopleListRequest`, `PeopleListResource`. |
| API-093-05 | REST GET /api/v3/Faces/{face_id}/photo/{variant} | Binary size variant of the photo carrying the face; `variant` ∈ `medium`, `small2x`, `small`, `thumb` (`FacePhotoAssetType`), resolved to the first existing row walking down medium → small2x → small → thumb (medium is allowed here, unlike `/Asset`, because only the owner or an admin passes); admin or photo owner; temporary-link MAC as on `/Asset`; same fallback, S3 redirect and watermark as `PhotoAssetController` (shared helper). | ADR-093-01. New `FacePhotoAssetController`, `GetFacePhotoAssetRequest`. |

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-093-01 | Setting `ai_vision_face_queue_limit` (integer, default 500, min 1), category AI Vision, expert setting, requires `ai_vision_enabled` and `ai_vision_face_enabled`. | settings migration, `all_settings.php` |

### UI States
| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-093-01 | Naming | A queued face is shown, box focused and empty. |
| UI-093-02 | Revisit named | Back to a named face; box pre-filled. |
| UI-093-03 | Revisit discarded | Back to a discarded face; discarded badge. |
| UI-093-04 | Done | Queue exhausted. |
| UI-093-05 | Error | Queue failed to load; retry button. |
| UI-093-06 | Failed face | Revisited face whose request failed; red badge, typed name kept. |
| UI-093-07 | Help | "?" opened the keybindings help. |

## Telemetry & Observability
No new telemetry.

## Documentation Deliverables
- Roadmap row for 093.
- Knowledge map: new view, `GET /api/v3/Faces/queue`, `GET /api/v3/People`, `GET /api/v3/Faces/{face_id}/photo/{variant}`.
- ADR-093-01 (face-scoped photo asset route).

## Fixtures & Sample Data
Existing face/person factories from Feature 030 tests.

## Spec DSL

```
routes:
  - id: API-093-01
    method: POST
    path: /api/v2/Face/{id}/assign
  - id: API-093-02
    method: PATCH
    path: /api/v2/Face/{id}
  - id: API-093-03
    method: GET
    path: /api/v3/Faces/queue
  - id: API-093-04
    method: GET
    path: /api/v3/People
  - id: API-093-05
    method: GET
    path: /api/v3/Faces/{face_id}/photo/{variant}
domain_objects:
  - id: DO-093-01
    name: ai_vision_face_queue_limit
    type: integer
    constraints: ">= 1, default 500"
ui_states:
  - id: UI-093-01
    description: Naming
  - id: UI-093-02
    description: Revisit named
  - id: UI-093-03
    description: Revisit discarded
  - id: UI-093-04
    description: Done
  - id: UI-093-05
    description: Error
  - id: UI-093-06
    description: Failed face
  - id: UI-093-07
    description: Help
```
