# How-To: Configure Album and Photo Pagination

**Author:** Lychee Team
**Last Updated:** 2026-01-10
**Feature:** 007-pagination
**Related:** [Feature 007 Spec](../4-architecture/features/007-pagination/spec.md)

## Overview

Lychee implements pagination for albums and photos to efficiently handle large collections. Instead of loading all content at once, data is fetched in configurable page sizes with multiple UI interaction modes. This guide covers configuring pagination settings for your deployment.

## Why Configure Pagination?

Pagination improves performance and user experience for large galleries:

- **Faster Initial Load**: Only load the first page of content instead of everything
- **Reduced Memory Usage**: Both server and browser use less memory
- **Configurable Page Sizes**: Balance between fewer requests and smaller payloads
- **Flexible UI Modes**: Choose the interaction style that fits your users

## Configuration Settings

Pagination is configured through four settings in the admin panel under **Settings > Gallery**.

### Page Size Settings

#### albums_per_page

Controls how many child albums are loaded per page.

| Property | Value |
|----------|-------|
| Type | Integer |
| Range | 1-1000 |
| Default | 30 |
| Location | Settings > Gallery |

**Recommendations:**
- **Small galleries (< 50 albums)**: Set to 50+ to show all at once
- **Medium galleries (50-200 albums)**: Keep default of 30
- **Large galleries (200+ albums)**: Consider 20-30 for faster loading

#### photos_per_page

Controls how many photos are loaded per page.

| Property | Value |
|----------|-------|
| Type | Integer |
| Range | 1-1000 |
| Default | 100 |
| Location | Settings > Gallery |

**Recommendations:**
- **Thumbnail-heavy layouts**: 50-100 photos per page
- **High-resolution displays**: 100-200 photos per page
- **Bandwidth-constrained users**: 50 or fewer photos per page

### UI Mode Settings

#### photos_pagination_ui_mode

Controls how users load additional photo pages.

| Property | Value |
|----------|-------|
| Type | Enum |
| Options | infinite_scroll, load_more_button, page_navigation |
| Default | infinite_scroll |
| Location | Settings > Gallery |

**Mode Descriptions:**

1. **infinite_scroll** (Default)
   - Photos load automatically as user scrolls down
   - Seamless browsing experience
   - Best for: Casual browsing, mobile users
   - Note: Memory usage grows with pages loaded

2. **load_more_button**
   - Shows "Load More (N remaining)" button at bottom
   - User controls when to load more
   - Best for: Users who want explicit control, limited bandwidth

3. **page_navigation**
   - Traditional page number navigation (1, 2, 3, ... Next)
   - Replaces content when changing pages
   - Best for: Large collections where users want to jump to specific pages

#### albums_pagination_ui_mode

Controls how users load additional album pages. Same options as photos.

| Property | Value |
|----------|-------|
| Type | Enum |
| Options | infinite_scroll, load_more_button, page_navigation |
| Default | infinite_scroll |
| Location | Settings > Gallery |

## Configuring via Admin Panel

1. Log in as an administrator
2. Navigate to **Settings** in the left menu
3. Scroll to the **Gallery** section
4. Adjust the pagination settings:
   - **Albums per page**: Enter a number between 1 and 1000
   - **Photos per page**: Enter a number between 1 and 1000
   - **Albums pagination UI mode**: Select from dropdown
   - **Photos pagination UI mode**: Select from dropdown
5. Click **Save** to apply changes

Changes take effect immediately for new album loads.

## Configuring via Database

Settings can also be modified directly in the `configs` table:

```sql
-- View current pagination settings
SELECT key, value FROM configs WHERE key LIKE '%pagination%' OR key LIKE '%_per_page';

-- Update page sizes
UPDATE configs SET value = '50' WHERE key = 'albums_per_page';
UPDATE configs SET value = '150' WHERE key = 'photos_per_page';

-- Update UI modes (options: infinite_scroll, load_more_button, page_navigation)
UPDATE configs SET value = 'load_more_button' WHERE key = 'photos_pagination_ui_mode';
UPDATE configs SET value = 'page_navigation' WHERE key = 'albums_pagination_ui_mode';
```

Clear the config cache after database changes:

```bash
php artisan config:clear
```

## Configuring via Environment Variables

Pagination settings can be set via environment variables for containerized deployments:

```bash
# Page sizes
LYCHEE_ALBUMS_PER_PAGE=30
LYCHEE_PHOTOS_PER_PAGE=100

# UI modes
LYCHEE_ALBUMS_PAGINATION_UI_MODE=infinite_scroll
LYCHEE_PHOTOS_PAGINATION_UI_MODE=infinite_scroll
```

Environment variables override database settings on container startup.

## Best Practices

### For Large Galleries (1000+ photos per album)

1. Keep `photos_per_page` at 100 or less
2. Use `infinite_scroll` for best UX
3. Monitor browser memory usage with very large collections
4. Consider `page_navigation` if users need to jump to specific ranges

### For Mobile Users

1. Lower `photos_per_page` to 50 for faster loading on cellular
2. `infinite_scroll` provides natural mobile browsing
3. Test on actual mobile devices to validate performance

### For Bandwidth-Constrained Environments

1. Set lower page sizes (30 albums, 50 photos)
2. Use `load_more_button` so users control data usage
3. Enable thumbnail caching if available

## Troubleshooting

### Photos Not Loading Beyond First Page

1. Check browser console for JavaScript errors
2. Verify API endpoints are accessible: `/api/v2/Album::photos?album_id=X&page=2`
3. Clear browser cache and retry

### Slow Page Loading

1. Reduce page size settings
2. Ensure database indexes exist on sorting columns
3. Check server resources (CPU, memory, database connections)

### UI Mode Not Changing

1. Clear browser cache
2. Hard refresh the page (Ctrl+Shift+R / Cmd+Shift+R)
3. Verify config was saved (check `configs` table)

## Related Documentation

- [API Design - Pagination Endpoints](../3-reference/api-design.md#pagination-endpoints)
- [Knowledge Map - Album Pagination](../4-architecture/knowledge-map.md#album-pagination-feature-007)
- [Feature 007 Specification](../4-architecture/features/007-pagination/spec.md)
