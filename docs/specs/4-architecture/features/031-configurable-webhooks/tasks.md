# Feature 031 Tasks – Configurable Webhooks

_Status: Draft_
_Last updated: 2026-03-25_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions.
> All open questions (Q-031-01 through Q-031-08) are now resolved — implementation can proceed fully.

## Checklist

### I1 – Database Migration, Enums & Eloquent Model

- [x] T-031-01 – Create `PhotoWebhookEvent`, `WebhookMethod`, and `WebhookPayloadFormat` enums (FR-031-01, DO-031-03).
  _Intent:_ `app/Enum/PhotoWebhookEvent.php` backed by string: `Add = 'photo.add'`, `Move = 'photo.move'`, `Delete = 'photo.delete'`. `app/Enum/WebhookMethod.php` backed by string: GET, POST, PUT, PATCH, DELETE. `app/Enum/WebhookPayloadFormat.php` backed by string: `Json = 'json'`, `QueryString = 'query_string'`.
  _Verification commands:_
  - `make phpstan`
  - `vendor/bin/php-cs-fixer fix --dry-run`

- [x] T-031-02 – Create new domain events: `PhotoAdded`, `PhotoMoved`, `PhotoWillBeDeleted` (FR-031-06, FR-031-07, FR-031-08, DO-031-03; Q-031-05 → C, Q-031-06 → D).
  _Intent:_
  - `app/Events/PhotoAdded.php` — carries `public string $photo_id`.
  - `app/Events/PhotoMoved.php` — carries `public string $photo_id`, `public string $from_album_id`, `public string $to_album_id`.
  - `app/Events/PhotoWillBeDeleted.php` — carries a photo snapshot: `public string $photo_id`, `public string $album_id`, `public string $title`, `public array $size_variants` (array of `['type' => string, 'url' => string]`). This event is fired **before** DB deletion.
  _Verification commands:_
  - `make phpstan`
  - `vendor/bin/php-cs-fixer fix --dry-run`

- [x] T-031-03 – Create `create_webhooks_table` migration (FR-031-01, NFR-031-02, DO-031-01; Q-031-03 → A hard delete).
  _Intent:_ ULID primary key, columns: name (string 255), event (string — PhotoWebhookEvent), method (string — WebhookMethod), url (string 2048), payload_format (string — WebhookPayloadFormat, default 'json'), secret (text nullable), secret_header (string nullable), enabled (boolean default true), send_photo_id (boolean), send_album_id (boolean), send_title (boolean), send_size_variants (boolean), size_variant_types (json nullable), timestamps. **No** `deleted_at` column.
  _Verification commands:_
  - `php artisan migrate`
  - `php artisan migrate:status`

- [x] T-031-04 – Create `Webhook` Eloquent model with casts and fillable (FR-031-01, NFR-031-03, DO-031-01).
  _Intent:_ `app/Models/Webhook.php`. Casts: `secret` → `encrypted`, `size_variant_types` → `array` (JSON), `event` → `PhotoWebhookEvent`, `method` → `WebhookMethod`, `payload_format` → `WebhookPayloadFormat`, boolean columns → `boolean`. Fillable: all except `id`. Scope: `enabled()` filters to `enabled = true`.
  _Verification commands:_
  - `make phpstan`
  - `vendor/bin/php-cs-fixer fix --dry-run`

- [x] T-031-05 – Create `WebhookFactory` and `WebhookResource` (DO-031-01, FX-031-01; Q-031-07 → A secret excluded).
  _Intent:_ `database/factories/WebhookFactory.php` generates plausible test data including both `payload_format` values. `app/Http/Resources/Models/WebhookResource.php` extends Spatie `Data`, exposes all fields **except** `secret`; exposes `has_secret` (boolean: `$this->secret !== null`) instead. Scenario S-031-22.
  _Verification commands:_
  - `php artisan test --filter=WebhookModelTest`
  - `make phpstan`

### I2 – Admin CRUD REST API

- [ ] T-031-06 – Write feature tests for webhook CRUD (before implementation) (S-031-01 through S-031-08, S-031-21, S-031-22, FR-031-12).
  _Intent:_ `tests/Feature/WebhookApiTest.php`. Tests: list returns 200 + `has_secret` (not raw secret); create valid returns 201 including `payload_format`; update returns 200; patch enabled returns 200; delete returns 204 (hard delete verified); non-admin returns 403; invalid body returns 422; HTTP URL accepted (S-031-21); `has_secret` = true when secret present (S-031-22). Use `WebhookFactory` to seed.
  _Verification commands:_
  - `php artisan test --filter=WebhookApiTest` (expected: RED before implementation)

- [x] T-031-07 – Create `StoreWebhookRequest`, `UpdateWebhookRequest`, `PatchWebhookRequest` (FR-031-01, FR-031-03, S-031-02–S-031-04, S-031-21; Q-031-01 → A).
  _Intent:_ `app/Http/Requests/Webhook/` — three FormRequest classes. `StoreWebhookRequest`: requires name, url (valid URL — HTTP allowed, no HTTPS enforcement), method (WebhookMethod), event (PhotoWebhookEvent), payload_format (WebhookPayloadFormat), validates secret_header as valid header name when secret is present, validates size_variant_types values against SizeVariantType enum. `UpdateWebhookRequest`: same as Store. `PatchWebhookRequest`: all fields optional with same per-field rules.
  _Verification commands:_
  - `php artisan test --filter=WebhookApiTest`
  - `make phpstan`

- [x] T-031-08 – Create `WebhookController` with all six CRUD methods (FR-031-01 through FR-031-06, API-031-01 through API-031-06; Q-031-03 → A hard delete).
  _Intent:_ `app/Http/Controllers/Admin/WebhookController.php`. Methods: `index` (paginated list), `store`, `show`, `update`, `patch`, `destroy` (hard delete — `$webhook->delete()`). Each method gates to admin via policy or middleware.
  _Verification commands:_
  - `php artisan test --filter=WebhookApiTest`
  - `make phpstan`

- [x] T-031-09 – Register webhook routes in admin API routes file (API-031-01 through API-031-06).
  _Intent:_ Add routes in appropriate route file (e.g., `routes/api_v2.php`) under admin middleware group: `GET /api/v2/Webhook`, `POST /api/v2/Webhook`, `GET /api/v2/Webhook/{id}`, `PUT /api/v2/Webhook/{id}`, `PATCH /api/v2/Webhook/{id}`, `DELETE /api/v2/Webhook/{id}`.
  _Verification commands:_
  - `php artisan route:list --name=webhook`
  - `php artisan test --filter=WebhookApiTest`

### I3 – Webhook Dispatch Job & Payload Builder

- [ ] T-031-10 – Write feature tests for webhook dispatch (before implementation) (S-031-09 through S-031-20).
  _Intent:_ `tests/Feature/WebhookDispatchTest.php`. Uses `Http::fake()`. Tests:
  - `PhotoAdded` → POST webhook with JSON body fires (S-031-09).
  - `PhotoMoved` → webhook fires with correct payload (S-031-10).
  - `PhotoWillBeDeleted` → webhook fires with pre-deletion snapshot (S-031-11).
  - Disabled webhook not fired (S-031-07).
  - Secret header present (S-031-13).
  - Size-variant filtering correct (S-031-14).
  - `payload_format = query_string` → payload sent as query params (S-031-15).
  - `payload_format = json` with `method = GET` → JSON body sent (S-031-20).
  - Bulk delete N photos → N calls per matching webhook (S-031-12, S-031-19).
  - Non-2xx logs error (S-031-16). Timeout logs error (S-031-17). No webhooks → no calls (S-031-18).
  _Verification commands:_
  - `php artisan test --filter=WebhookDispatchTest` (expected: RED before implementation)

- [x] T-031-11 – Create `WebhookPayload` DTO and `WebhookPayloadBuilder` service (DO-031-02, FR-031-09, FR-031-11).
  _Intent:_ `app/DTO/WebhookPayload.php` — plain DTO with optional fields: `?photo_id`, `?album_id`, `?title`, `?size_variants` (array). `app/Services/Webhook/WebhookPayloadBuilder.php` — accepts `Webhook` model + photo snapshot data (array), returns `WebhookPayload`. Applies `send_*` flags, filters size_variant_types, omits absent variants silently. For `payload_format = query_string`, size variant URLs are base64-encoded as `size_variant_{type}=<base64(url)>` params (Q-031-08 → base64).
  _Verification commands:_
  - `php artisan test --filter=WebhookPayloadBuilderTest`
  - `make phpstan`

- [x] T-031-12 – Create `WebhookDispatchJob` queued job (DO-031-04, NFR-031-01, NFR-031-04, NFR-031-05, NFR-031-08, TE-031-01, TE-031-02; Q-031-02, Q-031-04 → A).
  _Intent:_ `app/Jobs/WebhookDispatchJob.php`. Implements `ShouldQueue`. `$tries = 1` (no retry — Q-031-04 → A). Accepts `Webhook` + `WebhookPayload`. Fires outgoing HTTP request:
  - `payload_format = json`: JSON body with `Content-Type: application/json` sent via `withBody()`
  - `payload_format = query_string`: params appended to URL; size variant URLs base64-encoded as `size_variant_{type}=<base64(url)>` (Q-031-08 → base64)
  Sets `User-Agent: Lychee/Webhooks`, `X-Lychee-Event: <event>` on every request. Sets `$secretHeader: $secret` when secret is configured. On non-2xx or exception: `Log::error('webhook.dispatch.failure', [...])` with `webhook_id`, `event`, `method`, `error` (secret redacted). On success: `Log::debug('webhook.dispatch.success', [...])`.  _Verification commands:_
  - `php artisan test --filter=WebhookDispatchTest`
  - `make phpstan`

- [x] T-031-13 – Create `WebhookListener` and register in `EventServiceProvider` (FR-031-06 through FR-031-08, FR-031-13; Q-031-05 → C, Q-031-06 → D).
  _Intent:_ `app/Listeners/WebhookListener.php`. Three handler methods:
  - `handlePhotoAdded(PhotoAdded $event)`: loads photo model, loads enabled `photo.add` webhooks, builds payload, dispatches `WebhookDispatchJob` per webhook.
  - `handlePhotoMoved(PhotoMoved $event)`: loads photo model, loads enabled `photo.move` webhooks, dispatches.
  - `handlePhotoWillBeDeleted(PhotoWillBeDeleted $event)`: uses snapshot data from event (no DB load needed), loads enabled `photo.delete` webhooks, dispatches.
  Register in `EventServiceProvider::$listen`:
  - `PhotoAdded::class → [WebhookListener::class . '@handlePhotoAdded']`
  - `PhotoMoved::class → [WebhookListener::class . '@handlePhotoMoved']`
  - `PhotoWillBeDeleted::class → [WebhookListener::class . '@handlePhotoWillBeDeleted']`
  _Verification commands:_
  - `php artisan test --filter=WebhookDispatchTest`
  - `php artisan test --filter=WebhookListenerTest`
  - `make phpstan`

- [x] T-031-14 – Fire `PhotoAdded` from `SetParent` pipe for new photo records (FR-031-06; Q-031-05 → C).
  _Intent:_ Modify `app/Actions/Photo/Pipes/Shared/SetParent.php`: after the existing `PhotoSaved::dispatch($state->photo->id)` line, also dispatch `PhotoAdded::dispatch($state->photo->id)` **only when** this is a new record (not an update). Check `$state->photo->wasRecentlyCreated` or equivalent. **Do not** change the existing `PhotoSaved` dispatch.
  _Verification commands:_
  - `php artisan test --filter=WebhookDispatchTest`
  - `php artisan test --filter=PhotoCreateTest` (existing tests must still pass)

- [x] T-031-15 – Fire `PhotoMoved` from `MoveOrDuplicate` for cross-album moves (FR-031-07; Q-031-05 → C).
  _Intent:_ Modify `app/Actions/Photo/MoveOrDuplicate.php`: in the loop (or after processing) when `$from_album->get_id() !== $to_album?->id`, dispatch `PhotoMoved::dispatch($photo->id, $from_album->get_id(), $to_album->id)` for each photo. **Do not** change the existing `AlbumSaved` / `PhotoDeleted` dispatches.
  _Verification commands:_
  - `php artisan test --filter=WebhookDispatchTest`
  - `php artisan test --filter=MovePhotoTest` (existing tests must still pass)

- [x] T-031-16 – Fire `PhotoWillBeDeleted` from `Delete` action before DB deletion (FR-031-08; Q-031-06 → D).
  _Intent:_ Modify `app/Actions/Photo/Delete.php`. Before calling `$photos_to_be_deleted->executeDelete()`, load the photo snapshot data for each ID in `$delete_photo_ids`:
  - Query `photos` + `size_variants` for `photo_id`, `album_id` (from `$from_id`), `title`, and size-variant URLs (type + url).
  - Dispatch `PhotoWillBeDeleted::dispatch($photo_id, $album_id, $title, $size_variants_array)` per photo.
  **Do not** change the existing `PhotoDeleted::dispatch($from_id)` call after `executeDelete()`.
  _Verification commands:_
  - `php artisan test --filter=WebhookDispatchTest`
  - `php artisan test --filter=DeletePhotoTest` (existing tests must still pass)
  - `make phpstan`

### I4 – Artisan Test Command

- [x] T-031-17 – Create `php artisan lychee:webhook-test` command (CLI-031-01).
  _Intent:_ `app/Console/Commands/WebhookTest.php`. Accepts `{id}` argument. Loads `Webhook` by ID (or fails with error message and exit code 1). Builds synthetic `WebhookPayload` with sample data (`photo_id: 'TEST_PHOTO_ID'`, etc.). Dispatches `WebhookDispatchJob` **synchronously** (not queued). Prints HTTP status or error. Exits 0 on 2xx, 1 on failure.
  _Verification commands:_
  - `php artisan lychee:webhook-test <id>`
  - `php artisan test --filter=WebhookTestCommandTest`

### I5 – Admin UI (Vue 3 / PrimeVue)

- [ ] T-031-18 – Create `webhook-service.ts` API client (API-031-01 through API-031-06).
  _Intent:_ `resources/js/services/webhook-service.ts`. Typed functions: `list()`, `create(data)`, `get(id)`, `update(id, data)`, `patch(id, data)`, `remove(id)`. Uses axios consistent with existing service files. Uses `.then()` instead of async/await (per project convention). Response type includes `has_secret` boolean, not `secret`.
  _Verification commands:_
  - `npm run check`

- [ ] T-031-19 – Create `WebhooksView.vue` admin page with list table (UI-031-01, UI-031-02).
  _Intent:_ `resources/js/views/admin/WebhooksView.vue`. PrimeVue `DataTable` or equivalent. Columns: name, event, method, URL, enabled (toggle), actions (edit, delete). Empty-state message when no webhooks. Add-webhook button opens modal.
  _Verification commands:_
  - `npm run check`

- [ ] T-031-20 – Create `WebhookModal.vue` add/edit form (UI-031-03 through UI-031-06, UI-031-08, FR-031-01).
  _Intent:_ `resources/js/components/admin/WebhookModal.vue`. Fields: name, event (dropdown), method (dropdown), URL, `payload_format` (dropdown: "JSON body" | "Query string"), enabled (checkbox), secret, secret_header (disabled if secret empty), send_photo_id/send_album_id/send_title/send_size_variants checkboxes, size_variant_types checkboxes (disabled if send_size_variants unchecked). **HTTP URL warning**: when URL starts with `http://`, show inline warning "Plain HTTP transmits your secret key in cleartext." (UI-031-08). Client-side validation before submit. Emits `saved` event on success.
  _Verification commands:_
  - `npm run check`

- [ ] T-031-21 – Create `WebhookDeleteConfirm.vue` dialog and wire admin nav (UI-031-07).
  _Intent:_ `resources/js/components/admin/WebhookDeleteConfirm.vue`. PrimeVue `ConfirmDialog` or equivalent. Wire `WebhooksView` into admin navigation consistent with existing admin nav items.
  _Verification commands:_
  - `npm run check`

### I6 – Documentation & Knowledge-Map

- [ ] T-031-22 – Update `knowledge-map.md` and OpenAPI spec (FR-031-02, API-031-01 through API-031-06).
  _Intent:_ Add `Webhook` module entry to `docs/specs/4-architecture/knowledge-map.md`. Update or generate OpenAPI YAML/JSON with new routes and request/response schemas (including `payload_format` field and `has_secret` in response). Verify OpenAPI passes validation.
  _Verification commands:_
  - `php artisan route:list --name=webhook`

- [x] T-031-23 – Add `webhook_timeout_seconds` to `configs` table seeder/migration (NFR-031-08).
  _Intent:_ Add `webhook_timeout_seconds` entry (default: 10) to the Lychee `configs` table seeder or migration consistent with how other configuration values are stored.
  _Verification commands:_
  - `php artisan migrate:fresh --seed`
  - `php artisan test --filter=WebhookDispatchTest`

## Notes / TODOs

- **`size_variants` in query-string mode:** Use flat params `size_variant_{type}=<base64(url)>` with standard base64 encoding (Q-031-08 resolved). The type name is the lowercase SizeVariantType name (e.g. `size_variant_original`, `size_variant_medium`).
- **Duplication and `PhotoAdded`:** T-031-14 notes that `PhotoAdded` fires only for new records (`wasRecentlyCreated`). If photos are also duplicated via `Create` with `DuplicateDTO`, the same `SetParent` pipe will fire `PhotoAdded` — this is the intended semantic (a duplicate is a new photo in the album).
- **`PhotoWillBeDeleted` payload overhead:** T-031-16 loads photo data before deletion. Use a lean query (not full Eloquent hydration with all relations) to minimise overhead in bulk delete scenarios.
