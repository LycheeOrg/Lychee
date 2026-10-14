# Open Questions – Feature 093

Open questions for [Feature 093](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-093-01~~ | 093 | High | Which faces feed the naming queue, and from which endpoint? Options: A) new queue endpoint (unassigned, non-dismissed, clustered first then unclustered; optional `cluster_label` filter), B) reuse the per-cluster faces endpoint only, C) reuse the admin maintenance list | Resolved (A, as a v3 Struct-of-Arrays endpoint; spec FR-093-02, API-093-03) | 2026-10-14 | 2026-10-14 |
| ~~Q-093-02~~ | 093 | High | What does "discard / considered blurry" persist? Options: A) existing `is_dismissed` flag (reversible), B) hard-delete the face, C) new dismissal-reason column (`blurry`) | Resolved (A; spec FR-093-06) | 2026-10-14 | 2026-10-14 |
| ~~Q-093-03~~ | 093 | Medium | How does the name box pick or create a person? Options: A) one combobox, client-side filter, Enter takes the highlighted match or creates the typed name (exact case-insensitive match reuses), B) creating requires picking an explicit "Create «name»" entry, C) A plus a new server-side name search endpoint | Resolved (A; people list from a new v3 `toBase()` SoA endpoint since none exists; spec FR-093-03, API-093-04) | 2026-10-14 | 2026-10-14 |
| ~~Q-093-04~~ | 093 | Medium | How do arrow keys coexist with caret movement in the focused input? Options: A) arrows navigate only while the input is empty, B) arrows always navigate, C) Alt/Option+arrow navigates | Resolved (A; spec FR-093-04) | 2026-10-14 | 2026-10-14 |
| ~~Q-093-05~~ | 093 | High | For non-admins, which photos' faces enter the queue, and through which album is the photo served? Options: A) own photos only (admins: all), photo served via `/api/v3/Asset` through an album of the owner or `unsorted`, B) every photo the user can see, with a per-face `can_dismiss` flag, C) admins only | Resolved (A; spec FR-093-02, API-093-03) | 2026-10-14 | 2026-10-14 |
| ~~Q-093-06~~ | 093 | Medium | On a revisited, pre-filled face, the box is not empty, so arrows move the caret and the user cannot arrow away. Options: A) arrows also navigate while the text equals the pre-filled value, B) Escape clears/restores and arrows then work, C) keep the strict rule (buttons only) | Resolved (A, plus Backspace clears an unchanged pre-filled box in one press; spec FR-093-04, FR-093-06) | 2026-10-14 | 2026-10-14 |
| ~~Q-093-07~~ | 093 | Medium | Optimistic advance (NFR-093-01) versus "keep the face on screen on error" (old FR-093-03). Options: A) advance optimistically; on error toast with an "Go back" action and mark the face as failed in the session history, B) wait for the response before advancing, C) advance optimistically and auto-jump back on error | Resolved (A; spec FR-093-09) | 2026-10-14 | 2026-10-14 |
| ~~Q-093-08~~ | 093 | High | `features.struct-of-array` defaults to off (`STRUCT_OF_ARRAY_ENABLED=false`), so the v3-only view is hidden on default installs. Options: A) keep the flag gate (feature ships dark until SoA is on), B) the two new endpoints skip the flag check, C) v2 fallback endpoints when the flag is off | Resolved (A; flag will be removed later; spec FR-093-07) | 2026-10-14 | 2026-10-14 |
| ~~Q-093-09~~ | 093 | High | The chosen `album_id` can fail the asset access check: `unsorted` filters NSFW (`hide_nsfw_in_smart_albums`) and, with `enable_smart_album_per_owner`, admins only see their own unsorted photos; an admin may also lack a path to another user's album photo when it is NSFW-hidden. Options: A) show the face crop + a face-scoped image route `GET /api/v3/Faces/{id}/photo/{variant}` authorised by face rights, B) keep `/Asset` and pick the album smarter (own album first, unsorted only for the owner, skip faces with no usable album), C) show only the face crop (no full photo) | Resolved (A; ADR-093-01; spec FR-093-01, API-093-05) | 2026-10-14 | 2026-10-14 |
| ~~Q-093-10~~ | 093 | High | Pre-suggest a name for each face? Options: A) queue carries the best suggested person (from `face_suggestions` of assigned faces, else the name last given to a face of the same cluster in this session), shown pre-highlighted; Enter on an empty box accepts it, B) show suggestions as chips only (click/Tab), C) no suggestions | Resolved (A; spec FR-093-03, FR-093-12, API-093-03) | 2026-10-14 | 2026-10-14 |
| ~~Q-093-11~~ | 093 | Medium | How to create a new name that prefix-matches an existing one (typing "Ali" highlights "Alice")? Options: A) last list entry "Create «Ali»" reachable with ↑/↓ plus Shift+Enter always creates, B) nothing highlighted by default unless exact match; ↓ to pick, Enter creates, C) Shift+Enter only | Resolved (A; spec FR-093-03, FR-093-10) | 2026-10-14 | 2026-10-14 |
| ~~Q-093-12~~ | 093 | Medium | `PATCH /Face/{id}` is a blind toggle, so retrying a discard or an un-dismiss whose first request actually succeeded (timeout) flips the face back. Options: A) add an optional explicit `is_dismissed` boolean to the PATCH (sets instead of toggling when given), B) client re-reads the face state before retrying, C) never retry dismiss changes; the user re-acts by hand | Resolved (A; spec FR-093-09, API-093-02) | 2026-10-14 | 2026-10-14 |
| ~~Q-093-13~~ | 093 | Medium | Two persons with the same name. Options: A) homonyms allowed: list rows show crop + face count, exact-name auto-reuse only when the name is unique, "Create another «name»" entry and Shift+Enter always create, B) names unique case-insensitive, enforced server-side, C) keep exact-match reuse; homonyms only created elsewhere | Resolved (A; spec FR-093-03, FR-093-10, FR-093-13) | 2026-10-14 | 2026-10-14 |

## Question Details

### Q-093-01 – Queue source and scope

- **Context:** The People page has no list of unnamed faces. The cluster review page (`/people/clusters`) only sees faces with `cluster_label > -1` through `GET /FaceCluster/{label}/faces`. The admin maintenance list `GET /Face/maintenance` sees every face but is gated by `SettingsPolicy::CAN_EDIT` (admins only). Faces that the clustering left as noise (`cluster_label = -1` or `null`) are therefore not reachable by non-admins today.
- **Option A (recommended) – new queue endpoint:** `GET /Face/queue` returns unassigned, non-dismissed faces the user can assign (same gate as the cluster endpoints: `CAN_BATCH_FACE_OPS`), paginated, ordered by `cluster_label` (similar faces back to back) then by confidence, with unclustered faces last. An optional `cluster_label` filter scopes the queue when it is opened from a cluster card on the review page. Pros: covers every unnamed face; consecutive similar faces make typing fast; one data source for both entry points. Cons: one new endpoint, request class and resource; one more test class.
- **Option B – per-cluster endpoint only:** the view walks clusters one after another through the existing endpoints. Pros: no backend change. Cons: unclustered faces never show up; the client must page through clusters and then through faces.
- **Option C – admin maintenance list:** reuse `GET /Face/maintenance?unassigned_only=1`. Pros: no backend change. Cons: admins only, so regular users with face rights cannot use the view.

### Q-093-02 – Meaning of "discard / considered blurry"

- **Context:** Faces already have `is_dismissed` (set per face, per cluster, in batch, and when a person is deleted), toggled by `PATCH /Face/{id}`. Dismissed faces are hidden from overlays and lists and can be purged by the admin "Destroy dismissed faces" job. "Blurry" today is only the `laplacian_variance` quality metric; there is no blurry flag.
- **Option A (recommended) – existing `is_dismissed`:** Backspace/Space on an empty input dismisses the face. Pros: no schema change; undo is the same toggle; the existing purge job already cleans up. Cons: the reason (blurry vs. not a face vs. stranger) is not stored.
- **Option B – hard delete:** delete the face row and its crop at once. Pros: really "drops the face from existence". Cons: no undo when the key is hit by mistake, which conflicts with the "come back and change" requirement; the next scan may re-detect the same face.
- **Option C – dismissal reason column:** add `dismiss_reason` (e.g. `blurry`) next to `is_dismissed`. Pros: keeps the reason for later filtering or for tuning the detector. Cons: migration, resource and API changes for a value nothing reads yet.

### Q-093-03 – Picking or creating a person from the name box

- **Context:** `usePeopleList()` loads every page of `GET /People` and filters client-side; there is no name search on the server. `POST /Face/{id}/assign` accepts `person_id` or `new_person_name`; `new_person_name` always creates a new person (no dedupe).
- **Option A (recommended) – single combobox, client-side:** typing filters the people list (prefix and substring, case-insensitive); ↑/↓ move the highlight; Enter assigns the highlighted person. With no match, Enter creates a person with the typed name. An exact case-insensitive name match is always reused instead of creating a duplicate. Pros: fastest flow, no backend change, no duplicates from typos in case. Cons: loads the whole people list once per session (large instances).
- **Option B – explicit create entry:** like A, but creating needs selecting a "Create «name»" item. Pros: no accidental new persons. Cons: one more keystroke on every new name.
- **Option C – A plus server search:** add `q` to `GET /People` and query as the user types. Pros: scales to very large people lists. Cons: backend change and network latency on every keystroke, for a list that is usually small.

### Q-093-04 – Arrow keys versus caret movement

- **Context:** Focus always sits in the name input, where ←/→ normally move the caret. The requested behaviour is: → (← in RTL) skips to the next photo, and the user can come back to change a name.
- **Option A (recommended) – navigate only when the input is empty:** with an empty input, → (← in RTL) skips forward and ← (→ in RTL) goes back to the previous face. With text in the input, arrows move the caret. Pros: no conflict while typing; skipping happens on an untouched face, which is the normal case. Cons: to go back while text is typed, the user first clears it (or uses the on-screen button).
- **Option B – always navigate:** arrows always skip/go back; caret moves only with the mouse or Home/End. Pros: simplest rule. Cons: fixing a typo in the middle of a name is awkward.
- **Option C – modifier:** Alt/Option+arrow navigates; plain arrows move the caret. Pros: no ambiguity. Cons: less "Tinder-like"; two keys for the most frequent action.

### Q-093-05 – Queue scope for non-admins and photo serving

- **Context:** In `public`/`private` face mode any logged-in user may assign a face on any photo (`PhotoPolicy::canAssignFaceOnPhoto`), but only the photo owner may dismiss it (`canDismissFace`). The queue is a v3 Struct-of-Arrays endpoint, so the photo is shown through `GET /api/v3/Asset/{album_id}/{photo_id}/{variant}`, which needs an album the caller can access and that contains the photo. Admins see everything in every mode; in `privacy-preserving`/`restricted` mode non-admins have no batch face rights at all.
- **Option A (recommended) – own photos only:** non-admins get faces on photos they own; admins get every face. Each queue item carries one `album_id` through which the owner reaches the photo (lowest album id from `photo_album`, or `unsorted` when the photo is in no album). Pros: Backspace/Space discard always works (owner may dismiss); one cheap join for the album id; nobody names strangers in other users' libraries by default. Cons: in `public`/`private` mode, users cannot name faces in shared albums from this view (they still can from the photo view).
- **Option B – every visible photo:** non-admins get faces on every photo they can see (search-style visibility filter); each item carries `can_dismiss`, and the discard key is disabled on photos the user does not own. Pros: matches the full assign right. Cons: heavy visibility query; picking an accessible album per photo is costly; discard sometimes silently unavailable.
- **Option C – admins only:** the view and endpoint are admin only. Pros: simplest. Cons: regular users with face rights cannot use it.

### Q-093-06 – Arrow keys on a revisited, pre-filled face

- **Context:** Q-093-04 (A) navigates only while the box is empty. Going back to a named face pre-fills its name (FR-093-05), so the box is not empty and ←/→ move the caret: the user cannot move on by arrow without clearing the name, and clearing then pressing Enter un-assigns it.
- **Option A (recommended) – also navigate while unchanged:** arrows navigate while the box is empty *or* still holds exactly the pre-filled value; once edited, arrows move the caret. Pros: browsing back and forth through the history stays one key; editing is unaffected. Cons: to move the caret inside an unedited pre-filled name, the user clicks or uses Home/End.
- **Option B – Escape:** Escape restores/clears the box, then arrows work. Pros: explicit. Cons: two keys per step when browsing history.
- **Option C – strict rule:** only the on-screen Previous/Skip buttons leave a pre-filled face. Pros: no special case. Cons: breaks the keyboard-only flow.

### Q-093-07 – Optimistic advance versus error handling

- **Context:** NFR-093-01 shows the next face before the request returns, while the first draft of FR-093-03 said the face stays on screen with the typed text on error. Both cannot hold.
- **Option A (recommended) – optimistic, recoverable:** advance immediately; on error show a toast with a "Go back" action, and mark the face as failed in the session history (red badge when revisited, typed text kept). Pros: keeps the fast feel; nothing is lost. Cons: the user may be a few faces ahead when the error shows.
- **Option B – wait for the response:** advance only after success (~100–300 ms). Pros: simplest, errors appear on the right face. Cons: each name costs a round-trip; feels slower.
- **Option C – optimistic with auto-return:** advance immediately and jump back to the failed face on error. Pros: the error is shown in context. Cons: the screen jumps away while the user is typing the next name.

### Q-093-08 – Struct-of-array flag defaults to off

- **Context:** Every v3 request checks `config('features.struct-of-array') === true`, which defaults to `false` (`config/features.php:316`). With Q-093-01/03 answered "v3 SoA", the quick naming view is hidden on default installs.
- **Option A (recommended) – keep the gate:** the view ships dark like the other v3 features and appears when SoA is enabled. Pros: consistent with every v3 endpoint; no special case. Cons: invisible by default until SoA goes on by default.
- **Option B – skip the flag for these endpoints:** the two endpoints authorise without the flag. Pros: available immediately. Cons: breaks the v3 convention; v3 surface live while the flag says off.
- **Option C – v2 fallback:** add v2 equivalents used when the flag is off. Pros: available everywhere. Cons: duplicate endpoints and client paths.

### Q-093-09 – Serving the photo through an album

- **Context:** Q-093-05 (A) serves the photo through `GET /api/v3/Asset/{album_id}/{photo_id}/{variant}` with the lowest album id or `unsorted`. `GetPhotoAssetRequest` checks that the album is accessible and that the photo belongs to it through `$album->photos()`, which for smart albums applies the searchability filter. Consequences: an owner's NSFW photo in no album fails when `hide_nsfw_in_smart_albums` is on; an admin cannot reach another user's unsorted photo when `enable_smart_album_per_owner` is on. When temporary image links are enabled for logged-in users, plain `<img>` tags also need the MAC header path used by other v3 images.
- **Option A (recommended) – face-scoped image route:** add `GET /api/v3/Faces/{face_id}/photo/{variant}` authorised by the face rights (owner or admin, same as the queue), serving the size variant like `PhotoAssetController` (fallback chain, S3 redirect, watermark). The queue drops `album_ids`. Pros: always works for every queued face; no album guessing; one rule. Cons: one more binary route and test class; must reuse the asset controller internals.
- **Option B – smarter album pick:** prefer an album owned by the photo owner, use `unsorted` only when allowed, and drop faces with no usable album from the queue. Pros: no new route. Cons: complex query; some faces silently never appear.
- **Option C – crop only:** show only the face crop (no full photo). Pros: no image access issue. Cons: loses the "one photo, face highlighted" context the owner asked for.

### Q-093-10 – Pre-suggested name

- **Context:** Faces already carry `face_suggestions` (similar faces with a confidence), and the existing assignment modal shows them as chips with the suggested face's person name. The quick naming view as specified always starts from an empty box. Clustered faces come back to back, so the next face very often belongs to the person just named.
- **Option A (recommended) – pre-highlighted suggestion:** the queue adds `suggested_person_ids` / `suggested_person_names` (nullable). The value is the person of the highest-confidence suggestion whose suggested face is assigned, computed with a correlated subquery so the queue stays one SELECT. Client side, if the previous face of the same cluster was named in this session, that name wins. The suggestion is shown as a ghost placeholder in the empty box with a "Suggested" hint; Enter on the empty box accepts it, and typing replaces it. Pros: most faces become a single Enter, which is the real Tinder-like speed-up. Cons: changes FR-093-03 ("empty + Enter does nothing" becomes "accepts the suggestion when there is one"); a wrong suggestion accepted by reflex.
- **Option B – chips only:** show the suggestion as a clickable chip; Tab fills the box. Pros: no accidental accept. Cons: one more key per face.
- **Option C – no suggestions:** as specified now. Pros: simplest. Cons: the user types every name, even for a run of faces from the same cluster.

### Q-093-11 – Creating a name that prefix-matches an existing person

- **Context:** With Q-093-03 (A), Enter takes the highlighted match and creates a person only when nothing matches. Typing "Ali" while "Alice" exists highlights "Alice", so "Ali" cannot be created.
- **Option A (recommended) – create entry + Shift+Enter:** when the typed text is not an exact match, the list ends with "Create «Ali»" (reachable with ↑/↓), and Shift+Enter always creates from the typed text (an exact case-insensitive match is still reused). Pros: both paths are keyboard-only; the common case (existing person) stays one Enter. Cons: one more key binding to learn (listed in the help).
- **Option B – no default highlight:** nothing is highlighted unless the text matches a name exactly; ↓ picks a match; Enter otherwise creates. Pros: Enter never picks something the user did not type. Cons: existing names need typing in full or an extra ↓.
- **Option C – Shift+Enter only:** no list entry. Pros: minimal UI. Cons: not discoverable without the help.

### Q-093-12 – Retrying a toggle

- **Context:** FR-093-09 lets the user retry a failed action. Discard and un-dismiss go through `PATCH /api/v2/Face/{id}`, which flips `is_dismissed` with no target value. When a request times out after the server applied it, a retry flips the face back.
- **Option A (recommended) – optional explicit value:** `ToggleDismissedRequest` accepts an optional boolean `is_dismissed`; when present the face is set to that value (idempotent), when absent the endpoint keeps toggling for existing callers. Pros: retries are safe; tiny backend change with a test. Cons: touches a v2 endpoint used elsewhere (behaviour unchanged without the parameter).
- **Option B – re-read before retry:** the client fetches the face (`GET /Photo/{id}/faces`) to learn its state before retrying. Pros: no backend change. Cons: extra request and logic per retry.
- **Option C – no retry for dismiss changes:** failures only show the toast and badge; the user acts again by hand. Pros: simplest. Cons: acting again hits the same toggle problem.

### Q-093-13 – Two persons with the same name

- **Context:** `persons.name` is not unique, and `PersonFactory::findOrCreate` always creates a new person from `new_person_name`, so homonyms already exist in practice (two "John Smith", a parent and child with the same first name). The spec currently assumes names are unique: FR-093-03 reuses an exact case-insensitive match instead of creating, and Shift+Enter also reuses an exact match. With two persons named "John Smith", the reuse is ambiguous, and a second "John Smith" can never be created from the view. Suggestions and the same-cluster override work by `person_id`, so they are not affected.
- **Option A (recommended) – homonyms allowed, made visible:**
  - Every list row shows the representative crop and the face count, so two "John Smith" rows are told apart by face.
  - An exact match is auto-reused (and highlighted first) only when exactly one visible person has that name. When several do, all are listed first, ordered by face count, and the one matching the face's suggestion, if any, comes first. Enter takes the highlighted row.
  - The list always ends with a create entry: "Create «name»", or "Create another «name»" when the name already exists.
  - Shift+Enter always creates a new person, even when the name exists. This changes Q-093-11's "exact match still reused".
  - Pros: matches the data model; homonyms are explicit and visible; no migration.
  - Cons: when a name is shared, picking by face is still on the user.
- **Option B – unique names:** enforce case-insensitive uniqueness when creating (and renaming) persons; users disambiguate with names like "John Smith (work)". Pros: names are unambiguous everywhere. Cons: changes `PersonFactory`, the rename endpoint and the People page; existing duplicates need a migration or a merge prompt; it is a cross-feature rule (Feature 030), outside the scope of a naming UI.
- **Option C – keep exact-match reuse:** quick naming always reuses the first exact match; homonyms are created elsewhere (People page) and then picked from the list. Pros: no change. Cons: quick naming silently merges two real people into one when you type a shared name.
