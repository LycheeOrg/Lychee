# Feature 113 – Fix S3 Asset CORS

| Field | Value |
|-------|-------|
| Status | Testing |
| Last updated | 2026-11-10 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #113 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
On v8 with an S3 bucket (reported on Cloudflare R2), images loaded through `GET /api/v3/Asset/…` stay on the placeholder. The front end fetches `/Asset` with axios as a blob (the MAC is a request header, ADR-0008), the server answers `302` to a presigned S3 URL (FR-056-07), and the XHR follows it cross-origin with Lychee's own headers (`X-Requested-With`, `X-XSRF-TOKEN`, `Content-Type`, `X-Mac`, cache headers). The browser preflights the bucket, which has no CORS rule, and blocks the response. Album covers, Flow, map, search, face photos and every grid tile that falls back to `/Asset` are affected; grid tiles on FR-102-05 direct URLs are not. This feature changes `/Asset` and `/Faces/{face_id}/photo/{variant}` to hand the S3 URL to the client, which renders it with a plain `<img src>` (Q-113-01 Option A, ADR-113-01).

## Goals
- S3-backed images served through `/Asset` and `/Faces/{face_id}/photo/{variant}` load on any bucket with no CORS rule.
- No Lychee request header (CSRF token, MAC) is ever sent to the bucket host.
- S3 bytes still never pass through Lychee (Feature 056 non-goal kept).
- `/Asset` hands out the same S3 URL as FR-102-05 (bucket URL on a public bucket, presigned otherwise), so ADR-102-01's "exactly what `/Asset` would redirect to" holds.

## Non-Goals
- Proxying S3 bytes through Lychee (Q-113-01 Option B, rejected).
- Documenting a bucket CORS rule as the fix (Q-113-01 Option C, rejected).
- Local-disk responses: bytes, `Cache-Control`, `ETag` and `304` (FR-102-04) are unchanged.
- Authorization, MAC and fallback rules of Feature 056/093/102: unchanged.
- A retry on load error for map markers and face quick naming: links live `temporary_image_link_life_in_seconds` (default 24 h), far longer than those views stay open.
- v2 endpoints and v7.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-113-01 | For a size variant on an S3 disk (`AwsS3V3Adapter`), `GET /api/v3/Asset/{album_id}/{photo_id}/{size_variant}` and `GET /api/v3/Faces/{face_id}/photo/{variant}` answer `200` with `application/json` body `AssetUrlResource { url }` instead of a `302`. `url` is `UrlGenerator::getAwsUrl()` of the watermark-aware path: the bucket URL when `filesystems.disks.s3.visibility` is `public`, otherwise S3's presigned URL valid `temporary_image_link_life_in_seconds`. The response carries `Cache-Control: no-store, private`. This replaces FR-056-07's redirect. | Authorized caller, S3 variant → 200 JSON with the URL; no `Location` header. | Unchanged (Feature 056/093/102 request validation). | 401/403/404/422 unchanged, raised before any URL is generated. | None. | Q-113-01 A, ADR-113-01 |
| FR-113-02 | `ThumbAssetService.acquire()` and `FaceQuickNamingService.getPhoto()` resolve a response whose body type is `application/json` to its `url`, and any other body to an object URL as today. Only `blob:` URLs are ever passed to `URL.revokeObjectURL()`. | S3 image: the promise resolves to the S3 URL and the caller renders it with `<img src>`. Local image: object URL, unchanged. | — | A JSON body without a string `url` rejects the promise (the caller shows its placeholder, as for any failed request). | None. | Q-113-01 A |
| FR-113-03 | `Thumb` that renders a non-`blob:` URL obtained from `ThumbAssetService` and receives an image load error drops that cache entry (`ThumbAssetService.invalidate(albumId, photoId, type)`) and asks `/Asset` once more. A second load error in the same load shows the placeholder. A `blob:` URL that fails to load is not retried. The URL `/Asset` gave is matched before FR-102-06's direct URL, since on a public bucket both are the same string, and the tile shows the placeholder while it asks again, so a URL handed back unchanged loads again instead of sticking as a broken image. FR-102-06's direct-URL fallback is otherwise unchanged. | Expired presigned URL → one new `/Asset` call, fresh URL rendered. | — | Second failure → placeholder, no further request. | None. | Q-113-01 A (adjusted, see Appendix) |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-113-01 | No XHR is made to the bucket host: S3 images are loaded by the browser's image loader, so the bucket needs no CORS rule and receives no Lychee header (CSRF token, MAC). | Works out of the box on every S3 provider; no token leak to a third-party host. | Code review: no axios call follows a redirect to S3; `/Asset` never answers 3xx. Browser check on an S3 instance (operator). | FR-113-01, FR-113-02 | Q-113-01 A |
| NFR-113-02 | S3 bytes never pass through Lychee. | Bandwidth, egress cost, PHP worker time. | Feature test: S3 branch never reads the object (mocked disk exposes only `getAdapter`/`temporaryUrl`/`url`). | FR-113-01 | Feature 056 non-goal |

## UI / Interaction Mock-ups
No layout change: tiles, covers, map markers and face photos render as before; on S3 they now show the image instead of the placeholder.

```
Before (S3, no bucket CORS)          After
+--------+ +--------+                +--------+ +--------+
|  [?]   | |  [?]   |   cover tiles  | photo  | | photo  |
+--------+ +--------+                +--------+ +--------+
 Album A    Album B                   Album A    Album B
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-113-01 | Authorized `/Asset` request, variant on a private S3 bucket → 200 JSON `{ "url": <presigned URL> }`, `Cache-Control` contains `no-store`, no `Location`. |
| S-113-02 | Same on a public bucket → 200 JSON `{ "url": <bucket URL> }`, no presigned URL generated. |
| S-113-03 | Authorized `/Faces/{face_id}/photo/{variant}` request, variant on S3 → 200 JSON `{ "url": … }`. |
| S-113-04 | Caller denied for an S3 variant → 403 as before, no S3 URL generated. |
| S-113-05 | Local-disk variant → bytes, cache headers and `304` unchanged (existing Feature 056/102 tests). |
| S-113-06 | `ThumbAssetService.acquire()` with a JSON body → resolves to its `url`, no object URL created; `release()`/`clear()` never revoke it. |
| S-113-07 | `ThumbAssetService.acquire()` with a JSON body lacking a string `url` → rejects, entry dropped. |
| S-113-08 | `FaceQuickNamingService.getPhoto()` with a JSON body → resolves to its `url`. |
| S-113-09 | `Thumb` rendering an S3 URL from `/Asset` gets a load error → invalidates the entry and acquires once more; a second error → placeholder. Public bucket, direct URL of a missing file: direct error → `/Asset` (same URL) → error → one retry → error → placeholder, two `/Asset` calls in all. |
| S-113-10 | `Thumb` rendering a `blob:` URL gets a load error → no retry. |

## Test Strategy
- **REST API (v3):** `PhotoAssetV3Test` — S-056-15 rewritten as S-113-01; new S-113-02, S-113-04. `FacePhotoAssetTest` — new S-113-03. Existing local-disk tests stay green (S-113-05). Written first, seen red against the `302`.
- **Frontend (v8):** `thumb-asset-service.test.ts` (S-113-06, S-113-07), new `face-quick-naming-service.test.ts` (S-113-08), `Thumb.test.ts` (S-113-09, S-113-10). Written first, seen red.
- **Docs/Contracts:** new `App\Http\Resources\V3\AssetUrlResource` (`#[TypeScript()]`), `php artisan typescript:transform` rerun.

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-113-01 | `AssetUrlResource { url: string }` — S3 location of a served size variant. | `App\Http\Resources\V3`, `lychee.d.ts` |

### API Routes / Services
| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-113-01 | REST GET `/api/v3/Asset/{album_id}/{photo_id}/{size_variant}` | S3 variant: `200` `AssetUrlResource` (was `302`). Local: unchanged. | Amends API-056-01. |
| API-113-02 | REST GET `/api/v3/Faces/{face_id}/photo/{variant}` | Same S3 change, shared `ServesSizeVariantAssetTrait`. | ADR-093-01 route. |
| API-113-03 | TS `ThumbAssetService.invalidate(albumId, photoId, type)` | Drops one cache entry; holders keep their URL until release. | Used by `Thumb` (FR-113-03). |

### Telemetry Events
None.

### Fixtures & Sample Data
None.

### UI States
| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-113-01 | Retried S3 tile | S3 URL from `/Asset` fails to load → one new `/Asset` call → fresh URL, or placeholder on a second failure. |

## Telemetry & Observability
None added.

## Documentation Deliverables
- Feature 056 spec: FR-056-07, S-056-15 and API-056-01 point to FR-113-01.
- `docs/specs/3-reference/api-design.md`: `/Asset` response codes (`200` JSON for S3, no `302`).
- `docs/specs/4-architecture/knowledge-map.md`: `PhotoAssetController` entry.
- ADR-113-01.
- Roadmap row 113.

## Fixtures & Sample Data
None.

## Spec DSL

```
domain_objects:
  - id: DO-113-01
    name: AssetUrlResource
    fields:
      - name: url
        type: string
routes:
  - id: API-113-01
    method: GET
    path: /api/v3/Asset/{album_id}/{photo_id}/{size_variant}
  - id: API-113-02
    method: GET
    path: /api/v3/Faces/{face_id}/photo/{variant}
ui_states:
  - id: UI-113-01
    description: S3 tile retried once after a load error
```

## Appendix
- Q-113-01 Option A said a cached S3 URL would expire no later than `temporary_image_link_life_in_seconds`. The client does not know that value, and `ThumbAssetService` keeps an idle entry 1 h while the default link life is 24 h. FR-113-03 (retry once on load error) covers an expired link without sending the life to the client.
- Why the preflight: on a cross-origin redirect the browser keeps every author header except `Authorization`, so the non-safelisted headers above force a CORS preflight to the bucket host.
