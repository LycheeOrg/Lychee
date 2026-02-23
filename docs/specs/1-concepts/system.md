# System Features

This document explains statistics, job tracking, OAuth credentials, and system configuration.

## Table of Contents

- [Statistics and Analytics](#statistics-and-analytics)
- [Job History](#job-history)
- [OAuth Credentials](#oauth-credentials)
- [System Configuration](#system-configuration)

---

## Statistics and Analytics

### What are Statistics?

**Statistics** track engagement metrics for photos and albums, providing insights into how content is being used.

### Statistics Attributes

**Per Photo/Album Metrics:**
- **id**: Unique identifier
- **photo_id**: Link to Photo (nullable, either photo or album)
- **album_id**: Link to Album (nullable, either photo or album)
- **visit_count**: Number of times viewed
- **download_count**: Number of downloads
- **favourite_count**: How many users highlighted/favorited
- **shared_count**: Number of times shared

**Model Properties:**
- No timestamps (counters only)
- Either photo_id OR album_id set (not both)

### Use Cases

**Portfolio Insights:**
- Which photos get the most attention
- Popular vs. overlooked content
- Engagement patterns over time

**Client Reporting:**
- Delivery confirmation (downloads)
- View tracking for shared galleries
- Share tracking for social distribution

**Content Optimization:**
- Identify popular content themes
- Understand viewer preferences
- Data-driven portfolio curation

**Webshop Analytics:**
- Track most viewed products
- Correlate views with purchases
- Optimize pricing and offerings

---

## Job History

### What is Job History?

**JobHistory** tracks background jobs and long-running operations, providing visibility into system tasks.

### Job History Attributes

**Tracking Properties:**
- **id**: Unique identifier
- **owner_id**: User who initiated the job
- **job**: Job type/name identifier
- **status**: Current job status
- **created_at**: When job was queued
- **updated_at**: Last status change

### Job Status Types

**READY:**
- Queued and waiting to execute
- In job queue but not started

**STARTED:**
- Currently processing
- Job worker actively running

**SUCCESS:**
- Completed successfully
- Job finished without errors

**FAILURE:**
- Failed with error
- Check logs for error details

**Relationships:**
- `owner()` - BelongsTo User

### Use Cases

**Bulk Operations:**
- Mass photo imports from directories
- Batch metadata updates
- Album reorganization
- Size variant regeneration

**Maintenance Tasks:**
- Database cleanup
- Orphaned file detection
- Storage optimization
- Cache clearing

**User Feedback:**
- Show progress for long operations
- Notify when jobs complete
- Display job history in UI

**Error Tracking:**
- Identify failed background tasks
- Debug job failures
- Retry failed operations

### Example Jobs

- `Photo::import` - Import photos from directory
- `Album::sync` - Sync album structure
- `Thumbnail::regenerate` - Rebuild size variants
- `Database::optimize` - Maintenance tasks
- `Export::zip` - Create album export

---

## OAuth Credentials

### What are OAuth Credentials?

**OauthCredential** stores OAuth2 authentication tokens for enterprise single sign-on integration.

### OAuth Attributes

**Credential Properties:**
- **id**: Unique identifier
- **user_id**: Link to User account
- **provider**: OAuth provider type
- **token_id**: Unique token from OAuth provider (hidden/secret)
- **created_at**: When credential was created
- **updated_at**: Last token refresh

### Supported Providers

**Via Laravel Socialite:**
- Google
- Microsoft Azure AD
- GitHub
- Amazon
- Apple
- Facebook
- Twitter/X
- LinkedIn
- Bitbucket
- GitLab
- And 100+ others via community drivers

**Relationships:**
- `user()` - BelongsTo User

### OAuth Flow

1. **User clicks "Sign in with Google"**
2. **Redirect to Google OAuth consent screen**
3. **User authorizes Lychee**
4. **Google returns authorization code**
5. **Lychee exchanges code for access token**
6. **Token stored in OauthCredential**
7. **User logged in and linked to account**

### Use Cases

**Enterprise SSO:**
- Integrate with corporate identity providers
- Centralized user management
- IT department controls access
- Audit trail of authentication

**Passwordless Authentication:**
- Users authenticate via OAuth instead of passwords
- No password reset flows needed
- Leverage provider's 2FA/MFA

**Multi-Provider Support:**
- Users can link multiple OAuth accounts
- Sign in with any linked provider
- Fallback if one provider is down

**Security:**
- Token stored securely in database
- Hidden from API responses (`$hidden` array)
- Refresh tokens handled automatically
- Revocation support

---

## System Configuration

### What is System Configuration?

The **Configs** model stores application-wide settings and preferences that control Lychee's behavior.

### Config Attributes

**Configuration Properties:**
- **id**: Unique identifier
- **key**: Unique setting identifier (e.g., `upload_max_filesize`)
- **value**: Setting value (string, nullable)
- **cat**: Configuration category for grouping
- **type_range**: Data type and validation constraints
- **is_secret**: Whether to hide value in UI/API (passwords, API keys)
- **description**: Human-readable explanation
- **details**: Extended documentation
- **level**: Permission level required to modify (0=admin, 1=user)
- **not_on_docker**: Disable in Docker environments
- **order**: Display order in settings UI
- **is_expert**: Hide from basic settings view

### Config Categories

**ConfigCategory** groups related settings:
- **Gallery**: Display and layout settings
- **Image Processing**: Upload and variant generation
- **Privacy**: EXIF visibility, public access
- **Authentication**: OAuth, WebAuthn settings
- **E-commerce**: Webshop configuration
- **System**: Performance and maintenance
- **Advanced**: Expert-level tweaks

### Setting Types

**Type Range Validation:**
- **boolean**: true/false toggles
- **int**: Integer values with min/max
- **string**: Text values with validation
- **string_required**: Non-empty strings
- **license**: License type enum
- **map_provider**: Map integration enum
- **password**: Obscured in UI

### Use Cases

**System Behavior:**
- `upload_max_filesize`: Upload size limit
- `image_quality`: JPEG compression quality
- `retain_original`: Keep original files

**Feature Toggles:**
- `webshop_enabled`: Enable/disable e-commerce
- `oauth_enabled`: OAuth authentication
- `public_photos_hidden`: Hide from search engines

**Display Preferences:**
- `default_album_sort`: Photo sorting
- `layout`: Gallery layout style
- `photo_timeline`: Timeline granularity

**Privacy Controls:**
- `exif_display`: Which EXIF fields to show
- `location_show`: GPS coordinate visibility
- `download_public`: Allow public downloads

**Integration Settings:**
- `map_provider`: Google Maps, OpenStreetMap, etc.
- `stripe_api_key`: Payment provider credentials
- `oauth_google_client_id`: OAuth app credentials

### Configuration Best Practices

**Secrets:**
- Mark sensitive configs with `is_secret=true`
- API keys, passwords hidden in UI
- Never log or expose in error messages

**Docker Considerations:**
- Use `not_on_docker` for filesystem settings
- Environment variables override configs
- Container-specific defaults

**Expert Settings:**
- Hide advanced configs with `is_expert=true`
- Prevent accidental misconfiguration
- Advanced users can toggle expert mode

---

**Related:** [Users](users.md) | [E-commerce](e-commerce.md) | [Albums](albums.md)

---

*Last updated: December 22, 2025*
