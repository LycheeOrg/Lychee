# Open Questions – Feature 048

Open questions for [Feature 048](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-048-01~~ | 048 – Fix Multi-Group Permissions | High | Should a direct user-level `AccessPermission` override group permissions, or merge with them, once group-vs-group merging is fixed? | Resolved (A – merge everything via boolean OR) | 2026-07-01 | 2026-07-01 |

## Question Details

### ~~Q-048-01~~ · Direct user-level `AccessPermission` — override group grants, or merge with them? ✅ RESOLVED

**Status:** Resolved — **Option A** (merge direct-user row + every matching group row via boolean OR)
**Feature:** 048 – Fix Multi-Group Permissions
**Priority:** High
**Opened:** 2026-07-01
**Resolved:** 2026-07-01

**Resolution:** `current_user_permissions()` collects every `AccessPermission` row matching the current user — the direct `user_id` row (if any) plus every row whose `user_group_id` is one of the user's group ids — and returns a single synthetic, non-persisted `AccessPermission` whose 5 boolean grant flags are the logical OR across all collected rows. There is no precedence between a direct-user row and group rows; the most permissive applicable grant always wins, consistent with the existing public+current-user OR pattern in `AlbumPolicy` and the group-OR semantics already used in `canDeleteById`/`canEditById`.

**Spec impact:** Captured in FR-048-01 and NFR-048-01 below.
