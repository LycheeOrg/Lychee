# Open Questions – Feature 096

Open questions for [Feature 096](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-096-01~~ | 096 – Vite+ migration | High | Oxlint lints only the `<script>` blocks of `.vue` files: 55 template rules of the ESLint `vue` essential preset stop running (A accept, templates covered by vue-tsc and the Vue compiler · B keep ESLint + `eslint-plugin-vue` for the template rules as a second lint step) | Resolved (Option A; owner, 2026-10-16; spec FR-096-05, N-096-03, S-096-15, Appendix A; ADR-096-01) | 2026-10-16 | 2026-10-16 |
| ~~Q-096-02~~ | 096 – Vite+ migration | Medium | Package manager and bootstrap: where `npm` stays and where the global `vp` is required (A npm stays the package manager and the bootstrap; `vp` reached through `node_modules/.bin` · B global `vp` everywhere: installer in the Dockerfiles, Makefile and dist job · C switch to pnpm, Vite+'s default) | Resolved (Option A; owner, 2026-10-16; spec FR-096-10, FR-096-11, FR-096-13, N-096-04; ADR-096-01) | 2026-10-16 | 2026-10-16 |
| ~~Q-096-03~~ | 096 – Vite+ migration | Medium | Git hooks: keep `scripts/pre-commit` / `scripts/post-merge` and call `vp staged` from them, or adopt the Vite+ hook dispatcher (A keep the scripts, swap the Prettier step for `vp staged` · B `vp hooks enable` with `.vite-hooks/`, php-cs-fixer and post-merge moved there) | Resolved (Option A; owner, 2026-10-16; spec FR-096-12, DO-096-03; ADR-096-01) | 2026-10-16 | 2026-10-16 |
| ~~Q-096-04~~ | 096 – Vite+ migration | Medium | Toolchain scope of `vp check` / `vp fmt` / `vp lint` without paths (A frontend sources + root frontend configs + `e2e/` · B frontend sources only, as Prettier today · C whole repository, Markdown, YAML and JSON included) | Resolved (Option A; owner, 2026-10-16; spec Definitions (toolchain scope), FR-096-04, FR-096-05; ADR-096-01) | 2026-10-16 | 2026-10-16 |
| ~~Q-096-05~~ | 096 – Vite+ migration | Medium | Lint plugin set (A `vp migrate` mapping with Oxlint's default `oxc` and `unicorn` plugins, 18 warnings allowed, zero errors required · B strict parity: `typescript` and `vue` plugins only · C also enable type-aware rules now and fix the 100 findings) | Resolved (Option A; owner, 2026-10-16; spec FR-096-05, N-096-02; owner: type-aware rules (tsgolint) later) | 2026-10-16 | 2026-10-16 |
| ~~Q-096-06~~ | 096 – Vite+ migration | Low | Completed features' `plan.md` / `tasks.md` record `npm run format` / `npm run check` as verification commands in 179 files (A leave them · B rewrite them to the new commands) | Resolved (Option A; owner, 2026-10-16; spec FR-096-17, N-096-05) | 2026-10-16 | 2026-10-16 |

## Question Details

### ~~Q-096-01~~ – Vue template lint rules

**Context:** The ESLint configuration applies `eslint-plugin-vue`'s `flat/essential` preset through `vue-eslint-parser`, which parses templates. Oxlint lints `.vue` files on their `<script>` blocks only; `vp migrate` maps 30 script-level `vue/*` rules and skips 55 (7 not implemented, 48 "requires Vue template parsing"; list in spec Appendix A). Template coverage that remains after the migration: `vue-tsc` (unknown components, props and identifiers in templates, type errors in expressions) and the Vue compiler at build time (parsing errors, duplicate attributes). Not covered any more: `v-for` without `:key`, `v-if` with `v-for` on one element, duplicate `v-else-if` conditions, invalid directive arguments (`valid-v-*`), unused components, `vue/no-mutating-props`.

- **Option A (recommended):** Accept Oxlint's script-only coverage; `vue-tsc` and the compiler keep the template checks they already provide. Record the 55 rules in the spec and revisit when Oxlint parses templates (on the oxc roadmap).
  - ✅ One toolchain, no ESLint, no `vue-eslint-parser`, the point of the migration.
  - ✅ No rule of the skipped set fires on the current sources (CI is green today with them on; dropping them cannot turn CI red).
  - ❌ New template mistakes of those kinds are caught in review or at runtime instead of by lint.
- **Option B:** Keep `eslint` + `eslint-plugin-vue` (+ parser) for the template rules only, as a second CI step (`vp lint` then `eslint --config` with the 55 rules).
  - ✅ No coverage loss.
  - ❌ Two linters, two configs, ESLint stays in `devDependencies`; `vp check` is no longer the single gate; `vp migrate` idempotence must be re-checked on each upgrade.

**Resolution:** Option A. Oxlint lints the `<script>` blocks; template coverage is `vue-tsc` and the Vue compiler; the 55 rules stay listed in spec Appendix A for a later re-enable (plan backlog).

### ~~Q-096-02~~ – Package manager and bootstrap

**Context:** `vp` picks the package manager from the repository: `package-lock.json` selects npm (no `packageManager` field). The local `vite-plus` package installs `node_modules/.bin/vp`, so `npm run build` → `vp build` works in the Dockerfiles, the Makefile, `scripts/post-merge` and the CI dist job without the global installer (`curl -fsSL https://vite.plus | bash` or the `setup-vp` action). Vite+ defaults to pnpm when no lockfile exists.

- **Option A (recommended):** npm stays the package manager (`package-lock.json`, Dependabot `npm` ecosystem) and the bootstrap: Dockerfiles, Makefile, post-merge and the dist job keep `npm ci` + `npm run build`; developers and the JS CI job use `vp` (`setup-vp` action). No `packageManager` / `devEngines` field.
  - ✅ Smallest change surface; building from source needs Node.js and npm only, as the README says.
  - ✅ `vp install --frozen-lockfile` and `npm ci` read the same lockfile.
  - ❌ Two spellings of the same thing exist in the repository (`npm ci` in bootstrap files, `vp install` in docs/CI).
- **Option B:** Global `vp` everywhere: the Dockerfile node stage uses `ghcr.io/voidzero-dev/vite-plus` or the installer, the Makefile and the dist job call `vp install --frozen-lockfile` / `vp build`, the README lists Vite+ as a prerequisite.
  - ✅ One spelling.
  - ❌ A new base image or an installer download in every build path; contributors must install `vp` to run `make`.
- **Option C:** Switch to pnpm (`pnpm-lock.yaml`, `vp env pin pnpm@…`).
  - ✅ Vite+'s default manager; faster installs.
  - ❌ Lockfile migration, Dockerfiles, Makefile, post-merge, Dependabot and docs all change; unrelated to the toolchain goal.

**Resolution:** Option A. npm and `package-lock.json` stay; Dockerfiles, Makefile `npm-build`, `scripts/post-merge` and the dist job keep `npm ci` + `npm run build` (reaching `vp` through `node_modules/.bin`); developers and the JS CI job use `vp`; no `packageManager` / `devEngines` field.

### ~~Q-096-03~~ – Git hooks

**Context:** `scripts/pre-commit` (php-cs-fixer on changed PHP files, then `npm run format` and re-staging every modified JS file) and `scripts/post-merge` are copied by hand into `.git/hooks`. Vite+ offers `vp hooks enable`, which sets `core.hooksPath` to `.vite-hooks/` (then `.git/hooks/*` no longer run) and `vp staged`, which runs the `staged` block on staged files only. `vp staged` works from any script through `node_modules/.bin/vp` (verified).

- **Option A (recommended):** Keep both scripts; replace the Prettier step of `scripts/pre-commit` with `node_modules/.bin/vp staged` (`staged: { "*.{ts,vue,css}": "vp fmt" }`), guarded by the binary's presence like today.
  - ✅ php-cs-fixer step and post-merge untouched; same installation story.
  - ✅ Only staged files are formatted (today every modified JS file is formatted and staged).
  - ❌ Hooks remain opt-in copies.
- **Option B:** `vp hooks enable` with `.vite-hooks/pre-commit` (php-cs-fixer step + `vp staged`) and `.vite-hooks/post-merge` committed; `core.hooksPath` set per clone by `vp config`.
  - ✅ Hooks under version control and installed by one command.
  - ❌ Requires the global `vp` on every clone; moves the PHP step into a Vite+-managed directory; `VP_GIT_HOOKS=0` semantics to document.

**Resolution:** Option A. `scripts/pre-commit` calls `node_modules/.bin/vp staged`; `scripts/post-merge` unchanged; no hook dispatcher.

### ~~Q-096-04~~ – Toolchain scope

**Context:** Without paths, `vp check`, `vp fmt` and `vp lint` walk the working directory (gitignored files excluded). Oxfmt formats Markdown, YAML, JSON and TOML natively, so a bare `vp fmt` would rewrite the 562 Markdown, 123 JSON and 22 YAML files of the repository and `tsconfig.json`; Oxlint would lint `public/dist/custom.js`, `public/vendor/**`, `.github/skills/**` and `.claude/**`. Prettier today covers `resources/js/` and `resources/sass/` only; ESLint `resources/js/`. Gitignore-style `ignorePatterns` with negations (`/*`, `!/resources`, `/resources/*`, `!/resources/js`, …) restrict the walk (verified: only in-scope files visited).

- **Option A (recommended):** `resources/js/**`, `resources/sass/**`, `e2e/**`, `vite.config.ts`, `vite.embed.config.ts`, `playwright.config.ts`. The root configs are formatted for the first time (mixed indentation today) and linted (`vite-plus/prefer-vite-plus-imports`).
  - ✅ Every frontend source file under one rule; `vp check` without arguments is the gate.
  - ❌ One-time reformat of the three root configs and the Playwright sample.
- **Option B:** `resources/js/**` and `resources/sass/**` only.
  - ✅ Exactly today's Prettier/ESLint scope.
  - ❌ Config files stay unformatted and unlinted.
- **Option C:** Whole repository.
  - ✅ Markdown/YAML/JSON formatted consistently.
  - ❌ Thousands of lines of churn in docs, workflows, `composer.json`, `lang/*.json`; conflicts with Weblate-managed files.

**Resolution:** Option A. Scope = `resources/js/**`, `resources/sass/**`, `e2e/**`, `vite.config.ts`, `vite.embed.config.ts`, `playwright.config.ts` (spec Definitions).

### ~~Q-096-05~~ – Lint plugin set

**Context:** `vp migrate` enables Oxlint's default plugins (`oxc`, `unicorn`) plus `typescript` and `vue`, downgrades the correctness category to `warn` and maps the ESLint error rules. On the current sources this gives 0 errors and 18 warnings (`unicorn/no-useless-spread` 7, `unicorn/no-new-array` 7, `oxc/only-used-in-recursion` 2, `eslint/no-useless-escape` 2). Type-aware rules (`typeAware`, through tsgolint) add 100 warnings (`no-floating-promises` 72, `no-redundant-type-constituents` 14, others 14) and need the `env.d.ts` shim.

- **Option A (recommended):** Keep the `vp migrate` mapping: `oxc`, `typescript`, `unicorn`, `vue`; warnings allowed, zero errors required; type-aware off (N-096-02).
  - ✅ Parity on errors; the extra warnings are real hints and cost nothing in CI.
  - ❌ 18 warnings in the output until fixed.
- **Option B:** `typescript` and `vue` plugins only (strict parity with the ESLint rule set).
  - ✅ Clean output.
  - ❌ Drops useful default rules for no gain.
- **Option C:** Enable `typeAware` now and fix the 100 findings.
  - ✅ Stronger lint.
  - ❌ Source changes across 25 files in a toolchain feature; needs the `env.d.ts` shim; slower lint.

**Resolution:** Option A. `oxc`, `typescript`, `unicorn`, `vue`; warnings allowed, errors forbidden; type-aware rules off. Owner: "we can switch to tsgolint later" (plan backlog).

### ~~Q-096-06~~ – Completed features' documents

**Context:** 179 `plan.md` / `tasks.md` / `spec.md` files of completed features cite `npm run format`, `npm run check`, `npm run lint` or `npm run build` as the verification commands that were run.

- **Option A (recommended):** Leave them: they record what was run.
  - ✅ No churn in closed features.
  - ❌ A reader copying a command from an old plan gets `npm run check` (removed script).
- **Option B:** Rewrite the commands in all 179 files.
  - ✅ Every document runs as written.
  - ❌ Large diff of no behavioural value.

**Resolution:** Option A. Completed features' documents keep the commands they record.
