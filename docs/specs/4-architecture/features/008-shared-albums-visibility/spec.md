# Feature 008 – Shared Albums Visibility Control

| Field | Value |
|-------|-------|
| Status | In Progress (~85%) |
| Last updated | 2026-01-21 |
| Owners | Agent |
| Linked plan | `docs/specs/4-architecture/features/008-shared-albums-visibility/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/008-shared-albums-visibility/tasks.md` |
| Roadmap entry | #008 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

Currently, logged-in users see all albums they have access to on the gallery page: their own albums plus albums shared with them (by other users or public albums from other owners). This feature introduces configuration options at both server and user level to control how shared albums are displayed:

- **Server config**: Defines the default behavior for all users
- **User preference**: Allows individual users to override the server default

Affected modules: database (migrations for config + user preference column), application (RootConfig, Top action), REST API (user settings endpoint), UI (Vue components for tabbed view + settings).

## Goals

- Add a global server configuration option to control shared album visibility mode
- Add a per-user preference column to allow users to override the global setting
- Support four visibility modes: SHOW (inline), SEPARATE (tabs), SEPARATE-SHARED-ONLY, HIDE
- Send the effective visibility mode to the frontend via RootConfig
- Update the gallery UI to support tabbed display when mode is SEPARATE or SEPARATE-SHARED-ONLY
- Provide user settings UI to change the personal preference

## Non-Goals

- Changing the underlying album sharing/permission system
- Modifying how albums are queried or filtered in the backend (only UI presentation)
- Adding new sharing capabilities
- Retroactive application of settings (purely a display preference)

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-008-01 | Server config for shared album visibility | Admin can set `shared_albums_visibility_default` config with values: `show`, `separate`, `separate_shared_only`, `hide` | Validate value is one of allowed enum values | Reject invalid value, keep existing | None | User request |
| FR-008-02 | User preference column for shared album visibility | Users table has `shared_albums_visibility` column with values: `default`, `show`, `separate`, `separate_shared_only`, `hide` | Validate value is one of allowed enum values including `default` | Reject invalid value, keep existing | None | User request |
| FR-008-03 | Effective mode calculation | When user has `default`, use server config; otherwise use user preference | N/A | N/A | None | User request |
| FR-008-04 | Send effective mode to frontend | RootConfig includes `shared_albums_visibility_mode` field with the computed effective value | Mode sent only to authenticated users | For guests, field is omitted or set to `show` | None | User request |
| FR-008-05 | SHOW mode behavior | Shared albums displayed inline below owned albums (current behavior) | N/A | N/A | None | User request |
| FR-008-06 | SEPARATE mode behavior | Gallery shows two tabs: "My Albums" and "Shared with Me"; public albums from other owners included in "Shared with Me" | Tab visible only when shared_albums exist | N/A | None | User request |
| FR-008-07 | SEPARATE-SHARED-ONLY mode behavior | Gallery shows two tabs: "My Albums" and "Shared with Me"; only non-public shared albums shown in "Shared with Me" tab (excludes public albums from other owners) | Tab visible only when non-public shared albums exist | N/A | None | User request |
| FR-008-08 | HIDE mode behavior | Shared albums not displayed at all on gallery page; only owned albums shown | N/A | N/A | None | User request |
| FR-008-09 | User can update their preference | POST `/Profile::updateSharedAlbumsVisibility` accepts `shared_albums_visibility` field | Validate field value | Return 422 if invalid value | None | User request |
| FR-008-10 | Server config default value | Default server config is `show` to maintain backward compatibility | N/A | N/A | None | Backward compatibility |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-008-01 | Backward compatibility | Existing installations should behave identically after upgrade | Default config is `show`, users default to `default` → effective mode is `show` | Migration sets defaults | User expectation |
| NFR-008-02 | No performance regression | Mode calculation should be negligible overhead | Effective mode computed once per request in RootConfig | Simple conditional logic | Performance requirement |
| NFR-008-03 | TypeScript type safety | Frontend should have proper types for the enum values | TypeScript enum generated via Spatie TypeScriptTransformer | #[TypeScript] attribute on enum | Coding conventions |

## UI / Interaction Mock-ups

### Gallery Page - SHOW Mode (Current/Default Behavior)

```
┌─────────────────────────────────────────────────────────────┐
│ Gallery                                           [⚙] [🔍] │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ Smart Albums                                                │
│ ┌────────┐ ┌────────┐ ┌────────┐                           │
│ │ Recent │ │Starred │ │ On Map │                           │
│ └────────┘ └────────┘ └────────┘                           │
│                                                             │
│ My Albums                                                   │
│ ┌────────┐ ┌────────┐ ┌────────┐ ┌────────┐               │
│ │Vacation│ │ Family │ │ Work   │ │Hobbies │               │
│ │ 45 📷  │ │ 120 📷 │ │ 30 📷  │ │ 85 📷  │               │
│ └────────┘ └────────┘ └────────┘ └────────┘               │
│                                                             │
│ Shared Albums                                               │
│ ┌────────┐ ┌────────┐                                      │
│ │Friend's│ │Public  │   (from other users)                 │
│ │ Trip   │ │Gallery │                                      │
│ └────────┘ └────────┘                                      │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

### Gallery Page - SEPARATE Mode (Tabbed View - My Albums Tab)

Smart Albums shown above tabs (per Q-008-02 decision), tabs only visible when shared albums exist (per Q-008-03 decision).

```
┌─────────────────────────────────────────────────────────────┐
│ Gallery                                           [⚙] [🔍] │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ Smart Albums (always visible, above tabs)                   │
│ ┌────────┐ ┌────────┐ ┌────────┐                           │
│ │ Recent │ │Starred │ │ On Map │                           │
│ └────────┘ └────────┘ └────────┘                           │
│                                                             │
│  [ My Albums ]  [ Shared with Me ]                          │
│  ─────────────                                              │
│                                                             │
│ Albums                                                      │
│ ┌────────┐ ┌────────┐ ┌────────┐ ┌────────┐               │
│ │Vacation│ │ Family │ │ Work   │ │Hobbies │               │
│ │ 45 📷  │ │ 120 📷 │ │ 30 📷  │ │ 85 📷  │               │
│ └────────┘ └────────┘ └────────┘ └────────┘               │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

### Gallery Page - SEPARATE Mode (Shared Tab Selected)

```
┌─────────────────────────────────────────────────────────────┐
│ Gallery                                           [⚙] [🔍] │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ Smart Albums (always visible, above tabs)                   │
│ ┌────────┐ ┌────────┐ ┌────────┐                           │
│ │ Recent │ │Starred │ │ On Map │                           │
│ └────────┘ └────────┘ └────────┘                           │
│                                                             │
│  [ My Albums ]  [ Shared with Me ]                          │
│                 ─────────────────                           │
│                                                             │
│ Shared Albums                                               │
│ ┌────────┐ ┌────────┐ ┌────────┐                           │
│ │Friend's│ │Public  │ │Collab  │   (all accessible albums  │
│ │ Trip   │ │Gallery │ │Project │    from other users)      │
│ │ 78 📷  │ │ 200 📷 │ │ 45 📷  │                           │
│ └────────┘ └────────┘ └────────┘                           │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

### Gallery Page - SEPARATE Mode (No Shared Albums - Tabs Hidden)

When user has no shared albums, tab bar is hidden and behaves like SHOW mode.

```
┌─────────────────────────────────────────────────────────────┐
│ Gallery                                           [⚙] [🔍] │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ Smart Albums                                                │
│ ┌────────┐ ┌────────┐ ┌────────┐                           │
│ │ Recent │ │Starred │ │ On Map │                           │
│ └────────┘ └────────┘ └────────┘                           │
│                                                             │
│ Albums (no tabs shown - no shared albums available)         │
│ ┌────────┐ ┌────────┐ ┌────────┐ ┌────────┐               │
│ │Vacation│ │ Family │ │ Work   │ │Hobbies │               │
│ │ 45 📷  │ │ 120 📷 │ │ 30 📷  │ │ 85 📷  │               │
│ └────────┘ └────────┘ └────────┘ └────────┘               │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

### Gallery Page - HIDE Mode

```
┌─────────────────────────────────────────────────────────────┐
│ Gallery                                           [⚙] [🔍] │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ Smart Albums                                                │
│ ┌────────┐ ┌────────┐ ┌────────┐                           │
│ │ Recent │ │Starred │ │ On Map │                           │
│ └────────┘ └────────┘ └────────┘                           │
│                                                             │
│ Albums                                                      │
│ ┌────────┐ ┌────────┐ ┌────────┐ ┌────────┐               │
│ │Vacation│ │ Family │ │ Work   │ │Hobbies │               │
│ │ 45 📷  │ │ 120 📷 │ │ 30 📷  │ │ 85 📷  │               │
│ └────────┘ └────────┘ └────────┘ └────────┘               │
│                                                             │
│              (no shared albums section shown)               │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

### User Settings - Shared Albums Preference

```
┌─────────────────────────────────────────────────────────────┐
│ Settings                                                    │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ Display Preferences                                         │
│ ───────────────────                                         │
│                                                             │
│ Shared Albums Visibility                                    │
│ ┌─────────────────────────────────────────────────────────┐│
│ │ ○ Use server default (currently: Show inline)          ││
│ │ ○ Show inline with my albums                           ││
│ │ ○ Show in separate tab                                 ││
│ │ ○ Show in separate tab (shared only, no public)        ││
│ │ ○ Hide shared albums                                   ││
│ └─────────────────────────────────────────────────────────┘│
│                                                             │
│                                        [Cancel] [Save]      │
└─────────────────────────────────────────────────────────────┘
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-008-01 | User with preference=`default`, server config=`show` → Effective mode is `show`, shared albums inline |
| S-008-02 | User with preference=`hide`, server config=`show` → Effective mode is `hide`, no shared albums shown |
| S-008-03 | User with preference=`separate`, server config=`hide` → Effective mode is `separate`, tabs shown |
| S-008-04 | User with preference=`default`, server config=`separate` → Effective mode is `separate`, tabs shown |
| S-008-05 | Guest user (not logged in) → No shared albums section (guests only see public albums, treated as "their view") |
| S-008-06 | User changes preference from `default` to `hide` via settings → Gallery immediately reflects change |
| S-008-07 | Admin changes server config from `show` to `separate` → Users with `default` preference see tabs |
| S-008-08 | User has no shared albums → Tabs hidden even in `separate` mode (nothing to show) |
| S-008-09 | User has preference=`separate_shared_only`, has public albums from others but no direct shares → "Shared with Me" tab hidden |
| S-008-10 | Fresh installation → Server config defaults to `show`, all users default to `default` |

## Test Strategy

- **Core:** Unit tests for effective mode calculation logic
- **Application:** Feature tests for RootConfig returning correct effective mode based on user/server settings
- **REST:** API tests for user settings endpoint accepting valid values, rejecting invalid
- **UI (JS):** Component tests for tab rendering based on mode, settings form validation
- **Migration:** Test that existing installations default to `show` mode after migration

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-008-01 | SharedAlbumsVisibility enum: `show`, `separate`, `separate_shared_only`, `hide` | core, application, REST, UI |
| DO-008-02 | UserSharedAlbumsVisibility enum: `default`, `show`, `separate`, `separate_shared_only`, `hide` | core, application, REST, UI |
| DO-008-03 | RootConfig.shared_albums_visibility_mode: SharedAlbumsVisibility (computed effective value) | application, REST, UI |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-008-01 | POST /Profile::updateSharedAlbumsVisibility | Update user's shared albums visibility preference | Dedicated endpoint for this setting |
| API-008-02 | GET /Gallery::Init | Returns RootConfig with shared_albums_visibility_mode | Existing endpoint, new field in response |

### CLI Commands / Flags

None required for this feature.

### Telemetry Events

None required for this feature.

### Fixtures & Sample Data

| ID | Path | Purpose |
|----|------|---------|
| FX-008-01 | tests/Feature_v2/SharedAlbumsVisibilityTest.php | Test various mode combinations |

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-008-01 | Gallery inline mode | Mode is `show` → Shared albums section below owned albums |
| UI-008-02 | Gallery tabbed mode | Mode is `separate` or `separate_shared_only` → Tab bar shown at top |
| UI-008-03 | Gallery hidden mode | Mode is `hide` → No shared albums section or tab |
| UI-008-04 | Settings radio selection | User selects preference → Option highlighted, save enabled |
| UI-008-05 | Tab badge/count | Each tab shows count of albums in that category |

## Telemetry & Observability

No telemetry events required for this feature. Standard application logging for errors.

## Documentation Deliverables

- Update [knowledge-map.md](../../knowledge-map.md) with new config key and user preference column
- Document admin configuration option in admin guide
- Document user preference in user guide/settings documentation

## Fixtures & Sample Data

- Test fixtures with users having various preference settings
- Test fixtures with albums in different sharing states (owned, shared directly, public from others)

## Spec DSL

```yaml
domain_objects:
  - id: DO-008-01
    name: SharedAlbumsVisibility
    type: enum
    values:
      - show
      - separate
      - separate_shared_only
      - hide
    description: Server-level configuration for shared albums display mode
  - id: DO-008-02
    name: UserSharedAlbumsVisibility
    type: enum
    values:
      - default
      - show
      - separate
      - separate_shared_only
      - hide
    description: User-level preference for shared albums display mode (includes default option)
  - id: DO-008-03
    name: RootConfig.shared_albums_visibility_mode
    type: SharedAlbumsVisibility
    description: Computed effective visibility mode sent to frontend

routes:
  - id: API-008-01
    method: POST
    path: /Profile::updateSharedAlbumsVisibility
    parameters:
      - shared_albums_visibility: UserSharedAlbumsVisibility (required)
    response: UserResource
    notes: Dedicated endpoint for updating shared albums visibility preference
  - id: API-008-02
    method: GET
    path: /Gallery::Init
    response: InitConfig (includes RootConfig.shared_albums_visibility_mode)
    notes: Existing endpoint, response extended

config_keys:
  - id: CFG-008-01
    key: shared_albums_visibility_default
    type: enum
    values: show|separate|separate_shared_only|hide
    default: show
    category: Gallery
    description: Default visibility mode for shared albums

user_columns:
  - id: COL-008-01
    column: shared_albums_visibility
    type: enum
    values: default|show|separate|separate_shared_only|hide
    default: default
    nullable: false
    description: User preference for shared albums visibility

ui_states:
  - id: UI-008-01
    description: Gallery inline mode (shared albums below owned)
  - id: UI-008-02
    description: Gallery tabbed mode (My Albums / Shared with Me tabs)
  - id: UI-008-03
    description: Gallery hidden mode (no shared albums visible)
  - id: UI-008-04
    description: Settings preference selection
  - id: UI-008-05
    description: Tab with album count badges
```

## Appendix

### Existing Code References

**Album retrieval with owned/shared partition:**
File: [app/Actions/Albums/Top.php](../../../../app/Actions/Albums/Top.php)

The `Top::get()` method already partitions albums into owned vs shared:
```php
if ($user_id !== null) {
    list($owned, $shared) = $albums->partition(
        fn ($album) => $album->owner_id === $user_id
    );
    return new TopAlbumDTO(
        albums: $owned,
        shared_albums: $shared
    );
}
```

**RootConfig sending gallery configuration:**
File: [app/Http/Resources/GalleryConfigs/RootConfig.php](../../../../app/Http/Resources/GalleryConfigs/RootConfig.php)

**User model columns for preferences:**
File: [app/Models/User.php](../../../../app/Models/User.php)

### Mode Behavior Summary

| Mode | "Shared Albums" Section | Tab Display | What Albums Appear |
|------|-------------------------|-------------|-------------------|
| `show` | Inline below owned | No tabs | All accessible albums from other users |
| `separate` | Hidden from main | Two tabs | All accessible albums from other users in "Shared" tab |
| `separate_shared_only` | Hidden from main | Two tabs | Only directly shared albums (no public) in "Shared" tab |
| `hide` | Hidden | No tabs | None - only owned albums shown |

### SEPARATE vs SEPARATE-SHARED-ONLY

- **SEPARATE**: The "Shared with Me" tab includes ALL albums the user can access that they don't own, including:
  - Albums directly shared with the user (via access_permissions with user_id match)
  - Albums shared with a group the user belongs to
  - Public albums from other users (is_link_required = false, no specific user/group)

- **SEPARATE-SHARED-ONLY**: The "Shared with Me" tab includes ONLY albums specifically shared:
  - Albums directly shared with the user (via access_permissions with user_id match)
  - Albums shared with a group the user belongs to
  - EXCLUDES public albums from other users

### User Decisions Summary

All open questions (Q-008-01 through Q-008-03) have been resolved:

- **Q-008-01:** User preference stored in users table column (Option A) - follows existing Lychee pattern
- **Q-008-02:** Smart Albums shown above tabs, outside tab context (Option D) - clear that Smart Albums span all content
- **Q-008-03:** Hide empty tabs (Option A) - cleaner UX when no shared albums exist
