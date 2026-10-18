# Open Questions – Feature 100

Open questions for [Feature 100](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-100-01~~ | 100 – JPEG XL Support | High | Under `size_variant_format = original`, what are the small/medium variants of a JXL upload? | Resolved (Option A — `.jxl`, the uploader decides; FR-100-04) | 2026-10-18 | 2026-10-18 |
| ~~Q-100-02~~ | 100 – JPEG XL Support | High | What happens when JXL is used on a system that cannot process it? | Resolved (Option A — clear `MediaFileOperationException`, GD refuses `.jxl` targets; FR-100-06) | 2026-10-18 | 2026-10-18 |
| ~~Q-100-03~~ | 100 – JPEG XL Support | Medium | How are viewers whose browser cannot decode JXL handled? | Resolved (Option A — warning in the setting details only; G4, FR-100-10) | 2026-10-18 | 2026-10-18 |
| ~~Q-100-04~~ | 100 – JPEG XL Support | Medium | Which settings control JXL? | Resolved (Option A — `.jxl` uploads always accepted, `jxl` in `size_variant_format` is the only JXL setting; FR-100-01, FR-100-03) | 2026-10-18 | 2026-10-18 |
| ~~Q-100-05~~ | 100 – JPEG XL Support | High | Which generated variants does `size_variant_format = jxl` cover? | Resolved (Option A — every generated variant of every upload, like `webp`; FR-100-03) | 2026-10-18 | 2026-10-18 |
| ~~Q-100-06~~ | 100 – JPEG XL Support | Medium | Where is a JXL-capable ImageMagick provided (Docker, CI)? | Resolved (Option A — in the Docker image; FR-100-11, NFR-100-01, ADR-100-01) | 2026-10-18 | 2026-10-18 |
| ~~Q-100-07~~ | 100 – JPEG XL Support | Medium | How do admins learn that their system cannot process JXL? | Resolved (Option A — diagnostics line; FR-100-07) | 2026-10-18 | 2026-10-18 |

## Question Details

### ~~Q-100-01~~ · Variants of JXL originals under `size_variant_format = original` ✅ RESOLVED

**Status:** Resolved — Option A. Encoded in spec FR-100-04.  
**Feature:** F-100  
**Priority:** High

Under `original`, thumbs are JPEG and small/medium keep the format of the uploaded file (FR-073-04).

- **Option A (chosen) — `.jxl` small/medium.** The rule applies unchanged: whoever uploads a JXL file chooses JXL previews.
- **Option B — JPEG small/medium, like the thumbs.** Every browser can display them, but `original` would no longer keep the original format for this one type.
- **Option C — WebP small/medium.** Small and widely supported, but introduces a third format into the `original` rule.

---

### ~~Q-100-02~~ · System without JXL support (GD, or Imagick without libjxl) ✅ RESOLVED

**Status:** Resolved — Option A. Encoded in spec FR-100-06.  
**Feature:** F-100  
**Priority:** High

`\Imagick::queryFormats('JXL')` is empty when ImageMagick is built without libjxl. GD has no JXL, and its `save()` uses the source type for unknown extensions (FR-073-09), which would write non-JXL bytes into a `.jxl` file.

- **Option A (chosen) — fail clearly.** Loading a `.jxl` original or saving a `.jxl` target throws `MediaFileOperationException('JPEG XL requires Imagick with JXL support')`; `GdHandler::save()` throws for `.jxl` targets.
- **Option B — Option A plus rejecting `size_variant_format = jxl` on save when unsupported.** Needs capability-aware validation in the generic settings save path.
- **Option C — silent fallback to WebP / reject `.jxl` as unsupported.** Ignores the admin's choice without notice.

---

### ~~Q-100-03~~ · Viewers whose browser cannot decode JXL ✅ RESOLVED

**Status:** Resolved — Option A. Encoded in spec G4, FR-100-10.  
**Feature:** F-100  
**Priority:** Medium

Safari decodes JXL by default; Chrome and Firefox need a flag or Labs toggle.

- **Option A (chosen) — warning only.** The `size_variant_format` details text states the limitation; no runtime fallback, no frontend code.
- **Option B — `<picture>`/`srcset` with a second, WebP set of variants.** Universal display, but doubles generation and storage and needs model and frontend changes.
- **Option C — Option A plus a client-side banner** after probing JXL decoding. Frontend code in v7 and v8 plus a client config flag.

---

### ~~Q-100-04~~ · Which settings control JXL? ✅ RESOLVED

**Status:** Resolved — Option A. Encoded in spec FR-100-01, FR-100-03.  
**Feature:** F-100  
**Priority:** Medium

- **Option A (chosen) — one setting.** `.jxl` uploads are always accepted; `jxl` as a value of `size_variant_format` (next to `jpeg` and `webp`) is the only JXL setting.
- **Option B — an opt-in upload setting plus the `jxl` value.** Two settings and two warnings to maintain.
- **Option C — a single master setting** that both accepts uploads and makes all variants JXL. Cannot accept JXL uploads while keeping browser-safe variants.

---

### ~~Q-100-05~~ · Which variants does `size_variant_format = jxl` cover? ✅ RESOLVED

**Status:** Resolved — Option A. Encoded in spec FR-100-03.  
**Feature:** F-100  
**Priority:** High

- **Option A (chosen) — every generated variant of every upload** (`thumb`, `small`, `medium` and their 2x), exactly like `webp`.
- **Option B — only the variants of JXL uploads.** JPEG libraries could never get JXL variants.
- **Option C — every variant except thumbs.** Adds an exception the other formats do not have.

---

### ~~Q-100-06~~ · Where is a JXL-capable ImageMagick provided? ✅ RESOLVED

**Status:** Resolved — Option A. Encoded in spec FR-100-11, NFR-100-01, NFR-100-04 and [ADR-100-01](../../../6-decisions/ADR-100-01-docker-imagemagick-jxl.md).  
**Feature:** F-100  
**Priority:** Medium

Debian 13's ImageMagick 7.1.1-43 (base of `Dockerfile`) and the ImageMagick on GitHub's Ubuntu runners have no JXL coder.

- **Option A (chosen) — in the Docker image.** ImageMagick built against libjxl, smoke-tested in the Docker workflow; JXL encode tests skip elsewhere.
- **Option B — nowhere (code only).** JXL fails on the official image.
- **Option C — in a dedicated CI job only.** The encoder is verified, but Docker users have no JXL.

---

### ~~Q-100-07~~ · How do admins learn that their system cannot process JXL? ✅ RESOLVED

**Status:** Resolved — Option A. Encoded in spec FR-100-07.  
**Feature:** F-100  
**Priority:** Medium

- **Option A (chosen) — diagnostics line** (`ImagickJxlCheck`): info when JXL is unsupported, error when `size_variant_format = jxl` is also selected.
- **Option B — validation error when selecting `jxl` on an unsupported system.** Does not cover `.jxl` uploads, and needs capability-aware settings validation.
- **Option C — nothing until an upload fails.**
