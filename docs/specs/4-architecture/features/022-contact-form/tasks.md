# Feature 022: Contact Form — Task Checklist

**Status:** Ready for Implementation
**Last Updated:** 2026-02-28

---

## Phase 1: Database & Backend Infrastructure

### Increment 1: Migration & Model Setup
- [x] Create migration `create_contact_messages_table` with all columns per spec
- [x] Add proper indexes on `created_at` and `is_read`
- [x] Create `ContactMessage` Eloquent model with fillable/casts/hidden
- [x] Create `ContactMessageFactory` for testing
- [x] Run migration: `php artisan migrate` (verify success)
- [x] Test factory: `ContactMessage::factory()->create()` (verify works)
- [x] Update knowledge map entry for ContactMessage model

### Increment 2: Configuration & Settings System
- [x] Create settings category UI component for "Contact Form"
- [x] Add 7 config keys to settings:
  - `contact_form_sample_question`
  - `contact_form_sample_answer`
  - `contact_form_security_question`
  - `contact_form_security_answer`
  - `contact_form_custom_consent_text`
  - `contact_form_custom_privacy_url`
  - `contact_form_custom_submit_button_text`
- [x] Set defaults per spec (most null, submit button = "Send Message")
- [x] Lock all settings to Supporters license tier
- [x] Add labels/descriptions to settings UI (2–3 primary languages)
- [x] Verify settings appear in admin UI without errors

### Increment 3: Backend API Routes & Requests
- [x] Register 4 new API routes:
  - `POST /api/v2/contact` (public)
  - `GET /api/v2/contact` (admin)
  - `PATCH /api/v2/contact/{id}` (admin)
  - `DELETE /api/v2/contact/{id}` (admin)
- [x] Verify routes via `php artisan route:list`
- [x] Add middleware `may_administrate` to admin routes
- [x] Create `StoreContactMessageRequest` with validation rules
- [x] Create `UpdateContactMessageRequest` (validate is_read boolean)
- [x] Test auth with curl/Postman: 401 for public, 403 for unauthorized admin access

### Increment 4: API Controller & Business Logic
- [x] Create `ContactMessageController` with 4 methods: store, index, update, destroy
- [x] Create `ContactFormService` class with helper methods:
  - `submitMessage()`
  - `validateSecurityAnswer()`
  - `checkRateLimit()`
  - `getMessages()`
- [x] Implement rate-limiting: 5 submissions per IP per 24 hours
- [x] Create `ContactMessageResource` for API responses
- [x] Implement input sanitization (XSS prevention)
- [x] Handle validation errors with proper HTTP status codes (422, 429)
- [x] Test all endpoints return expected responses (unit curl tests or Postman)

---

## Phase 2: Frontend — Visitor Form

### Increment 5: Visitor Contact Form Page
- [x] Create `resources/js/pages/Contact.vue` (composition API + TypeScript)
- [x] Add form fields: name, email, message (required)
- [x] Track form state with reactive/ref
- [x] Add client-side validation with error messages
- [x] Conditionally render security question field (if config set)
- [x] Conditionally render sample Q&A section (if config set)
- [x] Conditionally render consent checkbox with privacy link (if config set)
- [x] Add custom submit button text (if config set, else default)
- [x] Use PrimeVue components (InputText, Textarea, Button, Checkbox)
- [x] Show character count for message field
- [x] Implement submit handler calling ContactFormService
- [x] Display success message and clear form on success
- [x] Display error toast/modal on validation or API errors
- [x] Verify form renders without console errors

### Increment 6: Visitor Form Service & Config Fetch
- [x] Create `resources/js/services/contactService.ts`:
  - `submitContactMessage(data)` - POST to `/api/v2/contact`
  - Error handling for 422 (validation) and 429 (rate limit)
- [x] Fetch 7 config keys on page mount (store in component state or Pinia)
- [x] Conditionally show/hide form fields based on config
- [x] Add translations to `lang/en/contact.php`
- [x] Test service methods compile and are callable
- [x] Verify config fetch works and form fields update

### Increment 7: Visitor Form Styling & Responsiveness
- [x] Style form container with max-width constraint
- [x] Ensure responsive layout (mobile, tablet, desktop)
- [x] Style error messages (red, icon)
- [x] Style success message (green, dismissible)
- [x] Add loading state to submit button during submission
- [x] Style with PrimeVue theme for consistency
- [x] Test mobile layout (no horizontal scroll, readable text)
- [x] Test accessibility: labels, ARIA, keyboard navigation
- [x] Verify form looks good on Chrome, Firefox, Safari

---

## Phase 3: Frontend — Admin Messages Page

### Increment 8: Admin Messages List View
- [x] Create `resources/js/pages/Admin/ContactMessages.vue` (composition API + TypeScript)
- [x] Add page title and intro
- [x] Create table with columns: Name, Email, Preview, Date, Read Checkbox
- [x] Fetch messages on mount: `GET /api/v2/contact` with pagination
- [x] Handle pagination: next/prev or page selector
- [x] Add filter controls: Read/Unread toggle, Search input
- [x] Show "No messages" state when empty
- [x] Track loading state and show spinner
- [x] Implement row click to expand/show full message
- [x] Verify table renders without console errors

### Increment 9: Admin Messages Interaction Features
- [x] Implement expand/modal to show full message details
- [x] Add checkbox in table/expanded view for read status toggle
- [x] On checkbox change, call PATCH endpoint to update `is_read`
- [x] Optimistic update: change UI immediately, revert on API error
- [x] Add Delete button to each row with trash icon
- [x] Show confirmation modal before deleting
- [x] Call DELETE endpoint and remove row on success
- [x] Show error toast if operation fails
- [x] Test all interactions work and persist to database

### Increment 10: Admin Messages Search & Filtering
- [x] Add search input field (search name, email, message)
- [x] Refetch messages with search query on input change (debounce)
- [x] Add Read/Unread filter toggle
- [x] Add sort options (date ascending/descending)
- [x] Pass filters to API: `is_read`, `search`, `sort`
- [x] Show "X results found" message
- [x] Show proper "No results" state when search returns nothing
- [x] Test all filters work and update results correctly

### Increment 11: Admin Messages Styling & Polish
- [x] Style table header with proper contrast
- [x] Add row hover effects and alternating row colors
- [x] Style delete and action buttons with PrimeVue icons
- [x] Make table responsive (collapse/scroll on mobile)
- [x] Style modal/expanded view with clean layout
- [x] Add loading spinner state
- [x] Add error notification styling
- [x] Ensure keyboard navigation in table
- [x] Test with screen reader (basic ARIA labels)
- [x] Verify page looks consistent with app design system

---

## Phase 4: Testing & Quality

### Increment 12: Unit Tests
- [x] Write tests for `ContactFormService::validateSecurityAnswer()`
  - Case-insensitive match test
  - Exact mismatch test
- [x] Write tests for `ContactFormService::checkRateLimit()`
  - Under limit (returns true)
  - At limit (returns false)
  - Expires after 24 hours
- [x] Write tests for `ContactMessage` model:
  - Factory creates valid model
  - Timestamps auto-set
  - Fillable/hidden properties work
- [x] Run tests: `php artisan test tests/Unit/...`
- [x] Aim for > 80% code coverage for services
- [x] Verify no skipped tests

### Increment 13: Feature Tests (API & Integration)
- [x] Test `POST /api/v2/contact`:
  - Valid submission stores message (200)
  - Missing required field returns 422
  - Security answer incorrect returns 422
  - Consent not checked returns 422
  - Rate limit: 5 passes, 6th returns 429
- [x] Test `GET /api/v2/contact`:
  - Unauthorized returns 403
  - Authorized returns 200 with paginated results
  - Filter by is_read works
  - Search by name/email/message works
- [x] Test `PATCH /api/v2/contact/{id}`:
  - Valid update returns 200, message marked read
  - Unauthorized returns 403
  - Invalid ID returns 404
- [x] Test `DELETE /api/v2/contact/{id}`:
  - Valid delete returns 204, message removed
  - Unauthorized returns 403
  - Invalid ID returns 404
- [x] Run tests: `php artisan test tests/Feature_v2/...`
- [x] Aim for > 85% code coverage

### Increment 14: Static Analysis & Quality Gates
- [x] Run `vendor/bin/php-cs-fixer fix` and fix style issues
- [x] Run `make phpstan` and fix all level 6 errors
- [x] Add license headers to new PHP files
- [x] Verify PSR-4 compliance, snake_case variables, === comparison
- [x] Verify no use of `empty()`, proper `in_array($a, $b, true)`
- [x] Run `npm run format` to apply Prettier
- [x] Run `npm run check` to lint TypeScript/Vue files
- [x] Run all tests: `php artisan test` (all pass)
- [x] Verify no TypeScript errors (`npm run build` or IDE check)

---

## Phase 5: Integration & Deployment

### Increment 15: Translation Completeness
- [x] Add all translation keys to `lang/en/contact.php`
- [x] Mirror keys to 21 other language files (lang/fr, lang/de, etc.)
- [x] Translate entries to respective languages (or use placeholder)
- [x] Verify all language files are complete (no missing keys)
- [x] Test visitor form with English and one other language
- [x] Test admin page with English and one other language
- [x] Check RTL language (e.g., Arabic) renders correctly

### Increment 16: Final Integration & E2E Testing
- [x] **Visitor Flow:**
  - Navigate to `/contact`, fill all fields, submit → success message
  - Check database for new message (correct data)
  - Test with optional fields (if configured)
- [x] **Admin Flow:**
  - Log in as admin, navigate to `/security/contact-messages`
  - Verify messages display in list
  - Toggle read status, verify persists
  - Test search, filters, and sorting
  - Delete a message, verify removal
- [x] **Configuration Test:**
  - Admin sets sample Q&A, visitor form shows it
  - Admin sets security question, visitor form shows it
  - Visitor submits with wrong answer → 422 error
  - Admin sets consent text, checkbox displays
  - Visitor unchecks consent, submit fails → 422 error
- [x] **License Gating:**
  - Non-Supporters: can see visitor form, admin returns 403/access denied
  - Supporters: full access to all features
- [x] **Rate Limiting:**
  - Submit 5 messages quickly → all pass
  - 6th submission → 429 error
- [x] **Performance:**
  - Admin page loads 100 messages in < 2 seconds
  - Searches filter responsively
- [x] No console errors or warnings in any flow

---

## Final Verification & Commit

- [x] All 16 increments completed ✓
- [x] All tests pass: `php artisan test`
- [x] PHPStan clean: `make phpstan` (0 errors in new code)
- [x] Code style applied: `vendor/bin/php-cs-fixer fix`
- [x] Frontend checks pass: `npm run format && npm run check`
- [x] All language translations complete (22 languages)
- [x] Knowledge map updated
- [x] Changelog entry added
- [x] Feature status changed to "Complete" in roadmap
- [x] Commit staged with message per commit protocol

---

*Last updated: 2026-02-28*
