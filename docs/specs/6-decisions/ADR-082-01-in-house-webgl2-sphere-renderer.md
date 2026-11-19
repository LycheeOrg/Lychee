# ADR-082-01: In-house TypeScript + WebGL2 sphere renderer

- **Status:** Accepted
- **Date:** 2026-10-03
- **Related features/specs:** Feature 082 (docs/specs/4-architecture/features/082-360-photos/spec.md), Feature 078 (docs/specs/4-architecture/features/078-lightbox-pan-zoom/spec.md)
- **Related open questions:** Q-082-01

## Context

The v8 lightbox must display equirectangular 360° photos as an interactive sphere (Feature 082). Constraints:
- Lychee works with no network connection; every asset is bundled.
- New npm dependencies need owner approval; Feature 078 (lightbox pan & zoom) was built dependency-free on Pointer Events (NFR-078-03).
- The sphere has to share the lightbox's gesture model: drag must never also navigate, the Feature 078 zoom keys and `PhotoState.zoom_controls` registration apply, the EXIF overlay rotates on tap.
- Partial photo spheres carry their crop in GPano XMP, which Lychee's generated size variants do not keep, so the crop comes from the API (`PanoramaResource`), not from the image file.

Rendering an equirectangular sphere is one full-screen quad: a fragment shader turns each screen pixel into a view ray, converts it to longitude/latitude and samples the texture. The per-frame CPU work is building one rotation from yaw, pitch and field of view and setting two uniforms; image decoding and downscaling are done by the browser (`createImageBitmap`).

Affected module: v8 frontend (`resources/js/v8/utils/sphere.ts`, `resources/js/v8/utils/sphereShader.ts`, `resources/js/v8/composables/useSphereViewer.ts`, `PhotoBox.vue`).

## Decision

Write the sphere renderer in TypeScript on WebGL2, without any library:
- pure camera and projection math in `sphere.ts` (pointer delta → yaw/pitch, clamps, zoom factor ↔ field of view, needed texture width, initial view, partial-panorama mapping);
- the GLSL program in `sphereShader.ts`, sampling with explicit gradients to avoid a seam at ±180°;
- a composable `useSphereViewer.ts` owning the canvas, Pointer Events, WebGL2 context, texture upload, context loss/restore and disposal, loaded as a lazy chunk;
- WebGL2 unusable → the lightbox keeps the flat image with Feature 078 zoom.

## Consequences

### Positive
- No new dependency, nothing to keep in sync with an upstream release cadence.
- The gestures, keys and zoom state reuse Feature 078's model directly.
- Small lazy chunk; galleries without panoramas download nothing extra.
- Partial-panorama data is passed explicitly from the API.

### Negative
- Lychee maintains the shader, the context-loss handling and the texture downscaling.
- Features that libraries provide (gyroscope, cubemaps, markers, video) have to be written when they are needed.
- No JS unit runner exists, so the math is checked by scratch scripts and Playwright rather than committed unit tests.

## Alternatives Considered

- **`@photo-sphere-viewer/core` + `three`:** mature (gyroscope, cubemaps, markers, video); two new dependencies of about 600 KB, own gesture and keyboard handling to reconcile with the lightbox, and GPano crop data would have to be injected because it reads it from the image file.
- **`pannellum`:** small, raw WebGL; global-script design, no ESM typings, slow release cadence.
- **Rust compiled to WASM (`wgpu` or `web-sys` WebGL bindings):** no speed gain since the work is in the GPU shader and the per-frame CPU work is negligible; about 1–2 MB of WASM with `wgpu`, JavaScript glue still needed for canvas and events, and the WASM build/embedding pipeline for a few hundred lines of TypeScript.
- **WebGPU instead of WebGL2:** not available in every supported browser; brings nothing for drawing one textured quad.

## Security / Privacy Impact

- No new network origin; textures come from the same size-variant URLs the lightbox already uses, and the original is requested only when its URL is exposed to the viewer (FR-082-14).
- No secrets involved.

## Operational Impact

- No server-side cost.
- GPU memory is released on navigation (FR-082-16); rendering happens only when the view changes (NFR-082-04).

## Links

- Related spec sections: `docs/specs/4-architecture/features/082-360-photos/spec.md` FR-082-08 to FR-082-16, NFR-082-02 to NFR-082-04
- Related open questions: `docs/specs/4-architecture/features/082-360-photos/open-questions.md` Q-082-01
