# Open Questions – Feature 091

Open questions for [Feature 091](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-091-01~~ | 091 | High | Login identifier when the setting is on: A email only (`username` stays a separate profile field), B email or username accepted, C `username` column literally stores the email | Resolved (B, spec FR-091-03, FR-091-05) | 2026-10-13 | 2026-10-13 |
| ~~Q-091-02~~ | 091 | High | Users with no email (or whose email was cleared) when the setting is on: A keep the username fallback for them, B lock them out until an admin sets an email, C migration refuses to enable the setting while such users exist | Resolved (A, spec FR-091-04) | 2026-10-13 | 2026-10-13 |
| ~~Q-091-03~~ | 091 | Medium | Account creation surfaces when the setting is on (`RegisterPage`, admin user management, `SetUpAdminController`): A email is the only identifier field and `username` is derived from it, B both fields stay and email becomes required, C registration unchanged | Resolved (B + auto-fill, spec FR-091-07, FR-091-08, FR-091-12) | 2026-10-13 | 2026-10-13 |
| ~~Q-091-04~~ | 091 | Medium | Profile self-service (`SetLogin.vue`, `UpdateProfileRequest`) when the setting is on: A email becomes required and non-clearable, B email stays clearable, C the email field becomes read-only for non-admins | Resolved (B, spec FR-091-13) | 2026-10-13 | 2026-10-13 |

## Question Details

### ❓ Q-091-01 · Login identifier semantics

**Status:** Resolved 2026-10-13, option B (spec FR-091-03, FR-091-05)
**Feature:** F-091 – Email as login username
**Preferred option:** 🅰️ (**recommended**) Option A – Email replaces the login identifier, `username` stays a profile field

**Question**
A new setting lets the email be used as the login username. Does the setting make the email the *only* accepted login identifier, does it make the email an *additional* accepted identifier next to the username, or does it make the `username` column itself hold the email?

**Resolution:** Option B. With `email_login_enabled` on, the identifier is resolved to one attribute before any password is checked — `username` when an account carries it, `email` otherwise — and exactly one `Auth::attempt()` follows (spec FR-091-03). A username equal to another account's email therefore resolves to the username owner whatever password is typed (FR-091-05). The first implementation attempted username then email and so let the password decide; CodeRabbit flagged it on PR #4871 and it was corrected.

---

#### 🅰️ (**recommended**) Option A – Email is the login identifier
- **Idea:** With the setting on, `AuthController::login()` authenticates on `email` instead of `username`; the login form labels and autocompletes the field as an email and validates it as one. `users.username` keeps its current role as the display/mention identity and stays unique.
- **Spec impact:** `LoginRequest` validates an email when the setting is on; `AuthController` picks the `Auth::attempt()` credential key from the setting; `InitConfig` exposes the flag so v7/v8 can relabel the field. LDAP keeps its own identifier (see Non-Goals).
- **Pros:**
  - ✅ One identifier at a time, so there is no ambiguity about which account a credential refers to.
  - ✅ `users.email` already carries a unique index (`2021_12_04_181200_refactor_models`), so no schema change is needed.
  - ✅ The username keeps working everywhere it is displayed today (owner labels, sharing, user management).
- **Cons:**
  - ❌ Accounts without an email can no longer sign in — depends on Q-091-02.
  - ❌ Every account-creation surface must make the email mandatory (Q-091-03).

---

#### 🅱️ Option B – Email *or* username accepted
- **Idea:** With the setting on, the login attempt tries the username first and falls back to the email (or picks the credential key by whether the input looks like an email). The form label becomes "Username or email".
- **Spec impact:** `LoginRequest` accepts either shape; `AuthController` performs up to two `Auth::attempt()` calls.
- **Pros:**
  - ✅ No account is locked out, so Q-091-02 and Q-091-03 become non-questions.
  - ✅ Enabling or disabling the setting is non-breaking in both directions.
- **Cons:**
  - ❌ Two credentials per account widens the brute-force surface and the login log becomes ambiguous about what was tried.
  - ❌ A username that happens to equal another user's email resolves to whichever is attempted first.
  - ❌ Reads as "login also accepts email", not as "use the email as the login username".

---

#### 🅲 Option C – `username` stores the email
- **Idea:** With the setting on, registration and user creation write the email into `users.username` as well, so the existing username-based login path is untouched.
- **Spec impact:** No change to `AuthController`/`LoginRequest`; changes concentrate in `Actions\User\Create`, `RegistrationRequest`, and `UpdateLogin`.
- **Pros:**
  - ✅ The authentication path itself stays exactly as it is today.
  - ✅ Existing accounts keep logging in with whatever is in `username`.
- **Cons:**
  - ❌ Email addresses leak into every place the username is displayed (album owner labels, user management, RSS, embeds).
  - ❌ Two columns must be kept in sync forever; changing the email has to rewrite the username, and `username` is capped at 100 chars.
  - ❌ Toggling the setting off leaves accounts whose username is an email address.

---

### ❓ Q-091-02 · Accounts without an email

**Status:** Resolved 2026-10-13, option A (spec FR-091-04)
**Feature:** F-091 – Email as login username
**Preferred option:** 🅰️ (**recommended**) Option A – Keep the username fallback for accounts with no email

**Question**
`users.email` is nullable, and `UpdateLogin::updateEmail()` lets a user clear it. If the setting makes the email the login identifier (Q-091-01 A), what happens to accounts that have no email?

**Resolution:** Option A, which Option B of Q-091-01 already provides: the username attempt comes first, so an account with no email keeps signing in with its username (spec FR-091-04).

---

#### 🅰️ (**recommended**) Option A – Username fallback for email-less accounts
- **Idea:** The login attempt resolves the account by email; if no account matches and the submitted value is not an email, fall back to a username attempt. In practice only email-less accounts (including the admin created at install) can still use it.
- **Spec impact:** Needs a failure-path requirement and a scenario for "no email on the account"; the login log records which identifier matched.
- **Pros:**
  - ✅ Enabling the setting on an existing instance cannot lock anybody out, including the admin.
  - ✅ No data migration and no blocked upgrade path.
- **Cons:**
  - ❌ Softens the "email only" guarantee of Q-091-01 A and inherits part of Option B's ambiguity.

---

#### 🅱️ Option B – Email-less accounts cannot sign in
- **Idea:** With the setting on, only an email authenticates. An admin must set an email on every account that needs access.
- **Spec impact:** A clean single-identifier rule; `UpdateProfileRequest` must stop accepting a null email (ties to Q-091-04 A).
- **Pros:**
  - ✅ Exactly one credential per account, which is what makes the setting predictable.
  - ✅ The login log stays unambiguous.
- **Cons:**
  - ❌ An admin who enables the setting without an email on their own account locks themselves out of the UI.
  - ❌ Needs a documented recovery path (artisan command or direct DB edit).

---

#### 🅲 Option C – Refuse to enable the setting while email-less accounts exist
- **Idea:** The setting can only be turned on when every account that may sign in has an email; the settings endpoint rejects the change otherwise and names the offending accounts.
- **Spec impact:** Validation logic on the settings update path plus a diagnostics check.
- **Pros:**
  - ✅ Keeps the single-identifier rule of Option B without the lock-out.
  - ✅ The error message tells the admin exactly what to fix.
- **Cons:**
  - ❌ Config values are set through the generic settings endpoint, so a per-key guard is a new pattern there.
  - ❌ Does not cover an email cleared *after* the setting was enabled, so Q-091-04 still has to forbid that.

---

### ❓ Q-091-03 · Account-creation surfaces

**Status:** Resolved 2026-10-13, option B + auto-fill (spec FR-091-07, FR-091-08, FR-091-12)
**Feature:** F-091 – Email as login username
**Preferred option:** 🅰️ (**recommended**) Option A – Email is the only identifier field, `username` is derived

**Question**
Three surfaces create accounts: self-registration (`RegistrationRequest`, `RegisterPage.vue`), admin user management (`AddUserRequest`), and the install-time admin setup (`SetUpAdminController`). What do they ask for when the setting is on?

**Resolution:** Option B, refined by owner directive: both fields stay and the email is **not** made required. If the username is omitted at creation, `Actions\User\Create` sets it to the email (spec FR-091-08). The registration form mirrors the email into the username field live while that field is untouched, so the user sees the value that will be used; editing the username stops the mirroring and clearing it resumes it (spec FR-091-12). Admin user management and the install-time admin setup are out of scope because neither collects an email (spec Non-Goals).

---

#### 🅰️ (**recommended**) Option A – Email only, `username` derived from it
- **Idea:** With the setting on, these forms drop the username field; `Actions\User\Create` derives `username` from the email (local part, de-duplicated on collision) so the display identity stays short and unique.
- **Spec impact:** `Create::do()` gains the derivation; `RegistrationRequest` and `AddUserRequest` make `username` optional and `email` required; both frontends hide the username field behind the flag.
- **Pros:**
  - ✅ One field to fill, which is the point of the setting.
  - ✅ Guarantees every new account has the email the login path needs.
  - ✅ Display surfaces never show a full email address.
- **Cons:**
  - ❌ Collision handling makes the derived username unpredictable (`john`, `john2`, …).
  - ❌ The user cannot choose their display identity at creation time; they must edit it afterwards.

---

#### 🅱️ Option B – Keep both fields, email becomes required
- **Idea:** The forms keep asking for a username (display identity) and an email (login identifier); with the setting on, the email is mandatory instead of optional.
- **Spec impact:** Only validation changes (`required` on email, conditional on the setting). No derivation logic.
- **Pros:**
  - ✅ Smallest change and no unpredictable derived values.
  - ✅ The user picks their own display identity.
  - ✅ The admin-setup and user-management forms keep their current shape.
- **Cons:**
  - ❌ Two fields for what the admin asked to be one login identifier.
  - ❌ `AddUserRequest` has no email field today, so one must be added.

---

#### 🅲 Option C – Creation surfaces unchanged
- **Idea:** Only the login path reads the setting; creation keeps email optional.
- **Spec impact:** None outside login.
- **Pros:**
  - ✅ Nothing to change in three forms and their tests.
- **Cons:**
  - ❌ New accounts can be created with no email and therefore no way to sign in.
  - ❌ Pushes the problem onto the admin for every new account.

---

### ❓ Q-091-04 · Profile self-service

**Status:** Resolved 2026-10-13, option B (spec FR-091-13)
**Feature:** F-091 – Email as login username
**Preferred option:** 🅰️ (**recommended**) Option A – Email becomes required and non-clearable

**Question**
`SetLogin.vue` and `UpdateProfileRequest` let a user change their username and email, and `UpdateLogin::updateEmail()` accepts `null` (which also deletes their notifications). What does the profile allow when the email is the login identifier?

**Resolution:** Option B. The profile is untouched: the email stays optional and clearable, and a user who clears it falls back to username login (spec FR-091-13).

---

#### 🅰️ (**recommended**) Option A – Email required, cannot be cleared
- **Idea:** With the setting on, `UpdateProfileRequest` requires a non-null email; the profile form marks it mandatory and keeps the username editable as the display identity.
- **Spec impact:** Conditional validation in `UpdateProfileRequest`; the v7/v8 profile form reads the flag.
- **Pros:**
  - ✅ A user cannot lock themselves out by clearing their email.
  - ✅ Holds the invariant Q-091-02 B/C depend on.
- **Cons:**
  - ❌ Users who deliberately keep no email on file lose that option once the setting is on.

---

#### 🅱️ Option B – Email stays clearable
- **Idea:** No change to the profile; clearing the email is allowed and means the account falls back to username login (Q-091-02 A) or loses access (Q-091-02 B).
- **Spec impact:** None.
- **Pros:**
  - ✅ No change to the profile code or its tests.
  - ✅ Consistent with the current privacy stance that an email is optional.
- **Cons:**
  - ❌ A self-inflicted lock-out is one click away under Q-091-02 B.
  - ❌ Breaks the invariant that Q-091-02 C tries to establish at enable time.

---

#### 🅲 Option C – Email read-only for non-admins
- **Idea:** With the setting on, the email is the login identifier and only an admin may change it.
- **Spec impact:** The profile form hides the email input; user management becomes the only place it changes.
- **Pros:**
  - ✅ The login identifier cannot be changed by the account holder, which some deployments require.
- **Cons:**
  - ❌ Every email change needs an admin, which does not scale for self-registration instances.
  - ❌ Diverges from how the username is handled today.
