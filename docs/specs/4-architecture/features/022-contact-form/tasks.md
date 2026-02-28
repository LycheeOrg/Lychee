# Feature 022: Contact Form — Task Checklist

**Status:** Ready for Implementation
**Last Updated:** 2026-02-28

---

## Phase 1: Database & Backend Infrastructure

### Increment 1: Migration & Model Setup
- [ ] Create migration `create_contact_messages_table` with all columns per spec
- [ ] Add proper indexes on `created_at` and `is_read`
- [ ] Create `ContactMessage` Eloquent model with fillable/casts/hidden
- [ ] Create `ContactMessageFactory` for testing
- [ ] Run migration: `php artisan migrate` (verify success)
- [ ] Test factory: `ContactMessage::factory()->create()` (verify works)
- [ ] Update knowledge map entry for ContactMessage model

### Increment 2: Configuration & Settings System
- [ ] Create settings category UI component for "Contact Form"
- [ ] Add 7 config keys to settings:
  - `contact_form_sample_question`
  - `contact_form_sample_answer`
  - `contact_form_security_question`
  - `contact_form_security_answer`
  - `contact_form_custom_consent_text`
  - `contact_form_custom_privacy_url`
  - `contact_form_custom_submit_button_text`
- [ ] Set defaults per spec (most null, submit button = "Send Message")
- [ ] Lock all settings to Supporters license tier
- [ ] Add labels/descriptions to settings UI (2–3 primary languages)
- [ ] Verify settings appear in admin UI without errors

### Increment 3: Backend API Routes & Requests
- [ ] Register 4 new API routes:
  - `POST /api/v2/contact` (public)
  - `GET /api/v2/contact` (admin)
  - `PATCH /api/v2/contact/{id}` (admin)
  - `DELETE /api/v2/contact/{id}` (admin)
- [ ] Verify routes via `php artisan route:list`
- [ ] Add middleware `may_administrate` to admin routes
- [ ] Create `StoreContactMessageRequest` with validation rules
- [ ] Create `UpdateContactMessageRequest` (validate is_read boolean)
- [ ] Test auth with curl/Postman: 401 for public, 403 for unauthorized admin access

### Increment 4: API Controller & Business Logic
- [ ] Create `ContactMessageController` with 4 methods: store, index, update, destroy
- [ ] Create `ContactFormService` class with helper methods:
  - `submitMessage()`
  - `validateSecurityAnswer()`
  - `checkRateLimit()`
  - `getMessages()`
- [ ] Implement rate-limiting: 5 submissions per IP per 24 hours
- [ ] Create `ContactMessageResource` for API responses
- [ ] Implement input sanitization (XSS prevention)
- [ ] Handle validation errors with proper HTTP status codes (422, 429)
- [ ] Test all endpoints return expected responses (unit curl tests or Postman)

---

## Phase 2: Frontend — Visitor Form

### Increment 5: Visitor Contact Form Page
- [ ] Create `resources/js/pages/Contact.vue` (composition API + TypeScript)
- [ ] Add form fields: name, email, message (required)
- [ ] Track form state with reactive/ref
- [ ] Add client-side validation with error messages
- [ ] Conditionally render security question field (if config set)
- [ ] Conditionally render sample Q&A section (if config set)
- [ ] Conditionally render consent checkbox with privacy link (if config set)
- [ ] Add custom submit button text (if config set, else default)
- [ ] Use PrimeVue components (InputText, Textarea, Button, Checkbox)
- [ ] Show character count for message field
- [ ] Implement submit handler calling ContactFormService
- [ ] Display success message and clear form on success
- [ ] Display error toast/modal on validation or API errors
- [ ] Verify form renders without console errors

### Increment 6: Visitor Form Service & Config Fetch
- [ ] Create `resources/js/services/contactService.ts`:
  - `submitContactMessage(data)` - POST to `/api/v2/contact`
  - Error handling for 422 (validation) and 429 (rate limit)
- [ ] Fetch 7 config keys on page mount (store in component state or Pinia)
- [ ] Conditionally show/hide form fields based on config
- [ ] Add translations to `lang/en/contact.php`
- [ ] Test service methods compile and are callable
- [ ] Verify config fetch works and form fields update

### Increment 7: Visitor Form Styling & Responsiveness
- [ ] Style form container with max-width constraint
- [ ] Ensure responsive layout (mobile, tablet, desktop)
- [ ] Style error messages (red, icon)
- [ ] Style success message (green, dismissible)
- [ ] Add loading state to submit button during submission
- [ ] Style with PrimeVue theme for consistency
- [ ] Test mobile layout (no horizontal scroll, readable text)
- [ ] Test accessibility: labels, ARIA, keyboard navigation
- [ ] Verify form looks good on Chrome, Firefox, Safari

---

## Phase 3: Frontend — Admin Messages Page

### Increment 8: Admin Messages List View
- [ ] Create `resources/js/pages/Admin/ContactMessages.vue` (composition API + TypeScript)
- [ ] Add page title and intro
- [ ] Create table with columns: Name, Email, Preview, Date, Read Checkbox
- [ ] Fetch messages on mount: `GET /api/v2/contact` with pagination
- [ ] Handle pagination: next/prev or page selector
- [ ] Add filter controls: Read/Unread toggle, Search input
- [ ] Show "No messages" state when empty
- [ ] Track loading state and show spinner
- [ ] Implement row click to expand/show full message
- [ ] Verify table renders without console errors

### Increment 9: Admin Messages Interaction Features
- [ ] Implement expand/modal to show full message details
- [ ] Add checkbox in table/expanded view for read status toggle
- [ ] On checkbox change, call PATCH endpoint to update `is_read`
- [ ] Optimistic update: change UI immediately, revert on API error
- [ ] Add Delete button to each row with trash icon
- [ ] Show confirmation modal before deleting
- [ ] Call DELETE endpoint and remove row on success
- [ ] Show error toast if operation fails
- [ ] Test all interactions work and persist to database

### Increment 10: Admin Messages Search & Filtering
- [ ] Add search input field (search name, email, message)
- [ ] Refetch messages with search query on input change (debounce)
- [ ] Add Read/Unread filter toggle
- [ ] Add sort options (date ascending/descending)
- [ ] Pass filters to API: `is_read`, `search`, `sort`
- [ ] Show "X results found" message
- [ ] Show proper "No results" state when search returns nothing
- [ ] Test all filters work and update results correctly

### Increment 11: Admin Messages Styling & Polish
- [ ] Style table header with proper contrast
- [ ] Add row hover effects and alternating row colors
- [ ] Style delete and action buttons with PrimeVue icons
- [ ] Make table responsive (collapse/scroll on mobile)
- [ ] Style modal/expanded view with clean layout
- [ ] Add loading spinner state
- [ ] Add error notification styling
- [ ] Ensure keyboard navigation in table
- [ ] Test with screen reader (basic ARIA labels)
- [ ] Verify page looks consistent with app design system

---

## Phase 4: Testing & Quality

### Increment 12: Unit Tests
- [ ] Write tests for `ContactFormService::validateSecurityAnswer()`
  - Case-insensitive match test
  - Exact mismatch test
- [ ] Write tests for `ContactFormService::checkRateLimit()`
  - Under limit (returns true)
  - At limit (returns false)
  - Expires after 24 hours
- [ ] Write tests for `ContactMessage` model:
  - Factory creates valid model
  - Timestamps auto-set
  - Fillable/hidden properties work
- [ ] Run tests: `php artisan test tests/Unit/...`
- [ ] Aim for > 80% code coverage for services
- [ ] Verify no skipped tests

### Increment 13: Feature Tests (API & Integration)
- [ ] Test `POST /api/v2/contact`:
  - Valid submission stores message (200)
  - Missing required field returns 422
  - Security answer incorrect returns 422
  - Consent not checked returns 422
  - Rate limit: 5 passes, 6th returns 429
- [ ] Test `GET /api/v2/contact`:
  - Unauthorized returns 403
  - Authorized returns 200 with paginated results
  - Filter by is_read works
  - Search by name/email/message works
- [ ] Test `PATCH /api/v2/contact/{id}`:
  - Valid update returns 200, message marked read
  - Unauthorized returns 403
  - Invalid ID returns 404
- [ ] Test `DELETE /api/v2/contact/{id}`:
  - Valid delete returns 204, message removed
  - Unauthorized returns 403
  - Invalid ID returns 404
- [ ] Run tests: `php artisan test tests/Feature_v2/...`
- [ ] Aim for > 85% code coverage

### Increment 14: Static Analysis & Quality Gates
- [ ] Run `vendor/bin/php-cs-fixer fix` and fix style issues
- [ ] Run `make phpstan` and fix all level 6 errors
- [ ] Add license headers to new PHP files
- [ ] Verify PSR-4 compliance, snake_case variables, === comparison
- [ ] Verify no use of `empty()`, proper `in_array($a, $b, true)`
- [ ] Run `npm run format` to apply Prettier
- [ ] Run `npm run check` to lint TypeScript/Vue files
- [ ] Run all tests: `php artisan test` (all pass)
- [ ] Verify no TypeScript errors (`npm run build` or IDE check)

---

## Phase 5: Integration & Deployment

### Increment 15: Translation Completeness
- [ ] Add all translation keys to `lang/en/contact.php`
- [ ] Mirror keys to 21 other language files (lang/fr, lang/de, etc.)
- [ ] Translate entries to respective languages (or use placeholder)
- [ ] Verify all language files are complete (no missing keys)
- [ ] Test visitor form with English and one other language
- [ ] Test admin page with English and one other language
- [ ] Check RTL language (e.g., Arabic) renders correctly

### Increment 16: Final Integration & E2E Testing
- [ ] **Visitor Flow:**
  - Navigate to `/contact`, fill all fields, submit → success message
  - Check database for new message (correct data)
  - Test with optional fields (if configured)
- [ ] **Admin Flow:**
  - Log in as admin, navigate to `/security/contact-messages`
  - Verify messages display in list
  - Toggle read status, verify persists
  - Test search, filters, and sorting
  - Delete a message, verify removal
- [ ] **Configuration Test:**
  - Admin sets sample Q&A, visitor form shows it
  - Admin sets security question, visitor form shows it
  - Visitor submits with wrong answer → 422 error
  - Admin sets consent text, checkbox displays
  - Visitor unchecks consent, submit fails → 422 error
- [ ] **License Gating:**
  - Non-Supporters: can see visitor form, admin returns 403/access denied
  - Supporters: full access to all features
- [ ] **Rate Limiting:**
  - Submit 5 messages quickly → all pass
  - 6th submission → 429 error
- [ ] **Performance:**
  - Admin page loads 100 messages in < 2 seconds
  - Searches filter responsively
- [ ] No console errors or warnings in any flow

---

## Final Verification & Commit

- [ ] All 16 increments completed ✓
- [ ] All tests pass: `php artisan test`
- [ ] PHPStan clean: `make phpstan` (0 errors in new code)
- [ ] Code style applied: `vendor/bin/php-cs-fixer fix`
- [ ] Frontend checks pass: `npm run format && npm run check`
- [ ] All language translations complete (22 languages)
- [ ] Knowledge map updated
- [ ] Changelog entry added
- [ ] Feature status changed to "Complete" in roadmap
- [ ] Commit staged with message per commit protocol

---

*Last updated: 2026-02-28*
