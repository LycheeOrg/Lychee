# Feature 097 Tasks – PDF Photo View

_Status: Complete_  
_Last updated: 2026-10-17_

## Checklist
- [x] T-097-01 – Skip wheel navigation while a PDF is shown (FR-097-01, S-097-01, S-097-02, S-097-05).  
  _Verification commands:_ `vp run typecheck`, `vp check --fix`

- [x] T-097-02 – Hide the dock for PDFs, remove `Dock.isNarrowMenu` (FR-097-02, S-097-03, S-097-04).  
  _Verification commands:_ `vp run typecheck`, `vp check --fix`

- [x] T-097-03 – Move and delete buttons in `PhotoHeader` for PDFs (FR-097-03, S-097-03, S-097-03b, S-097-04).  
  _Verification commands:_ `vp run typecheck`, `vp check --fix`

## Notes / TODOs
No frontend unit harness for these components. Verified 2026-10-17 with `vp check` and `vp run typecheck` on the three changed files. Manual browser check of S-097-01 … 05 left to the owner. Run `vp check --fix` on the changed files only: on the whole tree it rewrites unrelated files and breaks `EmbedCodeDialog.vue` (`<\/script>` unescaped).
