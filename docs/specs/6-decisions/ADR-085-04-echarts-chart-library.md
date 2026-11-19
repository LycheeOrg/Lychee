# ADR-085-04: ECharts draws the Insights charts

- **Status:** Accepted
- **Date:** 2026-10-05
- **Related features/specs:** Feature 085 (docs/specs/4-architecture/features/085-library-insights/spec.md)
- **Related open questions:** Q-085-06, Q-085-13

## Context

Lychee has no chart library: the Statistics punch card is a hand-made div grid. Insights phase 1 needs bars, histograms, a 7 × 24 heatmap, a calendar heatmap and rankings; phases 2 and 3 add a treemap, a streamgraph, a radar, stacked areas and a scatter. Lychee must run offline, so any library is bundled by Vite. New dependencies need owner approval.

Tree-shaken bundles of the chart types listed above, minified and gzipped with esbuild (2026-10-05):

| Library | Packages | Gzipped | Missing |
|---------|----------|---------|---------|
| ECharts 6.1.0 | `echarts`, `vue-echarts` | 250 KB | — |
| Chart.js 4.5.1 | `chart.js`, `vue-chartjs`, `chartjs-chart-matrix`, `chartjs-chart-treemap` | 74 KB | streamgraph; calendar assembled from the matrix plugin; canvas only |
| unovis 1.7.1 | `@unovis/ts`, `@unovis/vue` | 83 KB | radar, calendar layout, streamgraph |

## Decision

- Add `echarts` (Apache-2.0) and `vue-echarts` (MIT) as dependencies.
- Import from `echarts/core` only the chart types and components in use, with the SVG renderer, in one registration module under `resources/js/v8/`.
- Load the registration module and the chart components only from the Insights view, so they form a lazy chunk.
- Theme every chart from one shared theme built from the Nuxt UI colour tokens, switched with light and dark mode.

## Consequences

### Positive
- Every chart of phases 1–3 is built in, including the calendar heatmap (calendar coordinate system) and the streamgraph (themeRiver).
- SVG output keeps charts sharp and inspectable; one theme object covers all charts.

### Negative
- The largest of the three options; paid only by visitors of the Insights page.
- Option-object API instead of Vue components; chart definitions live in small typed builder functions.
- Theme colours must be read from CSS variables at runtime and re-applied on theme switch.

## Alternatives Considered

- **Hand-built SVG components:** no dependency, but every axis, tooltip and layout written by hand; rejected in Q-085-06.
- **Chart.js with plugins:** smallest, but four packages, canvas only, no streamgraph.
- **unovis:** SVG and Vue components, but no radar, calendar layout or streamgraph.

## Security / Privacy Impact

No network access: the library is bundled. No user-provided HTML is passed to tooltips without escaping (device names and lens strings come from EXIF).

## Operational Impact

- Dependabot tracks two more packages.
- The Insights route chunk grows by about 250 KB gzipped; other routes are unchanged.

## Links

- Related spec sections: `docs/specs/4-architecture/features/085-library-insights/spec.md#non-functional-requirements` (NFR-085-07)
- Related ADRs: ADR-085-03
