## Before You Code
- **Clarify ambiguity first.** Do not plan or implement until every requirement is understood. Ask the user, record unresolved items in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), and wait for answers. Capture accepted answers by updating the relevant specification’s requirements/NFR/behaviour/telemetry sections so the spec remains the single source of truth for behaviour.
  - **No-direct-question rule:** Never ask the user for clarification, approval, or a decision in chat until the matching open question is logged (table row + Question Details entry). Treat violations as blockers—stop work, add the missing entry, then resume the conversation by referencing that question ID.
  - Whenever you present alternative approaches—whether for open questions or general solution proposals—first capture or update the entry in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md) (summary row + Question Details section) so the ambiguity lives on disk, then present the stored text to the user **inline in chat**. Summarise the options directly in your reply (do not tell the user to open the file), call out the question ID (for example, Q013-01), and follow the numbered-heading/Options A, B, C format with pros/cons. **Order the options by preference** (Option A is always the recommended path, Option B the next-best, etc.) so the user sees our best advice first. Keep specifications/plans/tasks limited to that ID until the question is resolved.
- **Work in small steps.** During planning, break every change into logical, self-contained tasks that are expected to complete within ≤90 minutes. Execution can take longer if required; the goal is to plan manageable increments, and commit with a conventional message for each finished slice.
- **Prime the knowledge map.** Skim [docs/specs/4-architecture/knowledge-map.md](docs/specs/4-architecture/knowledge-map.md) and the up-to-date module snapshot in [docs/specs/architecture-graph.json](docs/specs/architecture-graph.json) before planning so new work reinforces the architectural relationships already captured there.
- **Template usage.** Author new specifications, feature plans, and task checklists using [docs/specs/templates/feature-spec-template.md](docs/specs/templates/feature-spec-template.md), [docs/specs/templates/feature-plan-template.md](docs/specs/templates/feature-plan-template.md), and [docs/specs/templates/feature-tasks-template.md](docs/specs/templates/feature-tasks-template.md) so structure, metadata, and verification notes stay uniform across features.
- **ADR context.** Before planning or implementation, skim ADRs under `docs/specs/5-decisions` whose related-features/specs entries reference the active feature ID so high-impact clarifications and architectural decisions are treated as required context alongside the roadmap, spec, plan, tasks, and knowledge map.

## Specification Pipeline
- Start every feature by updating or creating its specification at `docs/specs/4-architecture/features/<NNN>-<feature-name>/spec.md`.
`<featur-ename>` is a short, hyphen-separated name (2–4 words) that captures the essence of the feature in action-noun format (for example, "I want to add user authentication" → "user-auth", "Implement OAuth2 integration for the API" → "oauth2-api-integration", "Create a dashboard for analytics" → "analytics-dashboard", "Fix payment processing timeout bug" → "fix-payment-timeout")
- For any new UI feature or modification, include an ASCII mock-up in the specification (see [docs/specs/4-architecture/spec-guidelines/ui-ascii-mockups.md](docs/specs/4-architecture/spec-guidelines/ui-ascii-mockups.md)).
- Capture every high-impact clarification question (and each medium-impact uncertainty) per feature; log them in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md) and, once resolved, record the outcome directly in the spec (requirements, NFR, behaviour/UI, telemetry/policy sections). For architecturally significant clarifications (cross-feature/module boundaries, security/telemetry strategy, major NFR trade-offs), create or update an ADR under `docs/specs/5-decisions`/` using [docs/specs/templates/adr-template.md](docs/specs/templates/adr-template.md) after updating the spec, then mark the corresponding open-questions row as resolved with links to the spec sections and ADR ID. Tidy lightweight ambiguities locally and note the adjustment in the governing spec/plan.
- Generate or refresh the feature plan (`docs/specs/4-architecture/features/<NNN>-<feature-name>/plan.md`) only after the specification is current and high-/medium-impact clarifications are resolved and recorded in the spec (plus ADRs where required).
- Maintain a per-feature tasks checklist at `docs/specs/4-architecture/features/<NNN>-<feature-name>/tasks.md` that mirrors the plan, orders tests before code, and keeps planned increments ≤90 minutes by preferring finer-grained entries and documenting sub-steps when something nears the limit.
- When revising a specification, only document fallback or compatibility behaviour if the user explicitly asked for it; if instructions are unclear, pause and request confirmation instead of assuming a fallback.
- Run the analysis gate in [docs/specs/5-operations/analysis-gate-checklist.md](docs/specs/5-operations/analysis-gate-checklist.md) once spec, plan, and tasks agree; address findings before implementation.
- Treat legacy per-feature `## Clarifications` sections as removed; do not reintroduce them. Resolved clarifications must be folded into the spec’s normative sections (requirements, NFR, behaviour/UI, telemetry/policy), with history captured via [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), ADRs under `docs/specs/6-decisions/`, and session/plan logs.

## Session Kickoff
- Follow [docs/specs/5-operations/runbook-session-reset.md](docs/specs/5-operations/runbook-session-reset.md) whenever a chat session starts without prior context.
- Begin every fresh interaction by summarising roadmap status, feature plan progress, and open questions for the user.
- Request clarification on outstanding questions before planning or implementation; log any new questions immediately.

> Quick reference: See [docs/specs/5-operations/session-quick-reference.md](docs/specs/5-operations/session-quick-reference.md) for the Session Kickoff Checklist and handoff prompt template.
- Maintain [docs/specs/_current-session.md](docs/specs/_current-session.md) as the single live snapshot across active chats; always review/update it before closing a session.
- Feature ownership quick cues (Batch P3): Feature 009 now covers operator console/UI docs, Feature 010 owns documentation & knowledge automation, Feature 011 governs AGENTS/runbooks/hooks, Feature 012 centralises core cryptography & persistence docs, and Feature 013 aggregates toolchain/quality automation guidance.

## SDD Feedback Loops
- Specification-Driven Development (SDD) is the default cadence. Anchor every increment in an explicit specification, aligned with the [GitHub Spec Kit reference](https://github.com/github/spec-kit/blob/main/spec-driven.md).
- **Update specs before code.** For every task, refresh the relevant feature plan and note open questions; only move forward once the plan reflects the desired change.
- **Test-first cadence.** Write or extend executable specifications (unit, behaviour, or scenario tests) ahead of implementation, confirm they fail, and then drive code to green before refactoring.
- **Branch coverage upfront.** When outlining a feature, list the expected success, validation, and failure branches and add thin failing tests for each path before writing implementation code so coverage grows organically.
- **Reflection checkpoint.** After loops close, record lessons, coverage deltas, and follow-ups back into the feature plan or roadmap to keep the spec-driven history auditable.

## During Implementation
- **Follow coding conventions.** Adhere to all standards defined in [docs/specs/3-reference/coding-conventions.md](docs/specs/3-reference/coding-conventions.md) for PHP, Vue3/TypeScript, testing, and documentation. Key requirements: license headers in new files, strict comparison (`===`), no `empty()`, `in_array()` with third parameter true, snake_case variables, PSR-4 standard, test base classes (`AbstractTestCase` for Unit, `BaseApiWithDataTest` for Feature_v2).
- **Sync context to disk.** Update the roadmap ([docs/specs/4-architecture/roadmap.md](docs/specs/4-architecture/roadmap.md)), feature specs, feature plans, and tasks documents as progress is made. Use ADRs only for final decisions.
- **Check off tasks immediately.** Mark each task `[x]` in the feature's `tasks.md` as soon as it passes verification. Do not batch task completions—update the checklist after every individual task so progress is always visible.
- **No unapproved deletions.** Never delete files or directories—especially via recursive commands or when cleaning untracked items—unless the user has explicitly approved the exact paths in the current session. Features may be developed in parallel across sessions, so untracked files or directories can appear without warning; surface them for review instead of removing them.
- **Tests are compulsory.** Always run `phpunit`. If a test remains red, disable it with a TODO, note the reason, and capture the follow-up in the relevant plan.
- **Formatter policy.** Spotless now uses Palantir Java Format 2.78.0 with a 120-character wrap; configure IDE formatters to match before pushing code changes.
- **Maintain the knowledge map.** Add, adjust, or prune entries in [docs/specs/4-architecture/knowledge-map.md](docs/specs/4-architecture/knowledge-map.md) whenever new modules, dependencies, or contracts appear.
- **Straight-line increments.** Keep each increment's control flow flat by delegating validation/normalisation into tiny pure helpers that return simple enums or result records, then compose them instead of introducing inline branching that inflates the branch count per change.
- **RCI self-review.** Before hand-off, review your own changes, rerun checks, and ensure documentation/test coverage matches the behaviour.
- **Lint checkpoints.** When introducing new helpers/utilities or editing files prone to style violations (records, DTOs, generated adapters), run the narrowest applicable lint target before the full pipeline (for example `phpstan`). Note the command in the related plan/task so every agent repeats it.
- **Commit protocol.** Once an increment passes static analysis (`phpstan`), tests (`phpunit`) and formatting (`php-cs-fixer`), prepare the commit for the operator instead of executing it yourself. Stage the requested files (or list the exact paths that must be staged), run [./scripts/codex-commit-review.sh](./scripts/codex-commit-review.sh) (or equivalent) to obtain a Conventional Commit message that includes a `Spec impact:` line whenever docs and code change together, then present the staged summary plus copy/paste-ready `git commit …` command. Call out any required environment knobs (for example, `timeout_ms >= 300000` for the managed hooks). The human operator runs those commands locally unless they explicitly delegate the execution back to you.
  - Pre-commit hooks and the managed quality pipeline routinely take longer than two minutes. When invoking `git commit` (or any command that triggers that pipeline) via the CLI tool, always specify `timeout_ms >= 300000` so the process has enough time to finish cleanly.
- **Dependencies.** **Never add or upgrade libraries without explicit user approval.** When granted, document the rationale in the feature plan. Dependabot opens weekly update PRs—treat them as scoped requests that still require owner approval before merging.
- **No surprises.** Avoid destructive commands (e.g., `rm -rf`, `git reset --hard`) unless the user requests them. Stay within the repository sandbox.

## Guardrails & Governance
- **Backward-compat stance.** Treat every interface (REST, CLI, UI/HTML+JS, programmatic APIs, and any future facades) as greenfield. Do not add fallbacks, shims, or legacy checks unless the user explicitly instructs you to do so for the current task.
- **Intent logging.** Capture prompt summaries, command sequences, and rationale in the active feature plan or an appendix referenced from it so downstream reviewers know how the change was produced.
- **Escalation policy.** Propose risky refactors, persistence changes, or dependency updates to the user before touching code—record approvals in the relevant plan.

## Tracking & Documentation
- **Implementation plans.** Keep high-level plans in [docs/specs/4-architecture/roadmap.md](docs/specs/4-architecture/roadmap.md), store each feature’s spec/plan/tasks inside `docs/specs/4-architecture/features/<NNN>-<feature-name>/`, and remove plans once work is complete.
- **Open questions.** Log high- and medium-impact open questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md) and remove each row as soon as it is resolved, ensuring the answer is captured first in the governing spec’s normative sections and, for high-impact clarifications, in an ADR.
- **Decisions.** Record only confirmed architectural decisions and architecturally significant clarifications as ADRs under `docs/specs/6-decisions/`, using [docs/specs/templates/adr-template.md](docs/specs/templates/adr-template.md), and reference those ADR IDs from the relevant spec sections and (when applicable) the open-questions log.
- **Local overrides.** If a subdirectory requires different instructions, add an [AGENTS.md](AGENTS.md) there and reference it from the roadmap/feature plan.
- **Quality gates.** Track upcoming additions for contract tests, mutation analysis, and security/“red-team prompt” suites in the plans until automated jobs exist.

## After Completing Work
- Treat "completing work" as finishing any self-contained increment that was scoped during planning to fit within ≤90 minutes, even if actual execution takes longer. The checklist below fires after every increment that ends with a passing build.
- **Validate task checklist.** Before committing, confirm every completed task in `tasks.md` is marked `[x]` and the roadmap status reflects current progress.
- **Commit after each feature.** After completing each feature (all tests green), commit immediately before starting the next feature. This ensures atomic, reviewable history and prevents work loss.
- **Quality gate for Laravel projects:** Run formatting, tests, and static analysis before considering work complete. Only run checks for file types that were modified:
  
  **PHP changes** (if `.php` files were modified):
  1. `vendor/bin/php-cs-fixer fix` — apply PHP code style fixes
  2. `php artisan test` — all tests must pass
  3. `make phpstan` — PHPStan level 6 minimum; fix all errors before committing
  
  **Frontend changes** (if `.vue`, `.ts`, `.js`, or `.css` files were modified):
  1. `npm run format` — apply frontend code formatting with Prettier
  2. `npm run check` — all frontend tests must pass
  
  **Full quality gate** (if both PHP and frontend files were modified):
  1. `vendor/bin/php-cs-fixer fix`
  2. `npm run format`
  3. `npm run check`
  4. `php artisan test`
  5. `make phpstan`
- Update/close entries in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md).
- Summarise any lasting decisions in the appropriate ADR (if applicable).
- Publish prompt and tool usage notes alongside the feature plan update so future agents understand how the iteration unfolded.

## CRITICAL: Database Rules

### NEVER RUN THESE COMMANDS:
- `php artisan migrate:fresh` - **WILL DESTROY THE PRODUCTION DATABASE**
- `php artisan migrate:reset` - **WILL DESTROY THE PRODUCTION DATABASE**
- `php artisan db:wipe` - **WILL DESTROY THE PRODUCTION DATABASE**
- Any command that resets or wipes the database

### Why This Matters:
The test suite uses SQLite (`database/database.sqlite`) as configured in `phpunit.xml`:
```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value="database/database.sqlite"/>
```

However, the main application uses a **different database** (typically MySQL/PostgreSQL). Running migration commands without specifying the test database will affect the **production/development database**.

### Safe Database Operations:
1. **Running tests**: Just run `php artisan test` - migrations are applied automatically to the SQLite test database
2. **Resetting test database**: Delete and recreate `database/database.sqlite` file only
3. **New migrations**: Create the migration file, then run tests - they will be applied automatically

### If You Need to Reset the Test Database:
```bash
rm database/database.sqlite
touch database/database.sqlite
# Then run tests - migrations will be applied automatically
```

## Testing

- Run tests with: `php artisan test --filter=TestName`
- Tests use `DatabaseTransactions` trait - changes are rolled back after each test
- The test database is separate from the development database