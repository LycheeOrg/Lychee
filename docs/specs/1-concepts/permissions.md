# Access Permissions

This document explains how Lychee controls access to albums and photos through the permission system.

## Table of Contents

- [What are Access Permissions?](#what-are-access-permissions)
- [Permission Model](#permission-model)
- [Permission Hierarchy](#permission-hierarchy)
- [What Each Grant Allows](#what-each-grant-allows)
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
- Child albums do **not** inherit parent permissions automatically; the owner copies them down explicitly ("propagate" in the sharing UI)

**Grants:** What actions are allowed
- `grants_full_photo_access`: View high-resolution originals
- `grants_download`: Download photos
- `grants_upload`: Add new photos to the album
- `grants_edit`: Modify album and photo metadata; use the album as a destination
- `grants_move`: Move, copy and merge the album's content
- `grants_delete`: Delete the album's content

See [What Each Grant Allows](#what-each-grant-allows) for the exact rules.

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

## What Each Grant Allows

A share gives one user (or group) a set of grants on **one album**. Admins bypass every check, and the album owner (with the upload privilege) can do everything in their own albums. The rules below apply to everyone else.

### The rule of thumb

- Grants never spill over to other albums: a grant on an album does not apply to its sub-albums unless the owner shares those too.
- **Move** and **delete** act on the album's **content**: its photos and its sub-albums. They do not act on the album itself. Moving or deleting an album is decided by the grants on its **parent**, because the album is part of its parent's content.
- A destination album (for move, copy or merge) always needs **edit**.
- No combination of grants lets a user gain access they were not given. Anything that would put someone else's photos or albums under the user's ownership is refused unless the user already has the equivalent rights (see [Across owners](#across-owners)).

### Example setup

All examples below use this gallery. **Victor** owns everything and shares some albums with **Ursula**.

```
Victor's gallery
├─ 📁 A
│  ├─ 🖼️ photo A
│  └─ 📁 AA
│     └─ 🖼️ photo AA
└─ 📁 B

Ursula's gallery
└─ 📁 U
```

### Per grant

| Grant | Ursula can | Ursula cannot |
|-------|------------|---------------|
| *(any share)* | See the album and its photos, at medium resolution. | See sub-albums that are not shared with her. |
| **Full photo** (`grants_full_photo_access`) | Open the original (full-resolution) file of the album's photos. | — without it, only medium-size images are served. |
| **Download** (`grants_download`) | Download the photos and the album as an archive. | — |
| **Upload** (`grants_upload`) | Upload new photos into the album. | Rename, move or delete anything. |
| **Edit** (`grants_edit`) | Change the album's title, description, sorting, cover and header; change titles, descriptions, tags, licence and rotation of its photos; create sub-albums in it; use it as a **destination** for move, copy and merge. | Move, copy or delete anything **out of** the album; see originals or download; change the album's visibility or sharing. |
| **Move** (`grants_move`) | Move or copy the album's photos out of it; move its sub-albums out of it; merge it into another album (together with delete, see below). | Move the album itself; put the content into an album she cannot edit. |
| **Delete** (`grants_delete`) | Delete the album's photos; delete its sub-albums. | Delete the album itself (that needs delete on its parent; a root album can only be deleted by its owner). |

Public (link) shares can grant viewing, full photo, download and (Supporter Edition) upload; they never grant edit, move or delete.

Sharing decisions stay with the owner: no grant lets Ursula change who can see an album. Its visibility settings (public, link required, password, NSFW, and what the public may do) and its per-user shares can only be changed by the album owner (or an admin; for built-in smart albums, admins only).

### Examples

**Ursula has move on A and edit on B:**

| Action | Result | Why |
|--------|--------|-----|
| Move photo A from A to B | ✅ | move on A (source), edit on B (destination) |
| Copy photo A into B | ✅ | move on A, edit on B |
| Move photo A out of A, to Victor's unsorted photos | ✅ | move on A |
| Move AA from A into B | ✅ | AA is content of A |
| Move A into B | ❌ | A is a root album: only Victor can move it |
| Merge AA into B | ❌ | merging empties AA (needs move on AA) and deletes AA (needs delete on A) |

**Ursula has move on AA and delete on A, and edit on B:**

| Action | Result | Why |
|--------|--------|-----|
| Merge AA into B | ✅ | AA's content leaves AA (move on AA), AA is deleted (delete on A) |
| Move AA into B | ❌ | moving AA needs move on A |

**Ursula has edit on A only:**

| Action | Result | Why |
|--------|--------|-----|
| Rename A, retitle photo A, add tags | ✅ | edit on A |
| Create a sub-album in A | ✅ | edit on A |
| Move or copy photo A anywhere | ❌ | no move on A |
| Open the original of photo A | ❌ | no full photo grant |

**Ursula has delete on A:**

| Action | Result | Why |
|--------|--------|-----|
| Delete photo A | ✅ | photo A is content of A |
| Delete AA (and everything in it) | ✅ | AA is content of A |
| Delete photo AA | ❌ | photo AA is content of AA, not of A |
| Delete A | ❌ | A is a root album: only Victor can delete it |

**Ursula has edit on A (or even every grant on A):**

| Action | Result | Why |
|--------|--------|-----|
| Make A public, or add a password | ❌ | only Victor decides who can see A |
| Share A with another user | ❌ | same |

**Ursula has move on A, and B was also shared with edit, but only Victor may delete in A:**

| Action | Result | Why |
|--------|--------|-----|
| Move photo A to B | ✅ | move on A, edit on B |
| Delete photo A | ❌ | no delete on A |

### Across owners

Owning an album gives full rights over every photo in it, including photos that belong to someone else. So an operation that would place Victor's content in an album Ursula owns (her album U) is only allowed when Ursula gains nothing she does not already have:

| Action (Ursula has move on A, edit on U since she owns it) | Result | Why |
|--------|--------|-----|
| Copy or move photo A into U | ❌ | U is Ursula's: she would get the original and download through owning U |
| Same, but Ursula also has **full photo** and **download** on A | ✅ | she already has both |
| Move AA into U | ❌ | AA and everything inside it would become Ursula's; only Victor may hand his albums over |
| Merge AA into U | ❌ | same reason |
| Move her own album U into A (edit on A) | ✅ | Ursula gives U to Victor; U becomes Victor's |
| Copy a photo Ursula uploaded into A, into U | ✅ | the photo is Ursula's own |

Moves inside the same owner's albums (Victor's A to Victor's B) only need the move and edit grants described above, even if B is shared more widely than A.

### What the interface shows

The move, copy and merge dialogs only list albums the user can edit. An album that passes this filter can still be refused by the [across owners](#across-owners) rule; the user then sees an error message.

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

*Last updated: September 25, 2026*
