# Feature 081 Tasks – Configurable PDF MediaBox Match Limit

_Status: Complete_
_Last updated: 2026-10-04_

> Keep this checklist aligned with [plan.md](plan.md)'s increments.

## Checklist

- [x] T-081-01 – Add `pdf_mediabox_max_matches` config migration (FR-081-02).
  _Intent:_ New `BaseConfigMigration` row, default `'25'`, `cat='Image Processing'`, `type_range=self::POSITIVE`, `is_expert=true`, `level=0`, `order=95`.
  _Verification commands:_
  - `php artisan test --filter=PhotosAddHandlerImagickTest` (migrations run automatically against the SQLite test DB).
  _Notes:_ I1.

- [x] T-081-02 – Replace `MAX_MEDIABOX_MATCHES` constant with a config read in `ImagickHandler::assertPdfPageSizeIsSafe()` (FR-081-01, N-081-01).
  _Intent:_ Read `app(ConfigManager::class)->getValueAsInt('pdf_mediabox_max_matches')` once at the top of the method; remove the constant + its docblock.
  _Verification commands:_
  - `vendor/bin/php-cs-fixer fix app/Image/Handlers/ImagickHandler.php`
  - `make phpstan`
  _Notes:_ I2. Depends on T-081-01 (config must exist).

- [x] T-081-03 – Add English label/details strings for `pdf_mediabox_max_matches` (FR-081-03).
  _Intent:_ `lang/en/all_settings.php` labels array + details array.
  _Verification commands:_ none (reviewed by eye; picked up implicitly by I4 tests passing).
  _Notes:_ I3.

- [x] T-081-04 – Propagate the same key + English placeholder text to all other 22 `lang/<locale>/all_settings.php` files (FR-081-03).
  _Intent:_ Matches PR #4821's precedent — untranslated placeholder, not a translation task.
  _Verification commands:_
  - `grep -rn "pdf_mediabox_max_matches" lang/ | wc -l` (expect 46).
  _Notes:_ I3.

- [x] T-081-05 – Build `tests/Samples/pdf_many_legit_mediabox.pdf` fixture + `TestConstants` entry.
  _Intent:_ `pdf.pdf` header + 30 decoy-comment `/MediaBox` lines + unchanged `pdf.pdf` remainder (32 total safe matches once combined with `pdf.pdf`'s own 2 real ones).
  _Verification commands:_
  - `LC_ALL=C grep -ao "MediaBox" tests/Samples/pdf_many_legit_mediabox.pdf | wc -l` (expect 32).
  _Notes:_ I4 (S-081-01, S-081-02).

- [x] T-081-06 – Test: rejected at default limit 25 (S-081-01).
  _Intent:_ `testManyLegitMediaBoxesRejectedAtDefaultLimit()` — default config, expect null thumb + log entry.
  _Verification commands:_
  - `php artisan test --filter=PhotosAddHandlerImagickTest::testManyLegitMediaBoxesRejectedAtDefaultLimit`
  _Notes:_ I4. Depends on T-081-01, T-081-02, T-081-05.

- [x] T-081-07 – Test: accepted once limit raised (S-081-02).
  _Intent:_ `testManyLegitMediaBoxesAcceptedWhenLimitRaised()` — `Configs::set('pdf_mediabox_max_matches', 40)`, expect a real 200×200 thumbnail.
  _Verification commands:_
  - `php artisan test --filter=PhotosAddHandlerImagickTest::testManyLegitMediaBoxesAcceptedWhenLimitRaised`
  _Notes:_ I4. Depends on T-081-01, T-081-02, T-081-05.

- [x] T-081-08 – Full regression + quality gate (S-081-03).
  _Intent:_ Confirm the 3 pre-existing MediaBox tests and the 2 new ones all pass together; run php-cs-fixer + phpstan across all touched files.
  _Verification commands:_
  - `php artisan test --filter=PhotosAddHandlerImagickTest`
  - `vendor/bin/php-cs-fixer fix`
  - `make phpstan`
  _Notes:_ I5.

- [x] T-081-09 – Update roadmap.md row + plan.md Analysis/Drift Gate sections; prepare commit + PR description for operator.
  _Intent:_ Close out per AGENTS.md's "After Completing Work" checklist; do not execute `git commit` or push — present staged summary + commit message + PR description to the user.
  _Verification commands:_ n/a (documentation).
  _Notes:_ I5.

## Notes / TODOs

- Per user request (not in original plan), the final exception message in `assertPdfPageSizeIsSafe()` was extended to name `pdf_mediabox_max_matches` directly, so the logged error itself tells an admin how to fix a false positive rather than requiring a source read. Captured in spec.md FR-081-01.
- `php artisan test --filter=PhotosAddHandlerImagickTest`: 33/35 pass (all 6 MediaBox tests green, including both new ones). 2 pre-existing failures (`google motion photo upload`, `tricky video upload`) confirmed unrelated — same failures reproduce identically with `git stash` (i.e. without any of this feature's changes), an FFmpeg/environment issue, not a regression from this work.
- `vendor/bin/php-cs-fixer fix`: 0 files changed. `make phpstan`: no errors.
- **Full unfiltered `php artisan test`** (2026-10-04, ~28 min): 36 failed / 4263 passed. All 36 fall into two pre-existing, environment-caused clusters, neither touching this feature's files: (a) video/FFmpeg-related (`DownloadTest`, `VideoDataTest`, `PhotosAddHandlerGd/ImagickTest`, `RawUploadImagickTest`, `WatermarkerTest` — same root cause already confirmed via `git stash` to exist without this feature's changes); (b) LDAP-related (`LdapConfigurationTest`, `LdapServiceTest`) — `Undefined constant "LDAP_OPT_X_TLS_REQUIRE_CERT"`, this machine has no `ext-ldap` PHP extension (same gap `composer install` already flagged). AGENTS.md's "all tests must pass" quality gate is satisfied modulo these two documented, pre-existing environment limitations.
- **Additional manual verification against the real-world artifact** (user-requested, 2026-10-04): the actual `Heretaungan.pdf` (2017 edition, 42MB, 112 pages — the real document behind discussion #4826, confirmed via the standalone scan-logic probe: 112 real `/MediaBox` matches in the first 1MB, all 612.28×858.9pt, well within size/area limits) was run through the real upload pipeline via a temporary, uncommitted PHPUnit test (deleted after the run — not part of this feature's permanent suite, and the source PDF itself was never added to the repo per explicit instruction). Result: rejected in 0.87s at the default limit (25), and produces a real 200×200 JPEG thumbnail (15.54 KB) in 1.14s once raised to 2000 — confirming the fix end-to-end against the actual document that motivated the bug report, not just the synthetic fixture. (Note: an earlier, different Heretaungan PDF — "The Heretaungan 2010", 64MB/96 pages — was also checked and found to have only 1 real `/MediaBox` match, so it was never affected by this bug at any cap; not the file behind the original report.)
