# Feature Plan 096 – Vite+ Migration

_Linked specification:_ [spec.md](spec.md)  
_Status:_ Implemented — drift gate 2026-10-16  
_Last updated:_ 2026-10-16

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria

One CLI (`vp`) runs the frontend: dev server, both bundles and the embed widget, formatting, linting, staged-file formatting; `vue-tsc` keeps type-checking. Nothing shipped changes.

- S-096-01 … 07, S-096-12, S-096-16 pass through the scratchpad assertion script; S-096-09 … 11 through CI.
- Build parity (NFR-096-01): same manifest keys and asset count as the baseline captured in I1.
- Format parity (NFR-096-02): the first `vp fmt` changes `resources/js/lychee.d.ts` only.
- Lint parity (NFR-096-03): `vp lint` exits 0.
- No `npm run` command in the governance and how-to documents (S-096-16).

## Scope Alignment

- **In scope:** dependencies and lockfile, `vite.config.ts` / `vite.embed.config.ts`, `package.json` scripts, `scripts/generate-js-types.sh`, `scripts/pre-commit`, Makefile `formatting`, CI `js_check.yml` and `CICD.yml`, `.github/dependabot.yml`, `.vscode/extensions.json`, documentation of FR-096-09 and FR-096-17, ADR-096-01.
- **Out of scope:** N-096-01 … 08; Dockerfiles, Makefile `npm-build`, `scripts/post-merge`, the dist job's install step unless Q-096-02 chooses B or C.

## Dependencies & Interfaces

- Vite+ 1.3.0 installed globally at `~/.local/share/vite-plus/bin/vp` on the development machine (not on `PATH` in non-interactive shells: `export PATH="$HOME/.local/share/vite-plus/bin:$PATH"`); the migrated project carries its own `vp` under `node_modules/.bin`.
- `voidzero-dev/setup-vp` v1.21.2 (commit `68f39c78aaf39be468cabd2afb09c162574c84af`) for CI.
- Vite+ guides: migrate, migrate-rules, check, lint, fmt, run, commit-hooks, ci, package-management, env (viteplus.dev); Oxfmt language support and config reference, Oxlint config reference (oxc.rs).
- Scratch probe of 2026-10-16 (session scratchpad `vp-migrate-probe2/`): migrated copy of the configuration files and `resources/`, used for the measurements quoted in the spec.

## Assumptions & Risks

- **Assumptions:** npm stays (Q-096-02); CI runners can download the Vite+ installer through `setup-vp` (egress policy `audit`); Vite 8.3.4 bundled by `@voidzero-dev/vite-plus-core@1.3.0` behaves as the standalone `vite@8.3.4` (verified by the probe build).
- **Risks / Mitigations:**
  - `vp migrate` output must be edited by hand (duplicated blocks, `env.d.ts`, `no-unused-vars` options, scope): I1 lists every edit; S-096-14 checks idempotence afterwards.
  - Oxfmt and `.editorconfig`: without explicit `useTabs`/`tabWidth`/`insertFinalNewline` the diff is 915 files; FR-096-04 fixes the options (verified).
  - A Dependabot PR bumping `vite` or `vite-plus` alone breaks the alias equality: FR-096-14 groups them.
  - Docker alpine (musl) and arm64: lockfile bindings verified present (NFR-096-05); CI Docker build confirms.
  - `vp check` note about a `check` script: the script is renamed `typecheck` (FR-096-06).
  - Local `node_modules` of the checkout is stale (no `eslint`/`prettier`, extraneous `vitest`): I1 starts from `vp install`.
  - `vp migrate` on the pre-migration tree exhausted Node's default 4 GB heap during its ESLint-configuration step, after it had rewritten the lint directives (12 files), three scripts, the lockfile root and the `fmt` block, and had written `.oxlintrc.json`; the remaining steps were applied by hand from the probe (package.json, `vite.config.ts` blocks, `vite-plus` imports). On the migrated tree `vp migrate` is a no-op with the default heap (S-096-14), so the upgrade path (FR-096-16) holds.
  - Oxfmt's directory walk ignores root files excluded by `/*` even when a later `!/file` pattern re-includes them (Oxlint honours the re-include): the scope is an explicit ignore list (spec Definitions).

## Implementation Drift Gate

After I5: table FR-096-* → file → evidence (command output, CI run); rerun the assertion script; record under "Drift Gate Report".

## Increment Map

1. **I1 – Baseline and migration** (FR-096-01 … 05; S-096-01 … 04, S-096-14)
   - _Preconditions:_ Q-096-01 … 05 resolved and folded into the spec.
   - _Steps:_ assertion script in the scratchpad (T-096-01) capturing the baseline: `npm ci`, `npm run build` manifest keys and asset count, `npm run check-formatting`, `npm run lint`, `npm run check`; remove `@vitest/eslint-plugin` and its ESLint block; run `vp migrate --no-interactive --no-hooks --no-agent --no-editor`; then by hand: delete `env.d.ts`, hoist the `lint`/`fmt`/`staged` blocks into one `toolchain` constant spread into both returns, import from `vite-plus` in both configs, `fmt` options of FR-096-04, scope `ignorePatterns` (fmt and lint) and `resources/js/vendor/webauthn/**`, `no-unused-vars` options, `_res`/`_isSsrBuild`/`_isPreview`, `@ts-expect-error`; `vp fmt`; `vp lint`; `vp migrate` again (no diff).
   - _Commands:_ `vp install`, `vp check`, `vp migrate`.
   - _Exit:_ `vp check` green; diff limited to the configuration files, the lockfile and `lychee.d.ts`.
2. **I2 – Scripts, hooks, Makefile, Dependabot, editor** (FR-096-06 … 08, FR-096-11, FR-096-12, FR-096-14, FR-096-15; S-096-05 … 07, S-096-12, S-096-13)
   - _Steps:_ `package.json` scripts; `scripts/generate-js-types.sh`; `scripts/pre-commit`; Makefile `formatting`; `dependabot.yml` group; `.vscode/extensions.json`.
   - _Commands:_ `vp run typecheck`, `vp run build`, `bash scripts/generate-js-types.sh`, hook rehearsal (`scripts/pre-commit` on a staged scratch file).
   - _Exit:_ S-096-05 … 07 and S-096-12 pass.
3. **I3 – CI** (FR-096-10, FR-096-13; S-096-09, S-096-10)
   - _Steps:_ `js_check.yml` (setup-vp, steps), `CICD.yml` (path filter, dist job Node 26).
   - _Commands:_ `actionlint` if available, otherwise YAML review; CI run on the pull request (owner).
   - _Exit:_ workflow files reviewed; CI green after push.
4. **I4 – Documentation and ADR** (FR-096-09, FR-096-16, FR-096-17; S-096-16)
   - _Steps:_ AGENTS.md, coding conventions, quality gate, session quick reference, Contribute.md, frontend architecture, customize-v8-nuxt-ui, README, templates, knowledge map, roadmap; ADR-096-01.
   - _Commands:_ `grep -rn "npm run\|prettier\|eslint" <files>` (expect nothing but historical mentions outside the list).
   - _Exit:_ S-096-16 passes.
5. **I5 – Verification and drift gate** (NFR-096-01 … 07)
   - _Steps:_ run the assertion script against the migrated tree; `docker build` locally when Docker is available, otherwise CI; drift gate report.
   - _Commands:_ assertion script, `vp check`, `vp run typecheck`, `vp run build`.
   - _Exit:_ report written; commit prepared for the owner.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-096-01 | I1 / T-096-02 | `vp install` |
| S-096-02 | I1 / T-096-03, T-096-04 | scope and fmt options |
| S-096-03 | I1 / T-096-03, T-096-05 | lint options and ignore |
| S-096-04 | I1 / T-096-01 | assertion script |
| S-096-05 | I2 / T-096-06 | `typecheck` + pre-script |
| S-096-06 | I2 / T-096-06, I5 / T-096-13 | manifest comparison |
| S-096-07 | I2 / T-096-07 | type generation script |
| S-096-08 | I5 / T-096-13 | manual |
| S-096-09 | I3 / T-096-10 | CI |
| S-096-10 | I3 / T-096-11 | CI |
| S-096-11 | I5 / T-096-13 | Docker, CI |
| S-096-12 | I2 / T-096-08 | hook rehearsal |
| S-096-13 | I2 / T-096-09 | Dependabot group |
| S-096-14 | I1 / T-096-05 | idempotence |
| S-096-15 | I1 / T-096-05 | documented limitation |
| S-096-16 | I4 / T-096-12 | grep gate |

## Analysis Gate

2026-10-16, agent review, after Q-096-01 … 06 (all Option A, owner):

1. Specification completeness — pass: goals, FR-096-01 … 17, NFR-096-01 … 07 populated; Q-096-01 … 06 folded into Definitions (toolchain scope), N-096-02 … 05, FR-096-04, FR-096-05, FR-096-10 … 12, FR-096-17; no UI, so no mock-up.
2. Open questions review — pass: no open entry; ADR-096-01 written and linked from the spec header, the deliverables and the open-questions rows.
3. Plan alignment — pass: increments cite FR/NFR/S IDs; success and exit criteria use the spec's IDs; dependencies name the Vite+ version, the action SHA and the probe.
4. Tasks coverage — pass: FR-096-01/02 → T-096-02; FR-096-03 … 05 → T-096-03 … 05; FR-096-06/07 → T-096-06; FR-096-08 → T-096-07; FR-096-12 → T-096-08; FR-096-11, 14, 15 → T-096-09; FR-096-10, 13 → T-096-10/11; FR-096-09, 16, 17 → T-096-12; NFRs → T-096-13; the assertion script (T-096-01) is written before the toolchain changes and runs red on the current tree (no `vp` scripts, no `typecheck` script); increments ≤ 90 minutes.
5. Working agreements — pass: spec-first; the dependency change (`vite-plus` added, seven packages removed) is the feature itself and was approved through Q-096-01 … 05; no control flow added (configuration and scripts only); ADR-0006 reviewed (both bundles still built and type-checked together).
6. Tooling readiness — pass: commands per increment; `vp` global path noted in the plan dependencies and the tasks notes.

## Drift Gate Report

2026-10-16, agent review after T-096-01 … 13. Assertion script (scratchpad `096/assert-096.sh verify`) 23/23: `vp install --frozen-lockfile`, local `vp` binary, no ESLint/Prettier package, scripts, `vp fmt --check`, scope, `vp lint`, webauthn ignored, `vp check` detects an unformatted file and a lint error, `vp check --fix` repairs, `vp run typecheck`, generated `.d.ts`, `vp run build`, manifest and asset parity, embed, type-generation script, `vp migrate` idempotence, hook rehearsal, docs grep gate, Dependabot group, VS Code recommendations. Also: `docker build --target node` on `node:26-alpine` (both bundles), `vp dev` smoke test (entry module and stylesheet served on a spare port).

| Requirement | Code | Evidence |
|-------------|------|----------|
| FR-096-01 | `package.json` `dependencies.vite`, `dependencies.vite-plus`, `overrides.vite` | `vp toolchain`: vite-plus@1.3.0 → vite 8.3.4, rolldown 1.2.14, oxlint 1.88.0, oxfmt 0.73.0 |
| FR-096-02 | `package.json`, `package-lock.json` (114 packages added, 103 removed, one changed: the `vite` alias), 12 directive rewrites | `no-eslint-prettier`; lockfile comparison against HEAD |
| FR-096-03 | `vite.config.ts` `toolchain` / `TOOLCHAIN_SCOPE`, `vite.embed.config.ts` import | `vp lint` root walk green with the configs in scope; no `env.d.ts`, no `.oxlintrc.json` |
| FR-096-04 | `fmt` block | first run: `lychee.d.ts`, `vite.embed.config.ts`, `playwright.config.ts`; `fmt-check`, `fmt-scope` |
| FR-096-05 | `lint` block | `lint` exit 0 with 18 warnings; `lint-webauthn-ignored`; `check-detects-lint-error` |
| FR-096-06, FR-096-07 | `package.json` scripts | `scripts`, `typecheck`, `dts` |
| FR-096-08 | `scripts/generate-js-types.sh` | `gen-types` |
| FR-096-09 | governance and how-to documents, templates | `grep-gate` |
| FR-096-10, FR-096-13 | `js_check.yml`, `CICD.yml` | YAML review; CI run pending (owner push) |
| FR-096-11 | Makefile `formatting`; Dockerfiles, `npm-build`, `post-merge` unchanged | `docker build --target node` green (amd64); `make formatting` frontend step |
| FR-096-12 | `scripts/pre-commit`, `staged` block | `hook-rehearsal` |
| FR-096-14 | `.github/dependabot.yml` | `dependabot-group` |
| FR-096-15 | `.vscode/extensions.json` | `vscode` |
| FR-096-16 | — | `migrate-idempotent` (default heap) |
| FR-096-17 | knowledge map, roadmap, ADR-096-01, ADR index | review |
| NFR-096-01 | — | `manifest-parity` (350 names), `asset-count-parity` (371); 133 content hashes differ (Rolldown 1.2.13 → 1.2.14 runtime helper, cascading through imports; verified against a build of the pre-migration tree) |
| NFR-096-02 | — | `git diff --stat` after the first `vp fmt` |
| NFR-096-03 | — | `lint` |
| NFR-096-04 | — | same chunk set, no new host |
| NFR-096-05 | — | lockfile bindings (gnu, musl, arm64, darwin, win32); Docker node stage on alpine |
| NFR-096-06 | — | `js_check.yml` SHA pin; exact `vite-plus` version |
| NFR-096-07 | — | format 2.6 s, lint 1.3 s on 918 files |

Divergences from the draft, folded into the spec: `vite-plus` sits under `dependencies` (the Docker node stage installs with `NODE_ENV=production`); the toolchain scope is an explicit ignore list (Oxfmt's walk skips root files excluded by `/*` even when re-included); the fresh `vp migrate` exhausted the default Node heap and the migration was completed by hand from the probe; the first `vp fmt` also reformats the two root configs that join the scope. Pending: CI on Node 24 and 26, the dist job and the multi-platform Docker build through the pull request (owner); the local `.git/hooks/pre-commit` copy is refreshed from `scripts/pre-commit` by the owner. Addendum (Q-096-07, T-096-14): the Dependency Review run of PR #4891 failed on 16 Vite advisories attached to the alias (`vite@1.3.0`); removing the alias was tested and is impossible (`vp` refuses it, `vite-plus` depends on it); the IDs are allow-listed in `dependency-review.yml` (YAML parsed, 17 IDs), each verified against the GitHub advisory API as a Vite advisory whose range starts below 1.x and whose `vite-plus` ranges, where present, end at 0.1.x; S-096-17 passes with the next CI run (owner push). Note: `vp dev` spawns `node node_modules/vite/dist/vite/bin/vite.js dev …` as a child; stopping the `vp` parent leaves that child listening and `public/hot` in place (Laravel's `@vite` directive then points every asset at the dev server, which looks like a build problem). Stop the listener by port (`ss -ltnp`) or with Ctrl-C in the foreground, and check that `public/hot` is gone.

## Exit Criteria

- `vp check`, `vp run typecheck`, `vp run build` green locally; CI green on Node 24 and 26; Docker build green.
- Assertion script green against the migrated tree; manifest parity recorded.
- Tasks all `[x]`; roadmap row updated; knowledge map and ADR-096-01 written; open questions resolved.
- Commit prepared for the owner (Conventional Commit, `Spec impact:` line).

## Follow-ups / Backlog

- Type-aware lint rules (`typeAware`, tsgolint) and the 100 findings they report (N-096-02).
- Removal of `@vue/test-utils`, `jsdom`, `@types/jsdom`, the `rollup` override (no package depends on `rollup` any more) — dependency changes need approval (N-096-01, N-096-07).
- Oxlint Vue template parsing: re-enable the 55 rules when available (Q-096-01).
- `vp run` task caching for `build` (`vp run --cache build`) once CI uses the Vite Task cache.
- `overrides["@tailwindcss/typography"]["postcss-selector-parser"] = ^7.1.6` (owner directive 2026-10-17): the plugin pins 6.0.10, affected by GHSA-rj75-hqrm-r3gf; the override installs 7.1.6 for it, the built CSS is byte-identical, `npm audit` is clean and the osv-scanner ignore for that advisory is gone. Remove the override once `@tailwindcss/typography` ships tailwindlabs/tailwindcss-typography#429 (open on 2026-10-17; 0.5.20 still pins 6.0.10).

## Intent Log

- 2026-10-16: owner asked to migrate to Vite+. Session kickoff, repository sweep of every toolchain touchpoint (CI, Docker, Makefile, hooks, docs), Vite+ 1.3.0 documentation and CLI help read, `vp migrate` rehearsed on a scratch copy (first run blocked by `@vitest/eslint-plugin`; second run migrated; build, format, lint, staged and frozen-install measured). Spec, plan, tasks and Q-096-01 … 06 drafted; no repository file outside `docs/specs/` changed.
- 2026-10-16: owner answered Q-096-01 … 06 (all Option A). ADR-096-01, analysis gate, branch `vite-plus`. T-096-01 … 13: baseline captured with the assertion script; `vp migrate` crashed on the default Node heap after rewriting directives, scripts, the lockfile root and the `fmt` block, and the migration was completed by hand from the probe; `vite-plus` moved to `dependencies` after the Docker node stage failed with `vp: not found`; the scope became an explicit ignore list after Oxfmt skipped re-included root files; the lockfile was regenerated from the original one so only the added and removed packages change. Assertion script 23/23, Docker node stage and `vp dev` smoke test green; drift gate written; commit prepared for the owner.
- 2026-10-16: owner reported the Dependency Review failure on PR #4891 (16 Vite advisories on `vite@1.3.0`) and asked whether the alias could go. Tested on the scratch copy: `vp build` refuses a registry Vite and `vite-plus` carries the alias itself. Q-096-07 logged with osv-scanner, allow-list and pnpm as options; owner chose B (allow-list). Workflow comment and allow-list written, spec/ADR updated; commit prepared.
