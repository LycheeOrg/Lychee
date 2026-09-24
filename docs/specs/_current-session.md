# Current Session

_Last updated: 2026-09-24_

## Feature 071 – Album Date Scrubber: implemented, awaiting manual check + commit
- Spec, plan, tasks: `docs/specs/4-architecture/features/071-album-date-scrubber/`; ADR-0011.
- 19/20 tasks `[x]`. T-071-19 (manual browser check of S-071-01…12) is pending.
- Gates: php-cs-fixer clean, phpstan OK; `AlbumConfigDateScrubberTest` (20) and `UpdateAlbumDateScrubberTest` (11) green, plus the existing album-update classes. `npm run format`, ESLint on the touched files, and `npm run check` (only the two pre-existing app.ts/app-v8.ts i18n errors remain).
- Not committed. Next: operator runs the manual check, then commits (see the commit command in the chat hand-off).
