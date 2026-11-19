# Open Questions – Feature 078

Open questions for [Feature 078](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-078-01~~ | 078 – Lightbox pan & zoom | High | With `photo_click_action = zoom`, how does a touch user rotate the overlay? | Resolved (owner option — click target decides: overlay rotates, picture zooms; owner, 2026-10-03; spec FR-078-05/06) | 2026-10-03 | 2026-10-03 |
| ~~Q-078-02~~ | 078 – Lightbox pan & zoom | High | Zoom ceiling, and what happens when the original is not accessible. | Resolved (owner option — click zooms to 2×, pinch/wheel zoom further, past native resolution; source is the original when accessible, else `medium2x`; owner, 2026-10-03; spec FR-078-03/04/05/12) | 2026-10-03 | 2026-10-03 |
| ~~Q-078-03~~ | 078 – Lightbox pan & zoom | Medium | While zoomed, does dragging past the image edge navigate? | Resolved (Option A — no, rubber band; owner, 2026-10-03; spec FR-078-09) | 2026-10-03 | 2026-10-03 |
| ~~Q-078-04~~ | 078 – Lightbox pan & zoom | Medium | Is the minimap visible the whole time the photo is zoomed? | Resolved (owner option — fades to a configurable idle opacity after a configurable delay, per device class, plus per-class enable switch; owner, 2026-10-03; spec FR-078-16/19/20) | 2026-10-03 | 2026-10-03 |
| ~~Q-078-05~~ | 078 – Lightbox pan & zoom | High | In `zoom` mode, what do taps outside the picture and the overlay do, and how is the overlay rotated back from `none`? | Resolved (Option A — everything that is not the picture rotates, plus a hit zone while `none`; owner, 2026-10-03; spec FR-078-05) | 2026-10-03 | 2026-10-03 |
| ~~Q-078-06~~ | 078 – Lightbox pan & zoom | Medium | Maximum zoom level. | Resolved (Option B — `max(4, 4 × native)`; owner, 2026-10-03; spec FR-078-03) | 2026-10-03 | 2026-10-03 |
| ~~Q-078-07~~ | 078 – Lightbox pan & zoom | Medium | `0` is already bound to "rating 0" in `Album.vue`: which key resets zoom? | Resolved (owner option — `z` toggles, `escape` and `0` reset while zoomed, ratings hidden and rating keys inactive while zoomed; owner, 2026-10-03; spec FR-078-11/21) | 2026-10-03 | 2026-10-03 |
| ~~Q-078-08~~ | 078 – Lightbox pan & zoom | Medium | While zoomed, what does a plain wheel / trackpad two-finger scroll do? | Resolved (Option A — zoom; owner, 2026-10-03; spec FR-078-10) | 2026-10-03 | 2026-10-03 |

## Question Details

### ~~Q-078-01~~ – Overlay rotation when click zooms

**Context:** Today a click/tap on the photo is the only pointer way to rotate the overlay (date → EXIF → description → none). The `o` key does the same on desktop. With `photo_click_action = zoom` the click toggles zoom, so touch users would lose overlay rotation without another control.

- **Option A:** overlay-rotation icon button in `PhotoHeader.vue`, shown only in `zoom` mode; keep the `o` key.
- **Option B:** `o` key only.
- **Option C:** double-tap rotates the overlay in zoom mode.
- **Owner option (chosen):** the click target decides — a click on the overlay rotates it, a click on the picture zooms.

**Resolution:** owner option, recorded in [spec.md](spec.md) FR-078-05 and FR-078-06. Follow-up: Q-078-05.

### ~~Q-078-02~~ – Zoom ceiling and full-size availability

**Context:** `SizeVariantsResouce` sends `original` with `url = null` when the viewer has no full-photo access, but still sends its dimensions. `medium2x`/`medium` are always accessible.

- **Option A:** ceiling = 1:1 pixels of the largest accessible variant.
- **Option B:** zoom only with an accessible original.
- **Option C:** fixed 4× ceiling, upscaling when needed.
- **Owner option (chosen):** a 4K image on a 4K screen is still too small to inspect details, so zoom is not capped at native resolution. Click zooms to 2×; pinch/wheel zoom further. The image source respects access: original when its URL is exposed, otherwise `medium2x`.

**Resolution:** owner option, recorded in [spec.md](spec.md) FR-078-03, FR-078-04, FR-078-05 and FR-078-12. Follow-up: Q-078-06.

### ~~Q-078-03~~ – Navigation from a zoomed photo

- **Option A (chosen):** no navigation; the pan stops at the edge with a rubber band; zoom out to swipe.
- **Option B:** navigate after dragging 25 % of the viewport width past the edge.

**Resolution:** Option A, recorded in [spec.md](spec.md) FR-078-09.

### ~~Q-078-04~~ – Minimap visibility while zoomed

- **Option A:** fades out after 2 s idle.
- **Option B:** always visible while zoomed.
- **Owner option (chosen):** fades to an idle opacity (default 25 %, separate configs for desktop and touch) after a delay (default 2 s, one config); opaque again on hover (desktop) or on any pan/zoom (all devices) for the delay; a separate enable switch per device class.

**Resolution:** owner option, recorded in [spec.md](spec.md) FR-078-16, FR-078-19 and FR-078-20.

### ~~Q-078-05~~ – Taps outside the picture and the hidden overlay

**Context:** In `zoom` mode a click on the overlay rotates it and a click on the picture zooms (FR-078-05). Two cases are not covered. (1) Taps on the black area around a letterboxed picture at fit. (2) The rotation cycle includes `none` (`photoActions.ts`: none → desc → date → exif): with `none` there is no overlay to tap, so touch users could never bring it back. When zoomed, the picture usually covers the whole viewport, so case (1) mostly occurs at fit.

- **Option A (chosen):** everything that is not the picture rotates the overlay: the overlay text, the letterbox area, and, while the type is `none`, an invisible hit zone at the overlay's position (bottom-start corner, height of one title line, width 50 % of `#imageview`).
  - ✅ Old "tap rotates" behaviour kept wherever there is no picture.
  - ✅ `none` is always recoverable, even on a full-bleed zoomed photo.
  - ❌ The invisible zone swallows picture taps in that corner while the overlay is `none`.
- **Option B:** only the overlay area rotates (visible text, or the invisible hit zone while `none`); letterbox taps do nothing.
  - ✅ Strict "picture zooms, overlay rotates" rule.
  - ❌ Taps on the black area feel dead.
- **Option C:** only the visible overlay text rotates; leaving `none` needs the `o` key.
  - ✅ No invisible targets.
  - ❌ Touch users who cycle to `none` are stuck there until they reload.

**Resolution:** Option A, recorded in [spec.md](spec.md) FR-078-05 and FR-078-06.

### ~~Q-078-06~~ – Maximum zoom

**Context:** Zoom is no longer capped at native resolution (Q-078-02). Some maximum is still needed for clamping (FR-078-03). Native-resolution scale ranges from about 1× fit (4K image on a 4K screen, or `medium2x` only) to 8× fit or more (50 MP original on a phone).

- **Option A:** fixed maximum of 8× fit for every zoomable photo.
  - ✅ Predictable; one constant.
  - ✅ A 4K image on a 4K screen reaches 8 screen pixels per image pixel.
  - ❌ A 50 MP original on a phone cannot be zoomed much past its native resolution.
- **Option B (chosen):** maximum = 4× the source's native-resolution scale, never below 4× fit.
  - ✅ Follows the real detail available: big originals allow deeper zoom.
  - ❌ The range differs per photo and per viewer.
- **Option C:** admin config `photo_zoom_max` (`positive`, default 8).
  - ✅ Admins choose.
  - ❌ One more setting for a value few admins will change.

**Resolution:** Option B, recorded in [spec.md](spec.md) FR-078-03.

### ~~Q-078-07~~ – Zoom reset key

**Context:** FR-078-11 binds `0` to reset zoom, but `Album.vue` already binds `0`–`5` to set the photo's rating while the lightbox is open (`handleRatingClick`). `+`, `=` and `-` are free. Click (zoom mode) and double-tap (overlay mode) already reset zoom by pointer.

- **Option A (recommended):** `Escape` resets zoom when `s > 1` and is consumed (a capture-phase listener in `PhotoBox`); at fit, `Escape` keeps its current meaning (stop slideshow, close drawer, go back).
  - ✅ Common viewer convention; no new key to learn.
  - ✅ No conflict with ratings.
  - ❌ When zoomed, leaving the photo takes one extra `Escape`.
- **Option B:** no reset key; `-` zooms out ÷1.5 and snaps to fit once below 1.1×.
  - ✅ No change to `Escape`.
  - ❌ Several presses to get back from deep zoom.
- **Option C:** `z` toggles 2× ⇄ fit, mirroring the click; `+`/`-` step.
  - ✅ Keyboard equivalent of the click.
  - ❌ One more letter binding to keep free in the six gallery views.
- **Owner option (chosen):** combination: `z` toggles 2× ⇄ fit; while zoomed, `escape` and `0` reset to fit; while zoomed, the rating actions are hidden and the rating keys inactive, so `0` is free.

**Resolution:** owner option, recorded in [spec.md](spec.md) FR-078-11 and FR-078-21.

### ~~Q-078-08~~ – Plain wheel while zoomed

**Context:** FR-078-10 makes the plain wheel zoom while `s > 1`, matching the owner's "click to 2×, then zoom further with scroll". Browsers cannot reliably tell a mouse wheel from a trackpad two-finger scroll: on a trackpad, two-finger scroll would zoom instead of moving around. Trackpad pinch arrives as `wheel` + `ctrlKey` and zooms in every option.

- **Option A (chosen):** keep FR-078-10: plain wheel zooms while zoomed; trackpad users pan by click-dragging or with the minimap.
  - ✅ Matches the owner's direction; predictable.
  - ❌ Two-finger scroll on a trackpad zooms, which surprises Mac users.
- **Option B:** heuristic: wheel events with horizontal delta or small fractional pixel deltas are treated as trackpad and pan; others zoom.
  - ✅ Natural on both devices most of the time.
  - ❌ Misclassifies some smooth-scrolling mice and precision trackpads; hard to verify.
- **Option C:** plain wheel always pans while zoomed; zooming further uses ctrl+wheel, trackpad pinch, keyboard or touch pinch.
  - ✅ Natural for trackpads; same as PhotoSwipe.
  - ❌ Mouse users cannot zoom further with the wheel alone, against the owner's direction.

**Resolution:** Option A, recorded in [spec.md](spec.md) FR-078-10.
