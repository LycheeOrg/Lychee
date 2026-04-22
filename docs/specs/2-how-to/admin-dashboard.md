# Admin Dashboard

The Admin Dashboard (`/admin`) provides a single entry point for all administrative tools, plus a cacheable overview of key system metrics. It was introduced in Feature 037.

## Toggle: `use_admin_dashboard`

| Config key | Category | Default | Type |
|---|---|---|---|
| `use_admin_dashboard` | `config` | `1` (enabled) | Boolean |

When **enabled (default)**: the left-menu "Admin" submenu collapses to a single **Admin** link pointing to `/admin`. All admin tools are accessible from the dashboard tile grid.

When **disabled**: the original nested submenu is restored. Each admin tool appears as a sub-item with its updated `/admin/<slug>` URL.

To change the setting, go to **Settings → config** and toggle "Use admin dashboard".

## URL changes (old → new)

The following admin pages were moved from flat top-level paths to the `/admin/` namespace:

| Page | Old path (removed) | New path |
|---|---|---|
| Settings | `/settings` | `/admin/settings` |
| Users | `/users` | `/admin/users` |
| User Groups | `/user-groups` | `/admin/user-groups` |
| Purchasables | `/purchasables` | `/admin/purchasables` |
| Contact Messages | `/contact-messages` | `/admin/contact-messages` |
| Webhooks | `/webhooks` | `/admin/webhooks` |
| Moderation | `/moderation` | `/admin/moderation` |
| Maintenance | `/maintenance` | `/admin/maintenance` |
| Jobs | `/jobs` | `/admin/jobs` |

**Unchanged URLs** (bookmarks remain valid):
- Diagnostics: `/diagnostics`
- Logs: `/Logs`
- Clockwork: external link (unchanged)

> **Note**: Old flat paths (e.g., `/settings`) are no longer registered. Update any bookmarks or external links accordingly.

## Stats overview

The dashboard displays seven system metrics (visible to full admins only — `settings.can_edit`):

| Metric | Source |
|---|---|
| Photos | `Photo::count()` |
| Albums | `Album::count()` |
| Users | `User::count()` |
| Storage used | `SizeVariant::sum('filesize')` |
| Queued jobs | Database jobs table count |
| Failed jobs (24h) | `JobHistory` with status FAILURE in last 24 h |
| Last successful job | `JobHistory` most recent SUCCESS timestamp |

Results are cached for **5 minutes** under the key `admin.stats`. Click **Refresh** to bust the cache and recompute immediately. If any metric fails to compute, it is replaced with `0` and listed in an `errors` array; the partial result is returned without caching so the next load retries.

---

*Last updated: 2026-04-22*
