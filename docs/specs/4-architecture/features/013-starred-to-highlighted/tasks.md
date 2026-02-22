# Feature Tasks 013 – Starred to Highlighted Rename

## Checklist

- [x] T-013-01 Create migration `2026_02_22_100000_rename_starred_to_highlighted.php`
- [x] T-013-02 Update `app/Enum/SmartAlbumType.php` (STARRED → HIGHLIGHTED)
- [x] T-013-03 Update `app/Enum/ColumnSortingPhotoType.php` (IS_STARRED → IS_HIGHLIGHTED)
- [x] T-013-04 Update `app/Enum/ColumnSortingType.php` (IS_STARRED → IS_HIGHLIGHTED)
- [x] T-013-05 Create `app/SmartAlbums/HighlightedAlbum.php`, delete `StarredAlbum.php`
- [x] T-013-06 Update `app/Factories/AlbumFactory.php` + `database/factories/PhotoFactory.php`
- [x] T-013-07 Update `app/Models/Photo.php`
- [x] T-013-08 Update `app/Models/Extensions/Thumb.php`
- [x] T-013-09 Update `app/Jobs/RecomputeAlbumStatsJob.php`
- [x] T-013-10 Update DTOs (ImportParam, DuplicateDTO, InitDTO, StandaloneDTO)
- [x] T-013-11 Update Pipes (InitParentAlbum, SetStarred → is_highlighted)
- [x] T-013-12 Update `app/Contracts/Http/Requests/RequestAttribute.php`
- [x] T-013-13 Update `app/Http/Requests/Photo/SetPhotosStarredRequest.php`
- [x] T-013-14 Update `app/Http/Resources/Models/PhotoResource.php`
- [x] T-013-15 Update `app/Http/Controllers/Gallery/PhotoController.php`
- [x] T-013-16 Update `resources/js/lychee.d.ts`
- [x] T-013-17 Update `resources/js/config/constants.ts`
- [x] T-013-18 Update `resources/js/services/photo-service.ts`
- [x] T-013-19 Update `resources/js/stores/PhotosState.ts`
- [x] T-013-20 Update `resources/js/composables/album/photoActions.ts`
- [x] T-013-21 Update `resources/js/composables/contextMenus/contextMenu.ts`
- [x] T-013-22 Update Vue components (AlbumPanel, AlbumThumb, AlbumListItem, PhotoThumb, Dock, PhotoHeader, Search, AlbumHeader)
- [x] T-013-23 Update all 22 `lang/*/gallery.php` translation files
- [x] T-013-24 Update all 22 `lang/*/all_settings.php` translation files
- [x] T-013-25 Update test files (Precomputing, Feature_v2, Unit)
- [x] T-013-26 Run `php-cs-fixer fix`
- [x] T-013-27 Run `php artisan test` (key test suites pass; FrameTest failures pre-existing)
- [x] T-013-28 Run `make phpstan`
- [x] T-013-29 Run `npm run format` and `npm run check`

---

*Last updated: 2026-02-22*
