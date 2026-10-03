# Open Questions – Feature 079

Open questions for [Feature 079](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-079-02~~ | 079 | Medium | Remove `Maintenance::update` (GET/POST + Maintenance card): what happens to the `/Update` link | Resolved (Option C) | 2026-10-03 | 2026-10-03 |
| ~~Q-079-01~~ | 079 | Medium | Which simplifications to apply, including removing git tags mode | Resolved (items 1, 2, 3, 5) | 2026-10-03 | 2026-10-03 |

## Question Details

### ~~Q-079-02~~: Removing `Maintenance::update` and the Fate of the `/Update` Link ✅ RESOLVED

**Feature:** 079 – Versioning Code Simplification
**Priority:** Medium
**Status:** Resolved (Option C)
**Opened:** 2026-10-03
**Resolved:** 2026-10-03

**Resolution:** **Option C** — operator: "I want to remove the full update from online functionality" and "the migrate route stays". `/migrate` keeps its route, request and result view but only runs the database migration (no git pull, no composer).

**Spec Impact:** FR-079-07 … FR-079-10, S-079-10 … S-079-14, removed-code table (online updater rows).

**Context:** `POST /api/v2/Maintenance::update` (`UpdateController::check()`) never hydrates `GitHubVersion` / `FileVersion` with remote data, so it always reports no update. The operator proposes removing `GET`/`POST /api/v2/Maintenance::update` and the Maintenance "Update" card (v7 + v8). The card's text (channel, branch/commit, behind count) already appears in Diagnostics, and update availability already appears in the admin dashboard banner (FR-037-07). But the card's "Update" button is the only UI link to `/Update` (`UpdateController::view()`: `BranchCheck` → `GitPull` → `ArtisanMigrate` → `ComposerCall`).

**Option A (recommended) — Remove the card and both routes, move the "Update" button into the dashboard banner:** `Admin/UpdateStatus` gains `can_update` (not Docker and `UpdatableCheck::assertUpdatability()` passes); the banner shows an "Update" button to `/Update` when an update is available and `can_update` is true.
- Pros: one place for "an update exists" and "apply it"; removes `UpdateInfo`, `UpdateCheckInfo`, two service methods, two components, the broken `check()`.
- Cons: one new boolean in `Admin/UpdateStatus`, a button in both dashboards.

**Option B — Remove the card and both routes, keep `/Update` reachable by URL only:**
- Pros: smallest change.
- Cons: the online updater becomes a hidden feature; admins must know the URL.

**Option C — Remove the card, both routes and the online updater (`/Update`, `UpdateController::view()`, `ApplyUpdate` and its git/composer pipes):** updates happen through git/composer/Docker on the server; `/migrate` stays.
- Pros: largest deletion; no shell execution from the web process.
- Cons: removes an existing feature that some git installs may rely on; larger change touching the install/update pipeline.

---

### ~~Q-079-01~~: Scope of the Versioning Simplification ✅ RESOLVED

**Feature:** 079 – Versioning Code Simplification
**Priority:** Medium
**Status:** Resolved (items 1, 2, 3, 5)
**Opened:** 2026-10-03
**Resolved:** 2026-10-03

**Context:** After Feature 037 I8, the review proposed: (1) delete unused code, (2) drop the commits list, (3) drop tags mode, (4) rename `InstalledVersion::isRelease()`, (5) one git-info formatter, (6) lazy hydration. Item 3 removes a mode: installs on a detached tag lose the "N tags behind" text but keep the release signal from `FileVersion`.

**Resolution:** Operator answer "Do 1235". Items 4 and 6 are non-goals. Lightweight follow-ons recorded directly in the spec: a detached HEAD is channel `tag` and shows `<version.md> (<sha>)` (FR-079-05/06); `BranchCheck` refuses online updates on a detached HEAD (FR-079-04), where `git pull` failed anyway.

**Spec Impact:** FR-079-01 … FR-079-06, NFR-079-01/02, removed-code table.
