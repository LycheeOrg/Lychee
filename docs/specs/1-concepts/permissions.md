# Access Permissions

This document explains how Lychee controls access to albums and photos through the permission system.

## Table of Contents

- [What are Access Permissions?](#what-are-access-permissions)
- [Permission Model](#permission-model)
- [Permission Hierarchy](#permission-hierarchy)
- [Nested Tree Permissions](#nested-tree-permissions)
- [Password Protection](#password-protection)

---

## What are Access Permissions?

**Access Permissions** control who can view and interact with albums. Permissions are granted at the **album level** and apply to all photos within that album.

---

## Permission Model

Each `AccessPermission` record defines:

**Target:** Who gets access
- **User**: Specific user account
- **User Group**: All members of a group
- **Public**: Anyone (user_id and user_group_id are null)

**Album:** Which album is shared
- Permissions always target a specific album
- Child albums inherit parent permissions by default

**Grants:** What actions are allowed
- `grants_full_photo_access`: View high-resolution originals
- `grants_download`: Download photos
- `grants_upload`: Add new photos to the album
- `grants_edit`: Modify photo metadata and move photos
- `grants_delete`: Remove photos from the album

**Link Requirement:**
- `is_link_required`: Album is public but not listed
  - Users must know the direct URL
  - Useful for client galleries or unlisted sharing

**Password Protection:**
- Optional password required for access
- Applies even to users with explicit permissions

---

## Permission Hierarchy

```
1. Admin Override
   ↓ Admins bypass all permission checks
   
2. Ownership
   ↓ Album owner has full control
   
3. Direct User Permission
   ↓ AccessPermission with user_id set
   
4. User Group Permission
   ↓ AccessPermission with user_group_id set
   
5. Public Permission
   ↓ AccessPermission with both user_id and user_group_id null
   
6. Deny by Default
   → No access if none of the above apply
```

---

## Nested Tree Permissions

Permissions flow through the album hierarchy:

**Parent Album Permissions:**
- If a user can see a parent album, they can see child albums
- More restrictive child permissions can limit access
- Child albums cannot grant broader access than parents

**Example:**
```
Vacation 2024 (Public: view only)
├─ Paris (Public: view + download)
│  └─ Day 1 (User Alice: view + upload + edit)
└─ Rome (Password protected)
```

- Public can view and download Paris photos
- Only Alice can upload to "Day 1"
- Rome requires password even for public users

---

## Password Protection

Albums can require a password for access:
- Password applies to **all** users (including those with explicit permissions)
- Once unlocked, album remains accessible for the session
- Session tracked via `unlocked_albums` session key

---

**Related:** [Albums](albums.md) | [Users](users.md) | [Photos](photos.md)

---

*Last updated: December 22, 2025*
