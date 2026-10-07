# Open Questions – Feature 054

Open questions for [Feature 054](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-054-01~~ | 054 – Configurable Landing Page | Medium | T-054-03/FR-054-20 say to add the 12 new landing configs to `ConfigIntegrity`'s `SE_FIELDS`/`PRO_FIELDS` whitelist, but that whitelist sets the DB `level` column, which `SettingsController` uses to hide `level>0` configs from non-SE/non-Pro admins in the flat Settings list — directly contradicting T-054-58's regression guard that all 12 keys stay visible to every admin. The keys are only SE-gated at *render* time (`LandingPageResource`'s fail-safe fallback), never at config-write time (FR-054-21 even requires a previously-stored SE-only value to persist through an SE lapse). | Resolved (A — do NOT add the 12 keys to `SE_FIELDS`/`PRO_FIELDS`; leave `level=0` so they stay visible/editable everywhere; SE-gating is enforced only by `LandingPageResource`'s effective-value fallback and disabled dropdown options in `LandingConfig.vue`) | 2026-08-11 | 2026-08-11 |

## Question Details

_No entries._
