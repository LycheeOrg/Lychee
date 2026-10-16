# Feature 096 – Vite+ Migration

| Field | Value |
|-------|-------|
| Status | Implemented 2026-10-16 (drift gate in plan; CI run pending) |
| Last updated | 2026-10-16 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #096 |
| Decisions | [ADR-096-01](../../../6-decisions/ADR-096-01-vite-plus-single-frontend-toolchain.md) – Vite+ as the single frontend toolchain; Q-096-01 … 06 resolved in [open-questions.md](open-questions.md) |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

The frontend toolchain is Vite 8 for the two bundles (`resources/js/app.ts`, `resources/js/app-v8.ts`) and the embed widget (`vite.embed.config.ts`), ESLint 10 with `eslint-plugin-vue` and `typescript-eslint` for linting, Prettier 3.9 for formatting, `vue-tsc` for type-checking, and npm for dependencies and scripts. Every gate (AGENTS.md, CI `js_check.yml`, the pre-commit script, the Makefile, the Dockerfiles) names those tools and the `npm run …` scripts that wrap them.

This feature replaces that set with **Vite+** (`vp`, package `vite-plus` 1.3.0): one CLI whose local package bundles Vite 8.3.4 (`@voidzero-dev/vite-plus-core`, the same Vite version the repository builds with today), Oxlint 1.88 (lint), Oxfmt 0.73 (format, with Prettier bundled for `.vue` files), lint-staged (`vp staged`) and a task runner (`vp run`). ESLint, Prettier and their configuration packages leave; lint and format configuration moves into `vite.config.ts`. `vue-tsc` stays: Vite+'s type-check path (tsgolint) does not type-check Vue single-file components.

Measured on a scratch copy of the repository (2026-10-16, `vp migrate` then the adjustments of FR-096-03 … 05): the production build produces the same manifest entries and asset count as today; Oxfmt leaves 914 of 915 frontend files byte-identical (the generated `lychee.d.ts` differs in two union-type hunks); Oxlint reports no error on the current sources. The `vite-plus` package installs its own `vp` binary under `node_modules/.bin`, so `npm run build`, the Dockerfile node stage and the Makefile keep working without the global installer.

Affected layers: `package.json` and lockfile, `vite.config.ts` / `vite.embed.config.ts`, `scripts/generate-js-types.sh`, `scripts/pre-commit`, Makefile, `.github/workflows/js_check.yml` and `CICD.yml`, `.github/dependabot.yml`, `.vscode/extensions.json`, developer documentation. No PHP, no API, no shipped behaviour change.

## Goals

- G1 – One toolchain command surface for the frontend: `vp dev`, `vp build`, `vp check` (format + lint), `vp fmt`, `vp lint`, `vp staged`, `vp run typecheck`.
- G2 – Parity: identical build output composition, no formatting churn beyond generated files, no new lint error on the current sources.
- G3 – Every gate (AGENTS.md, CI, hooks, Makefile, Docker) runs through the new commands, and building from source still needs only Node.js and the package manager.
- G4 – Developer documentation names the new commands only.

## Non-Goals

- N-096-01 – Frontend unit tests or a Vitest setup. `vp test` stays unused; `@vue/test-utils`, `jsdom`, `@types/jsdom` are untouched (follow-up, needs approval to remove).
- N-096-02 – Type-aware lint rules (`lint.options.typeAware`, `typeCheck`, tsgolint). Off; follow-up (72 `no-floating-promises` and 14 `no-redundant-type-constituents` findings today).
- N-096-03 – Vue template lint rules that Oxlint cannot run (55 rules of `eslint-plugin-vue` need template parsing). Accepted (Q-096-01): templates are covered by `vue-tsc` and the Vue compiler only; the rules are listed in Appendix A for a later re-enable.
- N-096-04 – Changing the package manager or the lockfile format: npm and `package-lock.json` stay (Q-096-02).
- N-096-05 – Rewriting the `npm run …` commands recorded in completed features' `plan.md` / `tasks.md`. They record what was run (Q-096-06).
- N-096-06 – `tsconfig.json` formatting (hand-aligned comments; excluded from the format scope).
- N-096-07 – The Vite build configuration itself (plugins, chunking, aliases, the `rollup` override, `optionalDependencies`): unchanged.
- N-096-08 – PHP tooling (php-cs-fixer, phpstan, PHPUnit) and the PHP steps of the hooks.

## Definitions

- **Toolchain scope.** The files `vp check`, `vp fmt` and `vp lint` cover when called without paths (Q-096-04): `resources/js/**`, `resources/sass/**`, `e2e/**`, `vite.config.ts`, `vite.embed.config.ts`, `playwright.config.ts`. It is expressed as an ignore list (`TOOLCHAIN_SCOPE` in `vite.config.ts`, used by both the `fmt` and the `lint` block): non-frontend file types anywhere (`**/*.json`, `**/*.md`, `**/*.yml`, `**/*.yaml`, `**/*.toml`, `**/*.html`), the two generated root `.d.ts` files, every root directory other than `resources` and `e2e`, and `resources/fonts`, `resources/img`, `resources/views`. Negated re-includes (`/*` followed by `!/vite.config.ts`) do not bring root files back into Oxfmt's directory walk, so the list is explicit, and a new root directory holding frontend-like files is added to it. `tsconfig.json`, `public/**`, `.github/**`, `.claude/**`, docs, YAML and JSON files are outside the scope; `e2e/example.spec.ts` is gitignored and therefore skipped by the walk.
- **Bootstrap.** The commands that install dependencies and build the bundles on a machine without the global `vp` installer (Makefile, Dockerfiles, `scripts/post-merge`, the CI dist job).
- **Built-in command.** A `vp` subcommand implemented by Vite+ (`vp check`, `vp fmt`, `vp lint`, `vp dev`, `vp build`, `vp staged`, `vp test`). It cannot be overridden by a `package.json` script of the same name; `vp run <script>` runs the script.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-096-01 | `vite-plus` is an exact-version entry of `dependencies` (`1.3.0`), next to `vite` and the build plugins, because the Dockerfile node stage installs with `NODE_ENV=production` and npm then omits devDependencies; `vite` stays in `dependencies`, aliased to `npm:@voidzero-dev/vite-plus-core@1.3.0`, with the same alias under `overrides.vite`; the three versions are always equal. The lockfile is regenerated with `npm install` from the previous lockfile, so only the added and removed packages change (no transitive bump). | `vp toolchain` lists local `vite-plus@1.3.0` bundling `vite@8.3.4`, `oxlint@1.88.0`, `oxfmt@0.73.0`. | `vp --version` shows the local tools, not "Not found". | A mismatch between the alias and the devDependency is repaired with `vp migrate` (FR-096-16). | none | Vite+ migration rules |
| FR-096-02 | Removed devDependencies: `eslint`, `eslint-plugin-vue`, `@typescript-eslint/parser`, `@vue/eslint-config-prettier`, `@vue/eslint-config-typescript`, `@vitest/eslint-plugin`, `prettier`. Deleted files: `.prettierrc.json`, `eslint.config.ts`. Kept: `vue-tsc`, `typescript`, `@vue/tsconfig`, `sass`, `playwright`, `@playwright/test`, every runtime dependency, `overrides.rollup`, `optionalDependencies`. `package-lock.json` is regenerated by `vp install`. `// eslint-disable-next-line …` directives become `// oxlint-disable-next-line …` (12 files; the rule names keep their `@typescript-eslint/` prefix, which Oxlint accepts); `// prettier-ignore` directives stay (Oxfmt honours them). | `npm ls eslint prettier` finds nothing. | `vp migrate` blocks while `@vitest/eslint-plugin` is declared ("cannot determine the original Vitest version"): it is removed first. | — | none | Probe 2026-10-16 |
| FR-096-03 | Configuration lives in `vite.config.ts`: `defineConfig` and the config types are imported from `vite-plus` in both config files; the `lint`, `fmt` and `staged` blocks are declared once (a `toolchain` constant) and spread into every config object the exported function returns (local-development and build paths). No `.oxlintrc.json`, no `.oxfmtrc.json`, no `env.d.ts` module shim (vue-tsc resolves `.vue` modules). `vite.embed.config.ts` carries no lint/fmt block. | `vp lint vite.config.ts` reports no `vite-plus/prefer-vite-plus-imports` error. | `vp migrate` writes the blocks into each `return` and adds `env.d.ts`; both are rewritten by hand. | — | none | Vite+ lint/fmt guides |
| FR-096-04 | Formatter (`fmt` block): `printWidth: 150`, `semi: true`, `useTabs: true`, `tabWidth: 4`, `insertFinalNewline: true`, `sortPackageJson: false`, `ignorePatterns` limiting the toolchain scope (Definitions). `.vue` files are formatted by the Prettier bundled in Oxfmt, `.ts` and `.css` natively. `.editorconfig` is unchanged; the explicit options override its `indent_size`, `indent_style` and `insert_final_newline` for the formatter (Oxfmt reads them, Prettier did not read `insert_final_newline`). | First `vp fmt` run changes `resources/js/lychee.d.ts` (union members on their own lines with a leading `\|`) and, newly in scope, `vite.embed.config.ts` and `playwright.config.ts` (double quotes, tabs); every other frontend file is byte-identical; `vp fmt --check` is green afterwards. | Without the explicit options every file loses its final newline (710 files) and Vue attribute lists re-wrap (205 files). | — | none | Probe 2026-10-16 |
| FR-096-05 | Linter (`lint` block): `plugins: ["oxc", "typescript", "unicorn", "vue"]` (Q-096-05: Oxlint's default `oxc` and `unicorn` plugins kept, type-aware rules deferred per N-096-02), `categories.correctness: "warn"`, `env.builtin: true`, the error rules mapped by `vp migrate` from the ESLint configuration (30 `vue/*` script rules, `no-array-constructor`, `no-unused-expressions`, `no-unused-vars` with the `^_` ignore patterns and `ignoreRestSiblings`, 17 `typescript/*` rules; Appendix A), the TypeScript overrides that switch off core rules the compiler already enforces, `jsPlugins: vite-plus/oxlint-plugin` with `vite-plus/prefer-vite-plus-imports: error`, `options.typeAware: false`, `options.typeCheck: false`, `ignorePatterns` = toolchain scope plus `resources/js/vendor/webauthn/**`. `.vue` files are linted on their `<script>` blocks; template rules are not run (Q-096-01, N-096-03). | `vp lint` exits 0 on the migrated tree (18 warnings today: `unicorn/no-useless-spread` 7, `unicorn/no-new-array` 7, `oxc/only-used-in-recursion` 2, `eslint/no-useless-escape` 2). | `no-unused-vars` without the `caughtErrorsIgnorePattern` option reports the two `_error` catch parameters of `axios-config.ts` as errors; `vite.config.ts` needs `_res`, `_isSsrBuild`, `_isPreview` and `@ts-expect-error`. | — | none | Probe 2026-10-16 |
| FR-096-06 | Type-check stays `vue-tsc`: script `typecheck` = `vue-tsc --noEmit -p tsconfig.json --composite false --skipLibCheck`, with `pretypecheck` = `bash scripts/generate-js-types.sh` (`vp run` executes `pre*` scripts). The `check` script is removed: `vp check` is the built-in format + lint command and `vp check` never runs tsgolint (FR-096-05 options). | `vp run typecheck` regenerates `auto-imports.d.ts` / `components.d.ts`, then type-checks. | `vp check` prints a note when a `check` script exists ("use `vpr check`"); the rename removes the ambiguity. | — | none | Probe 2026-10-16 |
| FR-096-07 | `package.json` scripts: `dev` = `vp dev`; `build` = `vp build && vp build --config vite.embed.config.ts`; `build:embed` = `vp build --config vite.embed.config.ts`; `build-dev` = `vp build --mode development`; `typecheck`, `pretypecheck` (FR-096-06); `vca` unchanged. Removed: `check`, `precheck`, `check-formatting`, `lint`, `format` (built-ins `vp check`, `vp fmt`, `vp lint` replace them). | `npm run build` and `vp run build` produce `public/build` and `public/embed`. | — | — | none | G1 |
| FR-096-08 | `scripts/generate-js-types.sh` runs `node_modules/.bin/vp build --mode development --outDir "$build_out" --emptyOutDir --logLevel warn` (same flags as today, package-manager independent, no global `vp` needed). | Both `.d.ts` files exist after the run. | — | Missing files → exit 1 (unchanged). | none | Probe 2026-10-16 |
| FR-096-09 | Quality gate commands for frontend changes: locally `vp check --fix` (format, safe lint fixes) then `vp run typecheck`; in CI `vp check`, `vp run typecheck`, `vp run build`. These replace `npm run format`, `npm run check`, `npm run lint`, `npm run check-formatting`, `npm run build` in AGENTS.md, `docs/specs/3-reference/coding-conventions.md`, `docs/specs/5-operations/quality-gate.md`, `docs/specs/5-operations/session-quick-reference.md`, `docs/Contribute.md`, `docs/specs/3-reference/frontend-architecture.md`, `docs/specs/2-how-to/customize-v8-nuxt-ui.md` and the plan/tasks templates (`README.md` keeps "Composer, and npm": npm stays the bootstrap, FR-096-11). | A `grep -rn "npm run" ` over those files finds nothing. | — | — | none | G3, G4 |
| FR-096-10 | CI: `js_check.yml` replaces `actions/setup-node` + `npm ci -D` with `voidzero-dev/setup-vp` pinned by commit SHA (`68f39c78aaf39be468cabd2afb09c162574c84af`, v1.21.2) using `node-version: ${{ matrix.node-version }}` (24 and 26), `cache: true`, `run-install: false`; steps `vp install --frozen-lockfile`, `vp check`, `vp run typecheck`, `vp run build`. `CICD.yml` path filter: `eslint.config.ts` and `.prettierrc.json` removed, the toolchain-scope files added. The dist job (`make clean dist`) moves from Node 20 to Node 26 (Vite+ needs `^22.18.0 \|\| ^24.11.0 \|\| >=26.0.0`); it keeps npm (`make clean dist` → `npm ci`, `npm run build`; Q-096-02). | The JS job is green on Node 24 and 26; the dist job builds `Lychee.zip`. | `vp install --frozen-lockfile` fails when the lockfile is stale (npm ci semantics). | — | none | Vite+ CI guide, setup-vp action.yml |
| FR-096-11 | Bootstrap: Makefile `npm-build`, the Dockerfile node stages (`node:26-alpine`, `npm ci --no-audit`, `npm run build`) and `scripts/post-merge` keep npm (Q-096-02); they reach `vp` through `node_modules/.bin`. The Makefile `formatting` target runs `node_modules/.bin/vp fmt`. The lockfile carries the `linux-x64`/`linux-arm64` `gnu` and `musl` bindings of `vite-plus`, `oxlint` and `oxfmt`, so the alpine multi-platform Docker build resolves them. | `docker build` (amd64, arm64) succeeds; `make npm-build` succeeds. | — | — | none | Probe 2026-10-16, Q-096-02 |
| FR-096-12 | Git hooks (Q-096-03, no hook dispatcher): `scripts/pre-commit` replaces the Prettier step (`node_modules/prettier` check, `npm run format`, re-staging every modified JS file) with `node_modules/.bin/vp staged` guarded by `-x node_modules/.bin/vp`; the `staged` block maps `*.{ts,vue,css}` to `vp fmt`, so only staged frontend files are formatted and re-staged. The php-cs-fixer step and `scripts/post-merge` are unchanged. The install hint reads "install the frontend dependencies (`npm ci`)". | A staged, unformatted `.ts` file is formatted and re-staged; staged `.yml`/`.md`/`.php` files are untouched. | No `vp` binary → hint printed, commit continues (as today). | — | none | Probe 2026-10-16 |
| FR-096-13 | Node.js: no pin file (`.node-version`, `devEngines`, `engines`) is added; CI tests Node 24 and 26, the dist job and the Docker images use Node 26. | — | — | — | none | FR-096-10 |
| FR-096-14 | Dependabot: a group `vite-plus` with patterns `vite` and `vite-plus`, declared before the dependency-type groups, so the alias and the package bump in one PR. | A Dependabot PR bumps both to the same version. | — | A PR bumping one of them is closed and the upgrade done with `vp migrate` (FR-096-16). | none | FR-096-01 |
| FR-096-15 | Editor: `.vscode/extensions.json` recommends `oxc.oxc-vscode` in place of `dbaeumer.vscode-eslint` and `esbenp.prettier-vscode`. | — | — | — | none | Vite+ fmt guide |
| FR-096-16 | Upgrades: `vp migrate` (no flags) on the migrated project bumps `vite-plus` and the `vite` alias together and is the documented upgrade path (quality-gate how-to); `vp migrate --full` is not used. | `vp migrate` on the migrated tree is a no-op. | — | — | none | Vite+ migration rules |
| FR-096-17 | Documentation: knowledge map "Frontend Dependencies" and a new "Frontend toolchain (Feature 096)" pattern entry, roadmap row, ADR-096-01, the files of FR-096-09. Completed features' documents keep the commands they record (Q-096-06, N-096-05). | — | — | — | none | Documentation deliverables |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-096-01 | Build parity: for an unchanged source tree the production build keeps the same entries (`resources/js/app.ts`, `resources/js/app-v8.ts`) and the same set of chunk and asset names in `public/build/manifest.json` (350 keys once the content hashes are removed) and asset count (371), and `public/embed/lychee-embed.{js,css}` are produced. Content hashes differ: `@voidzero-dev/vite-plus-core` 1.3.0 bundles Rolldown 1.2.14 where the standalone `vite` 8.3.4 bundles 1.2.13, whose runtime helper differs by a few bytes, and every chunk's hash includes the hashes of the chunks it imports. | Same Vite 8.3.4 under a different launcher; nothing shipped may change. | Manifest names (hashes removed) and asset count before/after (plan drift gate). | FR-096-01, FR-096-07 | Probe 2026-10-16 |
| NFR-096-02 | Format parity: the first `vp fmt` on the toolchain scope changes `resources/js/lychee.d.ts` and the two root configs that join the scope (`vite.embed.config.ts`, `playwright.config.ts`); no other frontend file. | No formatting churn in history. | `git diff --stat` after the first run. | FR-096-04 | Probe 2026-10-16 |
| NFR-096-03 | Lint parity: `vp lint` exits 0 on the migrated tree; the 18 warnings of FR-096-05 are the baseline. | CI must stay green without code changes outside the toolchain. | `vp lint` exit code and warning count. | FR-096-05 | Probe 2026-10-16 |
| NFR-096-04 | No new runtime host: the toolchain is a build-time dependency only; the built bundles reference no additional external host (offline requirement). | Lychee runs with no network. | `grep` of the built assets for new hosts (none). | — | Project constraint |
| NFR-096-05 | Portability: the lockfile resolves on linux (x64, arm64; glibc, musl), macOS and Windows; `vp install --frozen-lockfile` and `npm ci` both install from it. | Docker alpine images, contributors' machines. | Lockfile entries for the `@voidzero-dev/vite-plus-*`, `@oxlint/binding-*`, `@oxfmt/binding-*` packages; CI Docker build. | FR-096-11 | Probe 2026-10-16 |
| NFR-096-06 | Supply chain: the GitHub Action is pinned by commit SHA with a version comment; `vite-plus` is an exact version. | Repository policy (scorecard, harden-runner, pinned actions). | Review of `js_check.yml`, `package.json`. | FR-096-10 | Existing workflows |
| NFR-096-07 | `vp check` on the toolchain scope finishes in seconds (measured: format 2.4 s, lint 1.3 s on 915 files). | Pre-commit and CI latency. | Timing in the drift gate. | FR-096-04, FR-096-05 | Probe 2026-10-16 |

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-096-01 | `vp install --frozen-lockfile` on a clean checkout installs from `package-lock.json` and provides `node_modules/.bin/vp`. |
| S-096-02 | `vp fmt --check` on the toolchain scope is green after the one-time reformat; `.md`, `.yml`, `.json`, `tsconfig.json`, `public/**`, `.github/**`, `.claude/**` are not visited. |
| S-096-03 | `vp lint` on the toolchain scope exits 0; `resources/js/vendor/webauthn/webauthn.ts` is not linted. |
| S-096-04 | `vp check` exits 1 on an unformatted in-scope file and on a lint error; `vp check --fix` repairs the formatting. |
| S-096-05 | `vp run typecheck` runs the pre-script (regenerates the two `.d.ts` files) and `vue-tsc` passes. |
| S-096-06 | `vp run build` (and `npm run build`) produce `public/build` with the same manifest keys and asset count as the baseline, plus `public/embed`. |
| S-096-07 | `vp build --mode development` through `scripts/generate-js-types.sh` writes `auto-imports.d.ts` and `components.d.ts`. |
| S-096-08 | `vp dev` with `VITE_LOCAL_DEV=true` and `VITE_HTTP_PROXY_TARGET` serves `vite/index.html` and proxies `/api/` (manual check). |
| S-096-09 | CI `js_check.yml` is green on Node 24 and 26 with `setup-vp`. |
| S-096-10 | CI dist job builds `Lychee.zip` on Node 26 through `make clean dist`. |
| S-096-11 | Docker build (amd64 and arm64) completes the node stage with `npm ci` and `npm run build`. |
| S-096-12 | Pre-commit: a staged unformatted `.ts` file is formatted and re-staged by `vp staged`; staged non-frontend files are untouched; without `node_modules/.bin/vp` the hint is printed and the commit proceeds. |
| S-096-13 | Dependabot proposes `vite` and `vite-plus` in one PR (group). |
| S-096-14 | `vp migrate` on the migrated tree reports nothing to change. |
| S-096-15 | A Vue file with a template-only `eslint-plugin-vue` violation (for example a `v-for` without `:key`) passes `vp lint` (documented limitation, Q-096-01). |
| S-096-16 | The governance and how-to documents of FR-096-09 contain no `npm run` command. |

## Test Strategy

- **Models / Actions / REST API / Artisan commands:** no PHP change; no PHP test.
- **Frontend (v7/v8):** no source change beyond the one-time reformat of `lychee.d.ts` and the lint fixes in `vite.config.ts`. Verification is command-based: an assertion script in the session scratchpad runs S-096-01 … 07, S-096-12 and S-096-16 and compares the build manifest with the baseline captured before the migration (T-096-01).
- **CI:** S-096-09 … 11 through the pull request (owner).
- **Docs/Contracts:** `grep` gate of S-096-16; `php artisan typescript:transform` followed by `vp fmt` yields no diff on `resources/js/lychee.d.ts` after the first reformat.

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-096-01 | `fmt` block of `vite.config.ts` (FR-096-04) | vite.config.ts |
| DO-096-02 | `lint` block of `vite.config.ts` (FR-096-05, Appendix A) | vite.config.ts |
| DO-096-03 | `staged` block of `vite.config.ts`: `{ "*.{ts,vue,css}": "vp fmt" }` (FR-096-12) | vite.config.ts, scripts/pre-commit |

### API Routes / Services
None.

### CLI Commands / Flags
| ID | Command | Behaviour |
|----|---------|-----------|
| CLI-096-01 | `vp install --frozen-lockfile` | Installs from the lockfile (npm ci semantics); fails on a stale lockfile. |
| CLI-096-02 | `vp check` / `vp check --fix` | Format check + lint on the toolchain scope; `--fix` writes formatting and safe lint fixes. |
| CLI-096-03 | `vp fmt [paths]` / `vp fmt --check` | Oxfmt on the scope or the given paths. |
| CLI-096-04 | `vp lint [paths]` | Oxlint on the scope or the given paths. |
| CLI-096-05 | `vp run typecheck` | `pretypecheck` (type generation) then `vue-tsc`. |
| CLI-096-06 | `vp run build` / `vp build` / `vp build --config vite.embed.config.ts` | Production bundles and embed widget. |
| CLI-096-07 | `vp dev` | Development server (same `vite.config.ts` behaviour). |
| CLI-096-08 | `vp staged` | Runs the `staged` block on staged files (called from `scripts/pre-commit`). |
| CLI-096-09 | `vp migrate` | Upgrades `vite-plus` and the `vite` alias together. |

### Telemetry Events
None.

### Fixtures & Sample Data
None.

### UI States
None.

## Telemetry & Observability
None: tooling only.

## Documentation Deliverables

- AGENTS.md quality gate (frontend and full), `docs/specs/3-reference/coding-conventions.md` (Frontend Code Quality), `docs/specs/5-operations/quality-gate.md`, `docs/specs/5-operations/session-quick-reference.md`, `docs/Contribute.md`, `docs/specs/3-reference/frontend-architecture.md`, `docs/specs/2-how-to/customize-v8-nuxt-ui.md`, `docs/specs/templates/feature-plan-template.md`, `docs/specs/templates/feature-tasks-template.md` (FR-096-09).
- `docs/specs/4-architecture/knowledge-map.md`: Frontend Dependencies (tooling) and a "Frontend toolchain (Feature 096)" entry under Architectural Patterns.
- `docs/specs/4-architecture/roadmap.md` row 096.
- [ADR-096-01](../../../6-decisions/ADR-096-01-vite-plus-single-frontend-toolchain.md) – Vite+ as the single frontend toolchain.

## Fixtures & Sample Data
None.

## Spec DSL

```
domain_objects:
  - id: DO-096-01
    name: fmt block
    fields:
      - { name: printWidth, type: integer, constraints: "150" }
      - { name: semi, type: boolean, constraints: "true" }
      - { name: useTabs, type: boolean, constraints: "true" }
      - { name: tabWidth, type: integer, constraints: "4" }
      - { name: insertFinalNewline, type: boolean, constraints: "true" }
      - { name: sortPackageJson, type: boolean, constraints: "false" }
      - { name: ignorePatterns, type: string[], constraints: "toolchain scope (Definitions)" }
  - id: DO-096-02
    name: lint block
    fields:
      - { name: plugins, type: string[], constraints: "oxc, typescript, unicorn, vue" }
      - { name: options.typeAware, type: boolean, constraints: "false" }
      - { name: options.typeCheck, type: boolean, constraints: "false" }
      - { name: ignorePatterns, type: string[], constraints: "toolchain scope + resources/js/vendor/webauthn/**" }
  - id: DO-096-03
    name: staged block
    fields:
      - { name: "*.{ts,vue,css}", type: string, constraints: "vp fmt" }
cli_commands:
  - { id: CLI-096-01, command: vp install --frozen-lockfile }
  - { id: CLI-096-02, command: vp check [--fix] }
  - { id: CLI-096-03, command: vp fmt [--check] }
  - { id: CLI-096-04, command: vp lint }
  - { id: CLI-096-05, command: vp run typecheck }
  - { id: CLI-096-06, command: vp run build }
  - { id: CLI-096-07, command: vp dev }
  - { id: CLI-096-08, command: vp staged }
  - { id: CLI-096-09, command: vp migrate }
routes: []
telemetry_events: []
fixtures: []
ui_states: []
```

## Appendix A – Lint rule mapping (from `vp migrate`, FR-096-05)

Error rules: `vue/no-arrow-functions-in-watch`, `vue/no-async-in-computed-properties`, `vue/no-computed-properties-in-data`, `vue/no-deprecated-data-object-declaration`, `vue/no-deprecated-delete-set`, `vue/no-deprecated-destroyed-lifecycle`, `vue/no-deprecated-events-api`, `vue/no-deprecated-model-definition`, `vue/no-deprecated-props-default-this`, `vue/no-deprecated-vue-config-keycodes`, `vue/no-dupe-keys`, `vue/no-export-in-script-setup`, `vue/no-expose-after-await`, `vue/no-lifecycle-after-await`, `vue/no-reserved-component-names`, `vue/no-reserved-keys`, `vue/no-reserved-props`, `vue/no-shared-component-data`, `vue/no-side-effects-in-computed-properties`, `vue/no-watch-after-await`, `vue/prefer-import-from-vue`, `vue/require-prop-type-constructor`, `vue/require-render-return`, `vue/require-slots-as-functions`, `vue/return-in-computed-property`, `vue/return-in-emits-validator`, `vue/valid-define-emits`, `vue/valid-define-options`, `vue/valid-define-props`, `vue/valid-next-tick`, `no-array-constructor`, `no-unused-expressions`, `no-unused-vars` (`args: all`, `argsIgnorePattern: ^_`, `caughtErrors: all`, `caughtErrorsIgnorePattern: ^_`, `destructuredArrayIgnorePattern: ^_`, `varsIgnorePattern: ^_`, `ignoreRestSiblings: true`), `typescript/ban-ts-comment`, `typescript/no-duplicate-enum-values`, `typescript/no-empty-object-type`, `typescript/no-explicit-any`, `typescript/no-extra-non-null-assertion`, `typescript/no-misused-new`, `typescript/no-namespace`, `typescript/no-non-null-asserted-optional-chain`, `typescript/no-require-imports`, `typescript/no-this-alias`, `typescript/no-unnecessary-type-constraint`, `typescript/no-unsafe-declaration-merging`, `typescript/no-unsafe-function-type`, `typescript/no-wrapper-object-types`, `typescript/prefer-as-const`, `typescript/prefer-namespace-keyword`, `typescript/triple-slash-reference`, `vite-plus/prefer-vite-plus-imports`.

Override for `**/*.ts`, `**/*.tsx`, `**/*.mts`, `**/*.cts`, `**/*.vue`: core rules the TypeScript compiler enforces are off (`constructor-super`, `getter-return`, `no-class-assign`, `no-const-assign`, `no-dupe-class-members`, `no-dupe-keys`, `no-func-assign`, and the rest of the `vp migrate` list).

`vue/multi-word-component-names` is off in the ESLint configuration and is not part of the Oxlint `vue` plugin.

Rules of the ESLint `vue` essential preset that Oxlint cannot run (55, Q-096-01): 7 not implemented (`vue/jsx-uses-vars`, `vue/no-deprecated-dollar-listeners-api`, `vue/no-deprecated-dollar-scopedslots-api`, `vue/no-mutating-props`, `vue/no-ref-as-operand`, `vue/no-use-computed-property-like-method`, `vue/require-valid-default-prop`) and 48 that need template parsing (`vue/require-v-for-key`, `vue/valid-v-for`, `vue/valid-v-if`, `vue/valid-v-else`, `vue/valid-v-else-if`, `vue/valid-v-model`, `vue/valid-v-on`, `vue/valid-v-slot`, `vue/valid-v-bind`, `vue/valid-v-show`, `vue/valid-v-html`, `vue/valid-v-text`, `vue/valid-template-root`, `vue/valid-attribute-name`, `vue/no-dupe-v-else-if`, `vue/no-duplicate-attributes`, `vue/no-use-v-if-with-v-for`, `vue/no-template-key`, `vue/no-textarea-mustache`, `vue/no-unused-components`, `vue/no-unused-vars`, `vue/no-parsing-error`, `vue/no-child-content`, `vue/no-useless-template-attributes`, `vue/no-v-text-v-html-on-component`, `vue/require-component-is`, `vue/require-toggle-inside-transition`, `vue/use-v-on-exact`, `vue/comment-directive`, `vue/block-lang`, the `vue/no-deprecated-*` template rules, `vue/valid-v-cloak`, `vue/valid-v-is`, `vue/valid-v-memo`, `vue/valid-v-once`, `vue/valid-v-pre`, `vue/no-v-for-template-key-on-child`).
