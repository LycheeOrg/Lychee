# Open Questions – Feature 023

Open questions for [Feature 023](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|

## Question Details

### ~~Q-023-01: Remember-me Cookie Duration and Admin Configurability~~ ✅ RESOLVED

**Decision:** Option C — Use a shorter default (4 weeks) with env override
**Rationale:** A 4-week (40320 minutes) default is more security-conscious than Laravel's ~5-year default while still being practical for home/personal instances. The duration is configurable via `REMEMBER_LIFETIME` env variable, loaded by `config/auth.php` in the lychee guard config (`'remember' => (int) env('REMEMBER_LIFETIME', 40320)`). The existing `SessionOrTokenGuard::createGuard()` already reads this key via `setRememberDuration()`. No admin UI control — env/config only.
**Updated in spec:** Non-Goals (clarified no admin UI for duration), NFR-023-01 (cookie duration = 4 weeks default)
