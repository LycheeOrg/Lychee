# ADR-113-01: S3 assets answer with their URL instead of a redirect

- **Status:** Accepted
- **Date:** 2026-11-10
- **Related features/specs:** Feature 113 (docs/specs/4-architecture/features/113-fix-s3-asset-cors/spec.md), Feature 056 (docs/specs/4-architecture/features/056-api-v3-asset-retrieval/spec.md), Feature 102 (docs/specs/4-architecture/features/102-asset-endpoint-speedup/spec.md)
- **Related open questions:** Q-113-01

## Context

v8 loads `/api/v3/Asset/…` and `/api/v3/Faces/{face_id}/photo/{variant}` with axios as a blob, because the temporary-link MAC is a request header (ADR-0008) and cannot ride on a plain `<img src>`. For an S3 size variant both routes answered `302` to S3's presigned URL (FR-056-07). The XHR followed the redirect cross-origin carrying `X-Requested-With`, `X-XSRF-TOKEN`, `Content-Type`, `X-Mac` and cache headers, so the browser sent a CORS preflight to the bucket host. Buckets without a CORS rule (reported on Cloudflare R2) failed it and every affected image stayed on the placeholder. With a CORS rule, the CSRF token and the MAC were sent to the bucket host on every request.

## Decision

1. For an S3 size variant, both routes answer `200` with `application/json` body `{ "url": … }` (`AssetUrlResource`), after the unchanged authorization, with `Cache-Control: no-store, private`. They never answer `3xx`.
2. `url` is `UrlGenerator::getAwsUrl()` of the watermark-aware path: the bucket URL on a public bucket, S3's presigned URL otherwise. It is the URL `GET /Albums/{album_id}/Photos/urls` already hands out (FR-102-05, ADR-102-01).
3. The client reads a JSON body as that URL and renders it with `<img src>`; any other body stays an object URL. A v8 `Thumb` whose S3 URL fails to load asks `/Asset` once more.

## Consequences

### Positive
- Works on every S3 provider with no bucket configuration.
- No Lychee header reaches the bucket host.
- S3 bytes still never pass through Lychee.
- One S3 URL rule for `/Asset` and the URL tier.

### Negative
- `/Asset` has two success bodies (bytes or JSON); clients branch on the content type.
- A presigned URL is new on every call, so the browser HTTP cache only helps while the client keeps the same URL (unchanged from the redirect).

## Alternatives Considered

- **Proxy S3 bytes through Lychee:** no client change, but every S3 image costs a PHP worker and is downloaded twice (S3 → Lychee → browser); reverses Feature 056's non-goal.
- **Keep the `302`, document a bucket CORS rule:** no code change, but every S3 install breaks until configured, the rule is coupled to front-end headers, and the CSRF token and MAC keep reaching the bucket host.

## Security / Privacy Impact

- Authorization is unchanged and runs before any URL is generated.
- The issued URL is S3's own presigned URL, or the bucket URL of a bucket the operator made public: no new exposure compared with FR-102-05.
- The CSRF token and the MAC are no longer sent to the bucket host.

## Operational Impact

- No new configuration. Operators who added a bucket CORS rule as a workaround can keep or remove it.

## Links

- Related spec sections: `docs/specs/4-architecture/features/113-fix-s3-asset-cors/spec.md#functional-requirements`
- Related ADRs: ADR-0008, ADR-093-01, ADR-102-01
