# Feature 070 – Fix Full-Photo-Access Over-Grant

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-09-24 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | Feature 070 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [open-questions.md](../../open-questions.md); resolved answers are encoded in the normative sections below.

## Overview

Five API surfaces decide whether a viewer may obtain a photo's **full-resolution file** by reading the `grants_full_photo_access` **config**. That config is not an authorization setting: it is the seed value `AccessPermission::ofPublic()`/`ofPublicHidden()` stamp onto a **newly created** public share. The authoritative value is the `access_permissions.grants_full_photo_access` **column**.

The two defaults point opposite ways — config `1`, column `0` — so the failure is systematic: an album shared with full-photo access explicitly **off** still yields full-resolution URLs. Demonstrated directly (same photo, same viewer, config ON, album grant OFF):

```
v2 original.url : https://lychee.test/uploads/original/24/35/886de0d1...jpg
v3 original.url : NULL
```

Feature 069 established the correct rule and proved it for the v3 search tier (`SearchV3FullPhotoAccessTest`). This feature applies that same rule to the remaining surfaces. Affected modules: REST (5 controllers/resources), core (one shared resolver, already built for Feature 069).

Nothing in the test suite covered this behaviour before Feature 069 — which is how it went unnoticed.

## Goals

1. Every surface that emits `size_variants.original.url` decides it per photo, from real permission rows.
2. No N+1: the decision for a whole collection costs **one** additional query.
3. `configs.grants_full_photo_access` retains exactly one role — seeding new public shares.
4. Regression coverage for each fixed surface, since none existed.

## Non-Goals

- **NG1 — The config key is not removed or renamed.** It stays, correctly used by `AccessPermission::ofPublic()`/`ofPublicHidden()`.
- **NG2 — No change to `PhotoPolicy::canAccessFullPhoto()`'s semantics.** This feature makes more call sites obey the existing rule; it does not redefine it.
- **NG3 — `grants_download` is out of scope.** The same resolver already computes it, but no photo resource carries a download signal today and wiring one is a separate UI-facing change.
- **NG4 — The v3 tiers are untouched.** Feature 069 already fixed them.
- **NG5 — `AlbumPolicy::canAccessFullPhoto()`'s config fallback for a `null`/smart album is left in place.** It is the documented answer for "no album in context", and after this feature no photo-listing surface reaches it.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry | Source |
|----|-------------|--------------|-----------------|--------------|-----------|--------|
| FR-070-01 | Full-resolution access is decided **per photo** by `PhotoPolicy::canAccessFullPhoto()`'s rule: owner → allowed; otherwise the OR of every containing album's `grants_full_photo_access` grant. | A photo the viewer owns is never downgraded; a photo in any granting album is not downgraded. | — | A photo in no granting album, not owned, is downgraded. | — | Q-069-12 |
| FR-070-02 | The decision for a collection of photos costs **one** additional query, via the existing `ResolvesPhotoGrants`. | One grouped query per response. | — | — | — | Owner: "minimize the number of queries and eager loads" |
| FR-070-03 | `GET /api/v2/Search` applies FR-070-01 per photo. | — | — | — | — | Q-069-13 |
| FR-070-04 | `GET /api/v2/Album/{id}/Photos` applies FR-070-01 per photo, for **all three** branches (smart album, tag/person album, regular album). | — | — | — | — | Q-069-13 |
| FR-070-05 | The Map position-data actions (`Actions\Albums\PositionData`, `Actions\Album\PositionData`) apply FR-070-01 per photo. | — | — | — | — | Q-069-13 |
| FR-070-06 | The embed stream (`EmbedController`) applies FR-070-01 per photo. | — | — | — | — | Q-069-13 |
| FR-070-07 | The v2 Timeline (`TimelineResource::fromData()`) applies FR-070-01 per photo. | — | — | — | — | Q-069-13 |
| FR-070-08 | The four collection resources take a per-photo map instead of one boolean, so a single value can no longer be applied to a whole response by construction. | `PhotoResource`'s own per-photo `bool` parameter is unchanged. | A photo absent from the map is downgraded (deny by default). | — | — | Design |
| FR-070-09 | An admin is never downgraded. | — | — | — | — | Existing policy |
| FR-070-10 | The RSS feed (`Actions\RSS\Generate`) applies FR-070-01 per photo. A downgraded, non-video photo with a medium derivative carries the best one (medium2x, else medium) as its `<enclosure>` and description `<img>`, with that variant's file size as the enclosure length; otherwise the original, exactly as `SizeVariantsResouce` exposes it. | Granted viewer gets the original. | — | Ungranted guest gets medium2x/medium, never the original. | — | GHSA-m9h3-925m-vvpp |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Source |
|----|-------------|--------|-------------|--------|
| NFR-070-01 | No N+1: query count per response is independent of photo count. | Owner directive. | Query-count test per fixed surface. | FR-070-02 |
| NFR-070-02 | No new eager load. Ownership comes from `photos.owner_id`, already selected. | Owner directive. | Code review. | — |
| NFR-070-03 | Deny by default — an unresolved photo id downgrades. | A rights fix must not fail open. | Unit test. | FR-070-08 |
| NFR-070-04 | Licence headers, `===`, no `empty()`, snake_case, PSR-4. | Conventions. | `php-cs-fixer`, `make phpstan` 0 errors. | coding-conventions.md |

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-070-01 | Config ON, album grant OFF, non-owner ⇒ downgraded (the over-grant, on every fixed surface). |
| S-070-02 | Config OFF, album grant ON, non-owner ⇒ not downgraded. |
| S-070-03 | Owner ⇒ never downgraded, regardless of config or grant. |
| S-070-04 | Admin ⇒ never downgraded. |
| S-070-05 | Two photos with different answers in one response ⇒ independent results. |
| S-070-06 | A photo in several albums, one of which grants ⇒ not downgraded (fixes the album-scoped under-grant). |
| S-070-07 | Query count is constant as photo count grows, on each fixed surface. |
| S-070-08 | A photo id missing from the resolved map ⇒ downgraded. |
| S-070-09 | Unsorted photo (no album) owned by the viewer ⇒ not downgraded. |
| S-070-10 | Existing v2 tests that assert real `original.url` file paths keep passing. |
| S-070-11 | RSS feed, public album grant OFF, guest ⇒ enclosure and `<img>` use medium2x (medium when medium2x is absent); the original path never appears. Grant ON or owner ⇒ original. No medium derivative ⇒ original (same as the album API). |

## Behavioural Change Register

Each row is a deliberate, owner-visible change.

| Surface | Before | After | Direction |
|---------|--------|-------|-----------|
| v2 Search | config decides for all photos | per photo | **restricts** (closes over-grant) |
| v2 Album Photos, smart-album branch | config decides | per photo | **restricts** |
| Map root position data | config decides | per photo | **restricts** |
| Embed stream | config decides | per photo | **restricts** |
| v2 Timeline | config decides | per photo | **restricts** |
| RSS feed | original always emitted | per photo | **restricts** (GHSA-m9h3-925m-vvpp) |
| v2 Album Photos, album branches | the containing album's grant, once per request | per photo | **relaxes** for a photo that is also in another granting album (fixes an under-grant) |

The final row is the only one that widens access. It is included because leaving two mechanisms in place is what allowed the confusion, and because under-granting a photo the viewer is genuinely entitled to is also a defect — flagged here explicitly so it can be vetoed.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-070-01 | `App\Actions\Photo\StructOfArrays\ResolvesPhotoGrants` — **reused**, built for Feature 069. Gains a `downgradeMap()` helper folding in ownership. | core |
| DO-070-02 | `App\DTO\PhotoGrants` — reused unchanged. | core |

### API Routes / Services

No new routes. Five existing surfaces change their response for affected viewers only.

### Telemetry Events

None.

## Documentation Deliverables

- roadmap.md entry; knowledge-map note; this spec; open-questions Q-069-13 resolved.
- No ADR: this applies an existing policy rule more widely; it does not introduce an architectural decision.

## Spec DSL

```yaml
domain_objects:
  - id: DO-070-01
    name: ResolvesPhotoGrants
    new: false
    note: reused from Feature 069; gains downgradeMap()
routes: []
telemetry_events: []
```
