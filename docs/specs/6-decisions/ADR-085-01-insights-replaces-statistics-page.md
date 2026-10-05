# ADR-085-01: Insights replaces the Statistics page

- **Status:** Accepted
- **Date:** 2026-10-05
- **Related features/specs:** Feature 085 (docs/specs/4-architecture/features/085-library-insights/spec.md)
- **Related open questions:** Q-085-01, Q-085-02

## Context

The Statistics page (`/statistics`, SE, v7 and v8) shows a size-variant meter, a punch-card calendar of capture/upload dates, totals and a per-album space table. Feature 085 adds a library Insights page whose calendar heatmap and totals overlap with it. The album statistics drawer (v7 and v8) calls `Statistics::sizeVariantSpace` and `Statistics::totalAlbumSpace` with an album ID.

## Decision

- A v8-only Insights page at `/insights` replaces the Statistics page and its left-menu entry.
- The size-variant meter and album space table become the Insights "Storage" section, reading the existing `Statistics::sizeVariantSpace`, `Statistics::albumSpace` and `Statistics::totalAlbumSpace` endpoints, which stay unchanged for the drawer as well.
- The punch card is replaced by the Insights calendar heatmap. `Statistics::getCountsOverTime`, `Statistics::userSpace` (no consumer), the v7 and v8 Statistics views and the punch-card components are removed.
- v7 gets no Insights page and loses the Statistics menu entry.

## Consequences

### Positive
- One page for numbers about a library; no duplicated calendar or totals.
- Frontend work in Nuxt UI only.

### Negative
- v7 users lose the Statistics page.
- Administrator disk-usage figures sit inside a page about photography habits.

## Alternatives Considered

- **Separate Insights page next to Statistics:** clean split, but overlapping totals and calendar on two pages.
- **Extend the Statistics page in place:** same result with a name that undersells the content.
- **v7 and v8:** parity, but every chart component written twice.

## Security / Privacy Impact

No change to the gating of the kept endpoints. Removing `Statistics::userSpace` removes an unused administrator endpoint.

## Operational Impact

None.

## Links

- Related spec sections: `docs/specs/4-architecture/features/085-library-insights/spec.md#functional-requirements` (FR-085-01 … FR-085-03, FR-085-07)
- Related ADRs: ADR-085-02
