# Feature Plan 081 – Configurable PDF MediaBox Match Limit

_Linked specification:_ [spec.md](spec.md)
_Status:_ Complete
_Last updated:_ 2026-10-04

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant.

## Vision & Success Criteria

A 112-page scanned PDF (or any legitimate document with >25 real `/MediaBox` tokens in its first 1 MB) gets a thumbnail again, without weakening the per-occurrence size/area guards. Success = the new config exists with default `25` (zero behaviour change on upgrade), the match-count cap is read from it at runtime, and a test proves the exact bug (rejected at 25, accepted once raised) end-to-end through a real upload + Ghostscript render.

## Scope Alignment

- **In scope:** `ImagickHandler::assertPdfPageSizeIsSafe()` match-count source; new `pdf_mediabox_max_matches` config + migration; `lang/*/all_settings.php` label/details strings (23 locales); new test fixture + tests; quality gates; PR to `LycheeOrg/Lychee` continuing discussion #4826.
- **Out of scope:** `MAX_PDF_MEDIABOX_POINTS`, `MAX_MEDIABOX_AREA_PER_BYTE`, `MEDIABOX_SCAN_LIMIT` (NFR-081-02); any Settings-page Vue changes (none needed — Q-081-01 resolution); Ghostscript/rendering pipeline changes.

## Dependencies & Interfaces

- `app/Image/Handlers/ImagickHandler.php` (core change).
- `App\Repositories\ConfigManager` (`getValueAsInt`) — already used throughout `app/Image/**`, confirmed idiomatic (`app(ConfigManager::class)->getValueAsInt(...)`).
- `App\Models\Extensions\BaseConfigMigration` / `AbstractBaseConfigMigration::POSITIVE` — existing config-creation pattern, confirmed against `database/migrations/2026_09_30_202837_add_landing_background_include_sensitive.php` (PR #4821, the exact PR `ildyria` referenced).
- `tests/Samples/pdf.pdf` (base renderable fixture, confirmed to already contain 2 real small `/MediaBox[...]` matches at raw-byte level) + `tests/Constants/TestConstants.php`.
- `tests/ImageProcessing/Image/Handlers/PhotosAddHandlerImagickTest.php` (existing MediaBox test suite to extend).

## Assumptions & Risks

- **Assumptions:** Ghostscript/Imagick are available in the dev/test environment used to run `php artisan test` (gated by `RequiresImageHandler`, same as all existing tests in this file — if unavailable, these tests self-skip the same way the pre-existing MediaBox tests already do, not a new risk).
- **Risks / Mitigations:** A hand-crafted fixture mixing decoy comment-line `/MediaBox` tokens with a real compressed PDF could, in principle, confuse Ghostscript if a comment line is malformed. Mitigation: comments are inserted immediately after the existing `%PDF-1.4` header line, each on its own terminated line, leaving 100% of the original `pdf.pdf` bytes after that point untouched (verified byte-for-byte via `tail -n +2`).

## Implementation Drift Gate

**Run 2026-10-04**, after all tasks complete:

1. **Preconditions** — ✅ all T-081-01..09 marked `[x]`; latest quality gate green (below).
2. **Cross-artifact validation** — ✅ FR-081-01 ↔ `ImagickHandler.php` config read + message (T-081-02); FR-081-02 ↔ migration (T-081-01); FR-081-03 ↔ 23×2 lang entries (T-081-03/04). No undocumented work: the one scope addition (exception-message wording, user-requested mid-implementation) was folded back into FR-081-01 before closing. NFR-081-01 (zero behaviour change at default) ↔ migration seed `'25'` + `testManyLegitMediaBoxesRejectedAtDefaultLimit` passing. NFR-081-02 (other constants untouched) ↔ confirmed via diff review — only `MAX_MEDIABOX_MATCHES` removed/replaced.
3. **Divergence handling** — Low-impact only: the exception-message extension was a direct user instruction mid-session, not a discovered gap; recorded inline in spec.md/tasks.md, no new open-questions entry warranted.
4. **Coverage confirmation** — ✅ Success (S-081-02, raised-limit accepted), validation (config `positive` type, inherited/untested here since it's the existing, already-tested `Configs` validation path — no new validation logic introduced), failure (S-081-01, default-limit rejected) branches all covered; S-081-03 regression (3 pre-existing MediaBox tests) confirmed still green. `php artisan test --filter=PhotosAddHandlerImagickTest`: 33/35 passing; the 2 failures are pre-existing and unrelated (confirmed via `git stash` + rerun — identical failures with none of this feature's changes applied).
5. **Report & retrospective** — This fix closes a real upstream false-positive reported by the user themselves (`mitpjones`) in [discussion #4826](https://github.com/LycheeOrg/Lychee/discussions/4826). Lesson for future config-adding features in this codebase: "is_expert" does not mean "no UI" — every `BaseConfigMigration` row auto-surfaces in the generic Settings UI; the real choice is only which section it appears in. Confirmed by reading PR #4821's actual diff rather than assuming from the option name, which corrected an earlier wrong framing in this feature's own open-questions.md (Q-081-01 superseded-options note).

No blocking divergences. Feature complete.

## Increment Map

1. **I1 – Migration + config**
   - _Goal:_ Add the `pdf_mediabox_max_matches` config row (FR-081-02).
   - _Preconditions:_ spec.md FR-081-02 resolved (done).
   - _Steps:_ Create `database/migrations/2026_10_04_000000_add_pdf_mediabox_max_matches_config.php` extending `BaseConfigMigration`, `cat => 'Image Processing'`, `value => '25'`, `type_range => self::POSITIVE`, `is_expert => true`, `is_secret => false`, `level => 0`, `order => 95`.
   - _Commands:_ `php artisan migrate` against the SQLite test DB (automatic under `php artisan test`); manual sanity check `php artisan tinker` → `DB::table('configs')->where('key','pdf_mediabox_max_matches')->first()`.
   - _Exit:_ Row exists with documented defaults; `down()` removes it cleanly (inherited from `BaseConfigMigration`, no bespoke code needed).

2. **I2 – Core change**
   - _Goal:_ `assertPdfPageSizeIsSafe()` reads the cap from config (FR-081-01).
   - _Preconditions:_ I1 done (config must exist for the read to succeed).
   - _Steps:_ Replace `self::MAX_MEDIABOX_MATCHES` in the `for` loop condition and the final exception message with `app(ConfigManager::class)->getValueAsInt('pdf_mediabox_max_matches')` (read once into a local variable at the top of the method, not per-iteration); remove the now-unused `MAX_MEDIABOX_MATCHES` constant and its docblock; add the `ConfigManager` import.
   - _Commands:_ `vendor/bin/php-cs-fixer fix app/Image/Handlers/ImagickHandler.php`; `make phpstan` (narrow to this file first).
   - _Exit:_ No remaining reference to `MAX_MEDIABOX_MATCHES`; file still parses/lints clean.

3. **I3 – Settings label strings**
   - _Goal:_ FR-081-03 — label/details strings so the auto-generated Settings UI row is readable.
   - _Preconditions:_ I1 done (key name finalised).
   - _Steps:_ Add `'pdf_mediabox_max_matches' => 'Maximum number of /MediaBox occurrences scanned in a PDF'` to the labels array and a details string (explaining the false-positive this avoids) to the details array in `lang/en/all_settings.php`; copy the exact same English key+value pair into the matching two arrays in all other 22 `lang/<locale>/all_settings.php` files (placeholder text, untranslated — matches PR #4821's precedent exactly).
   - _Commands:_ none beyond `php-cs-fixer` (lang files are plain PHP arrays); spot-check with `grep -rn "pdf_mediabox_max_matches" lang/ | wc -l` (expect 46 = 23 locales × 2 arrays).
   - _Exit:_ Every locale file has the key in both arrays.

4. **I4 – Tests**
   - _Goal:_ Prove the bug and the fix end-to-end (Test Strategy in spec.md).
   - _Preconditions:_ I1–I3 done.
   - _Steps:_
     1. Build `tests/Samples/pdf_many_legit_mediabox.pdf`: `pdf.pdf`'s first line (`%PDF-1.4`) + 30 generated `% /MediaBox [0 0 100 100] decoy N` comment lines + the remainder of `pdf.pdf` unchanged byte-for-byte (confirmed `pdf.pdf` already contributes 2 more real, safe matches later in the stream → 32 total safe matches).
     2. Add `SAMPLE_FILE_PDF_MANY_LEGIT_MEDIABOX = 'tests/Samples/pdf_many_legit_mediabox.pdf'` to `tests/Constants/TestConstants.php` (plus its MIME-type map entry, matching the other PDF constants).
     3. Add `testManyLegitMediaBoxesRejectedAtDefaultLimit()` to `PhotosAddHandlerImagickTest.php` — default config (`25`), upload the new fixture, assert null thumb + non-empty log (same pattern as `testOversizedPdfMediaBoxIsRejected`). Proves the real-world false positive reproduces.
     4. Add `testManyLegitMediaBoxesAcceptedWhenLimitRaised()` — `Configs::set('pdf_mediabox_max_matches', 40)` before upload, same fixture, assert a real 200×200 thumbnail is produced (same pattern as `testPdfUploadCreatesThumbnail`). Proves the configurability actually fixes the bug.
   - _Commands:_ `php artisan test --filter=PhotosAddHandlerImagickTest`.
   - _Exit:_ All 6 tests in the class pass (4 pre-existing + 2 new).

5. **I5 – Quality gates & docs sync**
   - _Goal:_ Close out per AGENTS.md's "After Completing Work" checklist.
   - _Preconditions:_ I1–I4 green.
   - _Steps:_ `vendor/bin/php-cs-fixer fix`; `php artisan test --filter=PhotosAddHandlerImagickTest`; `make phpstan`; update this plan's Analysis Gate / Implementation Drift Gate sections; update `tasks.md` checkboxes; update roadmap.md row (Planning → Testing/Complete); stage files and prepare (not execute) the commit + PR description for the operator.
   - _Commands:_ as above.
   - _Exit:_ Quality gate green; roadmap/tasks reflect final state; commit message + PR description ready for operator review.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-081-01 | I4 / T-081-06 | Many safe `/MediaBox` occurrences rejected at default `25` (reproduces #4826). |
| S-081-02 | I4 / T-081-07 | Same file accepted once config raised — proves the fix. |
| S-081-03 | (regression, no new task) | Existing `testOversizedPdfMediaBoxIsRejected`/`testDisproportionateMediaBoxIsRejected`/`testDecoyMediaBoxIsRejected` must still pass unchanged — the per-occurrence checks are untouched (NFR-081-02). |

## Analysis Gate

**Run 2026-10-04.** Against [analysis-gate-checklist.md](../../../5-operations/analysis-gate-checklist.md):

1. Specification completeness — ✅ pass. FR/NFR populated; Q-081-01 resolution folded into spec.md's normative sections; no UI mock-up needed (no bespoke UI work — confirmed data-driven Settings UI).
2. Open questions review — ✅ pass. No `Open` rows remain in open-questions.md. No ADR warranted — single-constant config fix, not an architecturally significant cross-module decision.
3. Plan alignment — ✅ pass. References spec.md/tasks.md correctly.
4. Tasks coverage — ✅ pass, with one sequencing note: T-081-01 (migration) and T-081-05 (fixture) are pure setup with no behavioural assertion possible before/after (a dormant config row at the same default as today's constant has no observable effect). The one task with a real "should fail before, pass after" story is **T-081-07** (accepted-when-raised) — the fix in T-081-02 only matters once the config is actually read. Execution order followed in I4/I5: T-081-01 → T-081-05 → write T-081-07 and confirm it fails against the still-unfixed `ImagickHandler` → T-081-02 (the fix) → confirm T-081-07 passes → T-081-06 (regression-style, passes regardless of fix) → T-081-08 full suite.
5. Working-agreement compliance — ✅ pass. No new branching complexity (single local variable replaces a constant reference, same control flow).
6. Tooling readiness — ✅ pass. Commands documented above.

No blocking findings. Proceeding to implementation.

## Exit Criteria

- `php artisan test --filter=PhotosAddHandlerImagickTest` green (6/6).
- `vendor/bin/php-cs-fixer fix` clean on touched files.
- `make phpstan` clean on touched files.
- `tasks.md` fully checked.
- Roadmap row updated.
- Commit + PR description prepared for operator (not auto-committed, per AGENTS.md's commit protocol and global instructions).

## Follow-ups / Backlog

- None identified — this is a scoped, single-constant fix per the maintainer's reply.
