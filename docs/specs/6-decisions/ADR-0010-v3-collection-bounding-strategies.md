# ADR-0010: Bounding strategies for v3 Struct-of-Arrays collection endpoints

- **Status:** Accepted
- **Date:** 2026-09-22
- **Related features/specs:** Feature 069 (docs/specs/4-architecture/features/069-search-struct-of-arrays/spec.md), Feature 062, Feature 064, Feature 066, Feature 067, Feature 068
- **Related open questions:** Q-069-02, Q-069-10, Q-068-04, Q-067-16, Q-064-01

## Context

ADR-0009 established *what shape* a v3 collection response has (Struct-of-Arrays), but said nothing about *how large* one may get. Six features have since answered that question independently, and by Feature 069 three distinct bounding strategies were in use with no recorded rule for choosing between them:

- **Bucket-windowed** (Feature 064 per-album photos, Feature 066 Timeline): a cheap `buckets` aggregate tier, then a `ratios` tier fetched incrementally windowed by `bucket_ids[]`. Cost is bounded by the requested window, never by total scope size.
- **Whole-scope unpaginated** (Feature 062 root albums, Feature 068 Flow): the entire scope in one request, bounded only by the natural size of the thing being listed (albums in a tree, albums in a feed).
- **Capped with a refusal fallback** (Feature 067 Map leaf tier): fetch `cap + 1`; over the cap, return nothing and let the client fall back to the aggregate tier.

Feature 069 (Search) did not fit any of them cleanly. Its scope is cross-album and genuinely unbounded — a two-character term can match an entire library, so "whole-scope" has no natural ceiling the way an album or a feed does — yet its result set is also not a browsing surface where a date scrubber makes sense, and returning nothing for a broad search would leave a blank grid where the Map at least keeps its bucket badges. The owner chose whole-scope delivery (Q-069-02) plus a hard result cap with an explicit truncation signal (Q-069-10), which is a fourth strategy. Recording only that outcome inside Feature 069's spec would leave the next v3 feature to re-derive the whole comparison from scratch, as Feature 069 had to.

Affected modules: rest-api (every v3 collection endpoint), ui (each consumer's fetch/render strategy).

## Decision

Every v3 collection endpoint must state, in its spec, which of four bounding strategies it uses and why. The choice is governed by one question: **what bounds the scope, and is that bound something the user can see and act on?**

1. **Whole-scope unpaginated** — when the scope has a natural, structural ceiling the user already understands (the albums in a tree, the albums in a feed, the photos in one album). No cap needed; the data itself is the bound.
2. **Bucket-windowed** — when the scope is open-ended *and* has a meaningful ordering axis the user can navigate (dates, titles). The `buckets` tier gives a cheap total shape; `ratios` is fetched per window. This is the default for any library-wide browsing surface.
3. **Capped with truncation** — when the scope is open-ended, has no navigable axis, and a partial answer is still useful. Fetch `cap + 1`; return the first `cap` rows with an explicit `is_truncated` flag; the client tells the user to narrow the query. **Search uses this.**
4. **Capped with refusal** — when the scope is open-ended, has no navigable axis, and a partial answer would be actively misleading. Fetch `cap + 1`; over the cap, return an empty result plus a flag, and let the client fall back to an aggregate tier. **Map's leaf tier uses this**, and only because it *has* an aggregate tier to fall back to.

Strategies 3 and 4 must expose their cap as an admin config, never a hard-coded constant, and must signal the bound explicitly in the response body — never silently truncate.

## Consequences

### Positive

- The next v3 feature picks a strategy by answering one question instead of re-deriving the comparison, which is exactly the work Feature 069 had to redo.
- Makes explicit that "unpaginated" was never a blanket principle — it was always conditional on the scope having a structural ceiling. Features 062/068 qualify; Search does not.
- Forces the distinction between truncation (partial answer, still useful) and refusal (partial answer misleading), which had been decided ad hoc per feature.
- Requiring the cap to be admin-configurable keeps a deployment-specific lever where operators already expect one; Feature 069's `search_result_limit` had already been tuned down once (1000 → 300) under its former name.

### Negative

- Four named strategies is more taxonomy than a small codebase strictly needs, and a future scope may still not fit cleanly — the rule is a decision aid, not an exhaustive partition.
- Strategy 3 means a capped response is not a complete answer. Clients must render the truncation signal or they will silently misrepresent results; this is a real correctness obligation pushed onto every consumer.
- Strategies 3 and 4 each add an admin config key, and every config key carries documentation and 22 locale files behind it.

## Alternatives Considered

- **A (chosen) — Name the four strategies and give a selection rule.** Pros: makes the implicit rule explicit and reviewable; cheap to apply. Cons: taxonomy overhead; may not cover a future case.
- **B — Mandate bucket-windowing for every open-ended v3 collection.** Pros: one rule, no judgement needed; strongest cost guarantee. Cons: forces a bucket tier onto scopes with no navigable axis — for Search it would mean alphabetical-prefix scrubbing over a result set the user is scanning, not browsing; rejected by the owner in Q-069-02.
- **C — Leave it per-feature, as today.** Pros: zero process; maximum flexibility. Cons: the status quo that made Feature 069 re-derive the comparison, and that let three strategies accumulate with no recorded rationale for choosing among them.
- **D — Always paginate open-ended collections.** Pros: universally bounded, trivially understood. Cons: reintroduces exactly the page-jump model Features 064–069 were built to remove, and defeats the virtualized-rendering stack every v8 view now shares.

## Security / Privacy Impact

None directly. One adjacent obligation carried over from Feature 069: a capped endpoint whose cache key is derived from user-supplied input (a search term) must key on a **digest** of the parsed input, never the raw text, so user-entered queries do not reach cache-event logs. This is a requirement on the cache key, not on the bounding strategy itself.

## Operational Impact

- Strategies 3 and 4 give operators a real tuning lever, and the response's truncation/refusal flag makes it observable when that lever is biting — a support question ("why do I only see 300 results?") becomes answerable from the response body.
- No change to any shipped endpoint. Features 062/064/066/067/068 are retroactively described by this ADR, not altered by it.

## Links

- Related spec sections: `docs/specs/4-architecture/features/069-search-struct-of-arrays/spec.md` (FR-069-02, NFR-069-01, NG1)
- Related open questions: Q-069-02, Q-069-10 (docs/specs/4-architecture/open-questions.md)
- Related ADRs: ADR-0009 (response *shape*; this ADR covers response *size*)
