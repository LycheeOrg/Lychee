# Feature Plan 095 – Scrubber Label Formats

_Linked specification:_ [spec.md](spec.md)  
_Status:_ Implemented — drift gate 2026-10-16  
_Last updated:_ 2026-10-16

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec’s normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria

Admins choose how the scrubber rail writes years, months, days and hours, and the day order is adjustable on every instance.

- S-095-01 … S-095-09 pass (tests for S-095-04, 06, 08, 09; browser for the rest).
- Defaults leave every Feature 094 example unchanged.
- `php-cs-fixer`, `phpstan`, `npm run format`, `npm run check` clean; `lychee.d.ts` diff limited to `InitConfig`.

## Scope Alignment

- **In scope:** four configs and their lang entries, `SE_FIELDS`, `InitConfig`/`LycheeState`, `railTicks.ts` formats parameter, rail label clipping, sub-album lens format, the default migration (`timeline_photo_date_format_hour`, `timeline_album_date_format_day`).
- **Out of scope:** separate lens formats (N-095-03), format preview (N-095-01), per-user formats, v7.

## Dependencies & Interfaces

- Feature 094 (`railTicks.ts`, `TimelineDatesV3.vue`, `Timeline.vue`); Feature 071 (`albumDateScrubber.ts`, `date_scrubber_label_format`); Feature 092 (FR-092-13 flushes listings when a timeline label format changes).
- `App\Models\Extensions\BaseConfigMigration`; category constant `Mod Timeline` (as in `2024_10_30_064336_timeline_options.php`).
- `tests/Feature_v3/Gallery/DateFormatsInitTest.php`.

## Assumptions & Risks

- **Assumptions:** `phpDateFormat()` handles every letter the admin may type. (Verified 2026-10-16: SE settings are hidden, not locked, on a non-SE instance, and their stored values are always served; an empty `STRING_REQ` value is rejected by `Configs::sanity()`; `Gallery::Init` has no server cache.)
- **Risks / Mitigations:**
  - 23 locales × 2 entries per key, translated per locale (non-English files hold real translations; `lang/*.json` is generated at build time by the vite plugin and git-ignored, so no `lang:json` step).
  - Rollback of FR-095-07 also reverts an admin who chose `H:00` on purpose: documented in the migration.
  - A direct DB update skips the FR-092-13 listing flush: the migration clears the cache (`Artisan::call('cache:clear')`, the `bump_version*` pattern).

## Implementation Drift Gate

After I5: map FR-095-01 … 07 to code and tests in a table under "Drift Gate Report"; rerun the commands of every increment.

## Increment Map

1. **I1 – Configs and migration** (FR-095-01, FR-095-07; S-095-04, 06, 08)
   - _Steps:_ failing tests first (`DateFormatsInitTest` levels/defaults, hour-default migration test); migration adding the four keys (`BaseConfigMigration`), migration fixing the defaults (`timeline_photo_date_format_hour` `g:i` → `H:00`, `timeline_album_date_format_day` `j M` → `j M Y`, then `cache:clear`); orders 23–26 for the new keys; `SE_FIELDS`; lang entries in every locale.
   - _Commands:_ `php artisan test --filter=DateFormatsInitTest`, the migration test class, `vendor/bin/php-cs-fixer fix`, `make phpstan`.
2. **I2 – InitConfig** (FR-095-02; S-095-09)
   - _Steps:_ test the five fields first; `InitConfig` properties; `php artisan typescript:transform`; `LycheeState` defaults and assignment.
   - _Commands:_ `php artisan test --filter=DateFormatsInitTest`, `make phpstan`, `npm run check`.
3. **I3 – Frontend formats** (FR-095-03, 04, 06)
   - _Steps:_ `TickFormats` in `railTicks.ts` (`formatTickLabel`, `buildRailTicks`, `hourlyLensLabels` take formats); `TimelineDatesV3.vue` and `Timeline.vue` pass `LycheeState` values; `albumDateScrubber.ts` uses `date_scrubber_album_label_format`; rerun the Feature 094 Node check with default formats.
   - _Commands:_ `npm run format`, `npm run check`, Node check.
4. **I4 – Clipping** (FR-095-05; S-095-05)
   - _Steps:_ `max-w-13 overflow-hidden` on the resting label span; check LTR/RTL.
   - _Commands:_ `npm run format`, `npm run check`.
5. **I5 – Browser check and docs** (S-095-01 … 03, 05 … 07)
   - _Steps:_ scratch instance with the Feature 094 seed; settings page; formats changed through the DB; docs deliverables; drift gate.
   - _Commands:_ `run-lychee`; `npm run check`; `make phpstan`.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-095-04, 06, 08 | I1 / T-095-01, T-095-02 | tests |
| S-095-10 | I5 / T-095-06 | browser: settings page |
| S-095-09 | I2 / T-095-03 | `DateFormatsInitTest` |
| S-095-01, 03, 07 | I3 / T-095-04 | Node check + browser |
| S-095-05 | I4 / T-095-05 | browser LTR/RTL |
| S-095-02 | I5 / T-095-06 | browser |

## Analysis Gate

Re-run 2026-10-16, agent review, after the ambiguity review and Q-095-06/07:

1. Specification completeness — pass: FR-095-01 … 07 (FR-095-01 orders and SE visibility, FR-095-07 both defaults and cache clear), NFR-095-01 … 03, inventory and mock-ups updated.
2. Open questions review — pass: Q-095-01 … 07 resolved; no ADR needed.
3. Plan alignment — pass: increments cite FR-095-01 … 07; S-095-01 … 10 tracked.
4. Tasks coverage — pass: every FR mapped; tests first in T-095-01 … 03; increments ≤ 90 minutes.
5. Working agreements — pass: no dependency; Feature_v3 tests on `BaseApiWithDataTest`; config-row changes approved through Q-095-05/07.
6. Tooling readiness — pass.

First run, 2026-10-16, agent review (superseded):

1. Specification completeness — pass: FR-095-01 … 07, NFR-095-01 … 03, label inventory, settings and rail mock-ups.
2. Open questions review — pass: Q-095-01 … 05 resolved and folded in; no ADR needed (settings and display only).
3. Plan alignment — pass: increments cite the FRs; success criteria use the scenario IDs.
4. Tasks coverage — pass: every FR maps to a task; tests precede code in T-095-01 … 03; increments ≤ 90 minutes.
5. Working agreements — pass: no dependency; backend tests in Feature_v3 with `BaseApiWithDataTest`; license headers on new PHP files; persistence change limited to config rows (approved through Q-095-05).
6. Tooling readiness — pass: commands per increment.

## Drift Gate Report

2026-10-16, agent review after T-095-01 … 06 (full quality gate green: php-cs-fixer, phpstan, scoped tests, `npm run format`, `npm run check`).

| Requirement | Code | Evidence |
|-------------|------|----------|
| FR-095-01 | `2026_10_16_000001_add_date_scrubber_format_configs.php`, `ConfigIntegrity::SE_FIELDS`, `lang/*/all_settings.php` | `ScrubberLabelFormatsSettingsTest` (defaults, levels, orders, free/SE listing and saving, empty → 422); `ConfigTranslationKeysTest`; browser settings page free / SE preview |
| FR-095-02 | `InitConfig`, `LycheeState` | `DateFormatsInitTest` (defaults, updated values, not on album/root configs); `lychee.d.ts` +5 fields |
| FR-095-03 | `railTicks.ts` `TickFormats`, `TimelineDatesV3.vue` | Node check 29/29 with defaults; browser `j.n.` |
| FR-095-04 | `hourlyLensLabels(…, hourFormat)`, `Timeline.vue` | Browser: pill `15h` (one day), `21 Oct 2026 10h` (several days) |
| FR-095-05 | resting label span `max-w-13 overflow-hidden` | Browser: `l j F Y` clipped inside the rail |
| FR-095-06 | `albumDateScrubber.ts` uses `date_scrubber_album_label_format` | Browser: sub-album pill `21 Oct 2026` / `21.10.2026`, matching headers |
| FR-095-07 | `2026_10_16_000002_fix_timeline_date_format_defaults.php` | `TimelineDateFormatDefaultsMigrationTest`; fresh install `H:00`, `j M Y`; hour headers `18:00` |
| NFR-095-01 … 03 | — | No dependency change; `lychee.d.ts` diff limited; formats apply on reload |

Divergences: none. Note: the Timeline settings tab lists the new rows after the granularity selectors (the photo date formats of orders 15–22 are not on that tab); order 23–26 still holds in "All settings".

## Exit Criteria

- All tasks `[x]`; quality gate green (PHP and frontend); `lychee.d.ts` regenerated.
- Roadmap, knowledge map, Feature 092/094 docs updated; `_current-session.md` refreshed.

## Follow-ups / Backlog

- Live preview of a format in the settings field (N-095-01).
- Rollback of FR-095-07 also reverts admins who chose `H:00` or `j M Y` themselves.

## Intent Log

- 2026-10-16: owner asked to look at the labels and how to configure their formatting → label inventory L-095-01 … 09, Q-095-01 … 05.
- 2026-10-16: owner answered Q-095-01 A, Q-095-02 A with the day format free, Q-095-03 … 05 A → spec rewritten, plan and tasks written, analysis gate run.
- 2026-10-16: owner asked for an ambiguity review → facts checked in code (SE visibility, `STRING_REQ`, FR-092-13 flush path, settings order, translations, Init caching, `albumDateScrubber.ts` scope, album day default); spec corrected; Q-095-06 (settings position) and Q-095-07 (album day default without year) opened.
- 2026-10-16: owner answered Q-095-06 A (orders 23–26) and Q-095-07 B (album day default `j M` → `j M Y` in the FR-095-07 migration); analysis gate re-run.
- 2026-10-16: implemented T-095-01 … 06 (tests first: settings, migration, Init); translations written for 23 locales via `scratchpad/lang095.py`; drift gate passed.
