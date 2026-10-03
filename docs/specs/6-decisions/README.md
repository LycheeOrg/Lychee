# Decisions

We track significant choices as Architecture Decision Records (ADRs). Each ADR is immutable; superseded entries should point at their replacements.

Numbering convention: new ADRs use `ADR-<NNN>-<YY>`, where `<NNN>` is the feature that raised the decision and `<YY>` is a two-digit sequence within that feature (`ADR-072-01`, `ADR-072-02`, …). Numbering per feature means parallel features cannot collide. ADRs created before this rule keep their global `ADR-XXXX` numbers; a decision that spans several features belongs to the feature that raised it.

| ADR | Status   | Summary                            |
|-----|----------|------------------------------------|
| [ADR-0003](ADR-0003-album-computed-fields-precomputation.md) | Accepted | Album Computed Fields Pre-computation Strategy |
| [ADR-0004](ADR-0004-multi-group-permission-merge.md) | Accepted | Multi-Group Permission Merge Policy |
| [ADR-0005](ADR-0005-nuxt-ui-migration.md) | Accepted, partially superseded by **ADR-0006** | Replace PrimeVue with Nuxt UI (standalone Vue mode) |
| [ADR-0006](ADR-0006-nuxt-ui-dual-tree-toggle.md) | Accepted | Dual-tree, feature-flag-gated cutover strategy for the Nuxt UI migration |
| [ADR-0007](ADR-0007-v8-admin-setup-gate-bypass.md) | Accepted | Exempting a dedicated route from the `admin_user:set` gate to serve v8's admin-setup page |
| [ADR-0008](ADR-0008-v3-asset-endpoint-signing-and-authorization.md) | Accepted | Temporary-link signing and authorization model for the v3 asset endpoint |
| [ADR-0009](ADR-0009-api-v3-response-shape-precedent.md) | Accepted | API v3 response-shape precedent — Struct-of-Arrays for collections, binary passthrough for single-item endpoints |
| [ADR-0010](ADR-0010-album-user-thumb-cache-purge-on-revocation.md) | Accepted | Purge cached album covers on revocation rather than re-checking permissions per asset request |
| [ADR-0011](ADR-0011-move-grant-separate-from-edit.md) | Accepted | Move grant separate from Edit, and cross-owner guards |
| [ADR-069-01](ADR-069-01-v3-collection-bounding-strategies.md) | Accepted | Bounding strategies for v3 Struct-of-Arrays collection endpoints |
| [ADR-071-01](ADR-071-01-date-scrubber-ticks-derived-client-side.md) | Accepted | Date Scrubber Ticks Are Derived Client-Side; Bucket Storage Stays Unchanged |
| [ADR-074-01](ADR-074-01-gallery-password-visitor-unlock.md) | Accepted | Gallery password as a per-visitor unlock, not a guest account |
| [ADR-074-02](ADR-074-02-gallery-unlock-encrypted-cookie.md) | Accepted | Gallery unlock stored in an encrypted cookie, not the server-side session |
| [ADR-074-03](ADR-074-03-password-config-type.md) | Accepted | Write-only `password` config type (hashed on write, masked on read) |
| [ADR-081-01](ADR-081-01-in-house-webgl2-sphere-renderer.md) | Accepted | In-house TypeScript + WebGL2 sphere renderer for 360° photos |

## Templates

- Use [adr-template.md](../templates/adr-template.md) when creating new ADRs.
- Each ADR documents context, decision, consequences, alternatives, security/privacy, operational impact, and reference links, plus pointers to the affected specs/open-questions.
