---
name: Lychee (v8)
description: A self-hosted photo manager that feels like a darkroom console — calm, precise chrome around photos that do the talking.
colors:
  primary: "#0ea5e9"
  secondary: "#3b82f6"
  success: "#16a34a"
  warning: "#eab308"
  error: "#b91c1c"
  neutral-light: "#f8fafc"
  neutral-dark: "#18181b"
  flag-nsfw: "#d946ef"
  flag-trophy: "#06b6d4"
  flag-person: "#9333ea"
  flag-rated: "#f97316"
typography:
  title:
    fontFamily: "Helvetica Neue, Helvetica, Arial, sans-serif"
    fontSize: "1.125rem"
    fontWeight: 700
    lineHeight: 1.4
  body:
    fontFamily: "Helvetica Neue, Helvetica, Arial, sans-serif"
    fontSize: "1rem"
    fontWeight: 400
    lineHeight: 1.5
  label:
    fontFamily: "Helvetica Neue, Helvetica, Arial, sans-serif"
    fontSize: "0.65rem"
    fontWeight: 500
    lineHeight: 0.8rem
    letterSpacing: "normal"
rounded:
  sm: "0.25rem"
  md: "0.375rem"
  lg: "0.5rem"
  xl: "0.75rem"
  full: "9999px"
components:
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "#ffffff"
    rounded: "{rounded.xl}"
  button-ghost:
    backgroundColor: "transparent"
    rounded: "{rounded.xl}"
  card:
    backgroundColor: "{colors.neutral-light}"
    rounded: "{rounded.lg}"
  photo-tile:
    rounded: "{rounded.lg}"
---

# Design System: Lychee (v8)

## Overview

**Creative North Star: "The Darkroom Console"**

Lychee v8 is the panel around the photograph, not a rival to it. The chrome is calm and restrained — neutral slate/zinc surfaces, ghost-first buttons, sky blue spent sparingly — so that when a photo appears, it's the only saturated thing on screen. That restraint is deliberate console-craft, not blankness: the interface is precise and utilitarian where it's doing archive work (dense lists, bulk-edit tables, sticky headers, badges that pack a lot of state into a few pixels), and warmer and more tactile exactly where the user is handling photos directly — album tiles fan out into a small stack of offset prints on hover, tiles pick up a soft shadow when they become interactive, corners round off.

The "darkroom" isn't a literal dark theme; it's the photo-viewing chrome specifically: the lightbox goes to true black, tile shadows lean into `shadow-black/25` rather than a generic gray, and NSFW/face overlays behave like something you'd handle carefully under red light. Everywhere that isn't directly framing a photo — nav, forms, tables, settings — stays on the ordinary slate/zinc console surface. This is the load-bearing distinction: **darkroom mood belongs to the photo-viewing surfaces, not the whole app.**

This is a self-hosted tool competing on native-app-feel and simplicity against heavier all-in-one platforms (see PRODUCT.md Positioning); the UI earns that by never reading as a generic open-source admin panel — no boxy unstyled tables, no default-Bootstrap chrome, no dashboard sprawl for its own sake.

**Key Characteristics:**
- Neutral-first surfaces (slate light / zinc dark); sky blue is a rare accent, not a wash.
- Ghost/soft buttons dominate; solid fills are reserved for the one action that matters on a screen.
- Photos and their framing (tiles, lightbox, overlays) are the only place true black and visible shadow live.
- A small, named palette of "flag" hues (fuchsia, cyan, purple, orange) carries semantic badge meaning and appears nowhere else.
- Dialogs and slide-over drawers, not push-down panels — content position never jumps when a tool opens.

## Colors

Overwhelmingly neutral, with color spent as signal, not decoration.

### Primary
- **Console Sky** (`#0ea5e9` light / `#38bdf8` dark, Tailwind `sky-500`/`sky-400`): the one accent color. Focus rings, active nav/route state, the count badge on nav items, and the rare high-emphasis solid button (e.g. scroll-to-top). Forced to white text on the solid variant in both themes (`vite.config.ts` compound variant) rather than Nuxt UI's default dark-neutral-on-light-primary, because "white text on blue" is the deliberate brand pairing here.

### Secondary
- **Ledger Blue** (`#3b82f6` light / `#60a5fa` dark, Tailwind `blue-500`/`400`): doubles as both the secondary role and the `info` semantic role (alerts, informational badges like the "public" flag).

### Neutral
- **Slate** (light mode, `slate-50`→`slate-950`): app background, panels, text, borders.
- **Zinc** (dark mode, `zinc-50`→`zinc-950`): the dark-mode neutral family is deliberately swapped from slate to zinc, not just re-shaded — this mirrors v7's Aura preset and keeps dark mode reading as genuinely neutral (zinc's near-neutral gray) rather than blue-tinted (slate's undertone). Preserve this family swap in any future palette work; don't let dark mode collapse to "slate but darker."

### Semantic (success / warning / error)
- **success** `#16a34a`/`#22c55e` (green-600/500) — confirmations, "public" success states.
- **warning** `#eab308`/`#facc15` (yellow-500/400) — also the "favorite"/starred badge role.
- **error** `#b91c1c`/`#dc2626` (red-700/600) — also the "danger" badge role (e.g. unsorted).

### Flag palette (badges only — do not use elsewhere)
A closed, named set of hues reserved exclusively for the small corner badges on album/photo tiles (`resources/js/v8/utils/albumBadgeColors.ts`), always paired 500 (light) / 400 (dark):
- **nsfw** — fuchsia (`#d946ef`/`#e879f9`)
- **trophy** (best-pictures, rated) — cyan (`#06b6d4`/`#22d3ee`)
- **person** (face/person album) — purple (`#9333ea`/`#a855f7`)
- **rated** — orange-500 (`#f97316`)
- **link** (public-hidden) — orange-400 (`#fb923c`), one shade lighter than **rated** to stay distinguishable within the same hue

### Named Rules
**The One Role, One Hue Rule.** Every badge role maps to exactly one hue at exactly one light/dark shade pair, centralized in `albumBadgeColors.ts`. A new badge role gets a new named entry there — never a hardcoded `bg-[#hex]` or a borrowed Tailwind shade at the call site. This is the fix for the drift `modernization.md` catalogued (hardcoded hex badges, mismatched borders); the map is the enforcement point.

**The Chrome Stays Neutral Rule.** Sky blue and the semantic colors are the only colors permitted on ordinary UI chrome (buttons, nav, panels, borders). If a screen needs more than one accent hue outside the flag-badge context, that's a sign color is being used decoratively, not semantically — stop and use weight/space instead.

## Typography

**Body Font:** Helvetica Neue (with Helvetica, Arial, sans-serif fallback) — a plain system stack, no custom display face loaded for v8 chrome. Text renders instantly and identically offline; this is deliberate, not an oversight (see PRODUCT.md's offline-only constraint).

**Character:** Utilitarian and dense-capable — no decorative type voice. Hierarchy is carried by weight and size steps, not by font choice.

### Hierarchy
- **Title** (700, 1.125rem `text-lg`, 1.4 line-height): panel and section headers — "Lychee" / "Admin" section labels in the nav, header titles.
- **Body** (400, 1rem, 1.5 line-height): default UI copy, form labels, list content.
- **Label** (500, 0.65rem `text-2xs` / 0.55rem `text-3xs`, tight line-height): badge counts, timestamps, and other high-density caption text — custom sizes added below Tailwind's default scale specifically because the archive/list views need a step smaller than `text-xs` to stay dense without crowding.

### Named Rules
**The No Display Face Rule.** v8 loads no separate display/hero typeface. Every future component reuses the system body stack; introducing a second font family is a departure-mode decision, not a component-level one.

## Layout

Standard Tailwind spacing scale (4px base unit), uncustomized — no project-specific spacing tokens beyond it. The app shell has one fixed dimension, `--ui-header-height: 3.5rem`, and a fluid `--ui-container: 100%` (no max-width constraint; the gallery is expected to use the full viewport at any size).

Gallery grids are responsive by breakpoint fraction rather than a fixed column count (album tiles: `sm:w-[25vw] → 4xl:13rem` stepped across six breakpoints), and mobile column count is separately user-configurable (1/2/3 per row) rather than purely viewport-driven — density here is a user preference, not just a media query.

Navigation is an off-canvas `USlideover`, not a persistent rail: on any viewport the gallery gets full width, and navigation is summoned rather than permanently reserving space. Bulk tools and filters use dialogs/drawers for the same reason — content never gets pushed down by an opened panel (a deliberate v7→v8 fix, see `Todo.todo`).

## Elevation & Depth

Elevation is being deliberately grown from today's single-shadow baseline into three named tiers. Codify these three going forward rather than reaching for shadow values ad hoc:

### Shadow Vocabulary
- **Resting** (`shadow-sm`): default state for panels, cards, and static surfaces. The majority case — most of the UI has no visible lift at rest.
- **Raised** (`shadow-md shadow-black/25`): the interactive-tile treatment. Photo and album tiles carry this always (not just on hover) — it's what marks something as "a print you can pick up," and the `black/25` tint (not a generic gray shadow) is what ties the tile back to the darkroom/photo-chrome mood rather than the neutral console chrome.
- **Floating** (`shadow-lg`): modals, slide-overs, and anything genuinely overlaying the page on open.

### Named Rules
**The Photos Get Weight Rule.** Visible shadow is earned by being a photograph or something that floats above the page (modal/drawer). Ordinary chrome — buttons, nav items, form fields — stays flat; depth is not spent on furniture.

## Shapes

Corners round from `sm` (0.25rem, small chips/inputs) through `md` (0.375rem) and `lg` (0.5rem — the default "surface" radius: cards, panels, standard photo/album tiles) up to `xl` (0.75rem — deliberately rounder than Nuxt UI's own button default, an intentional override in `theme.ts` making buttons read softer/friendlier than the rest of the chrome). `full` (pill) is reserved for avatars and circular icon buttons.

Photo/album tile rounding and the tile border are both **user-configurable** (`is_rounded_corners_enabled`, `is_album_border_enabled`), not fixed — a deliberate concession to users who want the flatter "print" look. Default new components to the rounded, borderless treatment; the flat/bordered look is a supported alternate state, not the target aesthetic to design toward.

## Components

### Buttons
- **Shape:** `rounded-xl` (0.75rem) — rounder than the rest of the chrome, by deliberate override.
- **Ghost** (default, ~60% of all button usage): transparent background, text-only, color appears on hover/focus. This is the default choice for any new button — reach for ghost first.
- **Soft** (~35%): tinted background at low opacity, used for secondary emphasis (e.g. a selected filter state) without committing to a solid fill.
- **Solid** (rare, reserved): full color fill, forced white text (`text-white`) even in dark mode. Use for the single highest-emphasis action on a screen — not a default, a deliberate escalation.
- **Outline:** occasional third option where a button needs a visible boundary without color commitment (e.g. sitting on a photo/image background).

### Named Rules
**The Ghost-First Rule.** Every new button starts as `ghost`. Escalate to `soft`, then `outline`, then — only when a screen has exactly one action that must win — `solid`. A screen with two solid buttons has lost the hierarchy this system depends on.

### Badges
- **Style:** small pill/rounded-full or rounded-sm chip depending on context (nav count badges are `size="sm" color="primary"`; album/photo corner flags use the closed flag-hue map above).
- **State:** count badges (numeric, primary color) vs. flag badges (icon-only, role-colored) are visually distinct families — don't blend their styling.

### Cards / Containers
- **Corner Style:** `rounded-lg` (0.5rem).
- **Background:** neutral surface (`bg-default`/`bg-elevated` per Nuxt UI's semantic scale — never a raw slate/zinc utility class at the call site).
- **Shadow Strategy:** Resting tier (`shadow-sm`) or flat, per Elevation & Depth.
- **Border:** `border-default` when a boundary is needed; avoid hardcoded `border-neutral-*` shades (see Do's and Don'ts).

### Photo & Album Tiles (signature component)
The tile is the one place the system allows itself real texture. A photo tile carries the Raised shadow permanently (`shadow-md shadow-black/25`), rounds by default, and shows a blurred low-res placeholder that cross-fades into the loaded image. An album tile goes further: it stacks three copies of its cover image, and on hover the two hidden copies fan out with a small rotate + translate (`group-hover:-rotate-2 group-hover:-translate-x-3 group-hover:translate-y-2` / mirrored), reading as a small pile of prints being spread by hand. This gesture is the system's one moment of physical playfulness and should stay unique to album tiles — don't reuse the fan-hover for unrelated components, its meaning ("this is a stack of many photos") depends on scarcity.

### Navigation
- **Style:** vertical `UNavigationMenu` inside an off-canvas slide-over (`USlideover`), not a persistent rail. Small leading icons (12–16px), `text-muted` default state, primary-color active/hover state. Section labels (`Admin`, `Lychee`) use the Title typography role at `text-toned`.
- **Mobile treatment:** identical to desktop — it's already an overlay drawer at every breakpoint, so there's no separate mobile nav pattern to maintain.

## Do's and Don'ts

### Do:
- **Do** default every new button to `ghost`, escalating through `soft`/`outline` before ever reaching `solid`.
- **Do** author badge and flag colors through the `AlbumBadgeRole` map (`albumBadgeColors.ts`), never a call-site hex or ad hoc Tailwind shade.
- **Do** use Nuxt UI's semantic scale (`text-muted`/`text-toned`/`text-highlighted`, `bg-default`/`bg-elevated`/`bg-muted`, `border-default`/`border-muted`/`border-accented`) for anything that must adapt across light/dark — never a raw `slate-*`/`zinc-*` utility at the call site.
- **Do** keep the Raised shadow (`shadow-md shadow-black/25`) exclusive to photo/album tiles; it's what marks something as a handled photograph.
- **Do** reach for a dialog or slide-over for any bulk tool, filter panel, or settings surface — never a dropdown that pushes page content down.

### Don't:
- **Don't** use the PrimeUI-era utility classes `text-muted-color`, `text-muted-color-emphasis`, or `text-primary-emphasis` in v8 — they resolve to nothing (no `--p-*` variables are defined outside the legacy v7 build) and silently fall back to inherited color. Use `text-muted`, `text-toned`/`text-highlighted`, and `text-primary` respectively.
- **Don't** hardcode a raw hex (`bg-[#ff82ee]`) or an off-map Tailwind shade for anything that already has a semantic or role-based token available.
- **Don't** spend a flag-palette hue (fuchsia/cyan/purple/orange) outside the album/photo badge context — that palette's legibility depends on it appearing nowhere else.
- **Don't** reach for `bg-black`/`text-white` directly for new chrome; that's legacy shorthand for what should be an inverted/elevated semantic token, except within the lightbox/overlay photo-viewing chrome where true black is the intended darkroom treatment.
- **Don't** let the interface read as a generic self-hosted admin panel — boxy unstyled tables, default form chrome, or dashboard sprawl for its own sake is the thing this system is explicitly designed against.
