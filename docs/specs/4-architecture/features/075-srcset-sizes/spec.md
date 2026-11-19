# Feature 075 – Responsive Image Selection (srcset/sizes)

| Field | Value |
|-------|-------|
| Status | Implemented |
| Last updated | 2026-10-01 |
| Owners | NikitaTH |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #075 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
Several image tags let the browser download a larger size variant than the screen needs. On a standard-density (DPR 1) display, album covers and embedded-gallery tiles always load the `2x` variants, and the photo view loads `medium2x` whenever the photo is height-constrained. Frontend only (v7, v8, embed); no backend, API or config change.

Observed on a production instance (DPR 1, 1130 px viewport):
- album cover: `srcset="…/small2x/x.webp"`, `currentSrc` = `small2x`;
- embed tiles of 316×211 px: `small2x` (1080×720); tiles up to 533×356 px: `medium` (1620×1080).

## Goals
- G1: on a DPR 1 display, no `2x` variant is downloaded where the 1x variant covers the rendered size.
- G2: HiDPI displays keep getting the `2x` variants.

## Non-Goals
- The virtualized (`STRUCT_OF_ARRAY_ENABLED`, v8-only) grids: `<Thumb>` is called with a hard-coded `type` (`small` for album covers, `small2x` for photo tiles), bypassing its own DPR-based default in `v8/components/thumbs/Thumb.vue`. Not changed because the change could not be verified on a running v8 instance (Q-075-01). Mentioned in the PR.
- The header image and other single-variant `<img>` tags.
- Fallback for a size-variant row whose file is missing: browsers do not retry another `srcset` candidate or `src` after a 404 (verified in Chromium). This is unchanged from before, where the always-chosen `2x` file had the same single point of failure. Stale rows are cleaned with `lychee:ghostbuster`.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-075-01 | Album cover `srcset` (v7 + v8 `AlbumThumbImage.vue`) carries a `2x` descriptor. | `srcset="<thumb2x> 2x"`; `src` (small/thumb) stays the 1x candidate. | No `thumb2x` → empty `srcset`, `src` only. | n/a | none | Descriptor-less candidate = `1x`, which replaces `src` (HTML srcset parsing). |
| FR-075-02 | Photo view (`PhotoState.sizesMedium`, bound in v7 + v8 `PhotoBox.vue`) passes `sizes="min(100vw, <w/h> * 100vh)"` next to the existing `w`-descriptor `srcset`. | Height-constrained photos select `medium` when it covers the rendered width. | Empty when `srcSetMedium` is empty (no `medium2x`). | Browsers not supporting math functions in `sizes` fall back to `100vw`, i.e. today's behaviour. | none | Without `sizes`, the browser assumes `100vw`. |
| FR-075-03 | Embed grid, filmstrip and lightbox images get `srcset` with `w` descriptors over the uncropped variants (`small`, `small2x`, `medium`, `medium2x`) and a `sizes` matching the rendered box (`resources/js/embed/utils/srcset.ts`). | Tiles: `cover` fit → `max(boxW, boxH × ratio)` px; filmstrip main: `contain` → `min(…)` px; lightbox: viewport formula as FR-075-02. | No uncropped variant → empty `srcset`/`sizes`, existing `src` choice (`getBestSizeVariant`, may be a square thumb) is used. | n/a | none | Embed always preferred `2x`/`medium` regardless of DPR. |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-075-01 | `sizes` estimates may only err towards a larger variant. | Never trade sharpness for bytes. | Viewport-based formulas ignore surrounding chrome (sidebars, padding), so the estimate is ≥ the rendered width. | — | Owner directive |
| NFR-075-02 | Embed bundle stays dependency-free. | FR-049-21 | New helper imports only `@/embed/types`. | — | Feature 049 |
