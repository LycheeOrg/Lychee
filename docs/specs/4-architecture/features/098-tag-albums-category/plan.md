# Feature Plan 098 – Tag Albums Category

_Linked specification:_ [spec.md](spec.md)  
_Status:_ Complete  
_Last updated:_ 2026-10-17

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec’s normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria
Admins can pull tag albums and/or person albums out of the Smart Albums panel into their own panels on the root gallery. Success: both settings default off with no visible change; each setting on moves exactly its group; v7 and v8 agree; no new request or query.

## Scope Alignment
- **In scope:** config migration (two settings), `RootConfig` fields, TypeScript types, `AlbumsState` getters, v7/v8 root `Albums.vue`, translations (`gallery.php`, `all_settings.php` in every language).
- **Out of scope:** album queries/caching, album pickers, non-root pages.

## Dependencies & Interfaces
- `App\Models\Extensions\BaseConfigMigration` (config rows).
- `RootConfig` shared by `RootAlbumResource` (v2) and `AlbumRootConfigResource` (v3).
- `AlbumsState` (`baseSmartAlbums`, `tagAlbums`, `personAlbums`).
- `AlbumThumbPanel` (v7, v8).

## Assumptions & Risks
- **Assumptions:** `tagAlbums`/`personAlbums` are already populated separately on both the v2 load and the v3 struct-of-array load (verified in `AlbumsState.ts`).
- **Risks / Mitigations:** `is-alone` drift across many template branches → keep `smartAlbums` as the full merged list (so existing `is-alone`, `titleOf` and the guest redirect stay correct) and add per-panel getters `smartAlbumsPanel`/`tagAlbumsPanel`/`personAlbumsPanel` plus `virtualPanelCount`.

## Implementation Drift Gate
After I4, re-read spec FR-098-01 … 07 against the diff; record evidence (test runs, browser check) in this plan's Appendix.

## Increment Map
1. **I1 – Settings and RootConfig (backend, test-first)**
   - _Steps:_ write `RootConfigSeparateCategoriesTest` (fails), add migration, add `RootConfig` fields, add `all_settings.php` strings in every language.
   - _Commands:_ `php artisan test --filter=RootConfigSeparateCategoriesTest`, `--filter=AlbumRootConfigV3Test`, `--filter=LangTest`, `vendor/bin/php-cs-fixer fix`, `make phpstan`.
2. **I2 – Types and store**
   - _Steps:_ `php artisan typescript:transform`; `AlbumsState` getters `smartAlbumsPanel`, `tagAlbumsPanel`, `personAlbumsPanel`, `virtualPanelCount` (`smartAlbums` unchanged).
   - _Commands:_ `vp run typecheck`.
3. **I3 – v7 and v8 panels + gallery strings**
   - _Steps:_ add Tag Albums and Person Albums panels below Smart Albums in both `Albums.vue`; switch `is-alone` to the store getter; add `gallery.tag_albums` / `gallery.person_albums` in every language.
   - _Commands:_ `vp check --fix`, `vp run typecheck`, `php artisan test --filter=LangTest`.
4. **I4 – Verification and docs**
   - _Steps:_ browser check on a scratch instance (S-098-02 … 06), knowledge-map note, roadmap update.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-098-01 | I1 / T-098-01, T-098-02 | `RootConfigSeparateCategoriesTest` default assertions |
| S-098-02 | I1, I3 / T-098-02, T-098-05 | config test + browser |
| S-098-03 | I1, I3 / T-098-02, T-098-05 | config test + browser |
| S-098-04 | I1, I3 / T-098-02, T-098-05 | config test + browser |
| S-098-05 | I3 / T-098-05 | browser |
| S-098-06 | I3 / T-098-05 | browser |

## Analysis Gate
2026-10-17: spec, plan and tasks agree; Q-098-01 … 03 resolved; no ADR needed (UI-local, no cross-module boundary).

## Exit Criteria
- Quality gate green (php-cs-fixer, `vp check --fix`, `vp run typecheck`, scoped tests, phpstan).
- `lychee.d.ts` regenerated.
- Roadmap and knowledge map updated.

## Follow-ups / Backlog
- Unscoped `vp check --fix` rewrites `<\/script>` in `EmbedCodeDialog.vue` (v7, v8) to `</script>`, breaking the SFC parse; needs a lint-rule exception or a code change (outside this feature).

## Appendix – Intent log
- 2026-10-17: owner asked for a setting (off by default) grouping tag albums outside Smart Albums. Q-098-01 A, Q-098-02 C, Q-098-03 A.
- 2026-10-17: implemented. Config keys follow the `TA_`/`PA_` prefixes (orders 13 and 103). Migration `2026_10_17_000001_add_tag_person_albums_separate_category_configs.php`. Translations added in all 23 languages (`fr/all_settings.php` keeps `details` before `documentation`, values placed accordingly).
- 2026-10-17: PR #4895 CodeRabbit review (comment 4301966066): a lone virtual panel hid its header while Featured or shared albums were visible. Fixed by `isVirtualPanelAlone` in both views (T-098-06). This also makes the Smart Albums panel keep its header next to Featured/shared albums, which it did not before.

## Drift gate (2026-10-17)
- FR-098-01/02: `RootConfigSeparateCategoriesTest` 4 passed; `AlbumRootConfigV3Test` 5 passed (v3 config equals v2 config, no album query → NFR-098-01).
- NFR-098-02: `LangTest` 2 passed.
- FR-098-03 … 06: scratch instance (`run-lychee`), tag album "Travel" and person album "Alice" created through `POST /api/v2/TagAlbum` / `PersonAlbum`. Panel headers per flag combination 00/10/01/11 — v8 (Nuxt, struct-of-array): `Smart Albums` / `+ Tag Albums` / `+ Person Albums` / `+ Tag Albums + Person Albums`; v7 (PrimeVue, v2 `GET /Albums`): same, followed by `Albums`. No console errors from the change (two image 404s present identically with both flags off).
- S-098-05/06 not exercised in the browser; covered by the `v-if="…Panel.length > 0"` guards and the `virtualPanelCount` `is-alone` rule.
- Static checks: php-cs-fixer (0 fixes), `make phpstan` OK, `vp check --fix` (scoped) OK, `vp run typecheck` OK.
