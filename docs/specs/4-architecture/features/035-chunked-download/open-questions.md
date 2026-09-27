# Open Questions – Feature 035

Open questions for [Feature 035](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|

## Question Details

### ~~Q-035-01: Behaviour of GET /Zip (no chunk param) when chunked mode is ON~~ ✅ RESOLVED

**Feature:** 035 – Chunked Archive Download
**Priority:** Medium
**Status:** Resolved
**Opened:** 2026-04-12

**Context:** When `download_archive_chunked` is enabled, a client that calls `GET /Zip` without a `chunk` parameter may be a legacy client or an incorrect integration. We need a defined contract for this case.

**Resolution:** **Option A** — Treat missing `chunk` as a regular single-archive download, regardless of the chunked-mode setting. This is backward-compatible: legacy frontends and direct URL downloads work without modification.

**Spec Impact:** Encoded in FR-035-05 and FR-035-07.

**Resolved:** 2026-04-12
