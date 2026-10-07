# Quick Reference for GitHub Copilot

> The single source of truth is [docs/specs/3-reference/coding-conventions.md](../docs/specs/3-reference/coding-conventions.md). This file only holds the one-line hints Copilot needs during inline completion; do not add rules here that are not in the conventions file.

# PHP

- New files start with the license header, followed by a single blank line after the opening PHP tag.
- Variables are snake_case; PSR-4 autoloading.
- Strict comparison (`===`), `in_array()` with `true` as third parameter, no `empty()`, only booleans in `if` conditions.
- Do not duplicate code across `if` and `else` branches; extract the common part.
- In Requests, `$this->user` is the requesting user; a user given by the query goes in `$this->user2`.
- Resource classes extend Spatie Data, not JsonResource. No Blade views.
- Money: `moneyphp/money`, stored as integers in the smallest currency unit; never floats.
- Translations: edit only `lang/<locale>/*.php`. Never touch the generated `lang/<locale>.json` or `lang/php_*.json`.

# Vue3 / TypeScript

- TypeScript + Composition API; `<template>` first, then `<script lang="ts">`, then `<style>`.
- axios calls live in a `services/` directory and use `${Constants.getApiUrl()}` as base URL.
- v8 (`resources/js/v8/`): Nuxt UI components; `async`/`await` and arrow functions allowed.
- v7 (everything else under `resources/js/`): PrimeVue components; `.then()` instead of `await`; `function name() {}` instead of `const name = () => {}`.

# Tests

- `tests/Unit` extends `AbstractTestCase`; `tests/Feature_v2` and `tests/Feature_v3` extend their own `Base\BaseApiWithDataTest`.
- Do not mock the database: tests use the SQLite file `database/database.sqlite` and roll back via `DatabaseTransactions`.

# Docs

- Files under `docs/` end with an hr line and `*Last updated: <date>*`, except `version.md` and files created from `docs/specs/templates/`.
