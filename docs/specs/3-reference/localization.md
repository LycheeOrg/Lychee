# Localization Reference

This document provides reference information about Lychee's localization system, including file structure, translation key conventions, and usage in code.

## Overview

Lychee supports multiple languages through Laravel's built-in localization system. Translation management is handled through Weblate, a self-hosted translation platform at https://weblate.lycheeorg.dev/.

## Language File Structure

Translations are organized in the `lang/` directory:

```
lang/
├── ar/          # Arabic
├── cz/          # Czech
├── de/          # German
├── el/          # Greek
├── en/          # English (source language)
├── es/          # Spanish
├── fa/          # Persian/Farsi
├── fr/          # French
├── hu/          # Hungarian
├── it/          # Italian
├── ja/          # Japanese
├── nl/          # Dutch
├── no/          # Norwegian
├── pl/          # Polish
├── pt/          # Portuguese
├── ru/          # Russian
├── sk/          # Slovak
├── sv/          # Swedish
├── vi/          # Vietnamese
├── zh_CN/       # Chinese (Simplified)
└── zh_TW/       # Chinese (Traditional)
```

### Translation Files

Each language directory contains the same set of PHP files that return associative arrays of translation keys:

- **`gallery.php`** - Main gallery interface, albums, photos, and navigation
- **`settings.php`** - Application settings and configuration options
- **`dialogs.php`** - Modal dialogs, confirmations, and user interactions
- **`toasts.php`** - Notification messages and alerts
- **`sharing.php`** - Album and photo sharing functionality
- **`profile.php`** - User profile management
- **`users.php`** - User management (admin features)
- **`user-groups.php`** - User group management
- **`statistics.php`** - Statistics and analytics displays
- **`maintenance.php`** - Maintenance mode and system operations
- **`jobs.php`** - Background job status and management
- **`diagnostics.php`** - System diagnostics and health checks
- **`changelogs.php`** - Version history and update information
- **`left-menu.php`** - Left sidebar navigation menu
- **`landing.php`** - Landing page content
- **`flow.php`** - Photo flow/timeline interface
- **`fix-tree.php`** - Album tree maintenance utilities
- **`duplicate-finder.php`** - Duplicate photo detection
- **`aspect_ratio.php`** - Aspect ratio and layout options

## Translation Key Conventions

### Naming Conventions

- Use snake_case for all keys
- Use descriptive names that indicate context
- Group related keys under common prefixes
- Avoid abbreviations unless they're widely understood

### Good Examples

```php
'album_create_button' => 'Create Album',
'photo_upload_success' => 'Photo uploaded successfully',
'settings_privacy_title' => 'Privacy Settings',
'error_network_timeout' => 'Network timeout occurred',
```

### Poor Examples

```php
'btn' => 'Button',              // Too vague
'msg1' => 'Success',            // Non-descriptive
'albumcreate' => 'Create',      // Poor formatting
```

### Nested Structures

Use nested arrays for logical grouping:

```php
return [
    'album' => [
        'actions' => [
            'create' => 'Create Album',
            'delete' => 'Delete Album',
            'share' => 'Share Album',
        ],
        'properties' => [
            'title' => 'Title',
            'description' => 'Description',
            'public' => 'Public',
        ],
    ],
];
```

## Using Translations in Code

### Backend (PHP/Laravel)

```php
// Simple translation
__('gallery.title')

// Nested key access
__('gallery.album.actions.create')

// With parameters
__('gallery.photos_count', ['count' => 5])
```

### Frontend (Vue.js)

```javascript
// In Vue components (Composition API)
import { trans } from "laravel-vue-i18n";

// Simple translation
$t('gallery.title')

// Using trans function in script
trans('gallery.title')

// With parameters
$t('gallery.photos_count', { count: 5 })
```

## Quality Assurance

### Automated Testing

Lychee's test suite validates translation consistency:

#### Key Consistency Tests

1. **Complete Coverage**: All keys present in English must exist in other languages
2. **No Extra Keys**: Other languages cannot have keys not present in English
3. **File Structure**: All language directories must have the same file structure
4. **Valid PHP Syntax**: All translation files must be valid PHP arrays

#### Running Translation Tests

```bash
# Run the full test suite (includes translation validation)
php artisan test

# Run specific translation tests
php artisan test --filter TranslationTest
```

#### Common Test Failures

- **Missing keys**: A key exists in English but not in another language
- **Extra keys**: A key exists in a translation but not in English
- **Syntax errors**: Invalid PHP syntax in translation files
- **Missing files**: A translation file exists in English but not in other languages

### Content Guidelines

- **Use clear, concise language** appropriate for the interface
- **Maintain consistent tone** throughout the application
- **Consider context** - where and how the text will be displayed
- **Use proper capitalization** following English UI conventions
- **Avoid technical jargon** unless necessary for the target audience

## Weblate Integration

### How Weblate Works

1. **Automatic Sync**: Weblate syncs with the Git repository
2. **Translation Interface**: Translators use the web interface to submit translations
3. **Quality Checks**: Weblate validates translations for consistency and formatting
4. **Review Process**: Translations can be reviewed before being committed
5. **Git Integration**: Approved translations are automatically committed back to the repository

### Translation Platform

**Weblate Instance**: https://weblate.lycheeorg.dev/

Weblate provides a web-based interface for translators to contribute translations without needing to directly edit PHP files. It handles version control integration and maintains translation quality through validation rules.

## Related Documentation

- [Translating Lychee](../2-how-to/translating-lychee.md) - How-to guide for adding translations and new languages
- [Coding Conventions](coding-conventions.md) - General coding standards

---

*Last updated: December 22, 2025*
