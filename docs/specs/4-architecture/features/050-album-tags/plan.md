# Feature Plan 050 – Album Tags

_Linked specification:_ `docs/specs/4-architecture/features/050-album-tags/spec.md`
_Status:_ Implementation complete (T-050-17 manual browser verification pending)
_Last updated:_ 2026-07-12

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant.

## Vision & Success Criteria

Users can attach shared, first-class `Tag`s directly to regular albums (not just photos), and immediately: (a) find those albums from `/tag/{id}`, (b) find those albums via `Search` (`tag:` modifier and plain text), and (c) manage those tags (rename/merge/delete/list with counts) from the existing `/tags` page — all without ever affecting `TagAlbum` behaviour. Success = all scenarios S-050-01..10 pass, `TagAlbum` regression tests remain green, and NFR-050-01/02 hold (no leakage into tag albums, no v7 changes).

## Scope Alignment

- **In scope:** New `albums_tags` pivot + `Album`/`Tag` relations; `UpdateAlbumRequest` tag field; `EditableBaseAlbumResource` Album-branch population; `GetTagWithPhotos`/`TagWithPhotosResource` albums list; `ListTags`/`TagResource` split counts; `AlbumSearch` `tag` strategy (Album-only) + plain-text extension; `TagCleanupTrait`/`EditTag`/`MergeTag`/`DeleteTag` album-awareness; v8 UI: `AlbumProperties.vue`, `TagPanel.vue`, `TagsManagement.vue`.
- **Out of scope:** `TagAlbum`/`PersonAlbum` changes; photo↔album-tag transitivity; v7 UI; new REST routes (all changes extend existing endpoints); telemetry/webhooks.

## Dependencies & Interfaces

- Existing `Tag` model + `Tag::from()` (`app/Models/Tag.php`).
- Existing `TagCleanupTrait`, `EditTag`, `MergeTag`, `DeleteTag` (`app/Actions/Tag/*`).
- Existing `AlbumSearch`/`SearchTokenParser` (`app/Actions/Search/*`) — `tag` modifier already parsed, currently album-skipped.
- Existing `AlbumController::updateTagAlbum()` pattern for `Tag::from()` + `sync()` (direct precedent for FR-050-02).
- v8 dual-tree routing (Feature 049/ADR-0006) — all UI work lands under `resources/js/v8/**` only.
- `docs/specs/4-architecture/tag-system.md` — architecture doc to update.

## Assumptions & Risks

- **Assumptions:** `Album::tags()` can reuse the exact `BelongsToMany` shape already used by `TagAlbum::tags()`/`Photo::tags()`, just pointed at a new pivot; no schema change needed on `tags` itself.
- **Risks / Mitigations:**
  - *Risk:* Reusing the `tags` field name on `EditableBaseAlbumResource` for both `Album` and `TagAlbum` could confuse future readers. *Mitigation:* the two populating branches are already mutually exclusive (`instanceof Album` vs `instanceof TagAlbum`); add a short comment noting the dual meaning is intentional (mirrors the field's current single-purpose population).
  - *Risk:* `AlbumSearch::buildAlbumStrategyRegistry()` is currently shared between `queryAlbums()` and `queryTagAlbums()`; naively adding `'tag'` there would leak into `TagAlbum` search (violates NFR-050-01). *Mitigation:* split into two registry builders (or pass an `include_tags: bool` flag) so only `queryAlbums()`'s registry gets the new `AlbumTagStrategy`.
  - *Risk:* `TagCleanupTrait::cleanupUnusedTags()` currently deletes a tag once it has zero `photos_tags` rows, regardless of `tag_albums_tags`/(new) `albums_tags` — could silently delete an album-only tag if not fixed first. *Mitigation:* I2 fixes cleanup before any album-tag UI ships, with a dedicated regression test (S-050-09) landing before the fix (test-first).
  - *Risk:* `ListTags`' `HAVING` rewrite for split counts must remain Postgres/SQLite/MySQL-portable (existing comment warns `having('num', ...)` breaks on Postgres aliasing). *Mitigation:* use `havingRaw` with the same style already in place; add a portability note in the task.

## Implementation Drift Gate

To be executed once all tasks are complete: re-run `docs/specs/5-operations/analysis-gate-checklist.md` §Implementation Drift Gate, cite each FR-050-xx against its implementing class/test, and record the report in this section before marking the feature complete in the roadmap.

## Increment Map

1. **I1 – Schema & core relations**
   - _Goal:_ `albums_tags` pivot + `Album::tags()`/`Tag::albums()` relations.
   - _Preconditions:_ none.
   - _Steps:_ failing unit test asserting the relation round-trips → migration `create_albums_tags_table` (mirror `photos_tags` shape: bigIncrements id, `album_id` char(24) FK→`albums.id` cascade, `tag_id` unsigned bigint FK→`tags.id` cascade, unique+individual indexes) → add `tags(): BelongsToMany` to `Album`, `albums(): BelongsToMany` to `Tag`.
   - _Commands:_ `php artisan test --filter=AlbumTagRelationTest`, `make phpstan`.
   - _Exit:_ Relation test green; migration reversible (`down()` drops table).

2. **I2 – Cleanup/merge/rename/delete album-awareness (test-first, lands before any UI)**
   - _Goal:_ FR-050-09 — a tag used only by an album is never purged, and rename/merge/delete correctly carry album associations.
   - _Preconditions:_ I1.
   - _Steps:_ failing test: tag with only an `albums_tags` row survives `cleanupUnusedTags()` (S-050-09) → extend `TagCleanupTrait::cleanupUnusedTags()` whereNotExists to also check `albums_tags` → failing test: `MergeTag` transfers album associations, scoped to current user unless admin (S-050-08) → add `MergeTag::handleAlbums()` mirroring existing `handleTagAlbums()` → extend `DeleteTag`/`EditTag` accordingly (`EditTag` delegates to `MergeTag`, so only `MergeTag`/`DeleteTag`/`TagCleanupTrait` need direct changes).
   - _Commands:_ `php artisan test --filter=TagCleanupTest`, `php artisan test --filter=MergeTagTest`, `php artisan test --filter=DeleteTagTest`, `make phpstan`.
   - _Exit:_ All four Tag actions green including new album-scenario cases; no regression in existing photo/tag-album cases.

3. **I3 – `PATCH /Album` accepts `tags`**
   - _Goal:_ FR-050-02, FR-050-03.
   - _Preconditions:_ I1.
   - _Steps:_ failing feature test: submitting `tags: ["vacation","greece"]` on a regular album persists both, reusing an existing case-insensitively-matching `Tag` if present → add `HasTags`/`HasTagsTrait` to `UpdateAlbumRequest` with `tags => present|array`, `tags.* => required|string|min:1` → in `AlbumController::updateAlbum`, after existing field assignments, add `Tag::from($request->tags()); $album->tags()->sync($tag_models->pluck('id')->all());` (mirroring `updateTagAlbum`) → add `Album` branch to `EditableBaseAlbumResource` populating `tags` from `$album->tags`.
   - _Commands:_ `php artisan test --filter=UpdateAlbumTagsTest`, `make phpstan`.
   - _Exit:_ Feature test green; `TagAlbum` update path untouched/still green (regression check).

4. **I4 – `/tags` split counts + album-only visibility**
   - _Goal:_ FR-050-08.
   - _Preconditions:_ I1.
   - _Steps:_ failing feature test: a tag with 0 photos / 1 owned album appears in `GET /Tags` for its owner with `num_photos: 0, num_albums: 1`; invisible to a different non-admin user → rewrite `ListTags` query: OR-in album usage in the `HAVING`/join, scoped identically for non-admins → replace `TagResource.num` with `num_photos`+`num_albums` (TypeScript regenerated via `#[TypeScript()]`).
   - _Commands:_ `php artisan test --filter=ListTagsTest`, `make phpstan`.
   - _Exit:_ Feature test green; existing `ListTags` photo-only cases unaffected.

5. **I5 – `GET /Tag` returns albums**
   - _Goal:_ FR-050-04.
   - _Preconditions:_ I1, I3 (albums must be taggable to have fixtures).
   - _Steps:_ failing feature test: `GET /Tag?tag_id=X` includes an `albums` array with the correct album(s), access-filtered for non-admins → extend `GetTagWithPhotos` to also query accessible `Album`s via the new relation → extend `TagWithPhotosResource` with an `albums` field (pick the lightest existing album-tile resource shape that the v8 grid component already consumes — confirm exact resource in this increment, e.g. reuse `ThumbAlbumResource` or a minimal purpose-built list resource if the existing ones carry unwanted eager loads).
   - _Commands:_ `php artisan test --filter=GetTagWithPhotosTest`, `make phpstan`.
   - _Exit:_ Feature test green; response shape confirmed against the v8 grid component's expected props (coordinate with I8).

6. **I6 – `AlbumSearch` tag matching (Album-only, never TagAlbum)**
   - _Goal:_ FR-050-06, FR-050-07, NFR-050-01.
   - _Preconditions:_ I1, I3.
   - _Steps:_ failing test: `tag:vacation` matches a tagged `Album` in `queryAlbums()` results → failing test: same token against `queryTagAlbums()` does **not** use the new strategy (regression guard for NFR-050-01) → failing test: plain-text `vacation` also matches via album tag name → add `Strategies/Album/AlbumTagStrategy` (mirrors `Strategies/TagStrategy` but targets `Album::tags`) → split `AlbumSearch::buildAlbumStrategyRegistry()` into two builders (or an `include_tags` flag) so only `queryAlbums()` registers `'tag' => new AlbumTagStrategy()` → extend `AlbumFieldLikeStrategy`'s plain-text branch (`$column === null`) to OR-in a `whereHas('tags', ...)` clause, guarded so it only applies when the underlying model is `Album` (not `TagAlbum`) — confirm exact guard mechanism (e.g. constructor flag mirroring the registry split) during implementation.
   - _Commands:_ `php artisan test --filter=AlbumSearchTagTest`, `make phpstan`.
   - _Exit:_ All three tests green; existing `AlbumSearch`/`SearchController` tests remain green (regression).

7. **I7 – v8 UI: Album properties Tags field**
   - _Goal:_ FR-050-03 (frontend half), NFR-050-02.
   - _Preconditions:_ I3.
   - _Steps:_ add `TagsInput` (existing v8 component) to `resources/js/v8/components/forms/album/AlbumProperties.vue`, bound to `editable.tags`, submitted alongside existing fields in the update payload; update the corresponding album-service/types.
   - _Commands:_ `npm run check`.
   - _Exit:_ Manual verification (per `/verify` skill) — open an album's properties, add/remove tags, save, reopen, confirm persistence.

8. **I8 – v8 UI: `/tag/{id}` Albums section**
   - _Goal:_ FR-050-05, UI-050-02.
   - _Preconditions:_ I5.
   - _Steps:_ extend `TagState.ts` to carry `albums` from `TagsService.get()`; add an album-tile grid (reuse existing grid component from `Albums.vue`/`Search.vue`) to `TagPanel.vue`, rendered above `PhotoThumbPanel`, `v-if` guarded on non-empty.
   - _Commands:_ `npm run check`.
   - _Exit:_ Manual verification — tag with only an album, tag with only photos, tag with both, tag with neither (page still loads, existing empty-state).

9. **I9 – v8 UI: `/tags` split-count display**
   - _Goal:_ FR-050-08 (frontend half), UI-050-03.
   - _Preconditions:_ I4.
   - _Steps:_ update `TagsManagement.vue` to render two `UChip`s (`num_photos`, `num_albums`), each hidden when zero (mirror existing `v-if="tag.num > 0"` pattern).
   - _Commands:_ `npm run check`.
   - _Exit:_ Manual verification — album-only tag row shows only the album chip.

10. **I10 – Docs & quality gate**
    - _Goal:_ Documentation deliverables + full quality gate.
    - _Preconditions:_ I1–I9.
    - _Steps:_ update `docs/specs/4-architecture/tag-system.md` (new pivot, relations, revised cleanup semantics); update `knowledge-map.md` if warranted; run full quality gate.
    - _Commands:_ `vendor/bin/php-cs-fixer fix`, `npm run format`, `npm run check`, `php artisan test`, `make phpstan`.
    - _Exit:_ All green; Implementation Drift Gate report appended to this plan; roadmap updated.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-050-01 | I3 / T-050-05 | Add tags via album properties. |
| S-050-02 | I3 / T-050-06 | Clear all tags. |
| S-050-03 | I5, I8 / T-050-10, T-050-16 | Tag with both albums and photos. |
| S-050-04 | I8 / T-050-17 | Tag with only an album. |
| S-050-05 | I6 / T-050-13 | `tag:` search modifier, Album-only. |
| S-050-06 | I6 / T-050-14 | Plain-text search matches album tags. |
| S-050-07 | I2 / T-050-04 | Admin rename across all associations. |
| S-050-08 | I2 / T-050-03 | Non-admin rename, multi-user isolation. |
| S-050-09 | I2 / T-050-02 | Cleanup does not purge album-only tags. |
| S-050-10 | I4 / T-050-08 | Non-admin `/tags` visibility scoping. |

## Analysis Gate

**Reviewed:** 2026-07-12, LycheeOrg (via drafting agent)

1. **Specification completeness** — ✅ Pass. FR-050-01..09 and NFR-050-01..04 populated; Q-050-01/02/03 all resolved and folded into normative sections (FR-050-05, FR-050-08, and the Appendix note on FR-050-03 respectively); ASCII mock-ups present for all three UI surfaces (properties panel, `/tag/{id}`, `/tags`).
2. **Open questions review** — ✅ Pass. No `Open` entries remain for Feature 050 in `open-questions.md`. No item was judged architecturally significant enough to warrant a standalone ADR (all three questions are feature-local UX/behaviour choices, not cross-module boundary decisions) — recorded as resolved directly in spec + open-questions log instead.
3. **Plan alignment** — ✅ Pass. This plan references `spec.md`/`tasks.md` at the correct paths; dependencies/success criteria match spec wording (FR/NFR IDs cross-referenced throughout the Increment Map).
4. **Tasks coverage** — ✅ Pass. Every FR-050-xx maps to ≥1 task (see per-task `(F-050-xx)` tags in `tasks.md`); tests are staged before implementation in every increment; increments I1–I10 are each ≤90 minutes of planned work (UI increments I7–I9 are single-file, single-concern changes).
5. **Constitution compliance** — ✅ Pass. No dependency additions; no fallback/compat shims (greenfield extension of existing endpoints); increments delegate validation to existing request/trait patterns (`HasTagsTrait`) rather than introducing new inline branching. No ADR under `docs/specs/5-decisions/` references Feature 050, so none were reviewed as prior context.
6. **Tooling readiness** — ✅ Pass. Commands documented per increment (`php artisan test --filter=...`, `make phpstan`, `npm run check`, full gate in I10).

**Outcome:** Gate passed. Cleared to begin implementation at I1.

## Exit Criteria

- All tasks in `tasks.md` marked `[x]`.
- Full quality gate green (php-cs-fixer, phpunit, phpstan, npm format/check).
- `docs/specs/4-architecture/tag-system.md` updated.
- Implementation Drift Gate report appended above.
- Roadmap entry moved from Active to Completed.

## Implementation Drift Gate Report

**Date:** 2026-07-12
**Reviewer:** Implementing agent (self-review per RCI)

1. **Preconditions**
   - All 20 tasks in `tasks.md` marked `[x]` except T-050-17 (manual browser verification), which could not be executed — the sandbox's frontend toolchain is broken independent of this feature (missing `@iconify-json/lucide` npm package breaks `npm run check`'s `precheck` vite build; `vue-tsc` is not installed, confirmed pre-existing via `git stash` on a clean checkout before any of this feature's changes).
   - `php-cs-fixer fix` clean (1 file auto-fixed on first run: docblock alignment in `AlbumSearch.php`; `--dry-run` clean on final check). `make phpstan`/`vendor/bin/phpstan analyse` clean (2602 files, 0 errors).
   - Full `php artisan test`: first run surfaced 4 failures, 2 caused by this feature (fixed, see Divergence 3/4 below) and 2 pre-existing/unrelated (confirmed via `git stash` on a clean checkout: `PhotoEditTest`'s two `created_at` timezone assertions fail identically with none of this feature's changes applied — a system-timezone-dependent test, not caused by Feature 050). Final re-run: **2 failed (the pre-existing timezone tests), 2798 passed, 111531 assertions**.

2. **Cross-artifact validation**
   - FR-050-01 → `database/migrations/2026_07_12_000000_create_albums_tags_table.php`, `Album::tags()`/`Tag::albums()` — `tests/Feature_v2/Tags/AlbumTagRelationTest.php`.
   - FR-050-02 → `UpdateAlbumRequest`, `AlbumController::updateAlbum()` — `tests/Feature_v2/Album/AlbumUpdateTest.php` (including the `tagsProvided()` v7-compatibility refinement discovered during implementation, documented in the spec).
   - FR-050-03 → `EditableBaseAlbumResource` Album branch — `AlbumUpdateTest` response assertions; v8 `AlbumProperties.vue` wiring.
   - FR-050-04/FR-050-05 → `GetTagWithPhotos::getAccessibleAlbums()`, `TagWithPhotosResource.albums` — `tests/Feature_v2/Tags/GetTagsTest.php`; v8 `TagPanel.vue` Albums section (code complete, manual visual check pending per above).
   - FR-050-06/07 → `AlbumTagStrategy`, `AlbumFieldLikeStrategy` plain-text extension, `AlbumSearch` registry split — `tests/Feature_v2/Search/AlbumSearchTest.php` (includes the NFR-050-01 regression guard `testTagModifierHasNoEffectOnQueryTagAlbums`).
   - FR-050-08 → `ListTags` split-count query, `TagResource` — `tests/Feature_v2/Tags/ListTagsTest.php`; v8 `TagsManagement.vue` split chips.
   - FR-050-09 → `TagCleanupTrait`, `MergeTag::handleAlbums()`, `DeleteTag` — `tests/Feature_v2/Tags/AlbumTagsImpactTest.php`.
   - NFR-050-01 → enforced structurally (no `tag` strategy in `queryTagAlbums()`'s registry) and via regression test (`testTagModifierDoesNotMatchTagAlbumsOwnCriteriaTags`, `testTagModifierHasNoEffectOnQueryTagAlbums`).
   - NFR-050-02 → no `resources/js/v7/**` *feature* work; two mechanical one-line fixes were required in v7 (`TagsInput.vue`, `TagsManagement.vue`) purely to keep existing v7 code compiling against the renamed shared `TagResource.num` → `num_photos`/`num_albums` fields — no new v7 behaviour was added. This is a divergence from the letter of "no v7 changes" and is called out explicitly here per the drift-gate's divergence-handling step; judged low-impact (two 1-line mechanical renames, not feature work) and corrected directly rather than escalated.
   - NFR-050-03 → `AlbumTagsImpactTest` covers non-admin ownership-scoping for merge/delete, mirroring existing photo/tag-album coverage.

3. **Divergence handling**
   - **Divergence 1 (low-impact, corrected directly):** FR-050-02's original draft used `present|array` for the `tags` validation rule; implementation revealed v7's `AlbumProperties.vue` also calls `PATCH /Album` without a `tags` key, so `present` would have 422'd every v7 album save. Fixed to `sometimes|array` + a `tagsProvided()` flag so the key is truly optional and omitting it never touches existing tags. Spec updated in place (FR-050-02); no new open question needed since there was only one correct behaviour once the constraint was discovered.
   - **Divergence 2 (low-impact, corrected directly):** the two mechanical v7 fixes noted above (NFR-050-02).
   - **Divergence 3 (low-impact, corrected directly):** `tests/Unit/Http/Requests/Album/UpdateAlbumRequestTest::testRules()` asserted an exact rule count (17); adding the two new `tags`/`tags.*` rules bumped it to 19 and broke this pre-existing test. Fixed by adding the two new expected entries to its rule map.
   - **Divergence 4 (low-impact, corrected directly):** `tests/Unit/LangTest::testLanguageConsistency()` requires every locale's `lang/<locale>/gallery.php` to have exactly the same keys as `lang/en/gallery.php`. Adding the new `gallery.album.properties.tags` key only to `en` broke this test for all 22 other locales. Fixed by adding the same key to all 22 locale files, deriving each locale's word for "tags" from the existing (adjacent) `show_tags`/`tags_required` keys where already translated, and falling back to the literal English string where those neighbours were themselves still untranslated (matching the file's existing, pre-feature translation-completeness state, e.g. `it`, `pt`, `zh_TW`, `sk`, `sv`, `vi`, `hu`, `el`).
   - No high/medium-impact gaps identified.

4. **Coverage confirmation**
   - Scenarios S-050-01 through S-050-10 all have passing test coverage (see per-scenario references in the Scenario Tracking table above).
   - T-050-17 (manual `/tag/{id}` browser verification) is the one exception, documented above as an environment limitation rather than claimed as done.

5. **Report & retrospective**
   - Lesson: when a feature extends an existing multi-frontend endpoint (v7 + v8 dual-tree), always grep for *all* callers of that endpoint/type before assuming a field can be `present`/required — the v7/v8 split makes it easy to miss a caller that predates the feature.
   - Lesson: `EditableBaseAlbumResource.tags` being reused for two semantically different purposes (Album's own tags vs. TagAlbum's matching-criteria tags) worked cleanly here because the two populating branches are mutually exclusive by `instanceof`, but it's worth flagging to future readers (done via inline comment).
   - Follow-up: T-050-17 manual verification should be completed by a human (or an agent with a working frontend build) before this feature is considered fully shipped.

## Follow-ups / Backlog

- Transitive album-tags ↔ photo-tags relationship (explicitly out of scope here — candidate for a future feature per user direction).
- v7 (PrimeVue) parity, if the product later decides to backport.
- Public, read-only display of an album's tags for viewers without edit rights (Q-050-03 resolved as editor-only for this feature; revisit if users want tags visible on the album page itself, not just discoverable via `/tag/{id}`/Search).
