# Session Quick Reference

Use this appendix to accelerate hand-offs and new-session spin-up. Update it whenever the standard workflow changes so every agent can copy/paste the same artefacts.

## Session Kickoff Checklist
- [ ] Run `git status -sb` to review branch, staged changes, and repo cleanliness.
- [ ] Confirm environment prerequisites: PHP 8.3+, Composer installed, npm/Node.js available; log the command/output in `_current-session.md` for traceability.
- [ ] Review current context: latest roadmap entry, active specification, feature plan, tasks checklist, and [docs/specs/4-architecture/open-questions.md](../4-architecture/open-questions.md).
- [ ] Confirm that the active feature spec already encodes known decisions directly in its requirements/NFR/behaviour/telemetry sections (no per-feature `## Clarifications` appendices).
- [ ] If new clarifications arise, record them in [docs/specs/4-architecture/open-questions.md](../4-architecture/open-questions.md), pause planning until answers are agreed, then update the spec sections and mark the questions as resolved with links to those sections; create or reference an ADR for architectural or other high‑impact decisions.
- [ ] Check feature organization under `docs/specs/4-architecture/features/<NNN>-<feature-name>/` where NNN is a 3-digit number (001, 002, 003, etc.) and feature-name is hyphen-separated (see [docs/specs/4-architecture/spec-guidelines/feature-numbering-conventions.md](../4-architecture/spec-guidelines/feature-numbering-conventions.md)).
- [ ] If scope introduces or modifies UI, confirm the spec includes an ASCII mock-up ([docs/specs/4-architecture/spec-guidelines/ui-ascii-mockups.md](../4-architecture/spec-guidelines/ui-ascii-mockups.md)).
- [ ] If the last build is stale or after syncing, run quality checks to ensure the baseline is green (capture or resolve any failures before proceeding):
  - PHP changes: `vendor/bin/php-cs-fixer fix && php artisan test && make phpstan`
  - Frontend changes: `npm run format && npm run check`
- [ ] When changing dependencies (approved): update `composer.json` or `package.json`, run `composer update` or `npm install`, and verify tests still pass.
- [ ] Check [docs/specs/_current-session.md](../_current-session.md) for the active snapshot; refresh it with today's status and update the `## Next suggested actions` section before you hand off.
- [ ] Confirm whether the user granted a compatibility exception; default is no fallbacks for any interface unless explicitly requested.

## Commit Protocol Reminder
- Default behavior: after a self-contained increment is green (quality checks pass), assistants prepare a commit handoff without waiting for an explicit "commit" prompt (unless the operator explicitly asks to defer commits). Stage the repository, verify checks are green, and gather the staged diff for review.
- Before presenting `git commit` / `git push` commands, include an Agent Delivery Optimization (ADO) note (max 5 bullets, action-only) proposing process/guardrail improvements that prevent regressions and speed the next run.
- Run `./scripts/codex-commit-review.sh` (or equivalent) to obtain a gitlint-compliant Conventional Commit message. When code and docs change together, include a `Spec impact:` line that explicitly lists the impacted artefacts (spec/plan/tasks/ADR paths). Commit messages must not contain semicolons; if a body needs multiple lines, compose it using multiple `-m` flags. Do **not** use yes/no flags. Then output copy/paste-ready fenced-code-block `git commit …` and `git push …` commands (with any required timeouts noted). The operator executes those commands locally unless they explicitly delegate execution.
- Pre-commit hooks and the managed quality pipeline routinely take longer than two minutes. When invoking `git commit` via the CLI tool, always specify `timeout_ms >= 300000` so the process has enough time to finish cleanly.

## Handoff Prompt Template
```
You're resuming work on Lychee photo management system. Core context:

- Environment: repo at /home/biv/Documents/Projects/Lychee, PHP 8.3+, Laravel framework, Vue3/TypeScript frontend. Follow docs/specs/3-reference/coding-conventions.md and AGENTS.md.
- Current status: roadmap entry #[…], spec […], plan […], tasks […]. Last green build(s): [commands + date].
- Recent increments: [brief bullet list of the last completed increments, noting key files touched and outcomes].
- Pending scope: [next planned increments/tasks], including failing tests to stage, implementation goals, telemetry/observability requirements, and documentation updates (keep this aligned with the `## Next suggested actions` section in `docs/specs/_current-session.md`).
- Git state: [branch], staged files [list or "clean"], outstanding TODOs [if any].
- ADO notes: [max 5 bullets of concrete process improvements to prevent regressions and accelerate the next run].
- Next steps you should take now: [ordered checklist, e.g., "1. Stage failing tests for … 2. Implement … 3. Update docs …"].
- Reminders: keep planned tasks/increments ≤90 minutes by organising work into logical slices (execution may run longer if needed), update docs (spec/plan/tasks/roadmap), run quality checks before commits, and document open questions in `docs/specs/4-architecture/open-questions.md`.
```
    
> Tip: Retros for new sessions should paste the filled template into the opening message so successors inherit complete context.

## Reminders
- Specification-Driven Development governs every change: verify the spec is current, stage failing tests, then execute the plan.
- Never document or implement fallback paths in specs, plans, or code without explicit user approval.
- Follow coding conventions from [docs/specs/3-reference/coding-conventions.md](../3-reference/coding-conventions.md):
  - PHP: PSR-4, strict comparison (`===`), no `empty()`, snake_case variables, license headers
  - Vue3: Composition API, `function` declarations (not arrow functions), `.then()` (not async/await in components)
  - Tests: Unit tests extend `AbstractTestCase`, Feature tests extend `BaseApiWithDataTest`

## Common Command Snippets

### Repository & Git
```bash
git status -sb                           # Repository snapshot
git diff --stat                          # Summarise local changes
git diff --cached --stat                 # Summarise staged changes
```

### PHP Backend Quality Checks
```bash
vendor/bin/php-cs-fixer fix              # Apply code style fixes (PSR-4)
php artisan test                         # Run full PHPUnit test suite
php artisan test --filter=AlbumTest      # Run specific test class
make phpstan                             # Static analysis (PHPStan level 6)
vendor/bin/phpstan analyse --memory-limit=2G  # PHPStan with more memory
composer dump-autoload                   # Rebuild autoload files
```

### Frontend Quality Checks
```bash
npm run format                           # Apply Prettier formatting
npm run check                            # ESLint + TypeScript + tests
```

### Laravel Development
```bash
php artisan migrate                      # Run pending migrations
php artisan migrate:fresh                # Fresh database (destroys data!)
php artisan tinker                       # Interactive REPL
php artisan typescript:transform         # Generate TypeScript types
php artisan route:list                   # List all routes
php artisan config:cache                 # Cache configuration
php artisan cache:clear                  # Clear application cache
```

### Dependency Management
```bash
composer install                         # Install PHP dependencies
composer update                          # Update PHP dependencies
composer show                            # List installed packages
npm install                              # Install frontend dependencies
npm update                               # Update frontend dependencies
```

### Full Quality Gate (Combined)
```bash
# PHP + Frontend changes
vendor/bin/php-cs-fixer fix && npm run format && npm run check && php artisan test && make phpstan

# PHP only
vendor/bin/php-cs-fixer fix && php artisan test && make phpstan

# Frontend only
npm run format && npm run check
```

### Documentation
```bash
# Validate internal links (if script exists)
./scripts/validate-docs.sh

# Find all TODO/FIXME comments
grep -r "TODO\|FIXME" app/ resources/js/ tests/ --exclude-dir=vendor
```

## Quality Gate Reference

See [quality-gate.md](quality-gate.md) for comprehensive details on:
- When to run each check
- Troubleshooting common issues
- CI integration
- Performance optimization

Quick reference:
- **PHP changes:** `vendor/bin/php-cs-fixer fix` → `php artisan test` → `make phpstan`
- **Frontend changes:** `npm run format` → `npm run check`
- **Both:** Run all checks in sequence before committing

---

*Last updated: December 22, 2025*
