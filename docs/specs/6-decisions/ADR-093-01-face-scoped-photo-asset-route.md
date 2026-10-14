# ADR-093-01: Face-scoped photo asset route

- **Status:** Accepted
- **Date:** 2026-10-14
- **Related features/specs:** Feature 093 (docs/specs/4-architecture/features/093-face-quick-naming/spec.md), Feature 030 (docs/specs/4-architecture/features/030-ai-vision-service/spec.md)
- **Related open questions:** Q-093-05, Q-093-09

## Context

The face quick naming view (Feature 093) shows the whole photo with the current face highlighted. The v3 image route `GET /api/v3/Asset/{album_id}/{photo_id}/{variant}` authorises access through an album: the caller must be able to access the album, and the photo must belong to it. For smart albums, membership is checked through `$album->photos()`, which applies the searchability filter.

The queue holds faces on the caller's own photos, or on every photo for admins (Q-093-05). For some of these faces, no album is guaranteed to pass the asset check:
- An owner's NSFW photo that is in no album fails through `unsorted` when `hide_nsfw_in_smart_albums` is on.
- An admin cannot reach another user's unsorted photo through `unsorted` when `enable_smart_album_per_owner` is on.

Choosing an album per face in the queue query would also make the single `toBase()` SELECT (ADR-0009) noticeably heavier.

## Decision

Add `GET /api/v3/Faces/{face_id}/photo/{variant}`. It serves a size variant of the photo that carries the face.

- **Authorisation:**
  - AI Vision and face recognition are enabled.
  - `features.struct-of-array` is on.
  - The caller is an admin or owns the photo. This is the same rule as the queue (FR-093-02) and as `PhotoPolicy::CAN_DISMISS_FACE`.
  - The face is not required to be unassigned or undismissed: the session may revisit faces after naming or discarding them.
- **Signature:** when temporary image links are required for the caller (`temporary_image_link_enabled` plus the logged-in/admin settings), the same MAC check as `GetPhotoAssetRequest` applies.
- **Serving:** identical to `PhotoAssetController`: the variant fallback chain, temporary redirect for S3 and other remote disks, the watermarker path, and the same middleware exemptions (read-only session, JSON errors). The serving code is extracted into a shared helper used by both controllers, with no behaviour change for `/Asset`.
- **Queue resource:** `FaceQueueResource` drops `album_ids`. The client builds image URLs from the face id.

## Consequences

### Positive
- Every queued face can be displayed, whatever its album membership, NSFW status or smart-album settings.
- The queue query stays a single SELECT with no album resolution.
- The access rule is simple and identical to the rule for dismissing a face.

### Negative
- A second binary image route to maintain next to `/Asset`.
- The serving logic must be extracted from `PhotoAssetController`, which touches a hot path; this is covered by regression tests.

## Alternatives Considered

- **A – Face-scoped route (chosen):** always works and needs no album guessing; costs one more route.
- **B – Choose an album per face:** needs no new route, but makes the query complex, and faces without a usable album silently disappear from the queue.
- **C – Show only the face crop:** has no access issue, but loses the photo context the feature is built around.

## Security / Privacy Impact

- The route never widens access. Only the photo owner or an admin passes, which is narrower than album sharing.
- Face ids are random 24-character ids, and an unauthorised caller gets 403 or 404 with no hint about the photo.
- Temporary-link signing is honoured exactly as on `/Asset`.

## Operational Impact

- One extra route in `routes/api_v3.php`. Its behaviour is the same as `/Asset` (watermarks, S3 redirects), so there is no new storage or caching behaviour to operate.
- Performance per request matches `/Asset` (a single face and size-variant lookup).

## Links

- Related spec sections: `docs/specs/4-architecture/features/093-face-quick-naming/spec.md` (FR-093-01, FR-093-02, API-093-03, API-093-05)
- Related ADRs: ADR-0009, ADR-069-01
