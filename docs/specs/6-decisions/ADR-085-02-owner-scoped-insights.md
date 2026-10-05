# ADR-085-02: Insights are scoped to one owner, administrators choose the owner

- **Status:** Accepted
- **Date:** 2026-10-05
- **Related features/specs:** Feature 085 (docs/specs/4-architecture/features/085-library-insights/spec.md)
- **Related open questions:** Q-085-03

## Context

Insights aggregate a library's capture dates, devices, settings and storage. A user may access photos owned by others (shared albums, public albums). The Statistics requests limit queries to `owner_id = Auth::id()` unless the user may administrate, in which case they see every owner.

## Decision

- Insights cover the photos owned by the scope's owner, never photos merely shared with the viewer.
- A non-administrator's scope is always their own library.
- An administrator chooses "My library", any single user, or "Whole instance" (every owner).
- The request layer enforces this: a non-administrator asking for another owner or the whole instance gets 403.

## Consequences

### Positive
- Insights describe one photographer's habits.
- No access-rights query in the aggregates: a single `owner_id` filter (or none for the whole instance).
- Administrators keep the instance-wide view Statistics gave them.

### Negative
- Viewers of a shared gallery get no insights about photos they can see but do not own.

## Alternatives Considered

- **Every accessible photo:** useful for shared family galleries, but mixes photographers and needs the access-rights query on every aggregate.
- **Own photos only, administrators included:** simplest, but administrators lose the instance-wide view.

## Security / Privacy Impact

A non-administrator can never obtain aggregates (counts, devices, capture times) over another owner's photos. Administrators already have that visibility.

## Operational Impact

"Whole instance" aggregates scan every photo; cost is bounded by the single-pass aggregation and its cache (ADR-085-03).

## Links

- Related spec sections: `docs/specs/4-architecture/features/085-library-insights/spec.md#functional-requirements` (FR-085-04), NFR-085-02
- Related ADRs: ADR-085-01
