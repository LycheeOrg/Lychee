# Users and Authentication

This document explains user accounts, user groups, and authentication methods in Lychee.

## Table of Contents

- [Users](#users)
- [User Groups](#user-groups)

---

## Users

### User Attributes

**Core Properties:**
- **id**: Unique identifier
- **username**: Display name and login identifier
- **password**: Hashed password (nullable for OAuth-only users)
- **email**: Optional email address
- **created_at**, **updated_at**: Account timestamps

**Permissions:**
- **may_administrate**: Full system access, bypass all permission checks
- **may_upload**: Can upload photos and create albums
- **may_edit_own_settings**: Can modify own profile and preferences

**Quota:**
- **quota_kb**: Storage limit in kilobytes
- Enforces per-user storage constraints

**Metadata:**
- **description**: User bio or profile text
- **note**: Internal admin notes about the user

**Tokens:**
- **token**: API access token for programmatic access
- **remember_token**: Session persistence token

### User Relationships

- `albums()`: HasMany - Albums owned by this user
- `photos()`: HasMany - Photos owned by this user
- `oauthCredentials()`: HasMany - OAuth authentication credentials
- `user_groups()`: BelongsToMany - Group memberships with role and created_at pivot data
- `shared`: Albums shared with this user via AccessPermission

### Authentication Methods

**Password Authentication:**
- Traditional username/password authentication
- Passwords hashed using bcrypt

**OAuth2 Integration:**
- Enterprise single sign-on via oauthCredentials
- Supported providers: Google, Microsoft, GitHub, Amazon, Apple
- Multiple OAuth accounts can link to one Lychee user

**WebAuthn (Passwordless):**
- FIDO2/biometric authentication
- Hardware security keys and platform authenticators
- Stored in `webauthn_credentials` table

### User Capabilities

**may_administrate (Admin):**
- Full system access
- Bypass all permission checks
- Manage all users and content
- Access system configuration

**may_upload:**
- Upload photos to owned or permitted albums
- Create new albums
- Organize content

**may_edit_own_settings:**
- Modify profile and preferences
- Change password
- Configure personal settings

**Storage Quota:**
- Enforced via `quota_kb` attribute
- Tracks total photo storage per user
- Upload blocked when quota exceeded

### User Types

**Admin (may_administrate=true):**
- Full system access
- Manage all users and content
- Configure system settings

**Regular User:**
- Access based on ownership and permissions
- Storage quota enforced
- Can share and collaborate

**Guest (unauthenticated):**
- Access public content only
- No upload or modification rights
- Limited to viewing permitted albums

### User Deletion

When a user is deleted:
- Albums and photos reassigned to the authenticated admin
- User's AccessPermission entries removed
- User's WebAuthn credentials deleted
- Group memberships cleaned up automatically
- OAuth credentials deleted

---

## User Groups

### What are User Groups?

**User Groups** are collections of users for simplified permission management. They allow granting access to multiple users at once via AccessPermission.

**Use Cases:**
- Teams and departments
- Client groups with shared access
- Family members with full access
- Collaborators with specific permissions

### Group Attributes

Stored in `user_groups` table with basic properties:
- **id**: Unique identifier
- **name**: Group display name
- **created_at**, **updated_at**: Timestamps

### Group Membership

**Pivot Table (`users_user_groups`):**
- Users can belong to multiple groups
- Pivot data includes:
  - **user_id**: Link to User
  - **user_group_id**: Link to UserGroup
  - **role**: User's role within the group
  - **created_at**: When user joined the group

**Ordering:**
- Groups ordered by role (ascending) then name (ascending)
- Always eager-loaded with User model for permission checks

### Permission Resolution

**How Group Permissions Work:**
- Group permissions combine with direct user permissions
- Most permissive grant wins (union of permissions)
- AccessPermission can target individual users OR user groups
- Efficient permission checks without database intersection queries

**Example:**
```
User Alice:
  ├─ Direct permission: Album "Project X" (view + download)
  ├─ Group "Team": Album "Work Files" (view + upload + edit)
  └─ Group "Clients": Album "Deliverables" (view only)

Result:
  → Project X: view + download
  → Work Files: view + upload + edit
  → Deliverables: view
```

### Example Use Cases

**Team Collaboration:**
- "Team Members" group with upload/edit access to work albums
- Simplify onboarding: add user to group instead of individual permissions

**Family Sharing:**
- "Family" group with full access to personal albums
- All family members see and contribute to shared albums

**Client Access:**
- "Clients" group with view-only access to portfolios
- Deliver content without upload rights
- Track which clients accessed which albums

---

**Related:** [Permissions](permissions.md) | [Albums](albums.md) | [System Configuration](system.md)

---

*Last updated: December 22, 2025*
