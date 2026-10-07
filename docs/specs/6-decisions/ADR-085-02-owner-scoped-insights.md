# ADR-085-02: Insights are scoped to one owner, administrators choose the owner

- **Status:** Accepted
- **Date:** 2026-10-05
- **Related features/specs:** Feature 085 (docs/specs/4-architecture/features/085-library-insights/spec.md)
- **Related open questions:** Q-085-03, Q-085-14

## Context

Insights aggregate a library's capture dates, devices, settings and storage. A user may access photos owned by others (shared albums, public albums). The Statistics requests limit queries to `owner_id = Auth::id()` unless the user may administrate, in which case they see every owner.

## Decision

- Insights cover the photos owned by the scope's owner, never photos merely shared with the viewer.
- A non-administrator's scope is always their own library.
- An administrator chooses "My library", any single user, or "Whole instance" (every owner).
- An album scope covers one regular album and its descendants (nested set), counting every photo in that tree whoever uploaded it. Only the album's owner, or an administrator, may choose it.
- The request layer enforces this: a non-administrator asking for another owner, the whole instance or an album they do not own gets 403.

## Consequences

### Positive
- Insights describe one photographer's habits.
- No access-rights query in the aggregates: a single `owner_id` filter (or none for the whole instance).
- Administrators keep the instance-wide view Statistics gave them.

### Negative
- Viewers of a shared gallery get no insights about photos they can see but do not own, and cannot scope Insights to a shared album.
- In album scope an owner sees aggregates over photos other users uploaded into their tree; they can already see those photos.

## Alternatives Considered

- **Every accessible photo:** useful for shared family galleries, but mixes photographers and needs the access-rights query on every aggregate.
- **Own photos only, administrators included:** simplest, but administrators lose the instance-wide view.
- **Album scope for any accessible album:** works for shared albums, but needs a per-album access query on every aggregate and in the cache key.
- **Album scope without descendants:** simplest query, but a trip's parent album shows nothing.

## Security / Privacy Impact

A non-administrator can never obtain aggregates (counts, devices, capture times) over another owner's photos. Administrators already have that visibility.

## Operational Impact

"Whole instance" aggregates scan every photo; cost is bounded by the single-pass aggregation and its cache (ADR-085-03).

## Links

- Related spec sections: `docs/specs/4-architecture/features/085-library-insights/spec.md#functional-requirements` (FR-085-04, FR-085-16), NFR-085-02
- Related ADRs: ADR-085-01
