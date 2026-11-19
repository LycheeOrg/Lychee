# Open Questions – Feature 020

Open questions for [Feature 020](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|

## Question Details

### ~~Q-020-01: RAW Conversion Failure Behavior~~ ✅ RESOLVED

**Decision:** Option C — Fall back to existing `raw_formats` behavior (store unprocessed, no conversion)
**Rationale:** Graceful degradation preserves the uploaded file. If Imagick cannot convert the RAW file, it is stored as-is using the existing accepted-raw path (the raw file becomes the ORIGINAL with no thumbnails). Additionally, a data migration will move existing files that are currently stored as ORIGINAL but match raw format extensions to the new RAW size variant type.
**Updated in spec:** FR-020-03 (failure path), FR-020-16 (migration of existing raw-format files from ORIGINAL to RAW type)

---

### ~~Q-020-02: RAW Conversion Tooling & Imagick Delegate Requirements~~ ✅ RESOLVED

**Decision:** Option A — Require Imagick with libraw/dcraw delegates; document system requirements
**Rationale:** Single code path through Imagick. Existing `HeifToJpeg` already uses Imagick. System requirement: `apt install libraw-dev` (or equivalent) for camera RAW delegate support. If a specific format is unsupported by the installed Imagick delegates, the fallback from Q-020-01 applies (file stored as-is).
**Updated in spec:** NFR-020-04 (Imagick requirement), FR-020-09 (conversion tooling)

---

### ~~Q-020-03: Async Conversion for Large RAW Files~~ ✅ RESOLVED

**Decision:** Option A — Synchronous conversion (already async via job pipeline)
**Rationale:** Lychee already processes uploads through queued jobs, so conversion is inherently asynchronous from the user's perspective. No additional async infrastructure is needed. The conversion runs within the existing job pipeline.
**Updated in spec:** NFR-020-02 (clarified: conversion happens in existing job pipeline)

---

### ~~Q-020-04: Interaction with Existing `raw_formats` Config~~ ✅ RESOLVED

**Decision:** Option A — Keep both systems separate, with refinement
**Rationale:** The `raw_formats` config continues to define accepted extra formats. However, files matching `raw_formats` are now stored as **RAW size variants** (not ORIGINAL) — unless they are PDF, which remains stored as ORIGINAL (since PDF can be rendered/displayed). The new convertible-RAW pipeline (camera RAW + HEIC/HEIF) is a separate hardcoded list that triggers conversion. If an extension is in both lists, the new RAW pipeline takes precedence.
**Updated in spec:** FR-020-03, FR-020-04, FR-020-09, FR-020-16 (unprocessed raw_formats files stored as RAW type, PDF exception)
