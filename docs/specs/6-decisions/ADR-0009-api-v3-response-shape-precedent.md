# ADR-0009: API v3 response-shape precedent — Struct-of-Arrays for collections, binary passthrough for single-item endpoints

- **Status:** Accepted
- **Date:** 2026-08-20
- **Related features/specs:** Feature 056 (docs/specs/4-architecture/features/056-api-v3-asset-retrieval/spec.md)
- **Related open questions:** Q-056-02

## Context

The owner stated that API v3's base response convention is Struct-of-Arrays (SoA — parallel-indexed arrays, e.g. `{ids: [...], titles: [...]}`) rather than v2's Array-of-Structs (AoS — an array of self-contained objects, e.g. `PaginatedPhotosResource`'s `data: [{id, title, ...}, ...]`). Feature 056's first v3 endpoint, however, retrieves exactly one binary file for one `photo_id`+`size_variant` pair — there is no collection of records in its response body for SoA vs. AoS to apply to. Because this is the endpoint that establishes v3's very first precedent, the decision of what "the SoA base convention" means for a non-collection endpoint needed to be recorded explicitly rather than left to guesswork by the next v3 feature.

## Decision

This endpoint returns a **pure binary passthrough**: raw file bytes with `Content-Type` set from the resolved file, no JSON envelope, no `data`/metadata wrapper — the same client contract as today's v2 `image/{path}` route. The SoA-vs-AoS response-shape principle is scoped to **future v3 endpoints that return collections** (e.g. a hypothetical v3 photo-listing endpoint); it does not apply to this or any other single-item binary-retrieval endpoint, because there is no array of records to shape either way.

## Consequences

### Positive
- Keeps this endpoint's client contract simple and standard (`<img src="...">`/direct byte stream), identical in shape to the existing, proven v2 file-serving pattern — no unnecessary base64 inflation or extra round-trip.
- Establishes an explicit, documented precedent (rather than an implicit one inferred from a single example) for the next v3 feature to follow: "SoA governs collections; single-item/binary endpoints are exempt."

### Negative
- A future v3 feature could misread this endpoint as "v3 abandoned JSON responses altogether" if this ADR isn't consulted — mitigated by referencing this ADR from `docs/specs/3-reference/api-design.md`'s future "API v3" section once it exists.

## Alternatives Considered

- **A (chosen) — Binary passthrough; SoA scoped to collection endpoints only.** Described above.
- **B — JSON envelope with base64-encoded data, for uniform JSON-shaped v3 responses everywhere.** Rejected: inflates payload size (~33% base64 overhead) for no benefit on a file-serving endpoint, and breaks the simple `<img src>` browser-native consumption pattern that photo galleries rely on throughout this codebase.
- **C — JSON metadata only (a resolved/signed URL), client makes a second request for bytes.** Rejected: adds a mandatory extra round-trip for the common case, and duplicates work the endpoint itself is meant to do (serve the file).

## Security / Privacy Impact

None beyond what Feature 056's spec/ADR-0008 already cover (this ADR is about response shape only, not access control).

## Operational Impact

- No caching/CDN behavior change versus v2's existing file-serving pattern — standard HTTP file response semantics apply.

## Links

- Related spec sections: `docs/specs/4-architecture/features/056-api-v3-asset-retrieval/spec.md` (FR-056-02, Non-Goals)
- Related open questions: Q-056-02 (docs/specs/4-architecture/open-questions.md)
- Related ADRs: ADR-0008 (this endpoint's authorization/signing model)
