# Feature 018 Tasks – Photo Albums Sidebar

_Status: Implementation Complete_
_Last updated: 2026-02-26_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.

## Checklist

### Increment I1 – Backend: Request, Controller, Route, Resource

- [x] T-018-01 – Create PhotoAlbumResource Spatie Data class (FR-018-04, DO-018-02).
  _Intent:_ Lightweight response resource with `id` (string) and `title` (string) fields extending `Spatie\LaravelData\Data`.
  _Verification commands:_
  - `make phpstan`
  _Notes:_ Place in `app/Http/Resources/Models/`. Add `#[TypeScript()]` annotation for frontend type generation.

- [x] T-018-02 – Create GetPhotoAlbumsRequest with authorization (FR-018-01, FR-018-02, DO-018-01).
  _Intent:_ Request class extending `BaseApiRequest` that resolves `photo_id` from the route parameter, loads the `Photo` model, and authorizes via `PhotoPolicy::CAN_SEE`. Supports both authenticated and guest users.
  _Verification commands:_
  - `make phpstan`
  _Notes:_ Place in `app/Http/Requests/Photo/`. Use `Gate::check(PhotoPolicy::CAN_SEE, ...)` for authorization. Extract photo from route model binding or manual lookup.

- [x] T-018-03 – Add `albums()` method to PhotoController (FR-018-01, FR-018-03, API-018-01).
  _Intent:_ Controller method accepting `GetPhotoAlbumsRequest`, loading the photo's albums via the `BelongsToMany` relationship, filtering each album through `AlbumPolicy::canAccess()`, and returning a collection of `PhotoAlbumResource`.
  _Verification commands:_
  - `make phpstan`
  _Notes:_ Eager-load albums in a single query to avoid N+1 (NFR-018-05). Filter in PHP after loading.

- [x] T-018-04 – Register GET route in api_v2.php (API-018-01).
  _Intent:_ Add `GET /Photo/{photo_id}/albums` route pointing to `PhotoController::albums`.
  _Verification commands:_
  - `php artisan route:list --path=Photo`
  - `make phpstan`
  _Notes:_ Place near existing Photo routes. Use route model binding or string parameter based on existing patterns.

### Increment I2 – Backend: Feature Tests

- [x] T-018-05 – Test: owner sees all albums for their photo (S-018-01).
  _Intent:_ Create `GetPhotoAlbumsTest` extending `BaseApiWithDataTest`. Test that a photo owner receives all albums the photo belongs to.
  _Verification commands:_
  - `php artisan test --filter=GetPhotoAlbumsTest`
  _Notes:_ Set up photo in multiple albums owned by the same user.

- [x] T-018-06 – Test: shared user sees only accessible albums (S-018-02, S-018-03).
  _Intent:_ Test that a non-owner authenticated user only sees albums they have access to (via sharing or public access). Albums they cannot access are omitted.
  _Verification commands:_
  - `php artisan test --filter=GetPhotoAlbumsTest`
  _Notes:_ Set up photo in both shared and private albums.

- [x] T-018-07 – Test: guest sees public albums only (S-018-04).
  _Intent:_ Test that an unauthenticated guest receives only albums with public access enabled.
  _Verification commands:_
  - `php artisan test --filter=GetPhotoAlbumsTest`
  _Notes:_ Photo must be in at least one public album to be visible to guest.

- [x] T-018-08 – Test: guest denied for private photo (S-018-05).
  _Intent:_ Test that a guest requesting albums for a photo they cannot see receives 403 Forbidden.
  _Verification commands:_
  - `php artisan test --filter=GetPhotoAlbumsTest`
  _Notes:_ Photo only in private albums, no public access.

- [x] T-018-09 – Test: non-existent photo returns 404 (S-018-06).
  _Intent:_ Test that requesting albums for a photo ID that does not exist returns 404 Not Found.
  _Verification commands:_
  - `php artisan test --filter=GetPhotoAlbumsTest`
  _Notes:_ Use a random non-existent ID.

- [x] T-018-10 – Test: photo with no albums returns empty array (S-018-07).
  _Intent:_ Test that a photo not belonging to any album returns an empty JSON array `[]`.
  _Verification commands:_
  - `php artisan test --filter=GetPhotoAlbumsTest`
  _Notes:_ Create photo without album associations.

- [x] T-018-11 – Run full backend quality gate.
  _Intent:_ Ensure all backend code passes static analysis and formatting after I1–I2.
  _Verification commands:_
  - `vendor/bin/php-cs-fixer fix`
  - `php artisan test`
  - `make phpstan`
  _Notes:_ Fix any issues before proceeding to frontend.

### Increment I3 – Frontend: Service Method & Sidebar Section

- [x] T-018-12 – Add `albums()` method to PhotoService (FR-018-06).
  _Intent:_ Add method `albums(photo_id: string): Promise<AxiosResponse>` to `resources/js/services/photo-service.ts` calling `GET ${Constants.getApiUrl()}/Photo/${photo_id}/albums`.
  _Verification commands:_
  - `npm run check`
  _Notes:_ Follow existing service method patterns. Use `.then()` not async/await.

- [x] T-018-13 – Add Albums section to PhotoDetails.vue (FR-018-05, FR-018-06, FR-018-07, UI-018-01 to UI-018-05, S-018-11).
  _Intent:_ Add a new section in `PhotoDetails.vue` between Tags and EXIF that:
  - Watches `areDetailsOpen` and `photoStore.photo.id` to trigger lazy fetch
  - Shows loading spinner while fetching
  - Displays album titles as clickable links
  - On click: closes sidebar (`are_details_open = false`), resets photo store, navigates via `router.push({ name: "album", params: { albumId } })`
  - Shows "This photo is not in any album." when empty
  - Shows error message on fetch failure
  - Caches result per photo ID to prevent duplicate requests
  _Verification commands:_
  - `npm run check`
  - `npm run format`
  _Notes:_ Use PrimeVue components where appropriate (e.g., `ProgressSpinner` for loading). Import `useRouter` from `vue-router`. Use regular function declarations, not arrow function assignments. Navigation uses named route `"album"` with `{ albumId }` param.

### Increment I4 – Translations & Quality Gate

- [x] T-018-14 – Add English translation keys.
  _Intent:_ Add keys to `lang/php_en.json`: `ALBUMS`, `NO_ALBUMS` ("This photo is not in any album."), `ALBUMS_LOADING_ERROR` ("Could not load albums.").
  _Verification commands:_
  - `npm run check`
  _Notes:_ Use snake_case keys.

- [x] T-018-15 – Add translation placeholders to other languages (21 languages).
  _Intent:_ Add the same keys with English fallback text to all other language files (ar, bg, cz, de, el, es, fa, fr, hu, it, ja, nl, no, pl, pt, ru, sk, sv, vi, zh_CN, zh_TW).
  _Verification commands:_
  - `npm run check`
  _Notes:_ Placeholders only; native speakers can update later.

- [x] T-018-16 – Run full quality gate.
  _Intent:_ Final validation that all code passes formatting, tests, and static analysis.
  _Verification commands:_
  - `vendor/bin/php-cs-fixer fix`
  - `npm run format`
  - `npm run check`
  - `php artisan test`
  - `make phpstan`
  _Notes:_ All must pass before considering feature complete.

### Post-Implementation

- [ ] T-018-17 – Update knowledge map.
  _Intent:_ Add entry for `GET Photo/{photo_id}/albums` endpoint and `PhotoAlbumResource` in `docs/specs/4-architecture/knowledge-map.md`.
  _Verification commands:_ Manual review.
  _Notes:_ Document relationship between PhotoController, PhotoDetails.vue, and PhotoService.

- [ ] T-018-18 – Update roadmap to Complete.
  _Intent:_ Move Feature 018 from Active to Completed in roadmap.
  _Verification commands:_ Manual review.
  _Notes:_ Include summary of delivered functionality.

- [ ] T-018-19 – Manual testing (S-018-08, S-018-09, S-018-10, S-018-11).
  _Intent:_ Verify sidebar behaviour in browser: albums section appears, loading state works, no duplicate requests on same photo, switching photos triggers new fetch, clicking an album title navigates to that album.
  _Verification commands:_ Manual browser testing.
  _Notes:_ Test with photos in multiple albums, single album, and no albums. Verify that clicking an album closes the sidebar and navigates to `/gallery/{albumId}`.

## Notes / TODOs

- The `photo_album` pivot table already has indexes on both `photo_id` and `album_id` columns, so no migration is needed.
- If TypeScript type generation via Spatie TypeScript Transformer auto-generates the `PhotoAlbumResource` type, no manual type definition is needed in T-018-12.
- The endpoint intentionally excludes smart albums — only real `Album` model records from the pivot table are returned.
