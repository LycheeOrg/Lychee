# Current Session

_Last updated: 2026-02-24_

## Active Features

**Feature 015 – Upload Watermark Toggle**
- Status: Planning (spec, plan, tasks complete)
- Priority: P2
- Started: 2026-02-24
- Dependencies: None

## Session Summary

Feature 015 specification, plan, and tasks updated to include admin setting for controlling watermark opt-out availability.

### Feature 015: Upload Watermark Toggle

**User Request:**
- Add toggle switch to upload modal for watermark control
- Toggle visible only when watermarking is globally enabled and configured
- Default state: ON (watermark enabled)
- Pass watermark preference through upload pipeline
- **NEW:** Admin setting `watermark_optout_disabled` to force watermarking (default: false)

**Key Design Decisions:**
- `is_watermarker_enabled` computed in UploadConfig (config + photo_id + Imagick check)
- `can_watermark_optout` computed as: `is_watermarker_enabled` AND NOT `watermark_optout_disabled`
- `apply_watermark` optional boolean parameter in upload request
- Flag passed through ProcessImageJob to ApplyWatermark pipe
- Toggle state persists per upload session, reset on modal close
- Backward compatible: missing parameter = use global setting
- Admin can disable opt-out via `watermark_optout_disabled` config

**Implementation Increments:**
| Increment | Description | Est. Time |
|-----------|-------------|-----------|
| I0 | Backend: Add watermark_optout_disabled config | 30 min |
| I1 | Backend: Extend UploadConfig with watermarker status | 30 min |
| I2 | Backend: Add watermark flag to upload request | 30 min |
| I3 | Backend: Pass watermark flag to ProcessImageJob | 45 min |
| I4 | Backend: ApplyWatermark pipe respects flag | 45 min |
| I5 | Frontend: Extend TypeScript types | 15 min |
| I6 | Frontend: Add toggle to UploadPanel | 45 min |
| I7 | Frontend: Pass watermark flag in upload service | 45 min |
| I8 | Translations (21 languages) | 30 min |
| I8b | Admin UI: Add watermark_optout_disabled setting | 30 min |
| I9 | Integration and documentation | 30 min |

**Tasks:** 24 tasks across 11 increments (I0-I9, I8b)

**Deliverables:**
1. [spec.md](docs/specs/4-architecture/features/015-upload-watermark-toggle/spec.md)
2. [plan.md](docs/specs/4-architecture/features/015-upload-watermark-toggle/plan.md)
3. [tasks.md](docs/specs/4-architecture/features/015-upload-watermark-toggle/tasks.md)

## Next Steps

1. Run analysis gate checklist
2. Begin implementation starting with I0 (watermark_optout_disabled config)

## Open Questions

None - no open questions for Feature 015.

## References

**Feature 015:**
- Feature spec: [015-upload-watermark-toggle/spec.md](docs/specs/4-architecture/features/015-upload-watermark-toggle/spec.md)
- Implementation plan: [015-upload-watermark-toggle/plan.md](docs/specs/4-architecture/features/015-upload-watermark-toggle/plan.md)
- Task checklist: [015-upload-watermark-toggle/tasks.md](docs/specs/4-architecture/features/015-upload-watermark-toggle/tasks.md)

**Common:**
- Roadmap: [roadmap.md](docs/specs/4-architecture/roadmap.md)
- Open questions: [open-questions.md](docs/specs/4-architecture/open-questions.md)

---

**Session Context for Handoff:**

Feature 015 (Upload Watermark Toggle) fully planned with 24 tasks across 11 increments. Key design:
1. Toggle in upload modal, visible only when watermarking enabled AND opt-out allowed
2. `is_watermarker_enabled` + `can_watermark_optout` properties in UploadConfig
3. `apply_watermark` optional boolean in upload request
4. ProcessImageJob passes flag to ApplyWatermark pipe
5. New admin setting `watermark_optout_disabled` (default: false) to force watermarking
6. Backward compatible: missing param uses global setting

Ready to begin implementation starting with I0 (config migration).
