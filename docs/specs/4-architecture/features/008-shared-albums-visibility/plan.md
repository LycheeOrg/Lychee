# Feature Plan 008 – Shared Albums Visibility Control

_Linked specification:_ `docs/specs/4-architecture/features/008-shared-albums-visibility/spec.md`
_Status:_ Draft
_Last updated:_ 2026-01-15

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

Allow logged-in users to control how shared albums (albums from other users) appear on their gallery page. Success criteria:
- Server admin can configure default visibility mode (`show`, `separate`, `separate_shared_only`, `hide`)
- Users can override the server default with their personal preference
- Frontend properly displays tabs when in `separate` modes with Smart Albums above tabs
- Tab bar hidden when no shared albums exist (cleaner UX)
- Backward compatibility: existing installations default to `show` mode

## Scope Alignment

- **In scope:**
  - Server config `shared_albums_visibility_default` with 4 modes
  - User preference column `shared_albums_visibility` with 5 options (includes `default`)
  - RootConfig extension to compute and send effective visibility mode
  - Frontend tab UI for `separate` and `separate_shared_only` modes
  - User settings UI to change preference

- **Out of scope:**
  - Changes to album sharing/permission system
  - Changes to how albums are queried (pure presentation layer change)
  - New sharing capabilities
  - Mobile-specific tab UI (use existing responsive patterns)

## Dependencies & Interfaces

| Dependency | Purpose |
|------------|---------|
| `App\Models\User` | Add `shared_albums_visibility` column |
| `App\Http\Resources\GalleryConfigs\RootConfig` | Add effective mode computation |
| `App\Models\Extensions\BaseConfigMigration` | Create server config |
| `resources/js/components/gallery/` | Tab UI implementation |
| TypeScript type generation | Generate enums for frontend |

## Assumptions & Risks

**Assumptions:**
- Existing `RootConfig` pattern can accommodate new field
- Frontend gallery component can be extended for tab UI
- User model cast patterns are standard

**Risks / Mitigations:**
- Risk: Frontend gallery refactoring complexity → Mitigation: Minimal changes, conditional rendering based on mode
- Risk: TypeScript enum not generated correctly → Mitigation: Verify after running `php artisan typescript:transform`

## Implementation Drift Gate

After each increment, verify:
1. Run `php artisan test` - all tests pass
2. Run `make phpstan` - no static analysis errors
3. Run `npm run check` - frontend checks pass
4. Verify TypeScript types generated correctly

## Increment Map

### I1 – Create PHP Enums
_Goal:_ Define the two enum types for visibility modes.
_Preconditions:_ None
_Steps:_
1. Create `App\Enum\SharedAlbumsVisibility` enum (`show`, `separate`, `separate_shared_only`, `hide`)
2. Create `App\Enum\UserSharedAlbumsVisibility` enum (adds `default` option)
_Commands:_
- `make phpstan`
_Exit:_ Both enums exist and pass PHPStan validation

### I2 – Create Server Config Migration
_Goal:_ Add `shared_albums_visibility_default` config to database.
_Preconditions:_ I1 complete
_Steps:_
1. Create migration extending `BaseConfigMigration`
2. Define config with `type_range` matching enum values
3. Set default value to `show`
_Commands:_
- `php artisan migrate`
- `make phpstan`
_Exit:_ Config appears in database, can be retrieved via `request()->configs()`

### I3 – Create User Column Migration
_Goal:_ Add `shared_albums_visibility` column to users table.
_Preconditions:_ I1 complete
_Steps:_
1. Create migration adding string column with default `default`
2. Update User model with `$casts` entry for enum
3. Update User model PHPDoc
_Commands:_
- `php artisan migrate`
- `make phpstan`
_Exit:_ Column exists, User model properly casts to enum

### I4 – Update RootConfig
_Goal:_ Compute and expose effective visibility mode to frontend.
_Preconditions:_ I2, I3 complete
_Steps:_
1. Add `shared_albums_visibility_mode` property to RootConfig
2. Implement effective mode calculation logic:
   - If guest: not applicable (omit or default)
   - If user preference is `default`: use server config
   - Otherwise: use user preference
3. Add `#[LiteralTypeScriptType]` attribute for proper typing
_Commands:_
- `make phpstan`
- `php artisan typescript:transform`
_Exit:_ RootConfig includes correct mode, TypeScript types generated

### I5 – Update User Settings Endpoint
_Goal:_ Allow users to update their visibility preference.
_Preconditions:_ I3 complete
_Steps:_
1. Find user settings request class and controller
2. Add `shared_albums_visibility` field validation
3. Handle update in controller
_Commands:_
- `php artisan test --filter=UserSettings`
- `make phpstan`
_Exit:_ PUT endpoint accepts and saves preference

### I6 – Backend Tests
_Goal:_ Test effective mode calculation and settings update.
_Preconditions:_ I4, I5 complete
_Steps:_
1. Create `SharedAlbumsVisibilityTest.php`
2. Test various user/server config combinations (S-008-01 to S-008-07)
3. Test settings endpoint validation
_Commands:_
- `php artisan test --filter=SharedAlbumsVisibility`
_Exit:_ All tests pass

### I7 – Frontend Tab UI Implementation
_Goal:_ Implement tabbed gallery view for `separate` modes.
_Preconditions:_ I4 complete
_Steps:_
1. Update gallery component to read `shared_albums_visibility_mode` from RootConfig
2. Render Smart Albums above tabs (always visible)
3. Render tab bar only when:
   - Mode is `separate` or `separate_shared_only` AND
   - Shared albums exist (count > 0)
4. Implement tab switching logic
5. Filter albums based on selected tab
_Commands:_
- `npm run check`
_Exit:_ Tabs render correctly, switching works, Smart Albums always visible above

### I8 – Frontend User Settings UI
_Goal:_ Add preference selector to user settings page.
_Preconditions:_ I5 complete
_Steps:_
1. Find user settings Vue component
2. Add radio button group for visibility preference
3. Connect to user settings API endpoint
_Commands:_
- `npm run check`
_Exit:_ User can change preference via settings UI

### I9 – Integration Testing & Quality Gate
_Goal:_ Final verification and code formatting.
_Preconditions:_ All previous increments complete
_Steps:_
1. Run full test suite
2. Run PHP code formatter
3. Run frontend formatter
4. Manual testing of all 4 modes
_Commands:_
- `vendor/bin/php-cs-fixer fix`
- `npm run format`
- `php artisan test`
- `make phpstan`
- `npm run check`
_Exit:_ All quality gates pass

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-008-01 | I4, I6 / T-008-06 | User default + server show |
| S-008-02 | I4, I6 / T-008-06 | User hide overrides server show |
| S-008-03 | I4, I6 / T-008-06 | User separate overrides server hide |
| S-008-04 | I4, I6 / T-008-06 | User default + server separate |
| S-008-05 | I4 / T-008-04 | Guest user handling |
| S-008-06 | I5, I8 / T-008-05, T-008-08 | User changes preference |
| S-008-07 | I4, I6 / T-008-06 | Admin changes server config |
| S-008-08 | I7 / T-008-07 | No shared albums - tabs hidden |
| S-008-09 | I7 / T-008-07 | Separate-shared-only filtering |
| S-008-10 | I2, I3 / T-008-02, T-008-03 | Fresh installation defaults |

## Analysis Gate

_To be completed after I6 (backend tests)_

## Exit Criteria

- [ ] All PHP tests pass (`php artisan test`)
- [ ] PHPStan passes (`make phpstan`)
- [ ] PHP code formatted (`vendor/bin/php-cs-fixer fix`)
- [ ] Frontend checks pass (`npm run check`)
- [ ] Frontend formatted (`npm run format`)
- [ ] TypeScript types generated correctly
- [ ] Manual testing of all 4 visibility modes
- [ ] Tab bar hidden when no shared albums
- [ ] Smart Albums visible above tabs in separate modes

## Follow-ups / Backlog

- Consider adding album count badges to tabs in future iteration
- Consider persisting selected tab in localStorage for session continuity
- Consider accessibility improvements for tab navigation (keyboard, ARIA)
