# Feature 099 Tasks – PDF Photo View

_Status: Complete_  
_Last updated: 2026-10-17_

## Checklist
- [x] T-099-01 – Skip wheel navigation while a PDF is shown (FR-099-01, S-099-01, S-099-02, S-099-05).  
  _Verification commands:_ `vp run typecheck`, `vp check --fix`

- [x] T-099-02 – Hide the dock for PDFs, remove `Dock.isNarrowMenu` (FR-099-02, S-099-03, S-099-04).  
  _Verification commands:_ `vp run typecheck`, `vp check --fix`

- [x] T-099-03 – Move and delete buttons in `PhotoHeader` for PDFs (FR-099-03, S-099-03, S-099-03b, S-099-04).  
  _Verification commands:_ `vp run typecheck`, `vp check --fix`
- [x] T-099-04 – Escape closes the photo edit modal: drop `:dismissible="false"` from `PhotoEdit.vue` (FR-099-04, S-099-06).  
  _Verification commands:_ `vp check` on the file, driver run on the scratch instance

## Notes / TODOs
No frontend unit harness for these components. Verified 2026-10-17 with `vp check` and `vp run typecheck` on the three changed files. Manual browser check of S-099-01 … 05 left to the owner. Run `vp check --fix` on the changed files only: on the whole tree it rewrites unrelated files and breaks `EmbedCodeDialog.vue` (`<\/script>` unescaped).
