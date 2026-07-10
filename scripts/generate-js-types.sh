#!/usr/bin/env bash
set -euo pipefail
# auto-imports.d.ts and components.d.ts are gitignored: they're written by the
# @nuxt/ui Vite plugin (unplugin-auto-import / unplugin-vue-components) as a
# side effect of Vite resolving its plugin pipeline. `npm run check` (vue-tsc)
# needs them to resolve globals like `defineShortcuts`, but doesn't run Vite
# itself - so on a fresh checkout the type-check fails with "Cannot find
# name" for every auto-imported symbol.
#
# A one-shot `vite build` triggers the same codegen without the risk of
# starting the dev server: the dev server sets up a persistent file watcher
# over the whole project, which - on a checkout that has real photo data
# under lychee/uploads/ - can exhaust the OS's inotify watch limit (ENOSPC).
# `vite build` never watches, so it's safe regardless of what's on disk.
# The bundle output itself is thrown away; only the root-level .d.ts files
# written by the plugins are wanted.

cd "$(dirname "$0")/.."

rm -f auto-imports.d.ts components.d.ts

build_out="$(mktemp -d)"
trap 'rm -rf "$build_out"' EXIT

npx vite build --mode development --outDir "$build_out" --emptyOutDir --logLevel warn

if [ ! -f auto-imports.d.ts ] || [ ! -f components.d.ts ]; then
	echo "Failed to generate auto-imports.d.ts / components.d.ts" >&2
	exit 1
fi
