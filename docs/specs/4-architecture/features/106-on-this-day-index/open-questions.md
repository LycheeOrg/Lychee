# Open Questions – Feature 106

Open questions for [Feature 106](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-106-01~~ | 106 | Medium | Day of a photo with a taken date: A local day where it was taken (as `taken_key`, Timeline), B UTC day as stored (today's result) | Resolved (A, FR-106-01, S-106-05) | 2026-11-01 | 2026-11-01 |

## Question Details

### ~~Q-106-01~~ · Which day a photo with a taken date belongs to

**Status:** Resolved (A, owner, 2026-11-01): the key holds the local day where the photo was taken (FR-106-01, S-106-05).
**Preferred option:** A – the local day where the photo was taken

**Question**
`taken_at` is stored in UTC with the zone it was taken in (`taken_at_orig_tz`). On This Day matches the month and day of the stored UTC value, while `taken_key` (Timeline, Feature 092) uses the local date where the photo was taken. For a photo taken near midnight away from UTC the two days differ: a photo taken at 00:30 on 2 November in Paris is stored as 1 November 23:30 UTC and is shown On This Day on 1 November. Which day does the key hold?

**Option A (recommended) – the local day where it was taken**
- Idea: key = month and day of `taken_key` (`Y-m-d-H` in `taken_at_orig_tz`); the backfill reads it from the stored `taken_key`.
- Pros: the day the photographer sees on the photo and in the Timeline; one rule across Lychee; the backfill needs no time-zone arithmetic in SQL.
- Cons: photos taken near midnight away from UTC move to the neighbouring day compared with today.

**Option B – the UTC day as stored**
- Idea: key = month and day of the stored `taken_at`, as the current query.
- Pros: exactly today's members.
- Cons: keeps the off-by-one day for photos taken near midnight; differs from the Timeline.
