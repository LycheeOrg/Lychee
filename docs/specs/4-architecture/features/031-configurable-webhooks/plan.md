# Feature Plan 031 – Configurable Webhooks

_Linked specification:_ `docs/specs/4-architecture/features/031-configurable-webhooks/spec.md`
_Status:_ Draft
_Last updated:_ 2026-03-25

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

Allow Lychee administrators to register HTTP endpoints that receive real-time notifications whenever photos are added, moved, or deleted. No photo operation is delayed by webhook delivery. The feature is self-contained, admin-gated, and does not require changes to the existing photo pipeline beyond adding event listeners.

**Success signals:**
- `Webhook` model, migration, factory, and resource created and tested.
- Admin CRUD API (6 endpoints) passes feature tests including auth enforcement.
- `WebhookDispatchJob` dispatches correctly shaped HTTP requests with correct headers and body (JSON or query-string per `payload_format`), verified with `Http::fake()`.
- Three new events (`PhotoAdded`, `PhotoMoved`, `PhotoWillBeDeleted`) fire from the correct action classes; listener correctly fires dispatch jobs for all three photo events.
- Admin Vue page renders list, create/edit modal, and delete confirmation; wired to API.
- `php artisan lychee:webhook-test` sends a test payload.
- Open question Q-031-08 resolved before implementation of query-string size_variants encoding.

## Scope Alignment

- **In scope:**
  - `webhooks` database migration (includes `payload_format` column; hard delete — no `deleted_at`).
  - `Webhook` Eloquent model with encrypted `secret` cast, JSON `size_variant_types` cast, `payload_format` enum cast.
  - `WebhookFactory` for tests.
  - `WebhookResource` (Spatie Data) exposing `has_secret` boolean instead of raw secret.
  - Six admin REST endpoints (`GET/POST/GET/{id}/PUT/{id}/PATCH/{id}/DELETE/{id}`).
  - `StoreWebhookRequest` and `UpdateWebhookRequest` FormRequests (with `payload_format` validation).
  - `PhotoWebhookEvent` enum (photo.add / photo.move / photo.delete).
  - **New Laravel events:** `PhotoAdded`, `PhotoMoved`, `PhotoWillBeDeleted` (each fired from the relevant action class; existing `PhotoSaved`/`PhotoDeleted` remain unchanged).
  - `WebhookPayloadBuilder` service that constructs `WebhookPayload` DTO from a Photo snapshot + webhook config.
  - `WebhookDispatchJob` queued job — sends JSON body (`payload_format = json`) or URL query params (`payload_format = query_string`; no retry on failure).
  - `WebhookListener` that subscribes to `PhotoAdded`, `PhotoMoved`, `PhotoWillBeDeleted` and dispatches jobs.
  - `EventServiceProvider` update to register the listener and new events.
  - `php artisan lychee:webhook-test` Artisan command (CLI-031-01).
  - Admin Vue page: webhook list table, add/edit modal, enable/disable toggle, delete confirmation, HTTP-URL warning.
  - OpenAPI schema update.
  - Knowledge-map update.

- **Out of scope:**
  - Album-level webhooks.
  - Incoming webhook consumption.
  - Automatic delivery retry (Q-031-04).
  - Real-time delivery status dashboard.

## Dependencies & Interfaces

- **`PhotoAdded` event (new)** — fired from `app/Actions/Photo/Pipes/Shared/SetParent.php` for newly created photos.
- **`PhotoMoved` event (new)** — fired from `app/Actions/Photo/MoveOrDuplicate.php` when source and destination albums differ, per photo in the batch.
- **`PhotoWillBeDeleted` event (new)** — fired from `app/Actions/Photo/Delete.php` **before** `executeDelete()`, per photo scheduled for deletion, carrying a full photo data snapshot.
- **`PhotoSaved` / `PhotoDeleted` (existing)** — unchanged; continue to serve existing listeners (album stats recomputation etc.).
- **Laravel Queue** — `WebhookDispatchJob` dispatched to the default queue.
- **Laravel HTTP Client** — used in `WebhookDispatchJob` to fire outgoing requests with configurable timeout (`webhook_timeout_seconds` from `configs` table). Sends JSON body or query-string params depending on `payload_format`.
- **Lychee `configs` table** — stores `webhook_timeout_seconds` (default: 10).
- **`SizeVariantType` enum** — for `size_variant_types` field validation and payload construction.
- **PrimeVue / Vue 3** — admin UI components consistent with existing admin pages.
- **Spatie Laravel Data** — `WebhookResource` extends `Data` (per codebase convention); exposes `has_secret` boolean instead of raw secret.
- **Laravel encrypted cast** — `secret` field on `Webhook` model.

## Assumptions & Risks

- **Assumptions:**
  - The three new events (`PhotoAdded`, `PhotoMoved`, `PhotoWillBeDeleted`) can be injected into the existing action classes without breaking existing listeners.
  - `Delete.do()` iterates or collects photo data before calling `executeDelete()`, giving an integration point for `PhotoWillBeDeleted`.
  - Queue workers are configured and running in production deployments.

- **Risks / Mitigations:**
  - *`Delete.do()` is optimised for bulk efficiency:* Loading full photo models (with size variants) for `PhotoWillBeDeleted` may add a query overhead for bulk deletions. Mitigation: load only the fields needed for the snapshot (photo_id, album_id, title, size_variant URLs) with a targeted query, not full Eloquent hydration.
  - *Q-031-08 (size_variants in query string) unresolved:* `send_size_variants` with `payload_format = query_string` is blocked until Q-031-08 is answered. Mitigation: implement query-string mode without `size_variants` initially; add encoding once resolved.
  - *Webhook URL points to internal services (SSRF):* Plain HTTP is allowed; operators bear responsibility. Document risk in admin guide.
  - *Queue not configured:* Dispatch silently drops if queue driver is `sync` — document in admin guide.

## Implementation Drift Gate

After each increment, verify:

1. `php artisan test --filter=Webhook` — all webhook tests pass.
2. `make phpstan` — 0 PHPStan errors.
3. `vendor/bin/php-cs-fixer fix --dry-run` — 0 formatting violations.
4. `npm run check` (after frontend increments) — 0 TypeScript/Vue errors.

## Increment Map

### I1 – Database Migration, Enums & Eloquent Model (≈70 min)

- _Goal:_ Create the `webhooks` table, new Laravel events, enums, and the `Webhook` Eloquent model with all casts, factory, and resource.
- _Preconditions:_ Q-031-08 noted as open but does not block I1.
- _Steps:_
  1. Create `app/Enum/PhotoWebhookEvent.php` backed enum (string): `Add = 'photo.add'`, `Move = 'photo.move'`, `Delete = 'photo.delete'`.
  2. Create `app/Enum/WebhookMethod.php` backed enum (string): GET, POST, PUT, PATCH, DELETE.
  3. Create `app/Enum/WebhookPayloadFormat.php` backed enum (string): `Json = 'json'`, `QueryString = 'query_string'`.
  4. Create new Laravel events: `app/Events/PhotoAdded.php` (carries `photo_id`), `app/Events/PhotoMoved.php` (carries `photo_id`, `from_album_id`, `to_album_id`), `app/Events/PhotoWillBeDeleted.php` (carries snapshot: `photo_id`, `album_id`, `title`, `size_variants`).
  5. Create migration `create_webhooks_table` with columns per DO-031-01. ULID primary key, **no** `deleted_at` (hard delete).
  6. Create `app/Models/Webhook.php` with `encrypted` cast on `secret`, JSON cast on `size_variant_types`, enum casts on `event`, `method`, `payload_format`, boolean casts on flag fields. Scope: `enabled()` filters to `enabled = true`.
  7. Create `database/factories/WebhookFactory.php`.
  8. Create `app/Http/Resources/Models/WebhookResource.php` (Spatie Data) — exposes `has_secret` boolean, excludes raw `secret`.
  9. Run `php artisan migrate` and verify schema.
- _Commands:_ `php artisan migrate`, `php artisan test --filter=WebhookModelTest`
- _Exit:_ Migration runs cleanly; model CRUD works in tinker; factory generates valid records; WebhookResource returns `has_secret`.

### I2 – Admin CRUD REST API (≈75 min)

- _Goal:_ Implement the six webhook CRUD endpoints with admin auth enforcement and validation.
- _Preconditions:_ I1 complete.
- _Steps:_
  1. Create `app/Http/Requests/Webhook/StoreWebhookRequest.php` (validation per FR-031-01).
  2. Create `app/Http/Requests/Webhook/UpdateWebhookRequest.php` (full) and `PatchWebhookRequest.php` (partial).
  3. Create `app/Http/Controllers/Admin/WebhookController.php` with `index`, `store`, `show`, `update`, `patch`, `destroy` methods. Gate all methods to `is_admin`.
  4. Register routes in `routes/api_v2.php` (or dedicated file) under admin middleware.
  5. Write feature tests: list, create (valid + invalid), show, update, patch (enabled toggle), delete, auth enforcement (S-031-01 through S-031-08, FR-031-12).
- _Commands:_ `php artisan test --filter=WebhookApiTest`
- _Exit:_ All six endpoints work; validation errors return 422; non-admin returns 403.

### I3 – Webhook Dispatch Job & Payload Builder (≈80 min)

- _Goal:_ Implement `WebhookPayloadBuilder`, `WebhookDispatchJob`, and the `WebhookListener`.
- _Preconditions:_ I1 complete. Q-031-08 must be resolved before implementing `send_size_variants` in query-string mode.
- _Steps:_
  1. Create `app/DTO/WebhookPayload.php` DTO.
  2. Create `app/Services/WebhookPayloadBuilder.php`: accepts `Webhook` + photo snapshot data, returns `WebhookPayload` with only selected fields. Size-variant filtering per `size_variant_types`.
  3. Create `app/Jobs/WebhookDispatchJob.php`: Implements `ShouldQueue`. Reads `payload_format` to determine delivery: `json` → JSON request body with `Content-Type: application/json`; `query_string` → append payload as URL query params (note: `size_variants` excluded from query-string mode until Q-031-08 resolved). Sets `User-Agent: Lychee/<version>` and `X-Lychee-Event: <event>` headers. On non-2xx or exception, logs at ERROR (TE-031-02). On success logs at DEBUG (TE-031-01). No retry.
  4. Create `app/Listeners/WebhookListener.php`: subscribes to `PhotoAdded` (→ `photo.add`), `PhotoMoved` (→ `photo.move`), `PhotoWillBeDeleted` (→ `photo.delete`). Loads active webhooks matching event; dispatches `WebhookDispatchJob` per webhook.
  5. Modify `app/Actions/Photo/Pipes/Shared/SetParent.php`: fire `PhotoAdded` for new photo records (not updates).
  6. Modify `app/Actions/Photo/MoveOrDuplicate.php`: fire `PhotoMoved` per photo when `$from_album->get_id() !== $to_album->id`.
  7. Modify `app/Actions/Photo/Delete.php`: before `executeDelete()`, load photo snapshots for `$delete_photo_ids` and fire `PhotoWillBeDeleted` per photo.
  8. Register listener in `EventServiceProvider`.
  9. Write feature tests with `Http::fake()` for all three event types; assert payload shape, method, URL, headers (S-031-09 through S-031-20).
- _Commands:_ `php artisan test --filter=WebhookDispatchTest`
- _Exit:_ Correct HTTP calls fired for all scenarios; disabled webhooks skipped; secrets in header; `payload_format` governs body vs query params; size-variant filtering works.

### I4 – Artisan Test Command (≈30 min)

- _Goal:_ Implement `php artisan lychee:webhook-test {id}` (CLI-031-01).
- _Preconditions:_ I1, I3 complete.
- _Steps:_
  1. Create `app/Console/Commands/WebhookTest.php` Artisan command.
  2. Command loads webhook by ID, builds a synthetic payload, dispatches `WebhookDispatchJob` synchronously (not queued), and prints result.
  3. Write a simple unit test or integration test for the command.
- _Commands:_ `php artisan lychee:webhook-test <id>`, `php artisan test --filter=WebhookTestCommandTest`
- _Exit:_ Command prints success/failure; exits with correct status code.

### I5 – Admin UI (Vue 3 / PrimeVue) (≈90 min)

- _Goal:_ Build the admin Webhooks management page with list, create/edit modal, and delete confirmation.
- _Preconditions:_ I2 complete; API spec finalised.
- _Steps:_
  1. Create `resources/js/services/webhook-service.ts` with typed API calls for all six endpoints.
  2. Create `resources/js/views/admin/WebhooksView.vue`: table, empty state, enabled toggle, edit/delete actions.
  3. Create `resources/js/components/admin/WebhookModal.vue`: form with all fields including `payload_format` dropdown; size-variant checkboxes disabled when `send_size_variants` is unchecked; secret header disabled when secret is empty; **HTTP URL warning** shown when URL starts with `http://` (UI-031-08).
  4. Create `resources/js/components/admin/WebhookDeleteConfirm.vue`: confirmation dialog.
  5. Wire into admin navigation (add menu entry consistent with existing admin nav).
  6. Write Vitest unit tests for list render, modal validation display, checkbox disabling logic, HTTP URL warning.
- _Commands:_ `npm run check`, `npm run test` (Vitest)
- _Exit:_ Admin page renders; CRUD operations work end-to-end; UI states UI-031-01 through UI-031-08 verified visually.

### I6 – Documentation & Knowledge-Map Update (≈20 min)

- _Goal:_ Update knowledge map, OpenAPI spec, and add admin how-to documentation.
- _Preconditions:_ All increments complete.
- _Steps:_
  1. Add `Webhook` module entry to `docs/specs/4-architecture/knowledge-map.md`.
  2. Update OpenAPI spec (or equivalent) with webhook CRUD routes and schemas.
  3. Add admin panel how-to section: configuring webhooks, secret header usage, testing connectivity.
- _Exit:_ Knowledge-map updated; OpenAPI passes validation.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-031-01 | I2 / T-031-05 | Happy path create |
| S-031-02 | I2 / T-031-06 | Validation: missing URL |
| S-031-03 | I2 / T-031-06 | Validation: invalid method |
| S-031-04 | I2 / T-031-06 | Validation: invalid event |
| S-031-05 | I2 / T-031-07 | Update |
| S-031-06 | I2 / T-031-08 | Delete |
| S-031-07 | I2 / T-031-09 | Disable |
| S-031-08 | I2 / T-031-10 | Auth enforcement |
| S-031-09 | I3 / T-031-13 | photo.add dispatch via PhotoAdded event |
| S-031-10 | I3 / T-031-14 | photo.move dispatch via PhotoMoved event |
| S-031-11 | I3 / T-031-15 | photo.delete dispatch via PhotoWillBeDeleted event |
| S-031-12 | I3 / T-031-16 | Bulk delete fan-out |
| S-031-13 | I3 / T-031-17 | Secret header |
| S-031-14 | I3 / T-031-18 | Size-variant filtering |
| S-031-15 | I3 / T-031-19 | payload_format=query_string sends query params |
| S-031-16 | I3 / T-031-20 | Non-2xx logging |
| S-031-17 | I3 / T-031-20 | Timeout logging |
| S-031-18 | I3 / T-031-21 | No matching webhooks |
| S-031-19 | I3 / T-031-16 | Bulk delete non-aggregation |
| S-031-20 | I3 / T-031-19 | payload_format=json with GET method sends JSON body |
| S-031-21 | I2 / T-031-06 | HTTP URL accepted, saved without error |
| S-031-22 | I2 / T-031-05 | List response returns has_secret, not raw secret |

## Analysis Gate

_Completed 2026-03-25. Q-031-01 through Q-031-07 resolved. One remaining question (Q-031-08: `size_variants` query-string encoding) is open but does not block most increments — only `send_size_variants` in query-string mode is deferred._

## Exit Criteria

- [ ] All feature tests pass (`php artisan test --filter=Webhook`).
- [ ] PHPStan 0 errors (`make phpstan`).
- [ ] php-cs-fixer clean (`vendor/bin/php-cs-fixer fix --dry-run`).
- [ ] TypeScript/Vue build clean (`npm run check`).
- [ ] All scenario IDs (S-031-01 through S-031-22) covered by at least one test.
- [ ] OpenAPI spec updated and validated.
- [ ] Knowledge-map updated.
- [ ] `php artisan lychee:webhook-test` command functional.
- [ ] Q-031-08 resolved and `size_variants` query-string encoding implemented.
- [ ] Three new events (`PhotoAdded`, `PhotoMoved`, `PhotoWillBeDeleted`) fire correctly from action classes.

## Follow-ups / Backlog

- Delivery retry policy with exponential back-off (Q-031-04).
- Webhook delivery log table (admin can see recent dispatch history and HTTP status codes).
- HMAC-SHA256 signing instead of raw secret header.
- Per-album webhook scoping (only fire for photos in a specific album).
- Album-level events (album create/update/delete).
