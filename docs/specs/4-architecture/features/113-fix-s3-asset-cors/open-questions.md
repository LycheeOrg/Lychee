# Open Questions – Feature 113

Open questions for [Feature 113](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-113-01~~ | 113 | High | How v8 loads S3-backed images served through `/Asset` and `/Faces/{id}/photo/{variant}` without needing CORS on the bucket | Resolved (A, owner 2026-11-10, spec FR-113-01–03, ADR-113-01) | 2026-11-10 | 2026-11-10 |

## Question Details

### ~~Q-113-01~~ — Loading S3-backed `/Asset` images without bucket CORS

**Context.** A user running v8 with a Cloudflare R2 bucket reports that thumbnails do not load. The browser blocks the request: `Access to XMLHttpRequest at 'https://…r2.cloudflarestorage.com/…/small2x/…jpg?X-Amz-…' (redirected from 'https://<host>/api/v3/Asset/{album_id}/{photo_id}/small2x') … has been blocked by CORS policy: Response to preflight request doesn't pass access control check`.

Mechanism:
- `ThumbAssetService` (`resources/js/services/thumb-asset-service.ts`) and `face-quick-naming-service.ts` fetch `/Asset` and `/Faces/{id}/photo/{variant}` with axios as a `blob`, because the MAC (`X-Mac`) is a request header (ADR-0008) and cannot ride on a plain `<img src>`.
- For an S3 disk, `ServesSizeVariantAssetTrait::tryServe()` answers `302` to `$disk->temporaryUrl()` (FR-056-07).
- The XHR follows the redirect cross-origin. It carries non-safelisted headers (`X-Requested-With`, `X-XSRF-TOKEN`, `Content-Type`, `X-Mac`, plus `Cache-Control`/`Pragma`/`Expires` from axios-cache-interceptor), so the browser sends a CORS preflight to the bucket host. A bucket with no CORS policy fails it, and the tile stays on the placeholder.
- Grid tiles of album views and the timeline are not affected when FR-102-05 hands out direct S3 URLs (plain `<img src>`, no CORS). Album covers, Flow, map, search, face photos, and every tile that falls back to `/Asset` (temporary links on, load error) are affected.
- Side effect today: when CORS *is* configured, the browser forwards the CSRF token and the MAC to the bucket host on every redirect (only `Authorization` is stripped on cross-origin redirects).

**Option A (recommended): S3 variants answer with the presigned URL, the client puts it in `<img src>`.** For an S3-backed variant, `/Asset` and `/Faces/{id}/photo/{variant}` answer `200` with a JSON body `{ "url": "<presigned URL>" }` instead of a `302`, after the same authorization (signature, policy, membership). `ThumbAssetService` and the face service read the JSON body (content type `application/json`) and resolve to that URL instead of an object URL. `Thumb` renders it directly. The client cache entry for an S3 URL expires no later than `temporary_image_link_life_in_seconds`, and a load error on it re-asks `/Asset` once.
- Pros: works on every bucket with no CORS setup. No Lychee headers (CSRF token, MAC) reach the bucket host. S3 bytes still never pass through Lychee. Same URL FR-102-05 already hands out for grid tiles (ADR-102-01).
- Cons: changes API-056-01 for S3 (`302` → `200` JSON). `ThumbAssetService` gets a second result kind (plain URL vs object URL). A presigned URL is new on every call, so the browser HTTP cache only helps while the client keeps the same URL.

**Option B: proxy S3 bytes through Lychee.** `tryServe()` streams the S3 object (`readStream`) with the same `Cache-Control`/`ETag`/`304` handling as local files (FR-102-04).
- Pros: no client change, no CORS, one serving path and one caching behaviour for every disk.
- Cons: every S3 image costs a PHP worker and travels S3 → Lychee → browser (double bandwidth, egress fees, latency). Reverses Feature 056's explicit non-goal ("Proxying S3-backed size variants through Lychee").

**Option C: keep the `302`, document a bucket CORS rule.** Operators configure CORS on their bucket (origin = Lychee URL, `GET`/`HEAD`, the request headers listed above). Optionally stop sending `Content-Type` on GETs to shrink the header list.
- Pros: no API or code change, quickest.
- Cons: every S3 operator must configure CORS, and installs that do not break silently. The CSRF token and the MAC keep reaching the bucket host. Header list is coupled to front-end internals (any new axios header breaks the rule).

Owner selected A on 2026-11-10. Normative outcome: spec FR-113-01–03, NFR-113-01/02, ADR-113-01. Adjustment: the expiry cap is replaced by the retry on load error alone (FR-113-03, spec Appendix), since the client does not know the link life and the default life (24 h) outlasts the cache's idle TTL (1 h).
