# Feature 083 Tasks – Fix Search Header Refresh

_Status: Done_
_Last updated: 2026-11-10_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).

## Checklist

- [x] T-083-01 – Diagnose root cause and verify fix on a real device (FR-083-01, FR-083-02, FR-083-03, S-083-01, S-083-02, S-083-03, S-083-04).
  _Intent:_ Root-cause the mobile-only header-disappears bug; implement the two-line fix; verify via real-device isolation testing (baseline / each fix alone / both together).
  _Verification commands:_
  - `chrome://inspect` remote debugging against a real phone (manual, no CLI command — see spec Appendix for the full matrix).
  _Notes:_ Completed in the investigation session prior to this spec/plan/tasks set being authored (see spec Appendix for the full evidence). Documented here retroactively per the "No unapproved work outside the spec" principle — the spec/plan/tasks now reflect what was actually done.

- [x] T-083-02 – Author spec/plan/tasks/open-questions and run the Analysis Gate (self-review).
  _Intent:_ Formalise the already-diagnosed bug and fix per AGENTS.md SDD process before preparing the PR.
  _Verification commands:_ None (docs only).
  _Notes:_ Q-083-01 resolved inline (ship both fixes). See plan.md "Analysis Gate" section for the completed checklist.

- [x] T-083-03 – Run the frontend quality gate (NFR-083-02).
  _Intent:_ Confirm the diff passes this repo's standard frontend checks.
  _Verification commands:_
  - `npm run format`
  - `npm run check`
  _Notes:_ Both clean. `npm run check`'s precheck (`generate-js-types.sh`) initially failed with `Cannot find package '@nuxt/ui'` — a pre-existing incomplete local `node_modules` install, confirmed unrelated to this change by reproducing identically with the diff stashed. Fixed via `npm install` (already declared/locked in `package.json`/`package-lock.json`, not a new/upgraded dependency — only `"dev": true` metadata normalization on optional platform binaries resulted, reverted from the tracked lockfile to keep this PR's diff scoped). Separately, `npm run format` surfaced pre-existing Prettier drift in two unrelated files (`resources/js/lychee.d.ts`, `resources/js/v8/composables/spotlight/useSpotlightRemoteSearch.ts`) — reverted, not this feature's concern (NFR-083-01).

- [x] T-083-04 – Add knowledge-map addendum.
  _Intent:_ Document the `scrollIntoView()`-into-`overflow-hidden`-ancestor pitfall as a reusable lesson (see spec "Documentation Deliverables").
  _Verification commands:_ None (docs only).
  _Notes:_ Added "Mobile Scroll-Into-View Ancestor Pitfall (Feature 083, v7)" to `docs/specs/4-architecture/knowledge-map.md`, next to the existing Search Struct-of-Arrays (Feature 069) entry.

- [x] T-083-05 – Prepare commit for the operator.
  _Intent:_ Stage the diff + new docs, run `scripts/codex-commit-review.sh`, hand the operator a ready `git commit`/`git push` command. Do not execute commit/push without explicit operator action (Commit protocol).
  _Verification commands:_
  - `scripts/codex-commit-review.sh`
  _Notes:_ Branch is `spec/083-fix-search-header-refresh` (not `master`) — see [[feedback_use_dedicated_branch_for_upstream_prs]]. Script suggested `feat` type (heuristic, doesn't distinguish fix from feat) — overridden to `fix(v7)` to match this repo's actual convention (`git log upstream/master`) and Conventional Commits semantics for a bug fix. Staged files confirmed to exclude unrelated pre-existing untracked files (`.env-tim`, `Dockerfile.github`, etc.) and the reverted incidental `lychee.d.ts`/`useSpotlightRemoteSearch.ts`/`package-lock.json` drift (see T-083-03 notes).

- [x] T-083-06 – Verify desktop no-regression with exact layout geometry (S-083-03).
  _Intent:_ Confirm the fix is a true no-op on desktop (no dynamic toolbar, so `svh`≈`vh` and `scrollIntoView`'s alignment mode never matters), beyond just "the bug never reproduced here" — with hard numeric evidence, not an eyeballed screenshot.
  _Verification commands:_ None (manual browser automation — see spec Test Strategy entry for S-083-03 for full method).
  _Notes:_ First attempt was invalid and had to be redone: re-`navigate()`-ing to the same URL after redeploying the backend silently left the *old* JS bundle running in the tab (caught by the operator asking "did you remember to refresh the screen?" — confirmed via `document.querySelector('script[type=module]').src` still showing the stale hash). Redone with an explicit hard refresh (`Ctrl+Shift+R`) and a bundle-hash check in the live DOM before every capture, toggling the working tree between `HEAD` (unpatched) and the staged fix, rebuilding/redeploying the container each time. `getBoundingClientRect()` on the header, search input, "Photos (N)" heading, and first thumbnail, plus `scrollY`/`bodyScrollHeight`, came out byte-identical both times. Working tree and the running `:8000` container were restored to the fixed state afterward and re-verified via `curl`.

- [x] T-083-07 – Roadmap entry and local session snapshot.
  _Intent:_ Satisfy AGENTS.md "Sync context to disk" / Tracking & Documentation requirements — every feature gets a roadmap.md row, and `_current-session.md` reflects the live session state.
  _Verification commands:_ None (docs only).
  _Notes:_ Added Feature 083 row to roadmap.md's Active Features table (Testing status, matching the convention used for similarly-sized fixes like #048/#080) and a dated entry to its "Last updated" log. Rewrote `docs/specs/_current-session.md` (gitignored, local-only) replacing the stale Feature 081 snapshot from 2026-10-04. Caught only after the operator explicitly asked whether anything else was required per AGENTS.md — not surfaced proactively at session start, since this work began mid-session rather than via the runbook-session-reset.md kickoff flow.

## Notes / TODOs
No automated JS/TS test exists for this bug class (see spec Test Strategy) — not a deferred/skipped test, this repo has no wired-up frontend test runner at all today. Flagged as a possible separate future investment, not a gap specific to this feature.
