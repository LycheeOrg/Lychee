# Feature 031 – Configurable Webhooks

| Field | Value |
|-------|-------|
| Status | Draft (questions Q-031-01 – Q-031-07 resolved) |
| Last updated | 2026-03-25 |
| Owners | LycheeOrg |
| Linked plan | `docs/specs/4-architecture/features/031-configurable-webhooks/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/031-configurable-webhooks/tasks.md` |
| Roadmap entry | #31 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

This feature introduces configurable outgoing webhooks that allow administrators to register HTTP endpoints to be notified when specific photo lifecycle events occur (Add, Move, Delete). Each webhook is defined per-event and per-HTTP-method, with optional HMAC-style secret authentication delivered via a configurable header. Only administrators may create, update, or delete webhook configurations. Payload fields (photo ID, album ID, title, size-variant URLs) are selectable per webhook. No payload aggregation occurs — one HTTP request is fired per photo per event.

Affected modules: `core` (Webhook model, domain events), `application` (WebhookDispatch job, listener), `REST` (admin CRUD API), `UI` (admin management page).

## Goals

1. Administrators can create, list, update, and delete webhook configurations via the admin UI and REST API.
2. On each photo Add, Move, or Delete event, all matching active webhooks fire independently — one request per photo.
3. Each webhook supports configurable HTTP method (GET, POST, PUT, PATCH, DELETE), target URL, optional secret key, and configurable secret-header name.
4. Administrators can choose which payload fields are included per webhook: `photo_id`, `album_id`, `title`, and any subset of the nine `SizeVariantType` URLs as a JSON array.
5. Webhook dispatch is non-blocking (queued asynchronously via Laravel Queue).
6. Failed dispatches are logged at ERROR level and discarded — no automatic retry (resolved: NFR-031-04, Q-031-04 → A).

## Non-Goals

- Webhooks for album-level events (album create, delete, update) — photo events only.
- Receiving/consuming inbound webhooks.
- Webhook signature verification on the Lychee side (Lychee sends signatures, does not receive them).
- Per-user webhook configurations — admin-only management.
- Delivery retries — failed dispatches are discarded after one attempt (Q-031-04 → A).
- Batching/aggregating multiple photos into a single webhook call.
- Real-time webhook delivery status dashboard in the UI.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-031-01 | Admin can create a webhook configuration with: `name` (human label), `url`, `method` (GET/POST/PUT/PATCH/DELETE), `event` (photo.add / photo.move / photo.delete), `payload_format` (`json` or `query_string`), optional `secret`, optional `secret_header` (header name carrying the secret), `enabled` flag, and a set of `payload_fields` flags (`send_photo_id`, `send_album_id`, `send_title`, `send_size_variants`, `size_variant_types[]`). | Webhook record persisted; admin sees it in list. | `url` must be a valid HTTP or HTTPS URL; plain HTTP is accepted but the admin UI displays a security warning (Q-031-01 → A). `method` must be one of the allowed enum values. `event` must be one of three allowed values. `payload_format` is required and must be `json` or `query_string`. `secret_header` must be a valid HTTP header name when `secret` is provided; `name` is required and ≤255 chars. | Return 422 with field-level validation errors. | — | Problem statement |
| FR-031-02 | Admin can list all configured webhooks. The list response includes `has_secret` (boolean) instead of the raw `secret` value. | Paginated list returned. | None (read-only). | — | — | Problem statement; Q-031-07 → A |
| FR-031-03 | Admin can update any field of an existing webhook. | Webhook record updated. | Same validation as FR-031-01. | Return 422 or 404 (webhook not found). | — | Problem statement |
| FR-031-04 | Admin can delete a webhook configuration (hard delete — no soft-delete). | Webhook record permanently removed; no further dispatches fired. | None. | Return 404 if webhook does not exist. | — | Problem statement; Q-031-03 → A |
| FR-031-05 | Admin can enable or disable a webhook without deleting it (`enabled` toggle). | `enabled` flag toggled; disabled webhooks are skipped during dispatch. | — | — | — | Problem statement |
| FR-031-06 | When a photo is **added** to Lychee (via upload, import, or duplication), the application fires the new `PhotoAdded` domain event once per photo. All `enabled` webhooks with `event = photo.add` fire once per photo, independently, in response. | Each webhook receives a HTTP request containing the configured payload fields. | Photo must exist before dispatch is queued. | If the HTTP request fails, the failure is logged at ERROR level (NFR-031-04). Dispatch does not block the photo-add flow. | Log entry per webhook dispatch attempt. | Problem statement; Q-031-05 → C |
| FR-031-07 | When a photo is **moved** between albums, the application fires the new `PhotoMoved` domain event once per photo. All `enabled` webhooks with `event = photo.move` fire once per photo. | Same as FR-031-06. | — | Same as FR-031-06. | — | Problem statement; Q-031-05 → C |
| FR-031-08 | When a photo is **deleted**, the `Delete` action fires the new `PhotoWillBeDeleted` domain event per photo, **before** the photo record is removed from the database, carrying the full photo snapshot (photo_id, album_id, title, size-variant URLs). All `enabled` webhooks with `event = photo.delete` fire once per photo using this snapshot. | Same as FR-031-06. | The event must carry full photo data captured before DB deletion; the `Delete` action is responsible for loading and serializing this data. | Same as FR-031-06. | — | Problem statement; Q-031-06 → D |
| FR-031-09 | The outgoing webhook request delivers only the fields selected in the webhook's `payload_fields` configuration: `photo_id` (string), `album_id` (string), `title` (string), `size_variants` (see below). The delivery format is governed by `payload_format`: **`json`** — payload sent as a JSON request body (`Content-Type: application/json`) with `size_variants` as a JSON array of `{type, url}` objects; **`query_string`** — scalar fields (`photo_id`, `album_id`, `title`) sent as plain query params; size variants sent as flat named params `size_variant_{type}=<base64(url)>` where the URL is standard base64-encoded (e.g. `size_variant_original=aHR0cHM6Ly9...`). Base64 encoding prevents any URL-encoding ambiguity for S3/CDN URLs. (Q-031-08 → base64). | Payload contains only requested fields in the configured format. | Fields not selected are absent from payload. | — | — | Problem statement; Q-031-02; Q-031-08 |
| FR-031-10 | When `secret` is configured on a webhook, Lychee sets the `secret_header` HTTP header (default: `X-Webhook-Secret`) to the raw secret value on every outgoing request. | Header is present with correct value. | If `secret` is configured but `secret_header` is empty, default to `X-Webhook-Secret`. | — | — | Problem statement |
| FR-031-11 | `size_variant_types[]` specifies which of the nine `SizeVariantType` values (RAW, ORIGINAL, MEDIUM2X, MEDIUM, SMALL2X, SMALL, THUMB2X, THUMB, PLACEHOLDER) should have their URL included in the `size_variants` array. Only variants that exist for the photo are included; missing variants are omitted silently. | Array contains `{type, url}` objects for available variants matching the selection. | — | — | — | Problem statement |
| FR-031-12 | Only administrators (`is_admin = true`) may call the webhook CRUD API endpoints. Non-admin requests receive 403. | — | Middleware/policy check on every webhook CRUD route. | Return 403. | — | Problem statement |
| FR-031-13 | Webhook dispatch does not aggregate: if 10 photos are deleted in a bulk operation, 10 separate webhook calls are made (one per photo per matching webhook). | — | — | — | — | Problem statement |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-031-01 | Webhook dispatch is fully asynchronous. Photo operations (add, move, delete) must not block waiting for HTTP responses. | User experience — photo operations must remain fast. | Photo add/move/delete response times unaffected by webhook configuration. | Laravel Queue | Problem statement |
| NFR-031-02 | Webhook configurations are hard-deleted (no soft-delete). The `enabled` flag provides sufficient protection against accidental triggering. | Persistence | Webhook record is permanently removed on delete. All CRUD operations survive application restarts. | Eloquent Model, migrations | Q-031-03 → A |
| NFR-031-03 | Secret values must be stored encrypted in the database using Laravel's `encrypted` cast. | Security — secrets must not be stored in plaintext. | PHPStan static analysis; encrypted cast applied on model. | Laravel encryption (`APP_KEY`) | Security best practice |
| NFR-031-04 | Failed webhook dispatches (non-2xx response, network error, timeout) are logged at ERROR level and discarded. No automatic retry. Operators can use `php artisan lychee:webhook-test` to verify connectivity manually. | Observability / simplicity | Log entry visible in `storage/logs/laravel.log`. | Laravel Logging | Q-031-04 → A |
| NFR-031-05 | Outgoing webhook requests carry a `User-Agent: Lychee/<version>` header on every call. | Identification | Verified in feature tests with HTTP fake. | Laravel HTTP client | Operational convention |
| NFR-031-06 | Webhook URL validation allows both HTTP and HTTPS. When an HTTP (non-HTTPS) URL is saved, the admin UI displays a visible security warning ("Plain HTTP transmits secrets in cleartext"). HTTPS is not enforced server-side. | Security UX — warns operators without blocking self-hosted HTTP environments. | UI review; no backend enforcement needed. | Vue 3 admin UI | Q-031-01 → A |
| NFR-031-07 | The admin Webhooks management page must be accessible via the existing Lychee admin panel navigation, consistent with the look and feel of other admin pages (PrimeVue components). | Consistency | Visual review against Settings and Maintenance pages. | PrimeVue, Vue 3 | Problem statement |
| NFR-031-08 | Webhook dispatch timeout is configurable via a Lychee `configs` table entry `webhook_timeout_seconds` (default: 10 seconds). | Resilience — prevents runaway HTTP connections from stalling queue workers. | Feature test asserts timeout value forwarded to HTTP client. | Laravel HTTP client, configs table | Problem statement |

## UI / Interaction Mock-ups

### Admin Webhooks Management Page

```
┌──────────────────────────────────────────────────────────────────────────────┐
│ Admin › Webhooks                                              [+ Add Webhook] │
├──────────────────────────────────────────────────────────────────────────────┤
│  Name            │ Event        │ Method │ URL               │ Enabled │ ·  │
│──────────────────┼──────────────┼────────┼───────────────────┼─────────┼────│
│  My Slack Hook   │ photo.add    │ POST   │ https://slack.com │   ✓     │ ✎ 🗑│
│  Delete Notifier │ photo.delete │ POST   │ https://myapp.io  │   ✓     │ ✎ 🗑│
│  (empty state)   │              │        │                   │         │    │
└──────────────────────────────────────────────────────────────────────────────┘
```

### Add / Edit Webhook Modal / Drawer

```
┌────────────────────────────────────────────────┐
│ Add Webhook                              [×]    │
├────────────────────────────────────────────────┤
│ Name *          [____________________________]  │
│ Event *         [photo.add            ▼]        │
│ Method *        [POST                 ▼]        │
│ URL *           [https://example.com/hook]      │
│                 ⚠ Plain HTTP sends secrets      │
│                   in cleartext                  │
│ Payload format* [JSON body            ▼]        │
│                 (JSON body | Query string)       │
│ Enabled         [✓]                             │
├─ Authentication ───────────────────────────────┤
│ Secret key      [____________________________]  │
│ Secret header   [X-Webhook-Secret           ]   │
├─ Payload fields ───────────────────────────────┤
│ [✓] Send photo ID                               │
│ [✓] Send album ID                               │
│ [✓] Send title                                  │
│ [✓] Send size variant URLs                      │
│     Size variants:                              │
│     [✓] original  [✓] medium  [ ] small         │
│     [ ] thumb     [ ] thumb2x [ ] small2x       │
│     [ ] medium2x  [ ] raw     [ ] placeholder   │
├────────────────────────────────────────────────┤
│                        [Cancel]  [Save Webhook] │
└────────────────────────────────────────────────┘
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-031-01 | Admin creates a webhook with all required fields → record persisted, returned in list. |
| S-031-02 | Admin creates a webhook with missing `url` → 422 with validation error on `url`. |
| S-031-03 | Admin creates a webhook with invalid `method` → 422. |
| S-031-04 | Admin creates a webhook with invalid `event` → 422. |
| S-031-05 | Admin updates an existing webhook → changes reflected. |
| S-031-06 | Admin deletes a webhook → record removed, no further dispatches. |
| S-031-07 | Admin disables a webhook → subsequent photo events do not trigger it. |
| S-031-08 | Non-admin user calls webhook CRUD endpoint → 403. |
| S-031-09 | Photo added (via `PhotoAdded` event) → enabled `photo.add` webhooks fire once per photo; payload contains selected fields only. |
| S-031-10 | Photo moved (via `PhotoMoved` event) → enabled `photo.move` webhooks fire once per photo. |
| S-031-11 | Photo deleted (via `PhotoWillBeDeleted` event) → enabled `photo.delete` webhooks fire once per photo with pre-deletion data. |
| S-031-12 | Bulk delete of N photos → N `PhotoWillBeDeleted` events → N × (number of matching webhooks) HTTP calls fired. |
| S-031-13 | Webhook with `secret` configured → outgoing request carries `secret_header` set to secret value. |
| S-031-14 | Webhook with `send_size_variants = true` and `size_variant_types = [original, medium]` → `size_variants` array contains only `original` and `medium` entries (skipping absent variants). |
| S-031-15 | Webhook with `payload_format = query_string` and `send_size_variants = true` → size variants sent as `size_variant_{type}=<base64(url)>` query params (e.g. `size_variant_original=aHR0cHM6Ly9...`); scalar fields sent as plain query params. |
| S-031-16 | Webhook HTTP request returns non-2xx → error logged; photo operation unaffected. |
| S-031-17 | Webhook HTTP request times out (exceeds `webhook_timeout_seconds`) → error logged; photo operation unaffected. |
| S-031-18 | No enabled webhooks for an event → no HTTP calls fired; no overhead. |
| S-031-19 | Photo deleted via bulk operation (multiple photos) → one webhook call per photo (not aggregated). |
| S-031-20 | Webhook with `payload_format = json` and `method = GET` → JSON payload sent as request body (explicit user choice; documented in admin guide). |
| S-031-21 | Admin creates a webhook with a plain HTTP URL → record saved; admin UI displays HTTP security warning. |
| S-031-22 | List response for a webhook with a configured secret → `has_secret = true` returned; raw secret value absent from response. |

## Test Strategy

- **Core (Unit):** Model validation, `WebhookPayloadBuilder` unit tests covering all field combinations and size-variant filtering.
- **Application (Feature):** Laravel feature tests using `Http::fake()` to assert HTTP calls fired with correct method, URL, headers, and body for all three events. Tests for disabled webhooks being skipped. Tests for bulk-delete fan-out (S-031-12, S-031-19). Tests for timeout behaviour.
- **REST (Feature):** CRUD endpoint tests: create, read, update, delete, auth (FR-031-12), validation (S-031-02–S-031-04).
- **UI (Vue/Vitest):** Unit tests for the webhook management page component (list render, modal open/close, form validation display).
- **Docs/Contracts:** OpenAPI spec updated for new webhook CRUD routes.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-031-01 | `Webhook` Eloquent model: `id` (ulid), `name` (string, 255), `event` (enum: photo.add / photo.move / photo.delete), `method` (enum: GET / POST / PUT / PATCH / DELETE), `url` (string, 2048), `payload_format` (enum: json / query_string), `secret` (string, nullable, encrypted — never exposed in API responses), `secret_header` (string, nullable, default `X-Webhook-Secret`), `enabled` (boolean, default true), `send_photo_id` (boolean), `send_album_id` (boolean), `send_title` (boolean), `send_size_variants` (boolean), `size_variant_types` (JSON array of SizeVariantType names, nullable) — timestamps. Hard-deleted (no `deleted_at`). API responses expose `has_secret` (boolean) instead of the raw secret. | core, application, REST |
| DO-031-02 | `WebhookPayload` DTO: built per webhook configuration from a Photo model snapshot. Fields: `photo_id?`, `album_id?`, `title?`, `size_variants?` (array of `{type: string, url: string}`). | application |
| DO-031-03 | `PhotoWebhookEvent` enum: `photo.add`, `photo.move`, `photo.delete`. Used as the `event` column value and as the event type key when dispatching. Three new Laravel events are introduced: `PhotoAdded` (fired from `Create` action per photo), `PhotoMoved` (fired from `MoveOrDuplicate` action per photo when albums differ), and `PhotoWillBeDeleted` (fired from `Delete` action per photo **before** DB deletion, carrying photo_id, album_id, title, and size-variant URLs as a serialized snapshot). | core |
| DO-031-04 | `WebhookDispatchJob` queued job: accepts one `Webhook` model and one `WebhookPayload` DTO; fires the HTTP request using `payload_format` to determine whether to send a JSON body or query-string parameters; logs failure on non-2xx or exception (no retry). | application |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-031-01 | `GET /api/v2/Webhook` | List all webhook configurations (admin only). | Returns paginated list of webhook records. |
| API-031-02 | `POST /api/v2/Webhook` | Create a new webhook configuration (admin only). | Request body: all DO-031-01 fields except `id` and timestamps. |
| API-031-03 | `GET /api/v2/Webhook/{id}` | Get a single webhook configuration (admin only). | 404 if not found. |
| API-031-04 | `PUT /api/v2/Webhook/{id}` | Full update of a webhook configuration (admin only). | Same validation as create. |
| API-031-05 | `PATCH /api/v2/Webhook/{id}` | Partial update — notably used for `enabled` toggle (admin only). | Validates only supplied fields. |
| API-031-06 | `DELETE /api/v2/Webhook/{id}` | Delete a webhook configuration (admin only). | Hard delete. |

### CLI Commands / Flags

| ID | Command | Behaviour |
|----|---------|-----------|
| CLI-031-01 | `php artisan lychee:webhook-test {id}` | Sends a synthetic test payload to the specified webhook and reports success/failure. Useful for verifying connectivity during setup. |

### Telemetry Events

| ID | Event name | Fields / Redaction rules |
|----|-----------|---------------------------|
| TE-031-01 | `webhook.dispatch.success` | `webhook_id`, `event`, `method`, `status_code`. Secret value redacted. |
| TE-031-02 | `webhook.dispatch.failure` | `webhook_id`, `event`, `method`, `error` (message only). Secret value redacted. |

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-031-01 | Webhooks list (empty) | No webhooks configured → "No webhooks configured" empty-state message + prominent Add button. |
| UI-031-02 | Webhooks list (populated) | Webhooks present → table with name, event, method, URL, enabled toggle, edit and delete actions. |
| UI-031-03 | Add/Edit modal — valid | All required fields filled → Save button enabled. |
| UI-031-04 | Add/Edit modal — validation errors | Missing required field → inline error messages, Save button disabled or shows errors. |
| UI-031-05 | Size-variant checkboxes disabled | `send_size_variants` unchecked → individual size-variant checkboxes greyed out. |
| UI-031-06 | Secret header field | `secret` field empty → `secret_header` field greyed out (not applicable). |
| UI-031-07 | Delete confirmation | Admin clicks delete → confirmation dialog before removal. |
| UI-031-08 | HTTP URL warning | Admin enters a plain HTTP URL → inline warning: "Plain HTTP transmits your secret key in cleartext." Warning disappears when HTTPS URL is entered. |

## Telemetry & Observability

On each dispatch attempt Lychee writes a structured log entry at DEBUG level on success and ERROR level on failure. The `webhook_id`, `event`, `method`, and HTTP `status_code` (or exception class) are included. The `secret` value is never logged. A future metrics integration may consume `TE-031-01` / `TE-031-02` events.

## Documentation Deliverables

- `docs/specs/4-architecture/knowledge-map.md` — add Webhook module entry.
- Update OpenAPI spec with webhook CRUD routes and request/response schemas.
- Admin panel how-to guide: configuring webhooks, secret header usage, payload field selection.

## Fixtures & Sample Data

| ID | Path | Purpose |
|----|------|---------|
| FX-031-01 | `database/factories/WebhookFactory.php` | Generates test webhook records for feature tests. |

## Spec DSL

```yaml
domain_objects:
  - id: DO-031-01
    name: Webhook
    hard_delete: true
    fields:
      - name: id
        type: ulid (string)
      - name: name
        type: string
        constraints: "max:255, required"
      - name: event
        type: enum (photo.add | photo.move | photo.delete)
      - name: method
        type: enum (GET | POST | PUT | PATCH | DELETE)
      - name: url
        type: string
        constraints: "max:2048, required, url"
      - name: payload_format
        type: enum (json | query_string)
        constraints: "required, default: json"
      - name: secret
        type: string (nullable, encrypted, excluded from API response)
      - name: has_secret
        type: boolean (virtual, computed, returned in API response instead of secret)
      - name: secret_header
        type: string (nullable)
        constraints: "default: X-Webhook-Secret"
      - name: enabled
        type: boolean
        constraints: "default: true"
      - name: send_photo_id
        type: boolean
      - name: send_album_id
        type: boolean
      - name: send_title
        type: boolean
      - name: send_size_variants
        type: boolean
      - name: size_variant_types
        type: json (array of SizeVariantType names, nullable)

domain_events:
  - name: PhotoAdded
    fired_from: app/Actions/Photo/Pipes/Shared/SetParent.php (new photo records only)
    carries: photo_id
  - name: PhotoMoved
    fired_from: app/Actions/Photo/MoveOrDuplicate.php (when source != destination album)
    carries: photo_id, from_album_id, to_album_id
  - name: PhotoWillBeDeleted
    fired_from: app/Actions/Photo/Delete.php (before executeDelete(), per photo)
    carries: photo_id, album_id, title, size_variants (serialized snapshot)

routes:
  - id: API-031-01
    method: GET
    path: /api/v2/Webhook
    auth: admin
  - id: API-031-02
    method: POST
    path: /api/v2/Webhook
    auth: admin
  - id: API-031-03
    method: GET
    path: /api/v2/Webhook/{id}
    auth: admin
  - id: API-031-04
    method: PUT
    path: /api/v2/Webhook/{id}
    auth: admin
  - id: API-031-05
    method: PATCH
    path: /api/v2/Webhook/{id}
    auth: admin
  - id: API-031-06
    method: DELETE
    path: /api/v2/Webhook/{id}
    auth: admin

cli_commands:
  - id: CLI-031-01
    command: php artisan lychee:webhook-test {id}

telemetry_events:
  - id: TE-031-01
    event: webhook.dispatch.success
  - id: TE-031-02
    event: webhook.dispatch.failure

fixtures:
  - id: FX-031-01
    path: database/factories/WebhookFactory.php

ui_states:
  - id: UI-031-01
    description: Webhooks list empty state
  - id: UI-031-02
    description: Webhooks list populated
  - id: UI-031-03
    description: Add/Edit modal valid
  - id: UI-031-04
    description: Add/Edit modal validation errors
  - id: UI-031-05
    description: Size-variant checkboxes disabled when send_size_variants unchecked
  - id: UI-031-06
    description: Secret header field disabled when secret is empty
  - id: UI-031-07
    description: Delete confirmation dialog
  - id: UI-031-08
    description: HTTP URL security warning displayed when plain HTTP URL is entered
```

## Appendix

### Outgoing Webhook Payload Example (JSON format — POST)

```json
{
  "photo_id": "01HXYZ1234ABCDEFGHIJK",
  "album_id": "01HXYZ5678LMNOPQRSTU",
  "title": "Sunset over the mountains",
  "size_variants": [
    { "type": "original", "url": "https://example.com/uploads/original/photo.jpg" },
    { "type": "medium",   "url": "https://example.com/uploads/medium/photo.jpg" }
  ]
}
```

### Outgoing Webhook Payload Example (query_string format — GET)

```
GET /hook?photo_id=01HXYZ1234ABCDEFGHIJK&album_id=01HXYZ5678LMNOPQRSTU&title=Sunset+over+the+mountains&size_variant_original=aHR0cHM6Ly9leGFtcGxlLmNvbS91cGxvYWRzL29yaWdpbmFsL3Bob3RvLmpwZw==&size_variant_medium=aHR0cHM6Ly9leGFtcGxlLmNvbS91cGxvYWRzL21lZGl1bS9waG90by5qcGc= HTTP/1.1
```

> Size variant URLs are base64-encoded to avoid any URL-encoding ambiguity for S3/CDN URLs (Q-031-08 → base64). The param name pattern is `size_variant_{type}` where `{type}` is the lowercase SizeVariantType name (e.g. `original`, `medium`, `thumb`).

### Outgoing Webhook Request Headers Example

```
POST /hook HTTP/1.1
Host: example.com
Content-Type: application/json
User-Agent: Lychee/6.x
X-Webhook-Secret: my-secret-value
X-Lychee-Event: photo.add
```

### Relationship to Existing Events / New Events

The following **new** Lychee domain events are introduced by this feature (Q-031-05 → C, Q-031-06 → D):

| New event class | Fired from | Maps to webhook event | Carries |
|---|---|---|---|
| `PhotoAdded` | `app/Actions/Photo/Pipes/Shared/SetParent.php` (new records only) | `photo.add` | `photo_id` |
| `PhotoMoved` | `app/Actions/Photo/MoveOrDuplicate.php` (when albums differ) | `photo.move` | `photo_id`, `from_album_id`, `to_album_id` |
| `PhotoWillBeDeleted` | `app/Actions/Photo/Delete.php` (before `executeDelete()`, per photo) | `photo.delete` | Full photo snapshot: `photo_id`, `album_id`, `title`, `size_variants[]` |

The existing `PhotoSaved` and `PhotoDeleted` events remain unchanged and continue to serve their existing listeners (album stats recomputation, etc.). The webhook system subscribes to the three new events above, not to `PhotoSaved` / `PhotoDeleted`.
