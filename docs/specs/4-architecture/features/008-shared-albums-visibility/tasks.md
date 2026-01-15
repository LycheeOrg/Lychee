# Feature 008 Tasks – Shared Albums Visibility Control

_Status: Complete_
_Last updated: 2026-01-15_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.

## Checklist

### Phase 1: Backend - Enums & Database

- [x] T-008-01 – Create SharedAlbumsVisibility enum (FR-008-01, DO-008-01).
  _Intent:_ Create server-level visibility mode enum.
  _Verification commands:_
  - `make phpstan` ✓
  _Notes:_ Values: `show`, `separate`, `separate_shared_only`, `hide`

- [x] T-008-02 – Create UserSharedAlbumsVisibility enum (FR-008-02, DO-008-02).
  _Intent:_ Create user-level preference enum (includes `default` option).
  _Verification commands:_
  - `make phpstan` ✓
  _Notes:_ Values: `default`, `show`, `separate`, `separate_shared_only`, `hide`

- [x] T-008-03 – Create server config migration (FR-008-01, FR-008-10, CFG-008-01).
  _Intent:_ Add `shared_albums_visibility_default` config to database.
  _Verification commands:_
  - `php artisan migrate` ✓
  - `make phpstan` ✓
  _Notes:_ Default value `show`, category `Gallery`

- [x] T-008-04 – Create user column migration (FR-008-02, COL-008-01).
  _Intent:_ Add `shared_albums_visibility` column to users table.
  _Verification commands:_
  - `php artisan migrate` ✓
  - `make phpstan` ✓
  _Notes:_ String column, default `default`

- [x] T-008-05 – Update User model (FR-008-02).
  _Intent:_ Add cast for new column, update PHPDoc.
  _Verification commands:_
  - `make phpstan` ✓
  _Notes:_ Cast to `UserSharedAlbumsVisibility` enum

### Phase 2: Backend - API & Logic

- [x] T-008-06 – Update RootConfig with effective mode (FR-008-03, FR-008-04, DO-008-03).
  _Intent:_ Compute and expose effective visibility mode to frontend.
  _Verification commands:_
  - `make phpstan` ✓
  - `php artisan typescript:transform` ✓
  _Notes:_ Only for authenticated users; compute from user pref or server default

- [x] T-008-07 – Update user settings endpoint (FR-008-09, API-008-01).
  _Intent:_ Accept `shared_albums_visibility` field in user settings update.
  _Verification commands:_
  - `make phpstan` ✓
  _Notes:_ Validate against UserSharedAlbumsVisibility enum

- [x] T-008-08 – Write backend tests (S-008-01 to S-008-07, S-008-10).
  _Intent:_ Test effective mode calculation and settings update.
  _Verification commands:_
  - `php artisan test --filter=SharedAlbumsVisibility` ✓
  _Notes:_ Test various user/server config combinations

### Phase 3: Frontend - Types & State

- [x] T-008-09 – Generate and verify TypeScript types.
  _Intent:_ Ensure enums are available in frontend.
  _Verification commands:_
  - `php artisan typescript:transform` ✓
  - `npm run check` ✓
  _Notes:_ Verify `App.Enum.SharedAlbumsVisibility` exists ✓ (line 54 of lychee.d.ts)

### Phase 4: Frontend - UI Implementation

- [x] T-008-10 – Implement gallery tab UI (FR-008-06, FR-008-07, UI-008-02).
  _Intent:_ Add tabbed view for separate modes with Smart Albums above tabs.
  _Verification commands:_
  - `npm run check` ✓
  _Notes:_ Hide tabs when no shared albums (S-008-08) ✓

- [x] T-008-11 – Handle SHOW mode (FR-008-05, UI-008-01).
  _Intent:_ Shared albums inline below owned albums (current behavior).
  _Verification commands:_
  - `npm run check` ✓
  _Notes:_ Default/backward compatible mode ✓

- [x] T-008-12 – Handle HIDE mode (FR-008-08, UI-008-03).
  _Intent:_ No shared albums shown at all.
  _Verification commands:_
  - `npm run check` ✓
  _Notes:_ Simply filter out shared albums ✓

- [x] T-008-13 – Implement SEPARATE-SHARED-ONLY filtering (FR-008-07, S-008-09).
  _Intent:_ In shared tab, exclude public albums from other owners.
  _Verification commands:_
  - `npm run check` ✓
  _Notes:_ Need to distinguish direct shares from public albums ✓

### Phase 5: Frontend - User Settings

- [x] T-008-14 – Add preference selector to user settings (UI-008-04).
  _Intent:_ Radio button group for visibility preference.
  _Verification commands:_
  - `npm run check` ✓
  _Notes:_ 5 options including "Use server default" ✓ (SetSharedAlbumsVisibility.vue created)

### Phase 6: Quality Gate

- [x] T-008-15 – Run full quality gate.
  _Intent:_ Final verification and formatting.
  _Verification commands:_
  - `vendor/bin/php-cs-fixer fix` ✓
  - `npm run format` ✓
  - `php artisan test` ✓
  - `make phpstan` ✓
  - `npm run check` ✓
  _Notes:_ All gates passed

## Notes / TODOs

- The SEPARATE-SHARED-ONLY mode requires distinguishing between:
  - Direct shares (access_permissions with user_id or user_group_id match)
  - Public albums from other owners (no specific user/group, is_link_required=false)
  - This filtering may need backend support or frontend logic based on album ownership
