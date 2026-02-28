# Feature 022: Contact Form

**Status:** Planning
**License:** Supporters Only
**Last Updated:** 2026-02-28

---

## Specification Summary

A dual-page contact form system allowing visitors to submit messages and administrators (with `may_administrate` permission) to manage received messages. The feature includes configurable security questions, consent text, and customizable UI elements. No email notifications are provided—messages are managed within the admin panel only.

---

## Functional Requirements

### 1. Visitor Contact Form Page (Public)

**Endpoint & Access:**
- Route: `GET /contact` (public route, available to all users/guests)
- Vue3 SPA page with TypeScript composition API

**Form Elements:**
- A text input field for the visitor's name/identifier
- A text input field for an email or contact method
- A message textarea for the contact message
- Optional: Security question challenge (if configured, admin-optional field)
- Consent checkbox (if configured, required for submission):
  - Text rendered from `contact_form_custom_consent_text` config
  - Link to privacy URL from `contact_form_custom_privacy_url` config
- Sample Q&A display section (optional):
  - If configured, displays a read-only Q&A pair from `contact_form_sample_question` and `contact_form_sample_answer`
  - Helps users understand the expected format or nature of responses

**Form Submission:**
- POST endpoint: `api/v2/contact` 
- Stores message to database immediately upon submission
- Response: Success confirmation message or validation errors
- No email sent to admin; messages must be viewed via admin panel

**Form Validation:**
- Name field: required, max 255 characters
- Email/contact field: required, max 255 characters
- Message field: required, min 10 characters, max 5000 characters
- Security answer (if configured): exact match against `contact_form_security_answer` (case-insensitive comparison)
- Consent checkbox (if configured): must be checked

**Rate Limiting:**
- Implement basic rate limiting: max 5 submissions per IP per 24 hours (configurable via future env or code constant)

---

### 2. Admin Messages Management Page

**Endpoint & Access:**
- Route: `GET /security/contact-messages` (admin panel, requires `may_administrate` permission)
- Vue3 SPA page with TypeScript composition API
- Returns 403 if user lacks `may_administrate` permission

**Display:**
- Table/list view of all contact messages
- Columns:
  - Submitter name
  - Submitter email/contact
  - Message preview (truncated or expanded)
  - Date submitted
  - Read status (checkbox)
- Sort options: by date (newest first, oldest first, most recent first)
- Filter options: by read/unread status

**Message Interaction:**
- Click on a message row to expand and view full message
- Checkbox to mark message as read/unread
  - Persist status change immediately via PATCH endpoint
  - Mark single message: `PATCH /api/v2/contact/{id}` with `{ is_read: boolean }`
  - Bulk operations (optional future enhancement): mark all as read

**Delete Messages:**
- Trash icon or delete button per message
  - DELETE endpoint: `DELETE /api/v2/contact/{id}`
- Optional: bulk delete (checkbox select + delete button)

**Display Options:**
- Pagination or infinite scroll (configurable page size, default 20)
- Search by name, email, or message content

---

## Configuration Schema

Create new `ContactForm` settings category in admin panel with the following keys:

### Sample Q&A (Optional)

- **Key:** `contact_form_sample_question`
  - **Type:** String (text)
  - **Default:** `null` (empty/unconfigured)
  - **Max length:** 500 characters
  - **Description:** Example question displayed to visitors on the contact form for guidance

- **Key:** `contact_form_sample_answer`
  - **Type:** String (text)
  - **Default:** `null` (empty/unconfigured)
  - **Max length:** 2000 characters
  - **Description:** Example answer displayed alongside the sample question

### Security Question (Optional)

- **Key:** `contact_form_security_question`
  - **Type:** String (text)
  - **Default:** `null` (empty/unconfigured)
  - **Max length:** 500 characters
  - **Description:** Security/SPAM prevention question shown to all form submitters

- **Key:** `contact_form_security_answer`
  - **Type:** String (text)
  - **Default:** `null` (empty/unconfigured)
  - **Max length:** 500 characters
  - **Description:** Expected answer to the security question (exact match, case-insensitive)

### Consent & Privacy (Optional)

- **Key:** `contact_form_custom_consent_text`
  - **Type:** String (text)
  - **Default:** `null` (empty/unconfigured)
  - **Max length:** 1000 characters
  - **Description:** Custom consent text displayed above the checkbox; if set, the consent checkbox becomes required

- **Key:** `contact_form_custom_privacy_url`
  - **Type:** String (URL)
  - **Default:** `null` (empty/unconfigured)
  - **Max length:** 2048 characters
  - **Description:** URL to your privacy policy; if set, displayed as a link alongside consent text

### UI Customization

- **Key:** `contact_form_custom_submit_button_text`
  - **Type:** String (text)
  - **Default:** `"Send Message"` (or language-specific equivalent from locale)
  - **Max length:** 100 characters
  - **Description:** Custom text for the submit button on the visitor form

---

## Data Model

### ContactMessage Table

New table: `contact_messages`

**Columns:**
- `id` (UUID, primary key)
- `name` (string, max 255)
- `email` (string, max 255)
- `message` (text)
- `is_read` (boolean, default: false)
- `ip_address` (string, nullable, for rate-limiting reference)
- `user_agent` (string, nullable, for diagnostic purposes)
- `created_at` (timestamp, auto-set)
- `updated_at` (timestamp, auto-set)

**Indexes:**
- `created_at` (for sorting)
- `is_read` (for filtering)

---

## API Endpoints

### Visitor Endpoints

**POST `/api/v2/contact`**
- Description: Submit a new contact message
- Authentication: None required (public)
- Request body:
  ```json
  {
    "name": "John Doe",
    "email": "john@example.com",
    "message": "I have a question about...",
    "security_answer": "security_word" (optional if security question is configured),
    "consent_agreed": true (optional, required if consent text is configured)
  }
  ```
- Response (200):
  ```json
  {
    "success": true,
    "message": "Thank you for your message. We will get back to you soon."
  }
  ```
- Response (422): Validation error
  ```json
  {
    "errors": {
      "message": ["Message must be at least 10 characters."]
    }
  }
  ```
- Response (429): Rate limit exceeded

### Admin Endpoints

**GET `/api/v2/contact`**
- Description: List all contact messages
- Authentication: Requires `may_administrate` permission
- Query parameters:
  - `page` (int, default: 1)
  - `per_page` (int, default: 20, max: 100)
  - `is_read` (boolean, optional filter)
  - `search` (string, optional search term)
- Response (200):
  ```json
  {
    "data": [
      {
        "id": "uuid",
        "name": "John Doe",
        "email": "john@example.com",
        "message": "Message content...",
        "is_read": false,
        "created_at": "2026-02-28T10:30:00Z"
      }
    ],
    "pagination": {
      "total": 42,
      "per_page": 20,
      "current_page": 1
    }
  }
  ```

**PATCH `/api/v2/contact/{id}`**
- Description: Update message (mark as read/unread)
- Authentication: Requires `may_administrate` permission
- Request body:
  ```json
  {
    "is_read": true
  }
  ```
- Response (200): Updated message object

**DELETE `/api/v2/contact/{id}`**
- Description: Delete a contact message
- Authentication: Requires `may_administrate` permission
- Response (204): No content

---

## UI Mockups

### Visitor Contact Form Page

```
┌─────────────────────────────────────────────────┐
│                   Contact Us                    │
├─────────────────────────────────────────────────┤
│                                                 │
│  We'd love to hear from you!                    │
│                                                 │
│  Name/Identifier:                               │
│  [________________________________]              │
│                                                 │
│  Email or Contact Method:                       │
│  [________________________________]              │
│                                                 │
│  [Optional] Sample Q&A:                         │
│  Q: How do I upload photos?                     │
│  A: Visit the upload section and select files.  │
│                                                 │
│  [Optional] Security Question:                  │
│  What is the capital of France?                 │
│  [________________________________]              │
│                                                 │
│  Message:                                       │
│  [________________________________]              │
│  [________________________________]              │
│  [________________________________]              │
│                                                 │
│  [Optional] ☐ I agree to the privacy policy     │
│              (see privacy)                      │
│                                                 │
│  [Send Message]  [Clear]                        │
└─────────────────────────────────────────────────┘
```

### Admin Messages Management Page

```
┌─────────────────────────────────────────────────────────────┐
│  Contact Messages                                  [Admin]   │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  Filter: ☐ Unread   ☐ Read     Search: [___________]       │
│                                                             │
│  ┌─────────────────────────────────────────────────────┐   │
│  │ ☐ Name    │ Email          │ Preview  │ Date │ ✓   │   │
│  ├─────────────────────────────────────────────────────┤   │
│  │ ☐ John Doe│john@ex...      │ I have a │2/28  │ ☐   │◄──┛ checkbox
│  ├─────────────────────────────────────────────────────┤   │ for read
│  │ ☐ Jane    │jane@exa...     │ Question │2/27  │ ☑   │   │
│  │   Smith   │                │ about... │      │     │   │
│  ├─────────────────────────────────────────────────────┤   │
│  │ ☐ Bob     │bob@exam...     │ Feature  │2/26  │ ☐   │   │
│  │           │                │ request  │      │     │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                             │
│  On row click, expand to show full message:                │
│  ┌─────────────────────────────────────────────────────┐   │
│  │ From: John Doe (john@example.com)     [Expand ▼]   │   │
│  │ Date: 2026-02-28 10:30 AM                          │   │
│  │ Status: Mark as read ☐                            │   │
│  ├─────────────────────────────────────────────────────┤   │
│  │ I have a question about the tagging feature...     │   │
│  │ [Full message content displayed here]              │   │
│  │                                          [Delete 🗑]│   │
│  └─────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

---

## Non-Functional Requirements

1. **License Gating:** Feature restricted to Supporters license tier
2. **Rate Limiting:** 5 submissions per IP per 24 hours (prevent spam)
3. **Performance:** Admin page must load within 2 seconds for < 1000 messages
4. **Security:**
   - Input sanitization on all text fields
   - CSRF protection on form submission
   - No executable code in stored messages
   - Rate-limiting by IP to prevent spam floods
5. **Accessibility:**
   - Form labels associated with inputs
   - Keyboard navigation support
   - ARIA attributes for screen readers
   - Color contrast compliance

---

## Database Migrations

- Create `contact_messages` table with indexed `created_at` and `is_read` columns
- Optional: add `contact_form_settings` table or store in generic `settings` table by category

---

## Translation Keys (i18n)

New translation entries under `lang/<locale>/contact.php`:

- `contact.title` - "Contact Us"
- `contact.name_label` - "Name"
- `contact.email_label` - "Email"
- `contact.message_label` - "Message"
- `contact.consent_label` - "I agree to the privacy policy"
- `contact.security_question_label` - "Security Question"
- `contact.sample_qa_label` - "Sample Q&A"
- `contact.submit_button` - "Send Message" (default)
- `contact.success_message` - "Thank you for your message"
- `contact.submit_error` - "An error occurred while submitting your message"
- `admin.contact_messages` - "Contact Messages"
- `admin.contact_read` - "Read"
- `admin.contact_unread` - "Unread"
- `admin.contact_delete_confirm` - "Are you sure you want to delete this message?"

---

## Implementation Approach

### Backend (Laravel)

1. **Model & Migration:**
   - Create `ContactMessage` Eloquent model
   - Create migration for `contact_messages` table
   - Add factory for testing

2. **Configuration:**
   - Add config keys to `config/contact.php` or integrate into settings system
   - Ensure all 7 keys are gatable by Supporters license

3. **Routes:**
   - Public: `POST /api/v2/contact` - submit form
   - Admin: `GET|PATCH|DELETE /api/v2/contact/{id}` - manage messages

4. **API Resources:**
   - Create `ContactMessageResource` for responses
   - Create `ContactMessageListResource` for list view

5. **Requests:**
   - `StoreContactMessageRequest` - visitor form submission validation
   - `UpdateContactMessageRequest` - admin mark-as-read
   - Rate-limiting middleware

6. **Services:**
   - `ContactFormService` - handle submission logic, rate limiting
   - Validation of security question, consent check

### Frontend (Vue3 + TypeScript)

1. **Visitor Page (`resources/js/pages/Contact.vue`):**
   - Composition API with TypeScript
   - Form state management
   - Axios service call to POST `/api/v2/contact`
   - Error/success messaging
   - Conditional rendering of optional fields

2. **Admin Page (`resources/js/pages/Admin/ContactMessages.vue`):**
   - Table/list view of messages with pagination
   - Inline checkbox to mark as read
   - Row expansion/modal for full message
   - Delete button with confirmation
   - Search and filter controls

3. **Axios Service (`resources/js/services/contactService.ts`):**
   - `submitContactMessage()`
   - `getMessages()`
   - `updateMessage()`
   - `deleteMessage()`

4. **Store Configuration (`resources/js/services/admin/contactSettingsService.ts`):**
   - API calls to retrieve/update admin settings

---

## Testing Strategy

### Unit Tests
- `ContactFormService::validateSecurityAnswer()` - exact match, case-insensitive
- `ContactFormService::checkRateLimit()` - IP-based limiting
- Contact message model relations

### Feature Tests
- POST `/api/v2/contact` - valid submission, validation errors, rate limit
- GET `/api/v2/contact` - admin access control, filters, pagination
- PATCH `/api/v2/contact/{id}` - mark as read/unread, permission check
- DELETE `/api/v2/contact/{id}` - delete message, permission check

### Frontend Tests (Optional)
- Form validation and submission
- Admin page load and interaction
- Checkbox toggle for read status

---

## Licensing & Feature Gates

All components require `Supporters` license tier:
- Visitor form page visibility (if fully gated)
- Admin management page (enforced via middleware)
- All configuration options

Consider: Should the visitor form be always visible but messages only stored for Supporters? Or fully hidden for non-Supporters? Default recommendation: **Always visible to visitors, but only Supporters can manage/view messages in admin panel.**

---

## Open Questions

None at this time—all requirements captured per user clarification.

---

## Related ADRs

- None initially; update if architectural decisions arise during implementation.

---

*Last updated: 2026-02-28*
