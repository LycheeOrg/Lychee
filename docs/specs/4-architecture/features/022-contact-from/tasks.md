# Feature 022 Tasks – Contact Form

_Status: Active_  
_Last updated: 2026-02-28_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.

## Checklist

### I1 – Config migration & InitConfig

- [ ] T-022-01 – Create config migration `2026_02_28_000006_add_contact_form_config.php` (FR-022-01, FR-022-02).  
  _Intent:_ Insert `contact_form_enabled` (bool, default `0`) and `contact_form_email` (string, default `''`) into the `configs` table.  
  _Verification commands:_
  - `php artisan test`
  _Notes:_ Extend `BaseConfigMigration`; follow the pattern in `2026_02_28_000002_add_raw_download_enabled_config.php`.

- [ ] T-022-02 – Add `is_contact_form_enabled` field to `InitConfig` (FR-022-03).  
  _Intent:_ Expose `public bool $is_contact_form_enabled` in `app/Http/Resources/GalleryConfigs/InitConfig.php`, assigned from `request()->configs()->getValueAsBool('contact_form_enabled')`.  
  _Verification commands:_
  - `php artisan test`
  - `make phpstan`

- [ ] T-022-03 – Update `LycheeState.ts` to include `is_contact_form_enabled` (FR-022-09).  
  _Intent:_ Add `is_contact_form_enabled: false` to the store state; populate it in the init action from `data.is_contact_form_enabled`.  
  _Verification commands:_
  - `npm run check`

### I2 – Backend pipeline

- [ ] T-022-04 – Create `app/Mail/ContactMessage.php` Mailable (FR-022-07).  
  _Intent:_ Mailable accepts `name`, `email`, `message` strings. Sends to `contact_form_email` config value; falls back to `config('mail.from.address')` when empty. Sets `reply-to` header to visitor's email.  
  _Verification commands:_
  - `make phpstan`
  - `vendor/bin/php-cs-fixer fix`

- [ ] T-022-05 – Create `resources/views/emails/contact-message.blade.php` (FR-022-08).  
  _Intent:_ Blade email template using `@component('mail::layout')` pattern; displays sender name, email, and message.  
  _Verification commands:_
  - `php artisan test --filter=ContactTest`

- [ ] T-022-06 – Create `app/Http/Requests/Contact/SendContactRequest.php` (FR-022-04, FR-022-05).  
  _Intent:_ Extends `BaseApiRequest`. `authorize()` returns true (public). Rules: `name` required string max 100, `email` required email:rfc max 100, `message` required string max 2000, `_honey` optional string (must be empty or absent). Exposes `name()`, `email()`, `message()`, `isHoneypot()` accessors.  
  _Verification commands:_
  - `make phpstan`
  - `vendor/bin/php-cs-fixer fix`

- [ ] T-022-07 – Create `app/Http/Controllers/Gallery/ContactController.php` (FR-022-04, FR-022-05, FR-022-06).  
  _Intent:_ Single `send(SendContactRequest $request)` method. Returns 403 when `contact_form_enabled` is false. Returns 204 silently when honeypot is filled. Otherwise dispatches `ContactMessage` mailable and returns 204.  
  _Verification commands:_
  - `php artisan test --filter=ContactTest`
  - `make phpstan`

- [ ] T-022-08 – Register route `Route::post('/Contact', ...)` with `throttle:5,10` in `routes/api_v2.php` (FR-022-04, NFR-022-01).  
  _Intent:_ Route registered in the public (unauthenticated) section; throttle middleware applied.  
  _Verification commands:_
  - `php artisan test --filter=ContactTest`

### I3 – Feature tests

- [ ] T-022-09 – Create `tests/Feature_v2/Gallery/ContactTest.php` (S-022-01 through S-022-07).  
  _Intent:_ Tests cover: disabled → 403, valid submission → 204 + mail queued, fallback email, honeypot → 204 no mail, rate limit → 429, validation errors → 422. Extends `BaseApiWithDataTest`. Uses `Mail::fake()`.  
  _Verification commands:_
  - `php artisan test --filter=ContactTest`
  _Notes:_ Write tests **before** implementing T-022-07 to follow test-first cadence.

### I4 – Frontend

- [ ] T-022-10 – Add `is_contact_form_visible` to `ModalsState.ts`.  
  _Intent:_ Add `is_contact_form_visible: false` to the `useTogglablesStateStore` state.  
  _Verification commands:_
  - `npm run check`

- [ ] T-022-11 – Add `toggleContactForm` to `galleryModals.ts` composable.  
  _Intent:_ Follow the pattern of `toggleShareAlbum`. Expose `is_contact_form_visible` and `toggleContactForm` from the composable.  
  _Verification commands:_
  - `npm run check`

- [ ] T-022-12 – Create `resources/js/services/contact-service.ts`.  
  _Intent:_ Axios POST to `${Constants.getApiUrl()}Contact` with `name`, `email`, `message`, `_honey` fields.  
  _Verification commands:_
  - `npm run check`

- [ ] T-022-13 – Create `resources/js/components/modals/ContactForm.vue` (FR-022-10).  
  _Intent:_ PrimeVue Dialog modal. Template-first, Composition API, `.then()` only. Fields: name (InputText), email (InputText), message (Textarea 5 rows), hidden honeypot div (position:absolute, left:-9999px). Submit calls `ContactService.send(...).then(...)`. Success → toast + close. Error → toast with message.  
  _Verification commands:_
  - `npm run format`
  - `npm run check`

- [ ] T-022-14 – Add contact button to `AlbumHero.vue` (FR-022-11).  
  _Intent:_ Add `<a v-if="lycheeStore.is_contact_form_enabled" ... @click="openContactForm">` icon button (class `pi pi-envelope`) in the action row. Emit `open-contact-form` event.  
  _Verification commands:_
  - `npm run check`

- [ ] T-022-15 – Wire `ContactForm.vue` into `AlbumPanel.vue`.  
  _Intent:_ Import `ContactForm.vue`; add `@open-contact-form="toggleContactForm"` to `AlbumHero`. Mount `<ContactForm v-model:visible="is_contact_form_visible" />` alongside `<ShareAlbum>`. Destructure `is_contact_form_visible`, `toggleContactForm` from `useGalleryModals`.  
  _Verification commands:_
  - `npm run format`
  - `npm run check`

### I5 – Translations

- [ ] T-022-16 – Add `contact` sub-array to `lang/en/gallery.php` (FR-022-12).  
  _Intent:_ Add 8 keys: `button_title`, `title`, `name`, `email`, `message`, `send`, `success`, `error`.  
  _Verification commands:_
  - `php artisan test`

- [ ] T-022-17 – Copy English `contact` strings to all 21 remaining locale files (FR-022-12).  
  _Intent:_ Locale files: ar, bg, cz, de, el, es, fa, fr, hu, it, ja, nl, no, pl, pt, ru, sk, sv, vi, zh_CN, zh_TW. Use English text as placeholder.  
  _Verification commands:_
  - `php artisan test`

### I6 – Quality gate & roadmap

- [ ] T-022-18 – Full quality gate.  
  _Intent:_ Run complete quality gate; fix any remaining issues.  
  _Verification commands:_
  - `vendor/bin/php-cs-fixer fix`
  - `npm run format`
  - `npm run check`
  - `php artisan test`
  - `make phpstan`

- [ ] T-022-19 – Update roadmap: move feature 022 to Completed.  
  _Intent:_ Edit `docs/specs/4-architecture/roadmap.md`.  
  _Verification commands:_ —

## Notes / TODOs

- Rate limiting test (S-022-05) can be implemented by faking the throttle or by making 6 sequential requests; use `$this->withoutMiddleware(ThrottleRequests::class)` to skip in non-rate-limit tests so the 5-request cap doesn't affect unrelated scenarios.
- The honeypot field name `_honey` follows the convention used in the shop checkout (`OfflineRequest`).
