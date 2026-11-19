# Open Questions – Feature 041

Open questions for [Feature 041](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|

## Question Details

### ✅ Q-041-01 · Frontend scope: session doc lists I4 but spec/plan/tasks exclude frontend

**Status:** Resolved (Option A) — 2026-05-31  
**Feature:** F-041 – Upload Photo Metadata  
**Resolution:** Frontend is out of scope. `_current-session.md` corrected to remove I4 (T-041-12/13) and reflect 12 tasks across increments I1–I3, I5.

**Question**  
`_current-session.md` describes a four-increment frontend increment I4 (T-041-12: update TS types / `upload-service.ts`; T-041-13: update `UploadingLine.vue` / `UploadPanel.vue`). However, `spec.md` Non-Goals and `plan.md` Out-of-Scope both explicitly exclude "Any UI / frontend changes (TypeScript types, Vue components, upload panel)." The `tasks.md` file has 12 tasks (T-041-01 to T-041-11, T-041-14) and contains no T-041-12 or T-041-13 entries; the plan has no I4 section. The session doc and the governing artefacts are contradictory. Are the frontend tasks in scope for this feature?

---

#### 🅰️ Option A – Frontend out of scope; correct session doc (**chosen**)
- **Idea:** Accept spec/plan/tasks as authoritative. Remove the I4 reference from `_current-session.md`; confirm 12 tasks / 4 increments (I1–I3, I5) is the final task count.
- **Spec impact:** None — spec already excludes frontend. Session doc gets a one-line correction.
- **Pros:**  
  - ✅ Keeps the feature footprint minimal (API-only, as originally designed).  
  - ✅ Eliminates the 12 vs 14 task-count inconsistency.  
  - ✅ No risk of breaking Vue component state management.
- **Cons:**  
  - ❌ Callers still need to update their own TS types manually until the TypeScript transformer is re-run.

---

### ✅ Q-041-02 · Validation on intermediate chunks: does HTTP 422 fire on every chunk?

**Status:** Resolved (Option A) — 2026-05-31  
**Feature:** F-041 – Upload Photo Metadata  
**Resolution:** Validation runs on every chunk (natural Laravel behaviour). The "silently ignored" clause in FR-041-01/02 refers to *application* of the value to the photo model (only on the final chunk), not to validation. FR-041-01 and FR-041-02 updated in spec.md to clarify this distinction.

**Question**  
FR-041-01 states two things: (1) "Rejected with HTTP 422 if `title` exceeds 100 chars" and (2) "Field silently ignored on intermediate chunks (only applied at final chunk)." Laravel's `FormRequest` validation runs on every request. If a client sends `title` on chunk 2 of 5 and it exceeds 100 chars, does the server return HTTP 422 immediately, or is validation deferred to the final chunk?

---

#### 🅰️ Option A – Validate on every chunk (natural Laravel behaviour) (**chosen**)
- **Idea:** Add the `title`/`description` validation rules to `UploadPhotoRequest::rules()` without any chunk-gating. Laravel validates all present fields on every request. A 422 is returned immediately if any chunk carries an invalid `title`.
- **Spec impact:** FR-041-01 "Validation path" and "Failure path" rows remain correct; the "silently ignored" clause refers to the *application* of the value to the photo model (not to validation).
- **Pros:**  
  - ✅ Consistent with Laravel conventions; no special-casing needed.  
  - ✅ Fast feedback: client learns about the bad value immediately, not after all chunks are sent.  
  - ✅ Simpler implementation — no chunk-number inspection in validation.
- **Cons:**  
  - ❌ Mildly surprising if a client intentionally sends `title` only on the final chunk but sent a large value on an earlier one as a test.

---

### ✅ Q-041-03 · `ApplyUserProvidedMetadata` pipe type: StandalonePipe or SharedPipe?

**Status:** Resolved (Option A) — 2026-05-31  
**Feature:** F-041 – Upload Photo Metadata  
**Resolution:** `ApplyUserProvidedMetadata` is implemented as a `StandalonePipe` only. For duplicate uploads the pipe never runs; user-supplied `title`/`description` are discarded. FR-041-09 in spec.md extended to state this explicitly. T-041-05 notes in tasks.md updated to confirm `StandalonePipe` only.

**Question**  
T-041-05 notes: "Pipe must handle `DuplicateDTO|StandaloneDTO` type union consistent with `SharedPipe` convention if applicable; otherwise implement as `StandalonePipe` only." The spec names the pipe `Standalone\ApplyUserProvidedMetadata` (suggesting `StandalonePipe`), and the `Create` action's plan inserts it only in `handleStandalone()` and `handlePhotoLivePartner()`, not in a shared duplicate path. However, there is no explicit statement about what should happen when a duplicate is detected and the caller also supplied a `title`. Should user-supplied `title`/`description` be applied to the *duplicate's* existing record, or discarded?

---

#### 🅰️ Option A – StandalonePipe only; discard title/description for duplicates (**chosen**)
- **Idea:** Implement `ApplyUserProvidedMetadata` as a `StandalonePipe`. It is inserted only in `handleStandalone()` and `handlePhotoLivePartner()`. For duplicate uploads the pipe never runs; the user-supplied metadata is discarded (the duplicate keeps its existing title/description).
- **Spec impact:** Consistent with the existing `Standalone\` namespace and the fact that FR-041-09 already accepts that `expected_id` is meaningless for duplicates. Extend FR-041-09 to state that `title`/`description` are also discarded on duplicate detection.
- **Pros:**  
  - ✅ Simplest implementation — no type-union handling needed.  
  - ✅ Consistent with Non-Goal: duplicate semantics are out of scope.  
  - ✅ Avoids silently mutating a shared photo record that other users/albums may reference.
- **Cons:**  
  - ❌ Caller-supplied `title` is silently dropped on duplicates; caller only learns this via the HTTP 409 status.
