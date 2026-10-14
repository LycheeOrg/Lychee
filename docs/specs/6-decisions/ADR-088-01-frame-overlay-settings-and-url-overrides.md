# ADR-088-01: Frame Overlay Settings, URL Overrides and Privacy Boundary

- **Status:** Accepted
- **Date:** 2026-10-13
- **Related features/specs:** Feature 088 (docs/specs/4-architecture/features/088-frame-overlays/spec.md)
- **Related open questions:** Q-088-01

## Context

Feature 088 adds a clock box and an info box over the Frame, with about 17 values (switches, time format, positions, colours, opacities). A frame often runs on a dedicated device opened by a guest or a kiosk account, and two devices on one instance may need different overlays. The owner chose admin settings as the defaults and URL query parameters as per-device overrides (Q-088-01, options A and C).

`GET /api/v2/Frame` is called on every refresh. The overrides live in the browser's URL, so the server must either receive them on each request or send everything the browser may need. Lychee's request convention avoids optional request fields: new fields are required and every caller sends them.

## Decision

1. The overlay settings are admin settings in the `Mod Frame` category (`level = 0`, `required_keys = mod_frame_enabled`), listed in spec DO-088-05.
2. Each overlay setting has a URL query parameter on `/frame/{albumId?}`, named after the setting without `mod_frame_`. The v8 Frame view merges the parameters over the admin values: a valid parameter wins, an absent or invalid one leaves the admin value. `mod_frame_include_subalbums` has no parameter.
3. `GET /api/v2/Frame` takes no new request field. Its response carries the admin values (`clock`, `info`) and the photo information (`photo`) that the privacy rules allow, whatever the overlay switches say. The browser applies the merged switches.
4. The privacy boundary is the existing rules, applied on the server: album titles only for albums the viewer can browse to (`AlbumQueryPolicy::applyBrowsabilityFilter`), no title for guests when `file_name_hidden` is on, no exposure or camera values when `display_exif_data` is off.

## Consequences

### Positive
- Every device can look different without storage or a settings panel; a bookmarked URL keeps working.
- The request contract of `GET /Frame` is unchanged, so v7 and existing callers need no update.
- Admin changes reach every frame on its next refresh.

### Negative
- Each refresh computes and sends the photo information even when no box shows it: a few strings and, in frames that offer the album line, one album query.
- The overlay switches are presentation defaults, not permissions: anyone can switch a box on through the URL.

## Alternatives Considered

- **Overrides sent to the server as request fields:** the server would send only the enabled parts. Rejected: about 17 optional request fields, against the convention, for no privacy gain since any visitor can set them.
- **Admin settings only (option A alone):** one look per instance. Rejected by the owner.
- **URL parameters only (option C alone):** no settings page support. Rejected by the owner.
- **Per-device settings panel in browser storage (option B):** a second UI to build and storage that can be cleared. Not chosen.

## Security / Privacy Impact

- No data beyond what the viewer could already see: the overlay switches never widen access, and the server applies the existing privacy settings and album browsability before sending anything.
- Hidden (link-only) albums are not named in the album line, even when the viewer could open them by link.

## Operational Impact

- One extra album query per refresh in the fully random frame and in sub-album mode; none in other frames.
- No new dependency, no network access beyond the Lychee server.

## Links

- Related spec sections: `docs/specs/4-architecture/features/088-frame-overlays/spec.md#functional-requirements` (FR-088-10, FR-088-13, FR-088-15)
- Related ADRs: none
