# Feature 041 – Album Tags Plan

## Scope
Implement direct tag assignment for regular albums across persistence, API, editable album resources, and the album properties UI.

## Increments
1. Backend schema and model support (`albums_tags`, `Album::tags()`, resource/eager-loading updates).
2. Mutation API (`SetAlbumTagsRequest`, controller action, route).
3. Frontend wiring (`album-service.ts`, `AlbumProperties.vue`, translation key).
4. Verification (feature tests, formatting, TypeScript, PHPStan).

## Verification
- `vendor/bin/php-cs-fixer fix`
- `npm run format`
- `npm run check`
- `php artisan test --filter AlbumTags`
- `make phpstan`
