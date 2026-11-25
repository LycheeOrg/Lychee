# ADR-118-01: Split user rights into upload, share and edit

- **Status:** Accepted
- **Date:** 2026-11-25
- **Related features/specs:** Feature 118 (docs/specs/4-architecture/features/118-share-rights-decoupling/spec.md), Feature 115 (docs/specs/4-architecture/features/115-server-import-improvements/spec.md), Feature 072 (docs/specs/4-architecture/features/072-edit-grant-escalation/spec.md)
- **Related open questions:** Q-118-01, Q-118-02, Q-118-03, Q-118-04, Q-118-06, Q-118-07, Q-118-08

## Context

`users.may_upload` was the only per-user content right. Besides uploading, it gated sharing with users and groups, the user and group pickers, editing, moving and deleting one's own albums and photos, album creation, tags, renamer rules, import from server and six smart albums. It did not gate public visibility, transfer, single-photo edits or uploading into an owned album, which only checked ownership. [Discussion #4982](https://github.com/LycheeOrg/Lychee/discussions/4982) needs users who share the albums imported for them but do not upload through Lychee. The single flag cannot express that.

Album grants (`access_permissions.grants_*`, Feature 072) act for their recipient whatever the recipient's own flags: users without upload rights upload into albums shared with `grants_upload`.

## Decision

1. **Three flags.** `users.may_upload` (add content), `users.may_share` (give others access: share with users and groups, public visibility, transfer, the user and group pickers) and `users.may_edit` (change existing content: edit, organise, create albums, move, merge, delete, tags, renamer rules, single and batch photo edits). The migration sets both new flags to `may_upload`, so nothing changes for current users with it.
2. **Upload implies the other two.** `may_upload` true forces `may_share` and `may_edit` true. This is enforced where users are written (`Actions\User\Create`, `Save`, LDAP provisioning, the migration, the test factory), never in the policies: each policy reads one flag. Allowed combinations are none, share, edit, share and edit, all three.
3. **Ownership needs the flag.** Every owner branch reads its flag: uploading into an owned album needs `may_upload`, changing its visibility or transferring it needs `may_share`, editing an owned photo needs `may_edit`. Admins skip all of them (`BasePolicy::before()`).
4. **Grants need what the sharer holds.** A sharer can give `grants_upload` only with `may_upload`, and `grants_edit`, `grants_move`, `grants_delete` only with `may_edit` (422 otherwise; admins skip). Only switching a grant on is checked: a grant already given can be resent unchanged after the sharer loses the right (Q-118-09). A grant still acts for its recipient whatever the recipient's flags.
5. **Import from server depends on `server_path` alone.** `may_upload` and `may_administrate` no longer count (amends FR-115-03). The import target must be owned by the requester or carry `grants_upload` (ability `CAN_IMPORT_INTO`, amends FR-115-07). `server_path` is set by the server owner only (FR-115-19), so it is already an explicit grant.
6. **New accounts.** `grant_new_user_share_rights` and `grant_new_user_edit_rights` join `grant_new_user_upload_rights`, initialised from it. LDAP users get all three flags. `lychee:create_user` gains `--may-share` and `--may-edit`.

## Consequences

### Positive
- An admin can give sharing or editing without upload, which the reported NAS setup needs.
- Every owner action is gated by exactly one flag; the hidden ownership-only paths (public visibility, transfer, owner uploads, single-photo edits) are gone.
- Users cannot work around a withheld right by granting it to each other.
- No priority logic in the policies: point 2 keeps each check a single flag read.

### Negative
- Owners without `may_upload` lose public visibility, transfer, uploads into their albums and single-photo edits on upgrade, unless the admin gives them the new flags.
- Collaborators without upload rights stop editing the photos they uploaded into someone else's album, unless that album grants edit.
- Revoking a flag does not remove grants the user already gave.
- One more column of checkboxes in the v7/v8 user forms, two settings, 23 languages.

## Alternatives Considered

- **Ownership alone decides sharing (Q-118-01 A).** No schema change, but no way to keep an owner from sharing. Rejected by the owner.
- **Keep editing under `may_upload` (Q-118-02 A), or split metadata from structure (B).** A keeps users who need to organise imported albums stuck; B splits `CAN_EDIT` across every request. Rejected for a dedicated flag (D).
- **Independent flags with priority logic in the policies.** `may_upload || may_share` in every check. Rejected by the owner: the implication is stored instead.
- **Recipient-side grant check (Q-118-07 B).** Breaks collaborators without upload rights who upload into shared albums.

## Security / Privacy Impact

- The user picker (every username) is restricted to users with `may_share`, as it was to users with `may_upload`.
- Point 4 closes an escalation path the split would otherwise open (two users without upload rights granting each other upload).
- Point 5 widens import to every user with a `server_path`; the path itself, its containment checks (ADR-115-01) and who may set it are unchanged.

## Operational Impact

- One migration adds two columns and backfills them; one adds two settings. Both are reversible.
- The smart-album rule of Feature 118 (FR-118-16) adds at most one `exists()` query per request.

## Links

- Related spec sections: `docs/specs/4-architecture/features/118-share-rights-decoupling/spec.md` FR-118-01 … 22, NFR-118-01
- Related ADRs: ADR-115-01, ADR-0011
- Related issues / PRs: [Discussion #4982](https://github.com/LycheeOrg/Lychee/discussions/4982)
