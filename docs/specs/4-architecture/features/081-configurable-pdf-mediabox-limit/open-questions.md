# Open Questions – Feature 081

Open questions for [Feature 081](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-081-01~~ | 081 – Configurable PDF MediaBox Limit | Medium | Default value for the new `pdf_mediabox_max_matches` config, and whether it should be exposed in the admin Settings UI or DB-only (expert setting) | Resolved (Custom — default `25`, `is_expert: true`) | 2026-10-04 | 2026-10-04 |

## Question Details

### ~~Q-081-01~~ · Default value and admin-UI exposure for the new MediaBox match-count config ✅ RESOLVED

**Status:** Resolved — **Custom** (default `25`, `is_expert: true`)
**Feature:** 081 – Configurable PDF MediaBox Limit
**Priority:** Medium
**Opened:** 2026-10-04
**Resolved:** 2026-10-04

**Resolution:** The new `pdf_mediabox_max_matches` config ships with **default `25`** — identical to the current hardcoded value, so no existing instance changes behaviour on upgrade; an admin who hits the false positive (e.g. the 112-page scanned-yearbook case) raises it themselves. It is added via a `BaseConfigMigration` row with `is_expert: true` — this does **not** mean "no UI": in this codebase every `BaseConfigMigration` row is automatically picked up by the generic, data-driven Settings UI (keyed by `cat`/`is_expert`/`level`/`order` plus a label/details pair in `lang/*/all_settings.php`), confirmed by inspecting PR [#4821](https://github.com/LycheeOrg/Lychee/pull/4821) (`cea5c7251`) — the exact PR maintainer `ildyria` pointed to as the template. `is_expert: true` only controls which settings-panel section it surfaces in (tucked into "Expert", hidden by default — appropriate for a niche hardening knob), not whether a bespoke frontend field is required; the diff shape (migration + `lang/en/all_settings.php` + 22 other locale files with English placeholder text, matching the `#4821` precedent exactly) is identical either way.

**Spec impact:** Captured in FR-081-01/02 and the Interface & Contract Catalogue (`DO-081-01`) below. Category `Image Processing` (`cat`), `type_range => self::POSITIVE`, `level => 0`, `order => 95` (next free slot after the existing `Image Processing` category entries, which top out at `94`).

**Context — initial options considered (superseded):** the original options (A: default `2000`/expert, B: default `2000`/dedicated field, C: default `100`/expert) were framed around a wrong assumption that "expert" meant "no UI at all" in this codebase, and that a "dedicated field" would require meaningfully more frontend work than an expert-flagged row. Both assumptions were corrected after inspecting `#4821`'s actual diff (see Resolution). The user separately chose default `25` over the researched `2000` value specifically for zero-behaviour-change-on-upgrade, overriding the original Option A/B/C default recommendation.
