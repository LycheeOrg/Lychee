# Open Questions – Feature 109

Open questions for [Feature 109](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-109-01~~ | 109 | High | Server side of the switch: (A) remove `features.struct-of-array` and `STRUCT_OF_ARRAY_ENABLED`, v3 listing endpoints always on, (B) gate v3 on `nuxt_ui` instead, (C) keep the flag with default `true` | Resolved (owner variant, 2026-11-07: remove `struct-of-array` and `nuxt_ui`; v3 listings, the v8 bundle and v8 features all on `features.v8`: FR-109-03, FR-109-04, ADR-109-01) | 2026-11-07 | 2026-11-07 |
| ~~Q-109-02~~ | 109 | High | v7 and the shared stores: (A) v7 stays on v2, shared stores choose the path by bundle, (B) v7 follows v8 to v3 and the stores lose their v2 branches, (C) v7 keeps an opt-in flag | Resolved (A, owner, 2026-11-07: FR-109-05, NFR-109-03) | 2026-11-07 | 2026-11-07 |
| ~~Q-109-03~~ | 109 | High | v8 listings with no v3 counterpart: (A) out of scope, recorded as follow-ups, (B) add v3 support for the album tag/person filter only, (C) build v3 for every remaining listing in this feature | Resolved (A, owner, 2026-11-07: FR-109-06, Non-Goals, roadmap backlog) | 2026-11-07 | 2026-11-07 |
| ~~Q-109-04~~ | 109 | Medium | Files made obsolete: (A) delete `tests/Feature_v2/StructOfArrayInitTest.php` now and the v8 files orphaned by the branch removal once their list is approved, (B) delete the test only, keep orphaned v8 files, (C) delete nothing | Resolved (A, owner "remove", 2026-11-07: the 6 listed paths deleted, T-109-09) | 2026-11-07 | 2026-11-07 |
| ~~Q-109-05~~ | 109 | High | v3 lacks three v2 behaviours that SoA-only now imposes on every v8 install (Flow header "fit", Flow top-grid "+N"/short layout, map zoom to content): (A) port them to v3 in Feature 109 before deleting v2 files, (B) accept v3 as is and record the change, (C) port the Flow card gaps only, map to backlog | Resolved (A, owner, 2026-11-07; item 2 withdrawn, "show more" header added: FR-109-07, FR-109-08) | 2026-11-07 | 2026-11-07 |
| ~~Q-109-06~~ | 109 | Medium | Flow header resolution: v2 loads `medium`, v3 `<Thumb type="small">` (360 px tall for a 384 px header, also on 2x screens): (A) request `small2x` for the header, (B) add `medium` to the v3 asset types, (C) keep `small` | Resolved (A "for now", owner, 2026-11-07: FR-109-07; `medium` kept as a backlog item) | 2026-11-07 | 2026-11-07 |
| ~~Q-109-07~~ | 109 | High | v2 `TopImages` (5-photo grid, "+N", short row) shows when the image header is off; v3 shows no image then: (A) restore it on the v3 card as in v2, (B) keep Feature 068's text-only card | Resolved (A, owner, 2026-11-07: FR-109-09, S-109-14 … 16; N-109-06 removed) | 2026-11-07 | 2026-11-07 |

## Question Details

### ~~Q-109-01~~ · Server side of the switch

**Status:** Resolved (owner variant, 2026-11-07): "NUXT_UI_ENABLED requires v8 right? So we are also going to remove it. So gate the v3 code paths to V8 feature directly." `features.struct-of-array` and `features.nuxt_ui` are both removed; `features.v8` (`V8_ENABLED`) serves the v8 bundle, enables v8 features and gates the v3 listing requests (FR-109-03, FR-109-04, ADR-109-01). The code never tied `nuxt_ui` to `v8`; the "v8 features on the v7 bundle" combination (`V8_ENABLED=true`, `NUXT_UI_ENABLED=false`, which `phpunit.xml` runs) ends with this decision. Gating tests are rewritten against `features.v8`, not removed.
**Preferred option:** A – remove the flag, v3 listing endpoints always on

**Question**
`config/features.php` `struct-of-array` (`STRUCT_OF_ARRAY_ENABLED`, default `false`) gates 19 v3 FormRequests (`authorize()`), `ModulesRightsResource::canQuickNameFaces()` and `InitConfig::$is_struct_of_array_enabled`. Once v8 always uses v3, what happens to that switch?

**Option A (recommended) – remove the flag**
- Idea: delete the `struct-of-array` entry and `STRUCT_OF_ARRAY_ENABLED`; the 19 requests keep their other checks (rights, AI Vision settings, SE) and drop the flag check; `InitConfig::$is_struct_of_array_enabled` goes (TypeScript types regenerated).
- Spec impact: FR-109-03; the flag-off cases in the 25 test files that set `features.struct-of-array` to `false` test a behaviour that no longer exists and are removed with it (owner approval of those removals is part of this answer).
- Pros: one listing API; nothing for an admin to set; no installation can run v8 against disabled endpoints.
- Cons: v3 listing endpoints are reachable on instances that serve v7 (read-only, same policies as v2); no switch left to fall back to v2 while troubleshooting.

**Option B – gate v3 on `nuxt_ui`**
- Idea: the 19 requests check `Features::active('nuxt_ui')` instead of `struct-of-array`.
- Spec impact: FR-109-03 becomes "v3 listings exist only while v8 is served".
- Pros: instances on v7 expose exactly what they expose today.
- Cons: ties the API to the choice of front end; still a flag in every request; API clients lose v3 on v7 instances.

**Option C – keep the flag, default `true`**
- Idea: change the default only; v8 code stops reading the flag.
- Spec impact: FR-109-03 becomes a default change.
- Pros: smallest server diff.
- Cons: setting it to `false` breaks v8 (its v3 calls get 403); a switch that must never be used.

### ~~Q-109-02~~ · v7 and the shared stores

**Status:** Resolved (A, owner, 2026-11-07): v7 stays on v2; `app-v8.ts` puts the shared stores on the v3 path at boot (FR-109-05, NFR-109-03).
**Preferred option:** A – v7 stays on v2, the stores choose by bundle

**Question**
v7 and v8 share `resources/js/stores` (`AlbumsState`, `AlbumState`, `TimelineState`, `SearchState`, `MapState`, `FlowState`), which choose v2 or v3 from `LycheeState.is_struct_of_array_enabled`. v7 views never render the v3-only state (`sharedAlbumsV3`, `AlbumNavPanel`, bucket boundaries). What should v7 do once v8 is v3-only?

**Option A (recommended) – v7 on v2, choice by bundle**
- Idea: `app-v8.ts` sets the store to the v3 path at boot; v7 never does. The shared stores keep their v2 branches, used by v7 only; every v8 component loses its v2 branch.
- Spec impact: FR-109-04; v7 behaviour is today's flag-off behaviour on every instance (its Maintenance page no longer shows "Recompute buckets").
- Pros: v7 stays as it is; no server field for a front-end choice; v2 store code can go when v7 is retired.
- Cons: the shared stores keep both paths for now.

**Option B – v7 follows v8 to v3**
- Idea: delete the v2 branches from the shared stores; v7 runs on v3.
- Spec impact: FR-109-04 plus v7 view work (shared albums, bucket layout).
- Pros: one store path.
- Cons: v7 views must learn the v3-only state or lose shared albums; work on a front end being retired.

**Option C – v7 keeps an opt-in**
- Idea: the flag survives for v7 only.
- Spec impact: contradicts Q-109-01 A.
- Pros: nothing changes for anyone running v7 with the flag on.
- Cons: the flag survives; v7 with the flag on was never complete (no shared albums).

### ~~Q-109-03~~ · v8 listings with no v3 counterpart

**Status:** Resolved (A, owner, 2026-11-07): out of scope; the listings stay on v2 and are recorded as roadmap backlog follow-ups (FR-109-06, Non-Goals).
**Preferred option:** A – out of scope, recorded as follow-ups

**Question**
A sweep of `resources/js/v8`, `stores` and `composables` for v2 listing calls (2026-11-07) found v8 listings that never branch on the flag, because no v3 endpoint exists for them: album photos with a tag or person filter (`isPhotoSoaActive` is `false`, v2 `Album::photos` with `tag_ids`/`person_id`); the tag page (`GET /api/v2/Tag`); the People page and the merge-person picker (`GET /api/v2/People`, paginated; v3 `/People` feeds only face quick naming); person detail photos (`GET /api/v2/Person/{id}/photos`); Spotlight search and the landing-config featured-item picker (`GET /api/v2/Search`). Smaller lookups also stay on v2: folder drop (`AlbumService.getAlbums`) and preview data (`getTargetListAlbums`). How much of this belongs in Feature 109?

**Option A (recommended) – out of scope, follow-ups**
- Idea: Feature 109 covers every listing that already has a v3 path; the listings above stay on v2 and are listed in the spec as follow-up features.
- Spec impact: Non-Goals list the remaining v2 listings; roadmap backlog gets the follow-ups.
- Pros: small, reviewable step; no new endpoints; each follow-up gets its own spec.
- Cons: v8 still reads v2 for those views; the v2 photo paging code in `AlbumState` stays for the filtered case.

**Option B – add the album tag/person filter**
- Idea: as A, plus `tag_ids`/`tag_logic`/`person_id` on the v3 album photo endpoints (buckets, ratios, details, urls) so the album view is v3 in every state.
- Spec impact: new FRs and API changes on four v3 endpoints; bucket and ratio queries filtered; cache keys include the filter.
- Pros: the album view loses its last v2 path; `AlbumState` loses v2 photo paging for v8.
- Cons: real backend work in the same feature; filter changes cache behaviour.

**Option C – everything in this feature**
- Idea: build v3 for every listing above.
- Spec impact: several new v3 endpoint families (tag page, people, person photos, search for Spotlight).
- Pros: v8 has no v2 listing left at the end.
- Cons: several features' worth of work behind one spec; long-running branch.

### ~~Q-109-04~~ · Files made obsolete

**Status:** Resolved (A, owner, 2026-11-07, after Q-109-05/07 ported the missing behaviours): deleted `tests/Feature_v2/StructOfArrayInitTest.php`, `resources/js/v8/components/gallery/flowModule/{AlbumCard,HeaderImage,TopImages,CarouselImages}.vue`, `resources/js/v8/components/gallery/timelineModule/TimelineDates.vue`.
**Preferred option:** A – delete the obsolete test now, orphaned v8 files after their list is approved

**Question**
FR-109-05 removes `InitConfig::$is_struct_of_array_enabled`, so `tests/Feature_v2/StructOfArrayInitTest.php` (`testDisabled`, `testEnabled`: the field follows the flag) tests a field that no longer exists. Removing the v2 branches from v8 components (FR-109-02) may also leave v8 components or utilities that nothing imports any more (for example the v2 timeline date list); their list is only known once the branches are gone. AGENTS.md requires approval of exact paths before any deletion.

**Evidence (2026-11-07, owner asked "Are you 100% sure that they are no longer used?")**
- Static reachability: no import of the five v8 files outside the set itself; PascalCase and kebab-case tag searches across `resources/`, `e2e/`, `tests/`, `app/`, `routes/`, both Vite configs and `components.d.ts` find none; no `import.meta.glob`/`defineAsyncComponent` reaches `flowModule/` or `timelineModule/`; no component auto-registration plugin in `vite.config.ts`. v7 imports its own copies under `@/v7/components/gallery/{flowModule,timelineModule}/`.
- v3 counterparts: `AlbumCard.vue` → `AlbumCardV3.vue` (Feature 068, #4754), which inlines `HeaderImage`/`TopImages`/`CarouselImages` through `<Thumb>`; `TimelineDates.vue` → `TimelineDatesV3.vue` (Features 066/094/095), a scrubber rail redesign, not a port. Not 1:1: see Q-109-05.

**Option A (recommended) – delete the test now, orphans after review**
- Idea: approve deleting `tests/Feature_v2/StructOfArrayInitTest.php`; after T-109-08 the orphaned v8 files are listed in tasks.md and deleted once the owner approves that list.
- Spec impact: FR-109-02 includes the deletion of orphaned v8 files.
- Pros: no dead test, no dead component left behind; each deleted path is approved.
- Cons: one more approval round after T-109-08.

**Option B – delete the test, keep orphaned v8 files**
- Idea: only the obsolete test goes; unused v8 files stay for a later clean-up.
- Spec impact: FR-109-02 limited to branches.
- Pros: one approval only.
- Cons: dead v8 files stay; lint does not flag unused SFCs.

**Option C – delete nothing**
- Idea: rewrite the test to assert the field is absent; keep every file.
- Spec impact: none.
- Pros: no deletion.
- Cons: a test that checks for the absence of a field; dead files stay.

### ~~Q-109-05~~ · v3 gaps that SoA-only imposes on every v8 install

**Status:** Resolved (A, owner, 2026-11-07). Corrections found while specifying the port: item 2 is withdrawn: v2 `AlbumCard.vue` renders `TopImages` only when its `header` is undefined, i.e. with the image header disabled (or no photo at all), the case Feature 068 deliberately changed; the v3 top-images branch (`is_image_header_enabled && photos.length > 0` after `headerPhotoId !== undefined`) cannot render, since any loaded photo becomes the header photo. Added under the same answer: v2 `HeaderImage.vue` shows the header photo at its natural height while "show more" is open; v3 keeps the fixed height. Spec: FR-109-07 (Flow header), FR-109-08 (map), S-109-09 … 13.
**Preferred option:** A – port the gaps to v3 before deleting the v2 files

**Question**
Until now a v8 install without `STRUCT_OF_ARRAY_ENABLED` rendered the v2 paths. Comparing them with their v3 counterparts (2026-11-07) found three v2 behaviours the v3 code never implemented and no feature recorded as a decision:
1. Flow header with `flow_image_header_cover = fit`: v2 `HeaderImage.vue` shows the whole photo (`object-contain`) over a blurred copy; `AlbumCardV3.vue` always crops (`object-cover`), so the setting has no effect in v3.
2. Flow top-images grid: v2 `TopImages.vue` adds a "+N" tile beyond 5 photos and uses one full-height row when the album has fewer than 4 photos; `AlbumCardV3.vue` has neither.
3. Map: v2 `Map.vue` fitted the view to the album's (or gallery's) photos and tracks (`fitBounds`); v3 always opens on the whole world (`setView([0, 0], 2)`).
Recorded as deliberate elsewhere, not part of this question: image header disabled → no images in v3 (Feature 068 tasks, v2 quirk), timeline date list → scrubber rail (066/094/095), no search pagination (069). Settings note: `timeline_quick_access_date_format_*` only feed the v2 date list, so they become v7-only.

**Option A (recommended) – port the three gaps to v3 in Feature 109**
- Idea: `AlbumCardV3.vue` gains the fit rendering (whole photo over a blurred `<Thumb>`), the "+N" tile and the short layout; `Map.vue` fits the first view to the content (album points from `GET /api/v3/Map/album` for an album map, bucket centroids and single photos of the first world fetch for the gallery map). Front end only. Then delete the v2 files (Q-109-04).
- Spec impact: FR-109-07 … 09, scenarios and Vitest cases; `flow_image_header_cover` stays a live setting.
- Pros: no visible regression for installs that ran v8 without the flag; the v2 files go only once nothing is lost.
- Cons: three more increments in this feature; the map fit adds one request for album maps.

**Option B – accept v3 as is**
- Idea: record the three differences in the spec as a behaviour change; delete the v2 files now.
- Spec impact: spec Overview/NFR note; `flow_image_header_cover` joins the settings audit as v7-only.
- Pros: smallest change.
- Cons: visible regressions for some v8 installs (cropped headers in fit mode, map opens on the world).

**Option C – port the Flow card gaps only**
- Idea: items 1 and 2 in this feature; the map fit goes to the roadmap backlog.
- Spec impact: FR-109-07, 08; backlog row for the map.
- Pros: the Flow card work is small and self-contained.
- Cons: the map regression ships with this feature.

### ~~Q-109-06~~ · Flow header resolution

**Status:** Resolved (A, owner, 2026-11-07: "A for now, we may reconsider later"): the header asks for `small2x` (FR-109-07); serving `medium` through the v3 asset API is a roadmap backlog item.
**Preferred option:** A – request `small2x` for the header

**Question**
v2 `HeaderImage.vue` loads the `medium` variant (up to 1080 px tall). `AlbumCardV3.vue` passes `type="small"` to `<Thumb>`, which overrides `<Thumb>`'s own density choice, so the header is 360 px tall for a 384 px (`flow_image_header_height` 24 rem) box on every screen, about 2× upscaled on high-density screens. The v3 asset API serves `small2x` at most (`SizeVariantAssetType`).

**Option A (recommended) – `small2x` for the header**
- Idea: the header `<Thumb>` asks for `small2x` (720 px tall); carousel and grid keep their sizes.
- Spec impact: FR-109-07 names the variant.
- Pros: sharp at the default header height on 1× and 2× screens; front end only; the asset endpoint already falls back when a variant is missing.
- Cons: still below `medium` for headers configured taller than about 45 rem or in the expanded "show more" view on 2× screens.

**Option B – add `medium` to the v3 asset types**
- Idea: `SizeVariantAssetType::MEDIUM` (+ `medium2x`), served by `GET /api/v3/Asset/...`; the header asks for it.
- Spec impact: API change, new tests on the asset endpoint, NFR on payload size.
- Pros: same resolution as v2.
- Cons: backend work outside this feature's scope; larger downloads per Flow card.

**Option C – keep `small`**
- Idea: no change.
- Spec impact: none.
- Pros: nothing to do.
- Cons: visibly soft headers on high-density screens.

### ~~Q-109-07~~ · Top-images grid when the image header is off

**Status:** Resolved (A, owner, 2026-11-07): the v3 card shows the v2 top-images grid whenever it has no header photo (FR-109-09).
**Preferred option:** A – restore it on the v3 card as in v2

**Question**
Owner (2026-11-07): "what matters to me is that the functions of Top Image, Header, Carousel and Album Card are present on the V3 card." v2 `AlbumCard.vue` renders `TopImages` whenever it has no header photo, that is with `flow_image_header_enabled` off (setting text: "The top of the card will highlight the cover of the album"; off means the grid instead of one cover). Feature 068 read this as a v2 bug and made the v3 card text-only in that case; its top-images markup cannot render and has neither the "+N" tile nor the short row. Q-109-05 withdrew this item on that basis, which does not meet the owner's criterion.

**Option A (recommended) – restore it as in v2**
- Idea: with the image header off, `AlbumCardV3.vue` shows up to 5 preview photos (`small`), a "+N" tile when the album holds more (N = `max(numPhotos, preview count) − 5`, so the 12-photo preview cap does not shrink it), one full-height row when fewer than 4 photos, NSFW blur, click opens the photo; the loading skeleton shows in that case too. The carousel stays tied to the image header, as in v2.
- Spec impact: FR-109-09 replaces N-109-06; Vitest cases (grid, "+N", short row); browser check.
- Pros: every v2 card function exists in v3; `flow_image_header_enabled` keeps its v2 meaning.
- Cons: reverses a Feature 068 behaviour (its tasks notes record it).

**Option B – keep the text-only card**
- Idea: no change; N-109-06 stays.
- Spec impact: none.
- Pros: no work.
- Cons: v8 installs that turned the image header off lose their photo grid.

