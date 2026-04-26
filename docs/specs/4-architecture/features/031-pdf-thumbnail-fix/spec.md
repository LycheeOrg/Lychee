# Feature 031 – PDF Thumbnail Generation Fix

| Field | Value |
|-------|-------|
| Status | Implemented |
| Last updated | 2026-04-26 |
| Owners | timconsidine |
| Linked plan | `docs/specs/4-architecture/features/031-pdf-thumbnail-fix/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/031-pdf-thumbnail-fix/tasks.md` |
| Roadmap entry | #031 |

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
| FR-031-01 | PDF thumbnails are generated correctly for all PDFs regardless of complexity or size. | Thumbnail appears in gallery after upload. | Upload a structurally complex PDF (e.g. one with cross-reference streams); verify thumb, small, and medium size variants are created. | No thumbnail produced; "Page drawing error" in Ghostscript output. |
| FR-031-02 | For locally stored PDFs, the real file path is passed directly to Imagick. | No temp file created; no additional I/O overhead. | Confirm `readImage($path . '[0]')` is called instead of `readImageFile($stream)`. | Falls through to stream path. |
| FR-031-03 | For remotely stored PDFs (e.g. S3), the file is copied to a named temp file before rendering, and the temp file is deleted after. | Thumbnail generated; no temp files left on disk. | Confirm temp file is created with `.pdf` extension and cleaned up in `finally` block. | Temp file left on disk; or rendering fails. |

## Non-Functional Requirements

| ID | Requirement |
|----|-------------|
| NFR-031-01 | No performance regression for locally stored PDFs — using a direct file path avoids any extra I/O. |
| NFR-031-02 | Temp files for remote PDFs are always cleaned up, even on rendering failure. |

## Root Cause

`ImagickHandler::load()` called `$this->im_image->readImageFile($stream)` for all file types including PDFs. For non-PDF images, Imagick can detect the format from the stream's magic bytes. For PDFs, Imagick delegates rendering to Ghostscript, which needs a named file with a `.pdf` extension to determine the format and to seek through the file structure. Anonymous streams provide neither.
