# ADR-096-01: Vite+ as the single frontend toolchain

- **Status:** Accepted
- **Date:** 2026-10-16
- **Related features/specs:** Feature 096 (docs/specs/4-architecture/features/096-vite-plus-migration/spec.md)
- **Related open questions:** Q-096-01, Q-096-02, Q-096-03, Q-096-04, Q-096-05, Q-096-07

## Context

The frontend is built by Vite 8 (two entries plus an embed widget), linted by ESLint 10 with `eslint-plugin-vue` and `typescript-eslint`, formatted by Prettier 3.9, type-checked by `vue-tsc`, and driven through npm scripts that every gate (AGENTS.md, CI, pre-commit script, Makefile, Dockerfiles) names. Vite+ (`vp`, package `vite-plus`) bundles Vite, Oxlint, Oxfmt, lint-staged and a task runner behind one CLI, with configuration in `vite.config.ts`.

Constraints: nothing shipped may change (same bundles, offline runtime); building from source must stay possible with Node.js and npm only; Vue single-file components must keep full type-checking, which Vite+'s tsgolint path does not provide; Oxlint lints only the `<script>` blocks of `.vue` files, so 55 template rules of the ESLint `vue` essential preset cannot run; Oxfmt formats Markdown, YAML and JSON natively, so its scope must be bounded; CI pins actions by commit SHA.

## Decision

1. `vite-plus` 1.3.0 (exact, under `dependencies` like `vite` and the build plugins, because the Docker node stage installs with `NODE_ENV=production`) is the frontend toolchain. `vite` stays declared, aliased to `@voidzero-dev/vite-plus-core` of the same version (plus the matching `overrides.vite`), so the build plugins resolve the bundled Vite 8.3.4. ESLint, Prettier and their configuration packages are removed; `eslint.config.ts` and `.prettierrc.json` are deleted; the `lint`, `fmt` and `staged` blocks live once in `vite.config.ts` and apply to every configuration the file returns. Both Vite configuration files import from `vite-plus`.
2. `vue-tsc` remains the type-checker (`vp run typecheck`, with the type-generation pre-script). `lint.options.typeAware` and `typeCheck` are off; the `env.d.ts` module shim is not added.
3. Vue files are linted on their `<script>` blocks only (Q-096-01). Template coverage is `vue-tsc` and the Vue compiler. The skipped rules are listed in the spec for a later re-enable.
4. Formatting is Oxfmt with explicit `printWidth: 150`, `semi`, `useTabs`, `tabWidth: 4`, `insertFinalNewline`, `sortPackageJson: false`, matching the Prettier output on the existing tree (one generated file differs). The toolchain scope is `resources/js/**`, `resources/sass/**`, `e2e/**` and the three root TypeScript configuration files, bounded with an explicit ignore list of non-frontend file types and of every other root directory (Q-096-04; Oxfmt's walk does not honour negated re-includes of root files).
5. Linting keeps the `vp migrate` mapping of the ESLint rules with Oxlint's default `oxc` and `unicorn` plugins; warnings are allowed, errors are not (Q-096-05). `resources/js/vendor/webauthn/**` stays ignored.
6. npm and `package-lock.json` stay (Q-096-02). Dockerfiles, Makefile, `scripts/post-merge` and the CI dist job keep `npm ci` and `npm run build`, which reach the `vp` binary installed by the local package under `node_modules/.bin`. Developers and the JS CI job (`voidzero-dev/setup-vp`, pinned by SHA) use `vp` directly: `vp check`, `vp run typecheck`, `vp run build`.
7. Git hooks stay the hand-copied `scripts/pre-commit` and `scripts/post-merge` (Q-096-03); the pre-commit script runs `vp staged` (formats and re-stages staged `.ts`, `.vue`, `.css` files) instead of a whole-tree Prettier pass. No Vite+ hook dispatcher.
8. Upgrades go through `vp migrate`, which moves `vite-plus` and the `vite` alias together; Dependabot groups the two packages.

## Consequences

### Positive
- One command surface and one configuration file for format, lint, staged formatting, dev server and builds.
- Format and lint parity verified on the existing tree; no source churn.
- Building from source, Docker images and the dist job are unchanged in their prerequisites.

### Negative
- Template-level Vue lint rules (for example `v-for` without `:key`) are not enforced until Oxlint parses templates.
- `vp` is a new tool for contributors; the project-local binary covers scripts, but the documented commands assume the CLI.
- Two spellings coexist: `npm ci` in bootstrap files, `vp install --frozen-lockfile` in documentation and the JS CI job.
- GitHub's dependency graph reads the aliased lockfile entry `node_modules/vite` as `vite@<Vite+ version>`, so the Dependency Review action and Dependabot attach every Vite advisory with an open range to it. The action keeps running with those advisory IDs allow-listed (Q-096-07, 16 IDs on 2026-10-16), Dependabot alerts for `vite` are dismissed as inaccurate, and the list grows when a new such advisory appears. The alias cannot be dropped: `vp` refuses a registry Vite at `node_modules/vite`, and `vite-plus` itself depends on the alias. osv-scanner, Trivy and `npm audit` read the real package.

## Alternatives Considered

- **Keep ESLint for the template rules next to Oxlint** — no coverage loss; two linters and configurations, ESLint stays installed, `vp check` no longer the single gate. Rejected (Q-096-01).
- **Global `vp` in every build path (installer or official image)** — one spelling; new download or base image in Docker, Makefile and the dist job, `vp` required to run `make`. Rejected (Q-096-02).
- **Switch to pnpm** — Vite+'s default; lockfile, Dockerfiles, Makefile, hooks and Dependabot all change for no toolchain gain. Rejected (Q-096-02).
- **Vite+ hook dispatcher (`.vite-hooks/`)** — versioned hooks; requires the global CLI on each clone and moves the PHP step under Vite+. Rejected (Q-096-03).
- **Whole-repository format scope** — consistent docs; thousands of lines of churn including Weblate-managed files. Rejected (Q-096-04).
- **Type-aware lint now** — stronger lint; 100 source findings inside a toolchain change. Deferred (Q-096-05).

## Security / Privacy Impact

- No secret handling changes. No new runtime host: the toolchain runs at build time only and the built bundles are unchanged.
- Supply chain: exact `vite-plus` version, the alias pinned to the same version, the GitHub Action pinned by commit SHA; the lockfile keeps every platform binding so installs stay reproducible on glibc, musl and arm64.

## Operational Impact

- Node.js floor rises to the Vite+ requirement (`^22.18.0 || ^24.11.0 || >=26.0.0`): CI tests 24 and 26, the dist job and Docker images use 26.
- `vp check` runs in seconds on the full scope; the pre-commit hook formats only staged files.
- Upgrade runbook: `vp migrate`, then `vp check`, `vp run typecheck`, `vp run build`.

## Links

- Related spec sections: `docs/specs/4-architecture/features/096-vite-plus-migration/spec.md#functional-requirements`, `#definitions`, `#appendix-a--lint-rule-mapping-from-vp-migrate-fr-096-05`
- Related ADRs: ADR-0006 (both bundles built and type-checked together)
- Related issues / PRs: —
