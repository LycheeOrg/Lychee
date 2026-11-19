# Open Questions – Feature 068

Open questions for [Feature 068](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-068-01~~ | 068 – Flow Struct-of-Arrays & Publish-Date Scheduling | High | Reuse/extend the existing `base_albums.published_at` column (add a timezone companion column only) to back `flow_strategy=opt-in`'s publish date, or introduce new, separate `flow_datetime`/`flow_datetime_tz` columns as originally proposed? `published_at` is also independently used by Landing Page's (Feature 054) automatic-featured-items/latest-album-cover ordering, so the choice affects more than just Flow. | Resolved (Option A — reuse and extend `published_at` in place, add `published_at_orig_tz` only, no new column; owner: "obviously A") | 2026-09-17 | 2026-09-17 |

## Question Details

_No entries._
