# Feature 096 Tasks – Vite+ Migration

_Status: Complete (CI run pending, T-096-14 verified by the next Dependency Review run)_  
_Last updated: 2026-10-16_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to the feature's [open-questions.md](open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/6-decisions/` reflect the clarified behaviour.

## Checklist

- [x] T-096-01 – Assertion script and baseline (S-096-01 … 07, S-096-12, S-096-16).  
  _Intent:_ scratchpad script that captures the baseline (`npm ci`, `npm run build` manifest keys + asset count, `npm run check-formatting`, `npm run lint`, `npm run check`) and, after the migration, asserts `vp install --frozen-lockfile`, `vp fmt --check`, `vp lint` exit 0, `vp check` exit codes on a broken scratch file, `vp run typecheck`, `vp run build` manifest parity, the two `.d.ts` files, the hook rehearsal and the `npm run` grep gate.  
  _Verification commands:_ script runs red against the current tree (no `vp` scripts yet).

- [x] T-096-02 – Dependencies and migration run (F-096-01, F-096-02, S-096-01).  
  _Intent:_ remove `@vitest/eslint-plugin` and its ESLint block; `vp migrate --no-interactive --no-hooks --no-agent --no-editor`; `vp install`; delete `env.d.ts`.  
  _Verification commands:_  
  - `vp toolchain`  
  - `npm ls eslint prettier` (nothing)

- [x] T-096-03 – `vite.config.ts` toolchain blocks (F-096-03, F-096-04, F-096-05, S-096-02, S-096-03).  
  _Intent:_ one `toolchain` constant (`lint`, `fmt`, `staged`) spread into both returns; imports from `vite-plus` in both config files; `fmt` options; scope `ignorePatterns`; `no-unused-vars` options; webauthn ignore; `_res`/`_isSsrBuild`/`_isPreview`; `@ts-expect-error`.  
  _Verification commands:_  
  - `vp lint vite.config.ts vite.embed.config.ts`  
  - `vp fmt --check` (visits only in-scope files)

- [x] T-096-04 – One-time reformat (F-096-04, N-096-02, S-096-02).  
  _Intent:_ `vp fmt`; confirm the diff is `resources/js/lychee.d.ts` plus the root configs of the scope.  
  _Verification commands:_  
  - `git diff --stat`  
  - `vp fmt --check`

- [x] T-096-05 – Lint green and idempotence (F-096-05, S-096-03, S-096-14, S-096-15).  
  _Intent:_ `vp lint` exits 0 (warnings recorded in the plan); `vp migrate` reports no change.  
  _Verification commands:_  
  - `vp lint`  
  - `vp migrate --no-interactive --no-hooks --no-agent --no-editor` (no diff)

- [x] T-096-06 – `package.json` scripts (F-096-06, F-096-07, S-096-05, S-096-06).  
  _Intent:_ `typecheck` + `pretypecheck`, `dev`, `build`, `build:embed`, `build-dev`; remove `check`, `precheck`, `check-formatting`, `lint`, `format`.  
  _Verification commands:_  
  - `vp run typecheck`  
  - `vp run build` (manifest parity)

- [x] T-096-07 – `scripts/generate-js-types.sh` (F-096-08, S-096-07).  
  _Verification commands:_  
  - `rm -f auto-imports.d.ts components.d.ts && bash scripts/generate-js-types.sh`

- [x] T-096-08 – Pre-commit script (F-096-12, S-096-12).  
  _Intent:_ `vp staged` replaces the Prettier step; hint text; rehearsal on a staged scratch file.  
  _Verification commands:_  
  - `bash scripts/pre-commit` with a staged unformatted `.ts` and a staged `.yml`

- [x] T-096-09 – Makefile `formatting`, Dependabot group, VS Code recommendations (F-096-11, F-096-14, F-096-15, S-096-13).  
  _Verification commands:_  
  - `make formatting` (frontend step)  
  - review of `.github/dependabot.yml`, `.vscode/extensions.json`

- [x] T-096-10 – `js_check.yml` (F-096-10, F-096-13, S-096-09).  
  _Intent:_ `setup-vp` pinned by SHA, `run-install: false`, `vp install --frozen-lockfile`, `vp check`, `vp run typecheck`, `vp run build`.  
  _Verification commands:_ YAML review; CI run (owner).

- [x] T-096-11 – `CICD.yml` path filter and dist job (F-096-10, F-096-13, S-096-10).  
  _Verification commands:_ YAML review; CI run (owner).

- [x] T-096-12 – Documentation (F-096-09, F-096-16, F-096-17, S-096-16).  
  _Intent:_ AGENTS.md, coding conventions, quality gate, session quick reference, Contribute.md, frontend architecture, customize-v8-nuxt-ui, README, templates, knowledge map, roadmap; ADR-096-01.  
  _Verification commands:_  
  - `grep -rn "npm run\|prettier\|eslint" AGENTS.md README.md docs/Contribute.md docs/specs/3-reference/coding-conventions.md docs/specs/3-reference/frontend-architecture.md docs/specs/5-operations/quality-gate.md docs/specs/5-operations/session-quick-reference.md docs/specs/2-how-to/customize-v8-nuxt-ui.md docs/specs/templates/feature-plan-template.md docs/specs/templates/feature-tasks-template.md` (nothing)

- [x] T-096-13 – Verification and drift gate (NFR-096-01 … 07, S-096-06, S-096-08, S-096-11).  
  _Intent:_ assertion script against the migrated tree; `vp dev` local-dev check; Docker build (local or CI); drift gate report in the plan; commit prepared for the owner.  
  _Verification commands:_  
  - assertion script  
  - `vp check && vp run typecheck && vp run build`

- [x] T-096-14 – Dependency Review allow-list (F-096-10, S-096-17).  
  _Intent:_ allow-list the 16 Vite advisories the dependency graph attaches to the `vite` alias, with the explanation in the workflow comment; verify each ID against the advisory API.  
  _Verification commands:_  
  - `python3 -c 'import yaml; yaml.safe_load(open(".github/workflows/dependency-review.yml"))'`  
  - `gh api advisories/<id>` for each ID (package `vite`, range open below 1.x)  
  - Dependency Review check on PR #4891 after the push (owner)

## Notes / TODOs

- The local `.git/hooks/pre-commit` is a hand copy of `scripts/pre-commit`: refresh it (`cp scripts/pre-commit .git/hooks/pre-commit`) so the hook runs `vp staged`; the old copy only prints "Please install prettier" and continues.

- Global `vp` is not on `PATH` in non-interactive shells on the development machine: `export PATH="$HOME/.local/share/vite-plus/bin:$PATH"` (or use `node_modules/.bin/vp` once installed).
- Never run two installs or builds concurrently in the same checkout (`vp install --frozen-lockfile` recreates `node_modules`).
