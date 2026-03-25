# Feature 031 Tasks – Configurable Webhooks

_Status: Draft_
_Last updated: 2026-03-25_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`FR-`), non-goal IDs (`NFR-`), and scenario IDs (`S-031-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](../../open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/5-decisions/` reflect the clarified behaviour.

## Prerequisite

Resolve all open questions (Q-031-01 through Q-031-07) in `open-questions.md` before beginning implementation. Tasks that depend on unresolved questions are marked with ⚠️.

## Checklist

### I1 – Database Migration & Eloquent Model

- [ ] T-031-01 – Create `PhotoWebhookEvent` and `WebhookMethod` enums (FR-031-01, DO-031-03).
  _Intent:_ `app/Enum/PhotoWebhookEvent.php` backed by string: `Add = 'photo.add'`, `Move = 'photo.move'`, `Delete = 'photo.delete'`. `app/Enum/WebhookMethod.php` backed by string: GET, POST, PUT, PATCH, DELETE.
  _Verification commands:_
  - `make phpstan`
  - `vendor/bin/php-cs-fixer fix --dry-run`

- [ ] T-031-02 – Create `create_webhooks_table` migration (FR-031-01, NFR-031-02, DO-031-01).
  _Intent:_ ULID primary key, all columns per DO-031-01 (name, event, method, url, secret, secret_header, enabled, send_photo_id, send_album_id, send_title, send_size_variants, size_variant_types JSON, timestamps). Run migration and verify schema.
  _Verification commands:_
  - `php artisan migrate`
  - `php artisan migrate:status`

- [ ] T-031-03 – Create `Webhook` Eloquent model with casts and fillable (FR-031-01, NFR-031-03, DO-031-01).
  _Intent:_ `app/Models/Webhook.php`. Casts: `secret` → `encrypted`, `size_variant_types` → `array` (JSON), `event` → `PhotoWebhookEvent`, `method` → `WebhookMethod`, boolean columns → `boolean`. Fillable: all except `id`. Scope: `enabled()` filters to `enabled = true`.
  _Verification commands:_
  - `make phpstan`
  - `vendor/bin/php-cs-fixer fix --dry-run`

- [ ] T-031-04 – Create `WebhookFactory` and `WebhookResource` (DO-031-01, FX-031-01).
  _Intent:_ `database/factories/WebhookFactory.php` generates plausible test data. `app/Http/Resources/Models/WebhookResource.php` extends Spatie `Data`, exposes all fields safe for API response (secret excluded or masked — Q-031-07).
  _Verification commands:_
  - `php artisan test --filter=WebhookModelTest`
  - `make phpstan`

### I2 – Admin CRUD REST API

- [ ] T-031-05 – Write feature tests for webhook CRUD (before implementation) (S-031-01 through S-031-08, FR-031-12).
  _Intent:_ `tests/Feature/WebhookApiTest.php`. Tests: list returns 200 + array, create valid returns 201, update returns 200, patch enabled returns 200, delete returns 204, non-admin returns 403, invalid body returns 422. Use `WebhookFactory` to seed.
  _Verification commands:_
  - `php artisan test --filter=WebhookApiTest` (expected: RED before implementation)

- [ ] T-031-06 – Create `StoreWebhookRequest`, `UpdateWebhookRequest`, `PatchWebhookRequest` (FR-031-01, FR-031-03, S-031-02–S-031-04).
  _Intent:_ `app/Http/Requests/Webhook/` — three FormRequest classes. `StoreWebhookRequest`: requires name, url (valid URL), method (WebhookMethod), event (PhotoWebhookEvent), validates secret_header as valid header name when secret is present, validates size_variant_types values against SizeVariantType enum. `UpdateWebhookRequest`: same as Store. `PatchWebhookRequest`: all fields optional with same per-field rules.
  _Verification commands:_
  - `php artisan test --filter=WebhookApiTest`
  - `make phpstan`

- [ ] T-031-07 – Create `WebhookController` with all six CRUD methods (FR-031-01 through FR-031-06, API-031-01 through API-031-06).
  _Intent:_ `app/Http/Controllers/Admin/WebhookController.php`. Methods: `index` (paginated list), `store`, `show`, `update`, `patch`, `destroy`. Each method gates to admin via policy or middleware.
  _Verification commands:_
  - `php artisan test --filter=WebhookApiTest`
  - `make phpstan`

- [ ] T-031-08 – Register webhook routes in admin API routes file (API-031-01 through API-031-06).
  _Intent:_ Add routes in appropriate route file (e.g., `routes/api_v2.php`) under admin middleware group: `GET /api/v2/Webhook`, `POST /api/v2/Webhook`, `GET /api/v2/Webhook/{id}`, `PUT /api/v2/Webhook/{id}`, `PATCH /api/v2/Webhook/{id}`, `DELETE /api/v2/Webhook/{id}`.
  _Verification commands:_
  - `php artisan route:list --name=webhook`
  - `php artisan test --filter=WebhookApiTest`

### I3 – Webhook Dispatch Job & Payload Builder

- [ ] T-031-09 – Write feature tests for webhook dispatch (before implementation) (S-031-09 through S-031-19).
  _Intent:_ `tests/Feature/WebhookDispatchTest.php`. Uses `Http::fake()`. Tests: photo.add fires POST to URL with correct body; photo.move fires correctly; photo.delete fires correctly with pre-deletion data; disabled webhook not fired; secret header present; size-variant filtering; bulk delete fires N calls; GET method uses query params (⚠️ Q-031-02); non-2xx logs error; timeout logs error; no webhooks → no HTTP calls.
  _Verification commands:_
  - `php artisan test --filter=WebhookDispatchTest` (expected: RED before implementation)

- [ ] T-031-10 – Create `WebhookPayload` DTO and `WebhookPayloadBuilder` service (DO-031-02, FR-031-09, FR-031-11).
  _Intent:_ `app/DTO/WebhookPayload.php` — plain DTO with optional fields. `app/Services/WebhookPayloadBuilder.php` — accepts `Webhook` model + `Photo` model (or snapshot array for delete case), returns `WebhookPayload`. Applies `send_*` flags, filters size_variant_types, omits absent variants silently.
  _Verification commands:_
  - `php artisan test --filter=WebhookPayloadBuilderTest`
  - `make phpstan`

- [ ] T-031-11 – Create `WebhookDispatchJob` queued job (DO-031-04, NFR-031-01, NFR-031-04, NFR-031-05, NFR-031-08, TE-031-01, TE-031-02).
  _Intent:_ `app/Jobs/WebhookDispatchJob.php`. Implements `ShouldQueue`. Accepts `Webhook` + `WebhookPayload`. Fires outgoing HTTP request via `Http::withHeaders([...secret header...])->timeout($timeout)->send($method, $url, $payloadArray)`. Sets `User-Agent: Lychee/<version>` and `X-Lychee-Event: <event>` headers. On non-2xx or exception, logs at ERROR with redacted fields (TE-031-02). On success logs at DEBUG (TE-031-01). ⚠️ GET/DELETE payload delivery method pending Q-031-02.
  _Verification commands:_
  - `php artisan test --filter=WebhookDispatchTest`
  - `make phpstan`

- [ ] T-031-12 – Create `WebhookListener` and register in `EventServiceProvider` (FR-031-06 through FR-031-08, FR-031-13, S-031-09 through S-031-12).
  _Intent:_ `app/Listeners/WebhookListener.php`. Listens to `PhotoSaved` and `PhotoDeleted`. For `PhotoSaved`: determines if add or move (⚠️ Q-031-05); loads matching enabled webhooks; dispatches `WebhookDispatchJob` per webhook. For `PhotoDeleted`: captures pre-deletion snapshot (⚠️ Q-031-06); dispatches per webhook. Register in `EventServiceProvider::$listen`.
  _Verification commands:_
  - `php artisan test --filter=WebhookDispatchTest`
  - `php artisan test --filter=WebhookListenerTest`
  - `make phpstan`

### I4 – Artisan Test Command

- [ ] T-031-13 – Create `php artisan lychee:webhook-test` command (CLI-031-01).
  _Intent:_ `app/Console/Commands/WebhookTest.php`. Accepts `{id}` argument. Loads `Webhook` by ID (or fails with error). Builds synthetic `WebhookPayload` with sample data. Dispatches `WebhookDispatchJob` **synchronously** (not queued). Prints HTTP status or error. Exits 0 on 2xx, 1 on failure.
  _Verification commands:_
  - `php artisan lychee:webhook-test <id>`
  - `php artisan test --filter=WebhookTestCommandTest`

### I5 – Admin UI (Vue 3 / PrimeVue)

- [ ] T-031-14 – Create `webhook-service.ts` API client (API-031-01 through API-031-06).
  _Intent:_ `resources/js/services/webhook-service.ts`. Typed functions: `list()`, `create(data)`, `get(id)`, `update(id, data)`, `patch(id, data)`, `remove(id)`. Uses axios consistent with existing service files. Uses `.then()` instead of async/await (per project convention).
  _Verification commands:_
  - `npm run check`

- [ ] T-031-15 – Create `WebhooksView.vue` admin page with list table (UI-031-01, UI-031-02).
  _Intent:_ `resources/js/views/admin/WebhooksView.vue`. PrimeVue `DataTable` or equivalent. Columns: name, event, method, URL, enabled (toggle), actions (edit, delete). Empty-state message when no webhooks. Add-webhook button opens modal.
  _Verification commands:_
  - `npm run check`

- [ ] T-031-16 – Create `WebhookModal.vue` add/edit form (UI-031-03 through UI-031-06, FR-031-01).
  _Intent:_ `resources/js/components/admin/WebhookModal.vue`. Fields: name, event (dropdown), method (dropdown), URL, enabled (checkbox), secret, secret_header (disabled if secret empty), send_photo_id/send_album_id/send_title/send_size_variants checkboxes, size_variant_types checkboxes (disabled if send_size_variants unchecked). Client-side validation before submit. Emits `saved` event on success.
  _Verification commands:_
  - `npm run check`

- [ ] T-031-17 – Create `WebhookDeleteConfirm.vue` dialog and wire admin nav (UI-031-07).
  _Intent:_ `resources/js/components/admin/WebhookDeleteConfirm.vue`. PrimeVue `ConfirmDialog` or equivalent. Wire `WebhooksView` into admin navigation consistent with existing admin nav items.
  _Verification commands:_
  - `npm run check`

### I6 – Documentation & Knowledge-Map

- [ ] T-031-18 – Update `knowledge-map.md` and OpenAPI spec (FR-031-02, API-031-01 through API-031-06).
  _Intent:_ Add `Webhook` module entry to `docs/specs/4-architecture/knowledge-map.md`. Update or generate OpenAPI YAML/JSON with new routes and request/response schemas. Verify OpenAPI passes validation.
  _Verification commands:_
  - `php artisan route:list --name=webhook`

- [ ] T-031-19 – Add `webhook_timeout_seconds` to `configs` table seeder/migration (NFR-031-08).
  _Intent:_ Add `webhook_timeout_seconds` entry (default: 10) to the Lychee `configs` table seeder or migration consistent with how other configuration values are stored.
  _Verification commands:_
  - `php artisan migrate:fresh --seed`
  - `php artisan test --filter=WebhookDispatchTest`

## Notes / TODOs

- ⚠️ **Q-031-02 (GET/DELETE payload):** Until resolved, implement `GET` and `DELETE` as sending payload fields as query-string parameters. Update if resolved differently.
- ⚠️ **Q-031-05 (add vs. move discrimination):** Until resolved, use `wasRecentlyCreated` on the reloaded `Photo` model within the `PhotoSaved` listener to distinguish add from move.
- ⚠️ **Q-031-06 (photo data at delete time):** Until resolved, consider listening to the Eloquent `deleting` model event on `Photo` to capture the snapshot before hard deletion.
- ⚠️ **Q-031-07 (secret in API response):** Until resolved, exclude `secret` from `WebhookResource`; return a boolean `has_secret` flag instead.
- All tasks marked ⚠️ are blocked on the respective open question. Do not mark them `[x]` until the question is resolved and the spec updated.
