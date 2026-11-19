# ADR-089-02: Dismissing an NSFW Finding Deletes It

- **Status:** Accepted
- **Date:** 2026-10-13
- **Related features/specs:** Feature 089 (docs/specs/4-architecture/features/089-moderation-history-nsfw-review/spec.md), Feature 045 (docs/specs/4-architecture/features/045-nsfw-moderation/spec.md)
- **Related open questions:** Q-089-03, Q-089-04

## Context

Feature 045 stores every actionable classifier result in `nsfw_detections`. After the callback, the rows feed the lightbox overlay and the album marking applied when an admin approves a held photo (`is_sensitive` rows). The new findings page (Feature 089) lets admins dismiss wrong findings, one at a time or per photo.

## Decision

1. Dismissing deletes the `nsfw_detections` rows (`DELETE /NsfwDetection/findings` by detection id, `DELETE /NsfwDetection/findings/photos` by photo id). There is no dismissed flag and no restore.
2. The photo's `nsfw_status` and `is_validated` are unchanged, and no moderation history event is written. A held photo is approved from the Moderation page.
3. The overlay and approval-time album marking read the remaining rows, so a dismissed sensitive finding no longer marks albums on approval. Albums already marked stay marked.

## Consequences

### Positive
- No schema change on `nsfw_detections` and no filter to add to its readers.
- Approving and dismissing stay separate, explicit actions.

### Negative
- No undo; whole-photo and bulk dismissals ask for confirmation.
- `nsfw_detections` is no longer a complete audit trail of classifier results.
- A forced re-scan can recreate a dismissed finding.

## Alternatives Considered

- **Soft `is_dismissed` flag, photo unchanged (Q-089-04 A):** undo and audit, at the cost of a migration and filters in every reader.
- **Soft flag, and dismissing the last finding approves the photo (Q-089-04 B):** one click for false positives, but the outcome depends on the uploader's trust level and a restore would not hold the photo again.

## Security / Privacy Impact

- Admin only and `support:se`, like the other NSFW admin endpoints.
- Deleting a finding can make a photo visible later through a plain approval without its album being marked NSFW; this is the admin's decision.

## Operational Impact

- None beyond the new endpoints.

## Links

- Related spec sections: `docs/specs/4-architecture/features/089-moderation-history-nsfw-review/spec.md#nsfw-findings-review` (FR-089-09 … FR-089-14)
- Related ADRs: ADR-089-01
