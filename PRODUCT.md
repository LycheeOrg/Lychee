# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

Three primary audiences, all self-hosting Lychee on their own server or web space rather than trusting a third-party cloud:

- **Self-hosting individuals/families** — privacy-conscious users storing, browsing, and selectively sharing a personal photo/video library.
- **Photographers/creators** — curating and publicly sharing albums (portfolios, events, galleries) with clients or an audience; self-hosting mainly for control over presentation.
- **Small teams/organizations** — using Lychee as a shared, managed archive across multiple accounts/roles (club, family group, small business), closer to a lightweight DAM than a single personal library.

## Product Purpose

A free, open-source photo-management tool that installs in seconds and lets people upload, organize, browse, and share photos and videos "just like using a native application," while keeping storage and control on infrastructure the user owns. Success means people trust it enough to make it their primary photo home instead of Google Photos/iCloud/Nextcloud alternatives, and it stays pleasant to use at real personal-library scale (tens of thousands of photos).

## Positioning

Two mechanisms a neighboring self-hosted gallery (Immich, PhotoPrism, Nextcloud Memories, Piwigo) could not truthfully copy without becoming a different product:

- **Polished, native-app-like UX as the differentiator itself** — not a feature checklist item but the product's actual edge; the interface is expected to read as premium, considered software, not a typical open-source admin panel.
- **Simplicity and lightweight footprint** — an install-in-seconds, low-resource-use, focused alternative to heavier all-in-one self-hosted platforms, for users who want a fast, uncomplicated gallery rather than a sprawling suite.

## Operating Context

- Self-hosted deployment: Docker (recommended) or bare web space, backed by MySQL/MariaDB or PostgreSQL. Users install and administer their own instance.
- Core surfaces: gallery browsing (albums/photos, list and thumb views), lightbox/photo viewer (images, video, Live Photos, RAW, PDF), timeline, tag view, map (geolocation of photos), search (scoped-to-album and global), favourites, face recognition/clusters, bulk album/photo editing, bulk sharing, job history, user/role administration, statistics.
- Frontend is mid-migration (v8) from PrimeVue to Nuxt UI (`@nuxt/ui` v4 + Tailwind v4) sitting alongside the legacy v7 stylesheet/app; both builds currently coexist behind a flag.
- Multi-language product (`lang/` translations via vue-i18n); UI copy and design work must stay translation-friendly.
- Reverse geocoding can run against a local/offline database as well as external services, reflecting the offline-capable requirement.

## Capabilities and Constraints

- MIT-licensed free core; a separately-sold **Supporter Edition (SE)** unlocks additional functionality and funds development. Where free/SE feature boundaries are visible in the UI, they must be shown clearly and honestly — no dark patterns, no controls disabled without explanation.
- Must work with zero network connection: no runtime dependency on an external host for icons, fonts, CDNs, or telemetry (durable constraint, applies to all future UI work).
- v8 is the active target for new/changed UI; v7 is legacy being phased out. Default new work to v8-only, forking shared modules rather than editing them in place when v7 still depends on them.
- No confirmed additional accessibility standard beyond ordinary good practice — not yet a hard compliance requirement.

## Brand Commitments

- Name: **Lychee**. Tagline framing: "A Stunning and User-Friendly Photo Management System."
- Primary color `sky`, neutral `slate` (light) / `zinc` (dark) — the in-progress v8 Nuxt UI theme (see `resources/js/v8/style/theme.ts`).

## Evidence on Hand

- `ui.todo` — the v8 go-live functional-parity checklist enumerating every surface (navigation shell, gallery browsing, lightbox media matrix, pagination, empty states, flow/frame/timeline/tag/map/search views) that must reach parity with v7.
- No fabricated testimonials, customer names, or benchmarks exist and none should be invented for future work.

## Product Principles

1. **Native-app feel is the product, not a feature.** Every surface should read as considered, premium software — the interface quality itself is the stated differentiator, not an afterthought on top of functional parity.
2. **Own your data, always available.** No feature may assume network access; offline operation is not a fallback mode, it's the baseline.
3. **v8 is the future, v7 is being retired.** New design work targets the Nuxt UI (`@nuxt/ui` + Tailwind v4) system exclusively; don't invest in or entangle further with the PrimeVue-era v7 styling.
4. **Honest free/SE boundary.** Supporter Edition upsell must never read as a dark pattern — gated features are legible as gated, not broken or hidden.
5. **Scale is real.** Design and interaction patterns (pagination, map, timeline) must hold up for libraries in the tens of thousands of photos, not just demo-sized ones.

## Accessibility & Inclusion

No product-specific accessibility standard or user need has been established beyond general good practice; not currently a binding constraint on design work.
