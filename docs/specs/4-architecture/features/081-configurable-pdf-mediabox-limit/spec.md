# Feature 081 – Configurable PDF MediaBox Match Limit

| Field | Value |
|-------|-------|
| Status | Complete |
| Last updated | 2026-10-04 |
| Owners | mitpjones |
| Linked plan | `docs/specs/4-architecture/features/081-configurable-pdf-mediabox-limit/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/081-configurable-pdf-mediabox-limit/tasks.md` |
| Roadmap entry | #081 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below.

## Overview

Upstream `LycheeOrg/Lychee` ([discussion #4826](https://github.com/LycheeOrg/Lychee/discussions/4826)) has a false-positive bug: `ImagickHandler::assertPdfPageSizeIsSafe()` hard-rejects PDF thumbnail generation once it finds more than `MAX_MEDIABOX_MATCHES = 25` `/MediaBox` occurrences in the first `MEDIABOX_SCAN_LIMIT` (1 MB) of a file — hardening added by PRs [#4511](https://github.com/LycheeOrg/Lychee/pull/4511) and [#4687](https://github.com/LycheeOrg/Lychee/pull/4687) to bound Ghostscript rasterization cost against malicious uploads. Legitimate large scanned multi-page documents (e.g. a 112-page, 42 MB scanned yearbook) can easily exceed 25 real `/MediaBox` occurrences within the first 1 MB and are rejected outright, even though each occurrence is already individually bounds-checked against `MAX_PDF_MEDIABOX_POINTS` and `MAX_MEDIABOX_AREA_PER_BYTE` — so the match-count cap adds no additional protection against an oversized/implausible page, only against the (cheap) cost of the scan loop itself.

Maintainer `ildyria` agreed a fix is warranted but asked (in the same discussion) for `MAX_MEDIABOX_MATCHES` to become an **admin-configurable setting** — using the codebase's existing `positive` config `type_range` for input validation — rather than simply raising the hardcoded constant. This feature implements that configurable setting and submits it upstream as a PR from the `mitpjones/Lychee` fork, now that the fork's `master` has been fast-forwarded to match `upstream/master` (`e95be7a55`).

Affected modules: core (`app/Image/Handlers/ImagickHandler.php`), persistence (new config row via a `BaseConfigMigration`). The admin Settings UI in this codebase is entirely data-driven from the `configs` table (`cat`/`is_expert`/`level`/`order` + `lang/*/all_settings.php` label/details strings) — any `BaseConfigMigration` row is automatically surfaced there, so there is no separate "UI module" to build (Q-081-01, resolved).

## Goals

- Replace the hardcoded `MAX_MEDIABOX_MATCHES = 25` constant in `ImagickHandler::assertPdfPageSizeIsSafe()` with a value read from a new config key, validated as `positive` (matches maintainer's requested approach).
- Preserve the existing per-occurrence safety checks (`MAX_PDF_MEDIABOX_POINTS`, `MAX_MEDIABOX_AREA_PER_BYTE`) and the `MEDIABOX_SCAN_LIMIT` scan window exactly as-is — only the match-count cap becomes configurable.
- Ship a migration that adds the new config row with a sensible default so existing instances are not left broken after upgrade, and are not silently exposed to an unbounded scan loop.
- Open a PR against `LycheeOrg/Lychee` implementing the maintainer's requested direction, continuing the existing discussion (#4826).

## Non-Goals

- Making `MAX_PDF_MEDIABOX_POINTS`, `MAX_MEDIABOX_AREA_PER_BYTE`, or `MEDIABOX_SCAN_LIMIT` configurable — the maintainer's reply only asked for the match-count cap to become configurable; the other constants are out of scope for this feature.
- Any change to Ghostscript invocation, PDF rendering pipeline, or thumbnail generation beyond the guard check itself.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-081-01 | `ImagickHandler::assertPdfPageSizeIsSafe()` reads the match-count cap from the `pdf_mediabox_max_matches` config instead of the hardcoded `MAX_MEDIABOX_MATCHES` constant, via `app(ConfigManager::class)->getValueAsInt('pdf_mediabox_max_matches')`. | A PDF with N `/MediaBox` occurrences (N ≤ configured value) within the scan window is accepted for thumbnail generation, identical to current behaviour at the default. | Config value is constrained to a strictly positive integer by `type_range => self::POSITIVE` (`ConfigType::POSTIIVE`) at the model-validation layer (`Configs::class`), same as every other `positive` config. | A PDF exceeding the configured cap throws `MediaFileUnsupportedException`, now naming the configured value **and** hinting that `pdf_mediabox_max_matches` can be raised — the original bug report had no indication this was adjustable short of reading source, so the logged message closes that discoverability gap directly. | No new telemetry; existing `errors.log` message already includes the limit value, now sourced from config and self-documenting. | Upstream discussion #4826 (ildyria reply); message wording — user request, this session. |
| FR-081-02 | A new migration (`BaseConfigMigration`) adds the `pdf_mediabox_max_matches` config row: `value => '25'`, `cat => 'Image Processing'`, `type_range => self::POSITIVE`, `is_expert => true`, `is_secret => false`, `level => 0`, `order => 95`. | Running `php artisan migrate` on an existing instance adds the row with default `25` — identical behaviour to pre-migration, zero change on upgrade. | N/A (migration is idempotent per Laravel's standard migration tracking; `BaseConfigMigration::down()` removes the row by key). | N/A. | N/A. | Q-081-01 resolution; pattern confirmed against PR #4821 (`cea5c7251`). |
| FR-081-03 | `lang/en/all_settings.php` gains a label (`'pdf_mediabox_max_matches' => 'Maximum number of /MediaBox occurrences scanned in a PDF'`) and details string explaining the setting, in both the labels array and the details array. The same key + **English placeholder text** (untranslated) is added to all other 22 locale `all_settings.php` files, matching the `#4821` precedent exactly. | The new setting renders with a readable label/description in the admin Settings UI's Expert section, in every locale (English text as placeholder outside `en`, pending community translation). | N/A. | N/A. | N/A. | PR #4821 diff (`lang/de`, `lang/fr`, etc. — English text duplicated, not translated). |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-081-01 | Zero behaviour change for any existing instance immediately after upgrade (default `25` matches today's hardcoded value exactly). | User explicitly chose default `25` over the researched `2000` value specifically to avoid changing behaviour on upgrade. | Code review: migration seed value is the literal string `'25'`; no other constant/threshold touched. | Migration must run before the config is read (standard Laravel migration-then-serve ordering — no special handling needed). | User decision, this session. |
| NFR-081-02 | The two independent per-occurrence safety checks (`MAX_PDF_MEDIABOX_POINTS`, `MAX_MEDIABOX_AREA_PER_BYTE`) and the scan window (`MEDIABOX_SCAN_LIMIT`) remain untouched constants — only the match-count cap becomes configurable. | Non-Goals; maintainer's reply scoped the fix to the count cap only. | Code review: diff touches only the `MAX_MEDIABOX_MATCHES` constant/usage, no other constant in `ImagickHandler.php`. | — | ildyria's discussion reply. |

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-081-01 | `pdf_mediabox_max_matches` config row: `key`, `value` (string-encoded positive int, default `'25'`), `cat='Image Processing'`, `type_range='positive'`, `is_expert=true`, `is_secret=false`, `level=0`, `order=95`, `description`, `details`. | persistence (`configs` table), core (`ImagickHandler`), admin Settings UI (read-only consumer, no bespoke code) |

### Fixtures & Sample Data

| ID | Path | Purpose |
|----|------|---------|
| FX-081-01 | `tests/Feature_v2/Image/...` or existing PDF test fixtures under `tests/` (exact path TBD in plan.md) | A synthetic/truncated PDF with >25 (and ≤ configured) `/MediaBox` occurrences, to prove the config value (not the old constant) now gates the check. |

## Test Strategy

- **Core:** Unit/feature test on `ImagickHandler::assertPdfPageSizeIsSafe()` (via its public PDF-loading entry point) proving: (a) a PDF with occurrences ≤ configured value passes, (b) a PDF with occurrences > configured value still throws `MediaFileUnsupportedException`, (c) changing the config value changes the threshold at runtime (no caching surprises).
- **Migration:** Confirm the config row exists with the documented defaults after `php artisan migrate`, and that `down()` removes it cleanly (existing `BaseConfigMigration` base class already guarantees this mechanically — a smoke test is sufficient, not a bespoke one).
- **UI (JS/Selenium):** None required — the Settings UI is generic/data-driven; no new Vue code is added.
- **Docs/Contracts:** `knowledge-map.md` — note the new config key under whichever existing "Image Processing" / config entries already exist there, if any are tracked at that granularity.

## Spec DSL

```
domain_objects:
  - id: DO-081-01
    name: pdf_mediabox_max_matches (config row)
    fields:
      - name: value
        type: string (positive integer)
        constraints: "> 0, default '25'"
fixtures:
  - id: FX-081-01
    path: tests/Feature_v2/Image/... (TBD)
```

## Appendix

- Upstream discussion: https://github.com/LycheeOrg/Lychee/discussions/4826
- Hardening PRs being adjusted: [#4511](https://github.com/LycheeOrg/Lychee/pull/4511), [#4687](https://github.com/LycheeOrg/Lychee/pull/4687)
- Current implementation (this fork's `master`, synced to `upstream/master` `e95be7a55`): `app/Image/Handlers/ImagickHandler.php` — constants at lines ~37-72, check at `assertPdfPageSizeIsSafe()` lines ~193-228.
