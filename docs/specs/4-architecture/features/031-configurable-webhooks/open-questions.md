# Open Questions – Feature 031

Open questions for [Feature 031](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|

## Question Details

### ~~Q-031-01: HTTPS Enforcement for Webhook URLs~~ ✅ RESOLVED

**Resolution:** **Option A** — Allow both HTTP and HTTPS. Plain HTTP URLs are accepted at the server; the admin UI displays a security warning ("Plain HTTP transmits your secret key in cleartext.") when a non-HTTPS URL is entered. No backend enforcement.

**Spec Impact:** Updated FR-031-01 (validation path), NFR-031-06, UI-031-08, S-031-21. HTTP URL warning added to modal mock-up.

**Resolved:** 2026-03-25

---

### ~~Q-031-02: Payload Delivery for GET and DELETE Methods~~ ✅ RESOLVED

**Resolution:** **New option** — Add a `payload_format` field to the `Webhook` model. Admins choose per-webhook whether to deliver the payload as a **JSON body** (`json`) or **URL query parameters** (`query_string`). This choice is independent of HTTP method. If the admin selects `payload_format = json` with `method = GET`, Lychee sends the JSON body regardless (explicit operator choice; documented in admin guide). Note: `size_variants` encoding in query-string mode is tracked separately in Q-031-08.

**Spec Impact:** Added `payload_format` field to DO-031-01 (Webhook model), FR-031-01, FR-031-09, S-031-15, S-031-20, `WebhookPayloadFormat` enum, migration, mock-up, WebhookDispatchJob, Spec DSL.

**Resolved:** 2026-03-25

---

### ~~Q-031-03: Hard Delete vs. Soft Delete for Webhook Records~~ ✅ RESOLVED

**Resolution:** **Option A** — Hard delete only. No `deleted_at` column. The `enabled` flag provides sufficient protection.

**Spec Impact:** Updated NFR-031-02, FR-031-04, DO-031-01 (no `deleted_at`), migration (no `deleted_at` column), `WebhookController.destroy()`.

**Resolved:** 2026-03-25

---

### ~~Q-031-04: Automatic Retry Policy for Failed Dispatches~~ ✅ RESOLVED

**Resolution:** **Option A** — No automatic retry. Log failure at ERROR level and discard. `WebhookDispatchJob.$tries = 1`.

**Spec Impact:** Updated NFR-031-04, DO-031-04, `WebhookDispatchJob`.

**Resolved:** 2026-03-25

---

### ~~Q-031-05: Distinguishing `photo.add` from `photo.move` via `PhotoSaved`~~ ✅ RESOLVED

**Resolution:** **Option C** — Add new dedicated events `PhotoAdded` and `PhotoMoved`, fired from the relevant action classes. `PhotoAdded` fired from `app/Actions/Photo/Pipes/Shared/SetParent.php` for new photo records. `PhotoMoved` fired from `app/Actions/Photo/MoveOrDuplicate.php` when source and destination albums differ. Existing `PhotoSaved` remains unchanged and continues to serve existing listeners.

**Spec Impact:** Added `PhotoAdded`, `PhotoMoved` to DO-031-03, Spec DSL `domain_events`, Appendix event table. Updated FR-031-06, FR-031-07, plan Dependencies, Scope, I1 steps, I3 steps. New tasks T-031-02, T-031-14, T-031-15.

**Resolved:** 2026-03-25

---

### ~~Q-031-06: Capturing Photo Data Before Hard Deletion~~ ✅ RESOLVED

**Resolution:** **Option D** — Create a new `PhotoWillBeDeleted` event that carries the full photo snapshot (`photo_id`, `album_id`, `title`, `size_variants[]`). This event is fired from `app/Actions/Photo/Delete.php` **before** `executeDelete()`, per photo scheduled for deletion. No Eloquent hooks or model observers. Existing `PhotoDeleted` event remains unchanged.

**Spec Impact:** Added `PhotoWillBeDeleted` to DO-031-03, Spec DSL `domain_events`, Appendix event table. Updated FR-031-08, plan Dependencies, I1 steps, I3 steps. New tasks T-031-02, T-031-16.

**Resolved:** 2026-03-25

---

### ~~Q-031-07: Secret Exposure in API Response~~ ✅ RESOLVED

**Resolution:** **Option A** — Exclude raw `secret` from all API responses. Return `has_secret` (boolean) computed as `secret !== null`. Admins must set a new secret if they lose it.

**Spec Impact:** Updated DO-031-01, FR-031-02, `WebhookResource`, S-031-22, Spec DSL.

**Resolved:** 2026-03-25

---

### ~~Q-031-08: `size_variants` Encoding in Query-String Payload Format~~ ✅ RESOLVED

**Feature:** 031 – Configurable Webhooks
**Priority:** High
**Status:** Resolved
**Opened:** 2026-03-25

**Context:** `payload_format = query_string` delivers all payload fields as URL query parameters. Simple scalar fields (`photo_id`, `album_id`, `title`) serialize trivially. However, `size_variants` is an array of objects (`[{type, url}]`), which has no single canonical query-string encoding.

**Resolution:** The URL of each size variant is **base64-encoded** (standard base64, not URL-safe) and delivered as a flat named query parameter using the pattern `size_variant_{type}=<base64(url)>`. For example: `size_variant_original=aHR0cHM6Ly9leGFtcGxlLmNvbS91cGxvYWRzL29yaWdpbmFsL3Bob3RvLmpwZw==&size_variant_medium=aHR0cHM6Ly9leGFtcGxlLmNvbS91cGxvYWRzL21lZGl1bS9waG90by5qcGc=`. Base64 encoding avoids any URL-encoding ambiguity for complex S3/CDN URLs.

**Spec Impact:** Updated FR-031-09, S-031-15, `WebhookPayloadBuilder`, and `WebhookDispatchJob`. Spec DSL updated.

**Resolved:** 2026-03-25
