# Open Questions – Feature 111

Open questions for [Feature 111](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|

| ~~Q-111-01~~ | 111 | Medium | Scope of login rate-limit feedback | Resolved (A, owner 2026-11-08, spec FR-111-01–03) | 2026-11-08 | 2026-11-08 |

## Question Details

### Q-111-01 — Scope of login rate-limit feedback

Option A (recommended): Handle HTTP 429 inline in both v7 and v8 password forms using translated feedback, suppress the duplicate global error for this endpoint, leave backend limits unchanged. Pros: focused fix covering both UIs. Cons: no exact retry countdown.

Option B: Add Retry-After based countdown and submission lockout too. Pros: precise guidance. Cons: more state, translations and tests.

Option C: Fix only v7 first. Pros: smallest diff for the reported production UI. Cons: v8 retains the same missing local feedback.

Owner selected A on 2026-11-08. Normative outcome: spec FR-111-01–03 and NFR-111-01.
