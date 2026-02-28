# Feature Plan 022 – Contact Form

_Linked specification:_ `docs/specs/4-architecture/features/022-contact-from/spec.md`  
_Status:_ Active  
_Last updated:_ 2026-02-28

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

Gallery visitors gain a first-class, server-side way to contact the gallery owner without exposing the owner's email address in the DOM. Success is measured by:

- `POST /Contact` route returns 204 on valid submission and sends an email to the configured address.
- Button visible in the album hero when `contact_form_enabled = 1`, hidden when disabled.
- Rate limiting (5/10 min per IP) and honeypot protection active.
- All 22 locale translation files updated.
- PHPStan level 6, php-cs-fixer, `npm run format`, `npm run check`, and `php artisan test` all pass.

## Scope Alignment

- **In scope:**
  - Config migration (`contact_form_enabled`, `contact_form_email`).
  - `InitConfig` change to expose `is_contact_form_enabled`.
  - `ContactMessage` mailable + Blade template.
  - `ContactRequest` + `ContactController` + route.
  - `LycheeState` + `ModalsState` + `galleryModals` composable changes.
  - `ContactForm.vue` modal component.
  - `AlbumHero.vue` contact button.
  - `AlbumPanel.vue` modal mount.
  - Translations in 22 locales.
  - Feature test (`ContactTest.php`).

- **Out of scope:**
  - CAPTCHA integration.
  - Per-album recipients.
  - Database storage of messages.
  - File attachments.
  - Admin inbox UI.

## Dependencies & Interfaces

| Dependency | Note |
|-----------|------|
| Laravel Mail (`Illuminate\Mail`) | Existing infrastructure; requires `MAIL_FROM_ADDRESS` fallback. |
| `BaseConfigMigration` | Reused pattern from features 015–020 for inserting config rows. |
| `InitConfig` (Spatie Data) | Add one bool field; TypeScript type auto-generated. |
| `BaseApiRequest` | Base class for `ContactRequest`. |
| PrimeVue (`Dialog`, `InputText`, `Textarea`, `Button`) | Existing UI component library used by all modals. |
| `useTogglablesStateStore` (Pinia) | Central modal visibility store. |
| Laravel `throttle` middleware | Built-in rate limiting. |

## Assumptions & Risks

- **Assumptions:**
  - `MAIL_FROM_ADDRESS` is always set in the production `.env` (standard Laravel deployment assumption).
  - The existing mail infrastructure (SMTP or another driver) is configured by the site operator.
  - PrimeVue `Textarea` component is available (it is — already used elsewhere in the codebase).

- **Risks / Mitigations:**
  - **Mail misconfiguration:** If `MAIL_FROM_ADDRESS` is not set and `contact_form_email` is also empty, the mailable will throw. Mitigation: validate in the controller that at least one is available and return 503 with a user-friendly error.
  - **Spam:** Rate limiting + honeypot reduce but do not eliminate spam. CAPTCHA is noted as a future follow-up.

## Implementation Drift Gate

After completing each increment: run `vendor/bin/php-cs-fixer fix`, then `make phpstan`, then `php artisan test --filter=Contact`. For frontend increments run `npm run format && npm run check` before committing.

## Increment Map

1. **I1 – Config migration & InitConfig**
   - _Goal:_ Add `contact_form_enabled` and `contact_form_email` to `configs` table; expose `is_contact_form_enabled` in `InitConfig`; update `LycheeState`.
   - _Preconditions:_ None.
   - _Steps:_
     1. Create migration `2026_02_28_000006_add_contact_form_config.php` extending `BaseConfigMigration`.
     2. Add `public bool $is_contact_form_enabled;` to `InitConfig` with constructor assignment.
     3. Add `is_contact_form_enabled: false` to `LycheeState` state.
     4. Populate `is_contact_form_enabled` from `data` in the `LycheeState` init action.
   - _Commands:_ `php artisan test`, `make phpstan`
   - _Exit:_ Migration runs, `InitConfig` includes the new field, TypeScript type updated.

2. **I2 – Backend: Mailable, Request, Controller, Route**
   - _Goal:_ Create the full backend pipeline for `POST /Contact`.
   - _Preconditions:_ I1 complete.
   - _Steps:_
     1. Create `app/Mail/ContactMessage.php` mailable.
     2. Create `resources/views/emails/contact-message.blade.php`.
     3. Create `app/Http/Requests/Contact/SendContactRequest.php`.
     4. Create `app/Http/Controllers/Gallery/ContactController.php`.
     5. Register route `Route::post('/Contact', ...)` with `throttle:5,10` middleware in `routes/api_v2.php`.
   - _Commands:_ `php artisan test --filter=Contact`, `make phpstan`, `vendor/bin/php-cs-fixer fix`
   - _Exit:_ Feature test `ContactTest.php` passes for all scenarios S-022-01 through S-022-07.

3. **I3 – Feature tests**
   - _Goal:_ Write `tests/Feature_v2/Gallery/ContactTest.php` before finalising the backend (test-first cadence).
   - _Preconditions:_ I1 complete (config keys exist).
   - _Steps:_
     1. Create test class extending `BaseApiWithDataTest`.
     2. Write tests for S-022-01 to S-022-07.
     3. Confirm tests fail before I2 implementation, pass after.
   - _Commands:_ `php artisan test --filter=ContactTest`
   - _Exit:_ All 7 scenario tests pass.

4. **I4 – Frontend: state, composable, modal, button**
   - _Goal:_ Add the contact form UI.
   - _Preconditions:_ I1 complete (TypeScript types regenerated).
   - _Steps:_
     1. Add `is_contact_form_visible: false` to `ModalsState`.
     2. Add `toggleContactForm()` to `galleryModals.ts` composable.
     3. Create `resources/js/components/modals/ContactForm.vue`.
     4. Add contact service method to `resources/js/services/contact-service.ts`.
     5. Add `open-contact-form` emit + contact button to `AlbumHero.vue`.
     6. Wire up modal and toggle in `AlbumPanel.vue`.
   - _Commands:_ `npm run format`, `npm run check`
   - _Exit:_ Contact button visible in gallery when feature enabled; form submits correctly.

5. **I5 – Translations**
   - _Goal:_ Add required translation strings to all 22 locale files.
   - _Preconditions:_ I4 complete (keys defined).
   - _Steps:_
     1. Add `contact` sub-array to `lang/en/gallery.php` with 8 keys.
     2. Copy English strings to all 21 remaining locales.
   - _Commands:_ `php artisan test`, `npm run check`
   - _Exit:_ All locale files contain the `contact` sub-array; no missing translation warnings.

6. **I6 – Quality gate & roadmap update**
   - _Goal:_ Full quality gate; mark feature complete.
   - _Preconditions:_ I1–I5 complete.
   - _Steps:_
     1. `vendor/bin/php-cs-fixer fix`
     2. `npm run format`
     3. `npm run check`
     4. `php artisan test`
     5. `make phpstan`
     6. Update roadmap: move 022 to Completed.
   - _Exit:_ All checks green; roadmap updated.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-022-01 | I2, I3 / T-022-02 | Feature disabled → 403 |
| S-022-02 | I2, I3 / T-022-02 | Valid submission → 204, email sent |
| S-022-03 | I2, I3 / T-022-02 | Fallback to `MAIL_FROM_ADDRESS` |
| S-022-04 | I2, I3 / T-022-02 | Honeypot filled → 204 silent |
| S-022-05 | I2, I3 / T-022-02 | Rate limit → 429 |
| S-022-06 | I2, I3 / T-022-02 | Validation failure → 422 |
| S-022-07 | I2, I3 / T-022-02 | Mail exception → 500 |

## Analysis Gate

Not yet run. To be executed after I3 (tests written and passing).

## Exit Criteria

- [ ] Migration inserts `contact_form_enabled` and `contact_form_email` config rows.
- [ ] `GET /Gallery::Init` includes `is_contact_form_enabled`.
- [ ] `POST /Contact` returns 403 when disabled, 204 when valid, 422 on validation error, 429 on rate limit, 204 (silent) on honeypot.
- [ ] Email delivered with correct subject, body, and reply-to header.
- [ ] Contact button visible in album hero only when `is_contact_form_enabled = true`.
- [ ] `ContactForm.vue` modal renders correctly; success/error toasts shown.
- [ ] All 22 locale files contain the 8 translation keys.
- [ ] PHPStan level 6: 0 errors.
- [ ] `php-cs-fixer fix`: no changes.
- [ ] `npm run format`: no changes.
- [ ] `npm run check`: passes.
- [ ] `php artisan test`: all tests pass (including new `ContactTest`).

## Follow-ups / Backlog

- CAPTCHA integration (e.g., hCaptcha or Turnstile) as an optional SE feature.
- Per-album contact recipients (owner of the album's top-level parent user).
- Admin inbox page to view received messages stored in the database.

---

*Last updated: 2026-02-28*
