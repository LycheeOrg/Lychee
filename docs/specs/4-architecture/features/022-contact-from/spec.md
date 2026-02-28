# Feature 022 – Contact Form

| Field | Value |
|-------|-------|
| Status | Active |
| Last updated | 2026-02-28 |
| Owners | — |
| Linked plan | `docs/specs/4-architecture/features/022-contact-from/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/022-contact-from/tasks.md` |
| Roadmap entry | #022 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

**GitHub issue:** [LycheeOrg/Lychee#1580](https://github.com/LycheeOrg/Lychee/issues/1580)

## Overview

Visitors of a public Lychee gallery currently have no in-app way to send a message to the gallery owner about a photo or album they are viewing. This feature adds an optional **contact form** that lets any visitor (authenticated or anonymous) send a message that is emailed to a configured address. The gallery owner enables the feature via an admin setting and optionally provides a dedicated contact email; when no dedicated address is configured, the application falls back to the `MAIL_FROM_ADDRESS` environment variable. The form is surfaced via a new icon button in the album hero action row, and the entire interaction is handled by a modal dialog built with PrimeVue.

**Affected modules:** Config system (`configs` table, `InitConfig`), Mail (`ContactMessage` mailable, Blade template), REST API (new `POST /Contact` route + `ContactController` + `ContactRequest`), Frontend (`LycheeState`, `ModalsState`, `galleryModals` composable, `AlbumHero.vue`, `AlbumPanel.vue`, new `ContactForm.vue` modal), Translations (22 locales).

## Goals

1. Allow any gallery visitor to send a contact message to the gallery owner from within the gallery UI.
2. Let the admin enable/disable the contact form via a boolean config key (`contact_form_enabled`).
3. Let the admin supply an optional contact email address (`contact_form_email`); when blank, fall back to `MAIL_FROM_ADDRESS`.
4. Protect the endpoint against abuse: server-side rate limiting (5 requests per 10 minutes per IP).
5. Include an invisible honeypot field to catch simple bots.
6. Deliver the message via Laravel's existing mail system (Mailable + Blade template).

## Non-Goals

- CAPTCHA integration (honeypot + rate limiting are sufficient for the initial release).
- Per-album contact recipients (one global address only).
- Storing messages in the database.
- Allowing visitors to attach files.
- OAuth or logged-in-user-only restriction on submitting the form (the form is accessible to guests).
- Admin UI to view/manage received messages inside Lychee.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-022-01 | Add boolean config key `contact_form_enabled` (default `0`/false) to the `configs` table. | Config row inserted via migration. `ConfigManager` returns bool. | Setting validated as boolean in admin panel. | Migration rollback removes the row. | — | Issue #1580 |
| FR-022-02 | Add string config key `contact_form_email` (default `''`) to the `configs` table. | Config row inserted via migration. `ConfigManager` returns string. | Setting validated as nullable email or empty string in admin panel. | Migration rollback removes the row. | — | Issue #1580 |
| FR-022-03 | Expose `is_contact_form_enabled: bool` in `InitConfig` (Spatie Data resource). Frontend reads this to show/hide the contact button. | `GET /Gallery::Init` response includes `is_contact_form_enabled: true` when feature enabled. | Hidden when `contact_form_enabled` is `0`. | — | — | FR-022-01 |
| FR-022-04 | `POST /Contact` endpoint accepts `name` (required, string, max 100), `email` (required, valid RFC email, max 100), `message` (required, string, max 2000), and `_honey` (optional string, must be empty — honeypot). | Valid request returns HTTP 204 No Content and sends the email. | 422 on validation failure. 429 when rate limit exceeded (5 per 10 min per IP). | 503 if mail system is unavailable (caught exception → 500 with generic error). | — | Issue #1580 |
| FR-022-05 | If `_honey` (honeypot field) is present and non-empty, the request is silently accepted (HTTP 204) but no email is sent. | Bot fills honeypot → response is 204 but mail is never dispatched. | Empty string passes validation as expected for a real user. | — | — | Anti-spam |
| FR-022-06 | When `contact_form_enabled` is false, `POST /Contact` returns HTTP 403. | Feature disabled → 403 Forbidden. | — | — | — | FR-022-01 |
| FR-022-07 | The `ContactMessage` mailable sends to the address in `contact_form_email`; if that is empty, it falls back to `config('mail.from.address')`. The `reply-to` header is set to the visitor's supplied email so the admin can reply directly. | Email delivered to configured address with reply-to set to visitor email. | Fallback to `MAIL_FROM_ADDRESS` tested with empty config value. | — | — | Issue #1580 |
| FR-022-08 | A Blade email template (`resources/views/emails/contact-message.blade.php`) renders the visitor's name, email, and message body. | Email body contains all three fields. | — | — | — | FR-022-07 |
| FR-022-09 | Frontend `LycheeState` store exposes `is_contact_form_enabled` (bool, default false), populated from `InitConfig` on gallery init. | Store field updated from API response. | — | — | — | FR-022-03 |
| FR-022-10 | A `ContactForm.vue` modal renders a dialog with: Name input, Email input, Message textarea (all required client-side), a Submit button, and a Cancel/Close button. The honeypot field is present but visually hidden. | User fills in fields, submits → spinner shown → success toast on 204 → modal closes. | Client-side required validation before submit. Server-side validation error displayed as inline toast. | Rate limit (429) and server error (5xx) show error toast. | — | FR-022-04, FR-022-08 |
| FR-022-11 | `AlbumHero.vue` shows a new envelope-paper icon button (e.g., `pi pi-envelope`) when `is_contact_form_enabled` is true. Clicking it opens the `ContactForm.vue` modal. The button is shown to all users (guests and logged-in) when the feature is enabled. | Icon appears in the action row alongside Share/Download icons. | Hidden when `is_contact_form_enabled` is false. | — | — | FR-022-03, FR-022-09 |
| FR-022-12 | Translations added for all 22 supported locales: `gallery.contact.button_title` (tooltip), `gallery.contact.title` (modal header), `gallery.contact.name` (label), `gallery.contact.email` (label), `gallery.contact.message` (label), `gallery.contact.send` (button), `gallery.contact.success` (success toast text), `gallery.contact.error` (error toast text). Non-English locales use the English string as a placeholder. | All translation keys present in 22 locale files. | — | — | — | Internationalisation standard |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-022-01 | Rate limiting: max 5 `POST /Contact` requests per 10 minutes per IP address. Implemented via Laravel's built-in `throttle` middleware. | Spam prevention | `ThrottleRequests` middleware with `5,10` parameters. | Laravel throttle middleware | Anti-abuse |
| NFR-022-02 | Honeypot field (`_honey`) must never appear in the rendered DOM in a way that is visible to regular users; it must be hidden via inline style (`position:absolute; left:-9999px`). | Anti-spam UX | Field not visible in rendered layout. Screen-reader users see `aria-hidden="true"`. | Vue template | Anti-spam, Accessibility |
| NFR-022-03 | Code follows Lychee PHP conventions: license headers, snake_case variables, strict comparison (`===`), PSR-4, no `empty()`, `in_array(..., true)`. | Maintainability | `php-cs-fixer`, PHPStan level 6. | php-cs-fixer, phpstan | coding-conventions.md |
| NFR-022-04 | Frontend follows Vue3/TypeScript conventions: template-first, Composition API, `.then()` (no async/await), regular function declarations, axios in services. | Maintainability | Prettier, eslint | npm run format, npm run check | coding-conventions.md |
| NFR-022-05 | Migration is reversible. `down()` removes the two config rows. | Operability | Laravel migration `down()` tested. | Laravel migration framework | Deployment standard |

## UI / Interaction Mock-ups

### Album Hero — Contact Button

```
┌─────────────────────────────────────────────────────────────┐
│  [↓ download]  [⬡ share]  [</> embed]  [✉ contact]         │
│                                          ↑ NEW              │
└─────────────────────────────────────────────────────────────┘
Contact button visible only when is_contact_form_enabled = true
```

### Contact Form Modal

```
┌───────────────────────────────────────────────────┐
│  Contact the gallery owner                   [×]  │
├───────────────────────────────────────────────────┤
│                                                   │
│  Name *                                           │
│  ┌─────────────────────────────────────────────┐  │
│  │  Your name                                  │  │
│  └─────────────────────────────────────────────┘  │
│                                                   │
│  Email *                                          │
│  ┌─────────────────────────────────────────────┐  │
│  │  your@email.com                             │  │
│  └─────────────────────────────────────────────┘  │
│                                                   │
│  Message *                                        │
│  ┌─────────────────────────────────────────────┐  │
│  │                                             │  │
│  │  (5 rows textarea)                          │  │
│  │                                             │  │
│  └─────────────────────────────────────────────┘  │
│                                                   │
│  [ Cancel ]                         [ Send ✉ ]   │
└───────────────────────────────────────────────────┘
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-022-01 | Feature disabled (`contact_form_enabled = 0`): contact button not shown; `POST /Contact` returns 403. |
| S-022-02 | Feature enabled, valid submission: email sent to configured address; HTTP 204 returned; success toast shown. |
| S-022-03 | Feature enabled, fallback email: `contact_form_email` is empty; mail is sent to `MAIL_FROM_ADDRESS`. |
| S-022-04 | Bot fills honeypot: HTTP 204 returned but no email sent. |
| S-022-05 | Rate limit exceeded: HTTP 429 returned; error toast shown in frontend. |
| S-022-06 | Validation failure (empty name/email/message, or invalid email): HTTP 422 returned; error toast shown. |
| S-022-07 | Mail system unavailable: exception caught; HTTP 500 returned; error toast shown. |

## Test Strategy

- **Feature (REST):** `tests/Feature_v2/Gallery/ContactTest.php` — covers S-022-01 through S-022-07; extends `BaseApiWithDataTest`.
- **Unit:** none required (no pure-logic helpers introduced beyond framework conventions).
- **UI (JS):** not in scope for initial release.

## Interface & Contract Catalogue

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-022-01 | REST POST /Contact | Submit a contact form message | Body: `name`, `email`, `message`, `_honey`. Rate limited 5/10min. |

### Spec DSL

```yaml
routes:
  - id: API-022-01
    method: POST
    path: /Contact
    rate_limit: "5,10"
    body_fields:
      - name: name
        type: string
        max: 100
        required: true
      - name: email
        type: email
        max: 100
        required: true
      - name: message
        type: string
        max: 2000
        required: true
      - name: _honey
        type: string
        required: false
        must_be_empty: true
```

---

*Last updated: 2026-02-28*
