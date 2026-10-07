# Open Questions – Feature 075

Open questions for [Feature 075](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-075-01~~ | 075 – srcset/sizes | Medium | The virtualized v8 grids call `<Thumb>` with a pinned `type`, bypassing its DPR-based default. Fix them in this feature? | Resolved (Option B — out of scope; no running v8 instance to verify against; owner, 2026-10-01) | 2026-10-01 | 2026-10-01 |

## Question Details

### ~~Q-075-01~~ – Virtualized v8 grids

**Context:** With `STRUCT_OF_ARRAY_ENABLED` (v8 only), `AlbumThumbVirtual.vue` passes `type="small"` and `PhotoThumbVirtual.vue` / root smart-album covers pass `type="small2x"` to `<Thumb>`, so `v8/components/thumbs/Thumb.vue`'s `devicePixelRatio` selection never runs.

- **Option A:** drop the pinned `type` so `<Thumb>` picks `small`/`small2x` by DPR. Small diff, but unverified on a running v8 instance.
- **Option B (chosen):** leave as is and mention it in the PR. Keeps the change limited to what was verified.

**Resolution:** Option B, recorded in [spec.md](spec.md) Non-Goals.
