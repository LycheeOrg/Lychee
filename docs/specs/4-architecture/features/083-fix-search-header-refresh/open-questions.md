# Open Questions – Feature 083

Open questions for [Feature 083](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-083-01~~ | 083 | Medium | Ship both independently-sufficient fixes (`svh` + `scrollIntoView` block:"nearest"), or just the minimal single-line fix? | Resolved (ship both) | 2026-11-09 | 2026-11-09 |
| ~~Q-083-02~~ | 083 | Medium | Upstream PR #4956 (merged, discussion #4953) fixed a related `v8` mobile-toolbar/`100vh` bug using `dvh`, not `svh`. Should FR-083-01 use `dvh` instead, for consistency with that precedent? | Resolved (keep svh) | 2026-11-10 | 2026-11-10 |

## Question Details

### ~~Q-083-01~~ — Ship both fixes or just the minimal one?

Real-device isolation testing (see spec Appendix) confirmed either the `svh` fix alone or the `scrollIntoView` `block: "nearest"` fix alone was independently sufficient to resolve the reported bug. Asked the user whether to ship the minimal single-line diff or both.

- **Option A (recommended, chosen):** Ship both. `block: "nearest"` fixes the actual landmine — a `scrollIntoView` call bleeding into an `overflow-hidden` ancestor it was never meant to touch — on its own merits, independent of this specific layout. The `svh` fix corrects a genuine `vh`/`svh` inconsistency that matches the convention already established elsewhere in this codebase (`Timeline.vue` uses `svh` consistently), closing off a second latent source of the same class of cold-load mobile bug.
- **Option B:** Ship only `scrollIntoView` `block: "nearest"` (smallest possible diff). Rejected — would leave the `vh`/`svh` inconsistency in place as an unaddressed, already-identified latent issue.

**Resolution:** Option A. Recorded in spec FR-083-03.

### ~~Q-083-02~~ — `svh` or `dvh`, given upstream's `dvh` precedent on a related bug?

While researching whether discussion #4968 had prior art, found discussion #4953 ("[v8] Photo viewer uses 100vh on mobile...") — the same root problem class (`100vh` on mobile lying about available space due to the toolbar) in a different component (`v8`'s `PhotoPanel.vue`). ildyria explicitly weighed `svh` vs `dvh` there and the merged fix, PR #4956, chose `dvh`. Asked the user whether FR-083-01 should match that precedent.

- **Option A (recommended, chosen):** Keep `svh`. The outer wrapper in `Search.vue` is pre-existing, untouched code already using `h-svh` — a static, worst-case value. The inner results container and the outer wrapper together partition one fixed total height (header + results = one screen), which needs both sides to agree on one *stable* number; `dvh` continuously tracks the live viewport as the toolbar animates, which is appropriate for `PhotoPanel.vue`'s single full-screen modal (nothing else is sized relative to it) but would risk the scrollable results container visibly resizing mid-scroll here, since its height has to stay in a fixed relationship with a sibling header rather than just fill one box. Keeps the diff at the originally-diagnosed 2 lines. The PR description notes the #4956 precedent and this rationale explicitly, so the maintainer sees the divergence was considered, not missed.
- **Option B:** Switch to `dvh`, also changing the outer wrapper's existing `h-svh` to `h-dvh` to keep both sides consistent (a 3rd line, outside the originally-diagnosed fix). Matches the maintainer's most recent precedent for this exact bug class, but the scroll-resize risk above is untested in either direction.

**Resolution:** Option A. Recorded in spec FR-083-01 note.
