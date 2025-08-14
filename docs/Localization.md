# Lychee Localization Documentation

This document explains how localization works in Lychee, including translation management, file structure, and development practices for maintaining multiple language support.

## Overview

Lychee supports multiple languages through Laravel's built-in localization system. Translation management is handled through Weblate, a self-hosted translation platform that allows contributors to translate the application into their native languages.

### Translation Platform

**Weblate Instance**: https://weblate.lycheeorg.dev/

Weblate provides a web-based interface for translators to contribute translations without needing to directly edit PHP files. It handles version control integration and maintains translation quality through validation rules.

## Language File Structure

Translations are organized in the `lang/` directory with the following structure:

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

## Development Workflow

### Adding New Translation Keys

When adding new translatable text to Lychee:

1. **Add the English translation first** in the appropriate file in `lang/en/`
2. **Use descriptive keys** that indicate the context and purpose
3. **Copy the new key to all other language files** with the English text as placeholder
4. **Run the test suite** to ensure consistency across all languages

#### Example: Adding a New Key

```php
// lang/en/gallery.php
return [
    // ... existing keys
    'new_feature_title' => 'New Feature',
    'new_feature_description' => 'This is a description of the new feature.',
];
```

Then copy to all other language files:

```php
// lang/fr/gallery.php (and all other languages)
return [
    // ... existing keys
    'new_feature_title' => 'New Feature', // Will be translated via Weblate
    'new_feature_description' => 'This is a description of the new feature.', // Will be translated via Weblate
];
```

### Translation Key Guidelines

#### Naming Conventions
- Use snake_case for all keys
- Use descriptive names that indicate context
- Group related keys under common prefixes
- Avoid abbreviations unless they're widely understood

#### Good Examples:
```php
'album_create_button' => 'Create Album',
'photo_upload_success' => 'Photo uploaded successfully',
'settings_privacy_title' => 'Privacy Settings',
'error_network_timeout' => 'Network timeout occurred',
```

#### Poor Examples:
```php
'btn' => 'Button',              // Too vague
'msg1' => 'Success',            // Non-descriptive
'albumcreate' => 'Create',      // Poor formatting
```

#### Nested Structures
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

### Using Translations in Code

#### Backend (PHP/Laravel)
```php
// Simple translation
__('gallery.title')

// Nested key access
__('gallery.album.actions.create')
```

#### Frontend (Vue.js)
```javascript
// In Vue components
$t('gallery.title')
```

## Quality Assurance

### Automated Testing

Lychee's test suite includes validation to ensure translation consistency:

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

### Manual Quality Checks

#### Before Submitting Changes
1. **Verify English content** is accurate and well-written
2. **Check key naming** follows conventions
3. **Ensure proper nesting** for logical grouping
4. **Run tests** to validate consistency
5. **Test in application** to verify context and formatting

#### Content Guidelines
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

### For Translators
- **Access**: Request access through the Lychee community or GitHub issues
- **Context**: Use the provided context and source code references to understand usage
- **Consistency**: Maintain consistent terminology throughout your translations
- **Pluralization**: Handle plural forms according to your language's rules
- **Testing**: Test your translations in the application when possible

### For Developers
- **Source Updates**: When English text changes, update Weblate to reflect the changes
- **New Keys**: Add new keys to the English files first, then sync with Weblate
- **Context**: Provide clear context in key names and comments for translators
- **Review**: Review translation pull requests for technical accuracy


## Contributing Translations

### Adding a New Language
1. **Create language directory** in `lang/` using the appropriate language code
2. **Copy all files** from `lang/en/` to the new directory
3. **Update language list** in relevant configuration files
4. **Add to Weblate** configuration for translation management
5. **Test the new language** in the application

### Improving Existing Translations
1. **Access Weblate** at https://weblate.lycheeorg.dev/
2. **Select the target language** and file to translate
3. **Provide translations** following the guidelines above
4. **Submit for review** through the Weblate interface

### Best Practices Summary

1. **Always start with English** translations
2. **Copy new keys** to all language files immediately
3. **Use descriptive key names** that provide context
4. **Test thoroughly** before submitting changes
5. **Follow Laravel conventions** for localization
6. **Coordinate with translators** through proper channels
7. **Keep documentation updated** as the system evolves

This localization system ensures Lychee remains accessible to users worldwide while maintaining code quality and translation consistency across all supported languages.

---

*Last updated: August 14, 2025*
