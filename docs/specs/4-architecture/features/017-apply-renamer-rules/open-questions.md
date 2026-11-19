# Open Questions – Feature 017

Open questions for [Feature 017](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|

## Question Details

### ~~Q-017-01: Context Menu Scope Behaviour for Photos vs Albums~~ ✅ RESOLVED

**Decision:** Option A — Scope radio hidden for photos, shown for albums
**Rationale:** Most intuitive UX. Photos have no descendants so scope is meaningless — hide it. Albums support "Current level" (rename only selected album titles) and "All descendants" (selected albums + sub-albums recursively). Backend receives `album_ids[]` + `scope` for the album path; `photo_ids[]` only (no scope) for the photo path.
**Updated in spec:** FR-017-07 (scope hidden for photos), FR-017-08 (scope shown for albums), FR-017-09 (contract split by target type)

---

### ~~Q-017-02: No Renamer Rules Configured Edge Case~~ ✅ RESOLVED

**Decision:** Option A — Show the empty preview with an enhanced message
**Rationale:** Simplest approach with no extra API calls. The empty-state message is enhanced: "No titles would change. If you haven't configured renamer rules yet, visit Settings → Renamer Rules." Minimal code change, no additional data dependencies.
**Updated in spec:** FR-017-05 (enhanced empty-state message), UI-017-05
