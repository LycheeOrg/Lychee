# Feature 038 – PDF Thumbnail Generation Fix

| Field | Value |
|-------|-------|
| Status | Implemented |
| Last updated | 2026-05-04 |
| Owners | timconsidine |
| Linked plan | `docs/specs/4-architecture/features/038-pdf-thumbnail-fix/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/038-pdf-thumbnail-fix/tasks.md` |
| Roadmap entry | #038 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below, and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications.

## Overview

PDF thumbnails fail to generate for complex PDFs when Imagick's `readImageFile()` is called with an anonymous PHP stream. Ghostscript — the delegate Imagick uses to render PDFs — requires a real seekable file path with a `.pdf` extension in order to resolve cross-reference tables and compressed object streams that are common in modern PDFs. When fed an anonymous stream, Ghostscript silently fails with "Page drawing error occurred. Could not draw this page at all." and no thumbnail is produced.

Simple PDFs that can be rendered linearly happen to work via the stream path; complex PDFs requiring random file access do not.

## Goals

- Ensure PDF thumbnails are generated correctly for all PDFs, including large and structurally complex ones.
- Pass a real filesystem path to Imagick for PDF files so Ghostscript can seek freely.
- Support both locally stored files and remotely stored files (e.g. S3).

## Non-Goals

- Changes to PDF upload handling.
- Changes to non-PDF image formats.
- Changes to the Ghostscript or ImageMagick configuration.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path |
|----|-------------|--------------|-----------------|--------------|
| FR-038-01 | PDF thumbnails are generated correctly for all PDFs regardless of complexity or size. | Thumbnail appears in gallery after upload. | Upload a structurally complex PDF (e.g. one with cross-reference streams); verify thumb, small, and medium size variants are created. | No thumbnail produced; "Page drawing error" in Ghostscript output. |
| FR-038-02 | For locally stored PDFs, the real file path is passed directly to Imagick. | No temp file created; no additional I/O overhead. | Confirm `readImage($path . '[0]')` is called instead of `readImageFile($stream)`. | Falls through to stream path. |
| FR-038-03 | For remotely stored PDFs (e.g. S3), the file is copied to a named temp file before rendering, and the temp file is deleted after. | Thumbnail generated; no temp files left on disk. | Confirm temp file is created with `.pdf` extension and cleaned up in `finally` block. | Temp file left on disk; or rendering fails. |

## Non-Functional Requirements

| ID | Requirement |
|----|-------------|
| NFR-038-01 | No performance regression for locally stored PDFs — using a direct file path avoids any extra I/O. |
| NFR-038-02 | Temp files for remote PDFs are always cleaned up, even on rendering failure. |

## Root Cause

`ImagickHandler::load()` called `$this->im_image->readImageFile($stream)` for all file types including PDFs. For non-PDF images, Imagick can detect the format from the stream's magic bytes. For PDFs, Imagick delegates rendering to Ghostscript, which needs a named file with a `.pdf` extension to determine the format and to seek through the file structure. Anonymous streams provide neither.

### Why the 50 MB limit in InMemoryBuffer masked the issue

`InMemoryBuffer` uses `php://temp/maxmemory:MAX_SIZE` (currently 50 MB). Below this threshold the stream is backed by RAM; above it PHP swaps it to an anonymous disk-backed temp file. The Flysystem stream returned by `readStream()` for local files is non-seekable, so it is always copied into an `InMemoryBuffer` before being passed to `readImageFile()`.

- **PDFs under 50 MB** — stream stays in RAM. Imagick can sniff the `%PDF` magic bytes from a memory-backed stream and Ghostscript can render it.
- **PDFs over 50 MB** — stream swaps to an anonymous disk file with no `.pdf` extension. Ghostscript receives no format hint and cannot seek through the file structure, producing silent "Page drawing error" failures and no thumbnail.

This made the bug appear size-dependent and led to the initial investigation of the `MAX_SIZE` constant. Increasing `MAX_SIZE` to 100 MB shifted the failure threshold but did not fix the root cause. The correct fix is to bypass stream-based loading entirely for PDFs and pass a real file path to Imagick.

---

*Last updated: 2026-05-04*
