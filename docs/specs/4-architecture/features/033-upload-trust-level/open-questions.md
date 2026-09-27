# Open Questions – Feature 033

Open questions for [Feature 033](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|

## Question Details

### ~~Q-033-01: Monitor Trust Level Behaviour~~ ✅ RESOLVED

**Feature:** 033 – Upload Trust Level  
**Priority:** High  
**Status:** Resolved  
**Opened:** 2026-04-09

**Resolution:** **Option A** — Photos from `monitor`-level users are immediately validated (public), but flagged for periodic admin review. A separate "monitoring queue" shows recently uploaded photos from `monitor` users for the admin to spot-check. No photos are hidden; this is a soft-audit mechanism.

**Spec Impact:** Updated FR-033-03 to clarify that `monitor` behaves as `trusted` (uploads immediately validated) in this iteration. The monitoring queue is deferred to a follow-up. Updated Non-Goals and Appendix Trust Level Decision Matrix.

**Resolved:** 2026-04-09

---

### ~~Q-033-02: Retroactive Trust Level Changes~~ ✅ RESOLVED

**Feature:** 033 – Upload Trust Level  
**Priority:** Medium  
**Status:** Resolved  
**Opened:** 2026-04-09

**Resolution:** **Option A** — No retroactive changes. Only future uploads are affected by the new trust level. Existing photos retain their `is_validated` status. This is the simplest and safest approach.

**Spec Impact:** Confirmed as a non-goal in spec.md. No additional follow-up tasks needed.

**Resolved:** 2026-04-09

---

### ~~Q-033-03: Admin Photo Uploads and Trust Level~~ ✅ RESOLVED

**Feature:** 033 – Upload Trust Level  
**Priority:** Medium  
**Status:** Resolved  
**Opened:** 2026-04-09

**Resolution:** **Option A** — Admin uploads are always immediately validated (`is_validated = true`) regardless of the admin's `upload_trust_level` setting. Admins are inherently trusted — they can approve their own photos anyway. The `SetUploadValidated` pipe checks `may_administrate` first and short-circuits to `true`.

**Spec Impact:** Updated FR-033-03 to explicitly state that admin uploads bypass trust level checks. Updated Appendix Trust Level Decision Matrix. Updated task T-033-07 to include the admin short-circuit logic.

**Resolved:** 2026-04-09
