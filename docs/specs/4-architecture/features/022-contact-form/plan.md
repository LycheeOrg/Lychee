# Feature 022: Contact Form — Implementation Plan

**Status:** Ready for Implementation
**Last Updated:** 2026-02-28

---

## Overview

This plan outlines the step-by-step implementation of the Contact Form feature, breaking down backend and frontend development into logical increments, each expected to complete within 90 minutes.

**Total Estimated Effort:** 12–14 increments (12–18 hours)

---

## Phase 1: Database & Backend Infrastructure (4 increments)

### I1: Migration & Model Setup
**Goal:** Create database table and Eloquent model
**Tasks:**
- [ ] Create migration: `create_contact_messages_table`
  - Columns: `id`, `name`, `email`, `message`, `is_read`, `ip_address`, `user_agent`, `created_at`, `updated_at`
  - Indexes on `created_at` and `is_read`
- [ ] Create `ContactMessage` Eloquent model with `$fillable`, `$casts`, `$hidden` properties
- [ ] Create factory `ContactMessageFactory` for seeding/testing
- [ ] Run migration and verify table structure
- [ ] Add model to knowledge map if needed

**Verification:**
- Migration runs cleanly: `php artisan migrate`
- Model instantiation works: `ContactMessage::factory()->create()`
- Database schema matches spec

---

### I2: Configuration & Settings System
**Goal:** Add all 7 config keys to settings system
**Tasks:**
- [ ] Create settings category `ContactForm` in admin UI
- [ ] Add 7 config keys (sample Q&A, security Q&A, consent, privacy URL, submit button)
  - Use generic settings table or dedicated config file per existing pattern
  - Ensure all keys have defaults as per spec
- [ ] Add license gate: all settings restricted to Supporters tier
- [ ] Verify settings appear in admin Settings UI (if applicable)
- [ ] Add translations for setting labels in 2–3 primary languages

**Verification:**
- All 7 keys appear in database/config
- License page shows Supporters-only badge
- Admin can view/edit all settings without errors

---

### I3: Backend API Routes & Requests
**Goal:** Set up API routes and request validation classes
**Tasks:**
- [ ] Register routes:
  - `POST /api/v2/contact` (public)
  - `GET /api/v2/contact` (admin, `may_administrate` middleware)
  - `PATCH /api/v2/contact/{id}` (admin)
  - `DELETE /api/v2/contact/{id}` (admin)
- [ ] Create `StoreContactMessageRequest`:
  - Validate name, email, message fields
  - Optional: security_answer and consent_agreed fields
  - Max lengths, required rules per spec
- [ ] Create `UpdateContactMessageRequest` for PATCH (validate `is_read` boolean)
- [ ] Middleware: add `may_administrate` check to admin endpoints
- [ ] Test routes return 401/403 appropriately

**Verification:**
- `php artisan route:list` shows correct routes
- Requests class methods compile without error
- Manual curl tests show proper auth behavior

---

### I4: API Controller & Business Logic
**Goal:** Implement API controller with full CRUD logic
**Tasks:**
- [ ] Create `ContactMessageController` with methods:
  - `store()` - POST handler: validate, create, rate-limit check
  - `index()` - GET handler: paginate, filter by `is_read`, search support
  - `update()` - PATCH handler: update `is_read` flag
  - `destroy()` - DELETE handler: remove message
- [ ] Create `ContactFormService` class:
  - `submitMessage($data)` - creates message, sanitizes input
  - `validateSecurityAnswer($userAnswer)` - case-insensitive match against config
  - `checkRateLimit($ipAddress)` - enforce 5/24hr rule
  - `getMessages($filters, $pagination)` - query and filter
- [ ] Implement rate-limiting logic (store attempts by IP in cache or DB)
- [ ] Create `ContactMessageResource` for API responses
- [ ] Handle all validation errors gracefully

**Verification:**
- POST endpoint creates message, returns 200 or 422
- Rate limit returns 429 after 5 attempts from same IP
- Admin endpoints return 403 for unauthorized users
- All fields sanitized (no XSS/code injection)

---

## Phase 2: Frontend — Visitor Form (2–3 increments)

### I5: Visitor Contact Form Page
**Goal:** Build public contact form Vue3 SPA page
**Tasks:**
- [ ] Create component `resources/js/pages/Contact.vue` (composition API + TypeScript)
- [ ] Form structure:
  - Name input field (required, max 255)
  - Email input field (required, max 255)
  - Message textarea (required, min 10, max 5000)
  - Optional security question display & input (if config set)
  - Optional sample Q&A display section (if config set)
  - Optional consent checkbox with privacy link (if config set)
  - Submit button with custom text (if config set, else default)
- [ ] Form validation:
  - Client-side validation with error messages
  - Show required indicators
  - Real-time character count for message field
- [ ] State management (ref, reactive):
  - Form data (name, email, message, security_answer, consent_agreed)
  - Loading state during submission
  - Success/error messages
- [ ] Call `submitContactMessage()` from service on submit
- [ ] Error handling: display validation errors and general errors
- [ ] Success: show thank-you message, optionally clear form
- [ ] Style with PrimeVue components (InputText, Textarea, Button, etc.)

**Verification:**
- Form renders without console errors
- Validation rules trigger correctly
- Submission calls API endpoint
- Success and error states display properly
- All optional fields show/hide conditionally based on config

---

### I6: Visitor Form Service & Config Fetch
**Goal:** Create axios service for contact form and fetch config on page load
**Tasks:**
- [ ] Create `resources/js/services/contactService.ts`:
  - `submitContactMessage(data)` - POST to `/api/v2/contact`
  - Handle 422, 429 errors gracefully
- [ ] Create config fetch utility (or reuse existing fetch):
  - Fetch 7 config keys on Contact page mount
  - Store in component state or global store (Pinia)
- [ ] Display form elements conditionally based on fetched config
- [ ] Translations: add to `lang/en/contact.php` and mirror to 2–3 other languages
- [ ] Accessibility:
  - Label-input associations
  - ARIA attributes for error messages
  - Keyboard navigation support

**Verification:**
- Service methods callable without error
- Config fetches on page load
- Form fields appear based on config values
- Translations display correctly
- Form is keyboard-accessible

---

### I7: Visitor Form Styling & Responsiveness
**Goal:** Apply CSS and ensure mobile-friendly layout
**Tasks:**
- [ ] Style form container, inputs, buttons (PrimeVue consistency)
- [ ] Responsive layout:
  - Desktop: form in centered column (max-width ~600px)
  - Tablet/Mobile: full-width with padding
- [ ] Error message styling (red text, icon)
- [ ] Success message styling (green, dismissible)
- [ ] Loading state: button disabled, spinner during submission
- [ ] Optional: add page title, intro text, footer info
- [ ] RTL support (if required by existing app)
- [ ] Browser compatibility test (Chrome, Firefox, Safari)

**Verification:**
- No layout breaks on mobile, tablet, desktop
- Styling matches app design system
- Button states (hover, active, disabled) work
- Form submits and displays confirmation

---

## Phase 3: Frontend — Admin Messages Page (3–4 increments)

### I8: Admin Messages List View
**Goal:** Build admin page to view all contact messages
**Tasks:**
- [ ] Create component `resources/js/pages/Admin/ContactMessages.vue` (composition API + TypeScript)
- [ ] Page structure:
  - Title "Contact Messages"
  - Filter controls: Read/Unread toggle, Search input
  - Paginated table with columns: Name, Email, Preview, Date, Read Checkbox
  - Empty state message if no messages
- [ ] Data fetching:
  - Fetch messages on mount: `GET /api/v2/contact?page=1&per_page=20`
  - Pagination: next/prev buttons or page number selector
  - Filters: pass `is_read` and `search` params to API
- [ ] Table interactions:
  - Click row to expand/modal showing full message
  - Checkbox column to toggle read status (calls API)
  - Delete button per row (with confirmation modal)
- [ ] State management (reactive):
  - Messages list, total count
  - Current filters (is_read, search)
  - Current page
  - Loading state

**Verification:**
- Page loads without console errors
- Messages display in table
- Pagination works (next/prev)
- Clicking row expands message
- Checkbox toggle calls API
- Delete button shows confirmation

---

### I9: Admin Messages Interaction Features
**Goal:** Implement mark-as-read, expand detail, delete functionality
**Tasks:**
- [ ] Expand/Modal:
  - Show full message on row click (modal or inline expand)
  - Display: name, email, message, date, status
  - Read checkbox toggle in expanded view
- [ ] Mark-as-Read:
  - Checkbox in table: on change, call PATCH `/api/v2/contact/{id}`
  - Update row UI immediately (optimistic)
  - Handle API errors (revert checkbox if PATCH fails)
- [ ] Delete Message:
  - Delete button on each row
  - Confirmation modal: "Are you sure?"
  - Call DELETE `/api/v2/contact/{id}`
  - Remove row from table on success
  - Handle errors gracefully
- [ ] Bulk delete (optional for I9 or defer to I10):
  - Checkbox on each row to select
  - "Delete selected" button
  - Bulk delete API endpoint or loop individual calls

**Verification:**
- Expanded view shows correct message content
- Checkbox toggle updates is_read in DB
- Delete removes message from table
- All interactions handle errors (show toast/snackbar)
- Optimistic updates revert on API error

---

### I10: Admin Messages Search & Filtering
**Goal:** Add search and advanced filtering
**Tasks:**
- [ ] Search input: filter by name, email, or message content (server-side)
- [ ] Filters:
  - Read/Unread toggle (single-select or tri-state)
  - Sort options: date ascending, date descending
- [ ] API integration:
  - Pass `search`, `is_read`, `sort` params to GET endpoint
  - Refetch messages on filter change
- [ ] UI feedback:
  - Show active filter badges
  - "X results found" message
  - No results state message

**Verification:**
- Typing in search field refetches messages
- Filter toggles update results
- Sort order changes displayed list
- Results count updates
- "No results" state appears when appropriate

---

### I11: Admin Messages Styling & Polish
**Goal:** Style admin page, ensure consistency with app design
**Tasks:**
- [ ] Table styling:
  - Header row with proper contrast
  - Alternating row colors or subtle grid lines
  - Hover effects on rows
  - Responsive: collapse columns on mobile if needed, or horizontal scroll
- [ ] Modal/Expand styling:
  - Clean modal with close button
  - Message text wrapping and formatted display
  - Proper spacing
- [ ] Button styling:
  - Delete, confirm, cancel buttons styled consistently
  - States (hover, disabled)
  - Icons from PrimeVue icon set
- [ ] Loading, empty, error states:
  - Spinner during data load
  - Empty state with icon and message
  - Error toast notifications
- [ ] Accessibility:
  - Table navigable by keyboard
  - Screen reader labels for buttons and checkboxes
  - Focus indicators visible

**Verification:**
- Admin page matches app design system
- All states render (loading, empty, error, data)
- No console warnings
- Keyboard navigation works
- Mobile view is usable

---

## Phase 4: Testing & Quality (2–3 increments)

### I12: Unit Tests
**Goal:** Write unit tests for service classes and model logic
**Tasks:**
- [ ] `ContactFormService` tests:
  - `validateSecurityAnswer()` - case-insensitive match, exact mismatch
  - `checkRateLimit()` - returns true under limit, false over limit
  - `getMessages()` - filters work, pagination works
- [ ] `ContactMessage` model tests:
  - Factory creates valid model
  - Fillable fields set correctly
  - Timestamps auto-set
- [ ] Run tests: `php artisan test tests/Unit/Services/ContactFormServiceTest.php`
- [ ] Aim for > 80% code coverage for services

**Verification:**
- All unit tests pass: `php artisan test`
- No skipped tests
- Coverage report shows > 80% for services

---

### I13: Feature Tests (API & Integration)
**Goal:** Write end-to-end tests for API endpoints and controller logic
**Tasks:**
- [ ] **POST /api/v2/contact tests:**
  - Valid submission → 200, message stored
  - Missing field → 422 validation error
  - Security answer incorrect → 422 error
  - Consent not checked → 422 error
  - Rate limit exceeded (6th submission in 24hr) → 429
- [ ] **GET /api/v2/contact tests:**
  - Unauthorized user → 403
  - Authorized user → 200, paginated messages
  - Filter by is_read → results filtered
  - Search by name/email/message → results filtered
- [ ] **PATCH /api/v2/contact/{id} tests:**
  - Valid update → 200, message marked read
  - Unauthorized → 403
  - Invalid ID → 404
- [ ] **DELETE /api/v2/contact/{id} tests:**
  - Valid delete → 204, message gone
  - Unauthorized → 403
  - Invalid ID → 404
- [ ] Rate limit edge case: exactly 5 in 24hr should pass, 6th should fail
- [ ] Run tests: `php artisan test tests/Feature_v2/Api/ContactMessageControllerTest.php`

**Verification:**
- All feature tests pass: `php artisan test`
- No skipped tests
- Code coverage > 85% for controllers and services

---

### I14: Static Analysis & Quality Gates
**Goal:** Run code quality checks and fix violations
**Tasks:**
- [ ] **PHP Code Style:**
  - Run `vendor/bin/php-cs-fixer fix` and fix any style issues
  - Ensure PSR-4 compliance, snake_case variables, strict comparison (===)
  - Check for no use of `empty()`, proper `in_array($a, $b, true)`
- [ ] **PHPStan:**
  - Run `make phpstan` and fix all level 6 errors in new code
  - Add stubs if necessary for external libraries
- [ ] **Frontend:**
  - Run `npm run format` to apply Prettier
  - Run `npm run check` to lint Vue/TS files
- [ ] **Test coverage:**
  - Run all tests: `php artisan test`
  - Aim for > 85% coverage for new code
- [ ] **Manual review:**
  - Code follows conventions (license headers, proper structure)
  - No console warnings/errors
  - No TypeScript errors

**Verification:**
- `make phpstan` runs clean (0 errors in new code)
- `php artisan test` all pass
- `npm run check` passes
- `npm run format` has no outstanding changes
- All files have proper license headers

---

## Phase 5: Integration & Deployment (1–2 increments)

### I15: Translation Completeness
**Goal:** Add translations for all 22 supported languages
**Tasks:**
- [ ] Add entries to `lang/en/contact.php` (primary language)
- [ ] Copy and translate to other 21 language files:
  - `lang/fr/contact.php`, `lang/de/contact.php`, etc.
  - Use existing translation patterns or tools
- [ ] Verify all keys are present in all language files
- [ ] Test UI with RTL language (e.g., Arabic) if applicable
- [ ] Check for proper punctuation/formatting in all languages

**Verification:**
- All language files contain all keys
- Admin UI and visitor form display correct language
- No missing translation warnings in logs

---

### I16: Final Integration & E2E Testing
**Goal:** Complete integration testing and verify all components work together
**Tasks:**
- [ ] **Visitor flow:**
  - Navigate to `/contact`
  - Fill form with all fields (including optional ones if configured)
  - Submit and verify success message
  - Check database for new message
- [ ] **Admin flow:**
  - Log in as admin with `may_administrate` permission
  - Navigate to `/security/contact-messages`
  - Verify messages appear in list
  - Toggle read status, verify changes persist
  - Delete a message, verify removal
  - Test filters and search
- [ ] **Configuration:**
  - Admin creates sample Q&A in settings
  - Visitor form shows sample Q&A
  - Admin sets security question and answer
  - Visitor form shows security question
  - Visitor submits with wrong security answer → 422 error
  - Visitor submits with correct answer → success
  - Admin sets consent text and privacy URL
  - Visitor form shows consent checkbox with link
  - Visitor unchecks consent → 422 error on submit
- [ ] **License gating:**
  - Non-Supporters user can see visitor form but admin pages show access denied
  - Supporters user can see and use all features
- [ ] **Rate limiting:**
  - Submit 5 messages in quick succession (same IP) → all succeed
  - 6th attempt → 429 error
  - Wait 24+ hours, reset attempts, 5 more succeed
- [ ] Performance:
  - Admin page loads with 100 messages → < 2 seconds
  - Search filters messages responsive (< 500ms)

**Verification:**
- All flows work end-to-end
- Database state matches expected (messages created, read flag updated, deleted)
- No console errors or warnings
- License gate works correctly
- Rate limiting prevents spam

---

## Rollout Checklist

- [ ] All 16 increments completed and verified
- [ ] All unit and feature tests pass
- [ ] PHPStan clean (0 errors in new code)
- [ ] PHP code style applied
- [ ] Frontend checks pass (Prettier, lint)
- [ ] All language translations complete (22 languages)
- [ ] Knowledge map updated with ContactMessage model and endpoints
- [ ] User documentation (if needed) written
- [ ] Changelog entry added
- [ ] Feature marked Complete in roadmap
- [ ] Commit message prepared and staged

---

## Key Dependencies

- Laravel authentication & authorization system
- Existing settings UI framework
- Pinia store (if using global state)
- Axios HTTP client
- PrimeVue component library
- PHPUnit test framework
- No new external dependencies (use existing stack)

---

## Risk Mitigations

| Risk | Mitigation |
|------|-----------|
| Rate limit skewed by proxy IPs | Store by IP appropriately; consider X-Forwarded-For |
| Message storage unscalable at high volume | Index `created_at` and `is_read`; paginate results |
| Security answer brute-force | Rate limit by IP; display generic error ("Answer incorrect") |
| Spam via form | Rate limiting (5/24hr) + optional captcha (future enhancement) |
| Admin page slow with 10K+ messages | Pagination (20/page default); elastic search if needed later |

---

## Estimated Timeline

- **Phase 1 (Backend infra):** 4 hours → I1–I4
- **Phase 2 (Visitor form):** 3 hours → I5–I7
- **Phase 3 (Admin page):** 4 hours → I8–I11
- **Phase 4 (Testing):** 3 hours → I12–I14
- **Phase 5 (Integration):** 2 hours → I15–I16

**Total: ~16 hours** (12–18 hour range realistic depending on surprises)

---

*Last updated: 2026-02-28*
