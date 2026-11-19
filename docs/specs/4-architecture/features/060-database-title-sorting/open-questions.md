# Open Questions – Feature 060

Open questions for [Feature 060](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-060-01~~ | 060 – Database-Driven Title Sorting | Medium | Does Description get the same title_base/title_index split as Title, or is it dropped as a sort criterion entirely? | Resolved (Description ordering removed completely — no split columns for description; existing Description-based sort configs are migrated to Title, FR-060-10) | 2026-08-27 | 2026-08-27 |
| ~~Q-060-02~~ | 060 – Database-Driven Title Sorting | Medium | Should the numeric-suffix splitter be a single hardcoded rule or a pluggable/configurable pattern system, given other patterns (e.g. parenthesised numbering) may be wanted later? | Resolved (hardcoded ordered 2-rule chain — trailing digits, then trailing parenthesised number — baked into one `TitleSplitter` function, FR-060-02; a fully pluggable/admin-configurable system explicitly deferred as a Non-Goal/Follow-up) | 2026-08-27 | 2026-08-27 |
| ~~Q-060-03~~ | 060 – Database-Driven Title Sorting | High | User review of the draft `TitleSplitter` heuristic found both rules only match at the absolute end of the string, so a trailing file extension (e.g. `xxx_123.jpg`, `xxx (123).xts`) silently defeats both the trailing-digit and parenthesised-number rules, falling to the no-index fallback — a real regression for the single most common case (photo filenames as titles). | Resolved (added a Stage-A extension-aware pre-strip — `\.([A-Za-z][A-Za-z0-9]{0,4})$`, deliberately excluding digit-only suffixes like `.2` so those still hit the trailing-digit rule directly — applied before both rules; see Q-060-04 for a correction to how the extension is subsequently handled) | 2026-08-28 | 2026-08-28 |
| ~~Q-060-04~~ | 060 – Database-Driven Title Sorting | High | Implementation-time contradiction found in Q-060-03's resolution: the drafted FR-060-02 said the extension is "discarded, not appended back to base" once an index is found, but NFR-060-09/S-060-21 require `photo_5.jpg` and `photo_5.heic` to get *different* `title_base` values so they don't tie — under the literal "discarded" rule both compute an identical `base`/`index` and do tie. | Resolved (the extension is re-appended to `base` — lower-cased — once Stage B successfully extracts an index from the extension-stripped stem; e.g. `photo_5.jpg` → `base="photo_.jpg"`, `photo_5.heic` → `base="photo_.heic"`, both `index=5`, so they sort as two separate runs. FR-060-02's wording and the Appendix reference implementation/worked-examples table are corrected accordingly) | 2026-08-28 | 2026-08-28 |

## Question Details

_No entries._
