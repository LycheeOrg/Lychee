# Feature Plan 038 – PDF Thumbnail Generation Fix

_Linked specification:_ `docs/specs/4-architecture/features/038-pdf-thumbnail-fix/spec.md`
_Status:_ Implemented
_Last updated:_ 2026-05-04

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections have been updated.

## Architecture Overview

This fix is **backend-only**. No frontend, API, or database changes are required.

The change is isolated to `app/Image/Handlers/ImagickHandler::load()`. For PDF files, instead of calling `readImageFile($stream)`, the handler now resolves a real filesystem path and calls `readImage($path . '[0]')`.

```
ImagickHandler::load()
    ├── Non-PDF files  →  readImageFile($stream)         (unchanged)
    └── PDF files
            ├── NativeLocalFile          →  getPath()              →  readImage($path[0])
            ├── FlysystemFile (local)    →  toLocalFile()->getPath() →  readImage($path[0])
            └── FlysystemFile (remote)  →  copy to named temp file  →  readImage($tmp[0])
                                                                        cleanup temp in finally
```

The `[0]` suffix instructs Imagick to render only the first page of the PDF, avoiding the overhead of processing all pages when only a thumbnail is needed.

## Implementation Increments

### I1 – PDF path resolution in ImagickHandler

- Add `getLocalPath(MediaFile $file): ?string` private method that returns a real filesystem path for local files, or `null` for remote files.
- In `load()`, detect PDF files and branch to path-based loading instead of stream-based loading.
- For remote PDFs, copy to a named `.pdf` temp file, render, then clean up in a `finally` block.
- Add `use function Safe\fopen` and `use function Safe\stream_copy_to_stream` imports.
- Add `use App\Image\Files\FlysystemFile` and `use App\Image\Files\NativeLocalFile` imports.
- **Refs:** FR-038-01, FR-038-02, FR-038-03, NFR-038-01, NFR-038-02

---

*Last updated: 2026-05-04*
