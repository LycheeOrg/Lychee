# Translating Lychee

This guide explains how to contribute translations to Lychee, whether you're adding new translation keys as a developer or translating the application into your native language.

## For Developers: Adding Translation Keys

When adding new translatable text to Lychee:

### Step 1: Add the English Translation

Add the translation to the appropriate file in `lang/en/`:

```php
// lang/en/gallery.php
return [
    // ... existing keys
    'new_feature_title' => 'New Feature',
    'new_feature_description' => 'This is a description of the new feature.',
];
```

### Step 2: Copy to All Languages

Copy the new key to all other language files with the English text as placeholder:

```php
// lang/fr/gallery.php (and all other languages)
return [
    // ... existing keys
    'new_feature_title' => 'New Feature', // Will be translated via Weblate
    'new_feature_description' => 'This is a description of the new feature.', // Will be translated via Weblate
];
```

### Step 3: Run Tests

Ensure consistency across all languages:

```bash
php artisan test --filter LangTest
```

### Best Practices for Developers

1. **Add English first** - Always create the English translation before any others
2. **Copy to all languages** - Immediately copy new keys to all language files
3. **Use descriptive key names** - Follow the naming conventions in the reference documentation
4. **Test thoroughly** - Run translation tests before committing
5. **Update Weblate** - Sync changes to Weblate for translators to complete

### Manual Quality Checks

Before submitting changes:

1. **Verify English content** is accurate and well-written
2. **Check key naming** follows conventions
3. **Ensure proper nesting** for logical grouping
4. **Run tests** to validate consistency
5. **Test in application** to verify context and formatting

## For Translators: Using Weblate

### Getting Access

1. Visit https://weblate.lycheeorg.dev/
2. Request access through the Lychee community or GitHub discussions
3. Select your target language

### Translation Workflow

1. **Select Language and File**: Choose the language and file you want to translate
2. **Review Context**: Use the provided context and source code references
3. **Provide Translation**: Enter your translation following the guidelines
4. **Submit for Review**: Submit your translation through the Weblate interface

### Translation Guidelines

#### Context is Key

- Understand where the text will appear in the interface
- Consider the space available for the text
- Maintain consistency with existing translations

#### Consistency

- Use consistent terminology throughout your translations
- Follow established conventions for your language
- Maintain the same tone as the English source

#### Pluralization

- Handle plural forms according to your language's rules
- Test plural forms with different values when possible

#### Testing

- Test your translations in the application when possible
- Verify that translations fit in the UI without breaking layout
- Check for proper encoding of special characters

## Adding a New Language

### Step 1: Create Language Directory

Create a new directory in `lang/` using the appropriate language code:

```bash
# Example: Adding Spanish (es)
mkdir lang/es
```

### Step 2: Copy Translation Files

Copy all files from `lang/en/` to the new directory:

```bash
cp lang/en/*.php lang/es/
```

### Step 3: Update Configuration

Add the language to relevant configuration files (this may vary based on Lychee's current implementation):

```php
// config/app.php (or similar)
'available_locales' => [
    'en' => 'English',
    'es' => 'Español',
    // ... other languages
],
```

### Step 4: Add to Weblate

Configure Weblate to include the new language:

1. Access Weblate admin interface
2. Add the new language to the Lychee project
3. Configure auto-commit settings if needed

### Step 5: Test

Test the new language in the application:

```bash
# Run full test suite
php artisan test

# Test in the application
# - Change language in settings
# - Verify all UI elements display correctly
# - Check for encoding issues
```

## Improving Existing Translations

### Identifying Issues

- Missing translations (English text in non-English language)
- Incorrect translations
- Inconsistent terminology
- Poor fit for UI space

### Making Improvements

1. **Via Weblate** (Recommended):
   - Access https://weblate.lycheeorg.dev/
   - Find the translation to improve
   - Suggest or apply the correction
   - Submit for review

2. **Via Pull Request**:
   - Fork the repository
   - Make corrections in the appropriate language file
   - Test your changes
   - Submit a pull request with clear description

## Contributing Best Practices

### For Everyone

1. **Respect existing conventions** - Follow established patterns
2. **Communicate** - Coordinate with other translators
3. **Test thoroughly** - Verify translations work in context
4. **Be consistent** - Maintain terminology across the application
5. **Stay updated** - Keep translations current as Lychee evolves

### For Developers

1. **Always start with English** translations
2. **Copy new keys** to all language files immediately
3. **Provide context** in key names for translators
4. **Test with translations** before finalizing features
5. **Coordinate with translators** for major text changes

### For Translators

1. **Ask for context** if unclear about usage
2. **Be consistent** with terminology
3. **Test when possible** to verify fit and correctness
4. **Collaborate** with other translators for your language
5. **Report issues** through appropriate channels

## Getting Help

- **Weblate Issues**: Report through the Weblate platform
- **Translation Questions**: Ask in GitHub discussions or Discord
- **Technical Issues**: Open an issue on GitHub
- **Language Coordination**: Connect with other translators for your language

This localization system ensures Lychee remains accessible to users worldwide while maintaining code quality and translation consistency across all supported languages.

## Related Documentation

- [Localization Reference](../3-reference/localization.md) - Technical reference for file structure and conventions
- [Coding Conventions](../3-reference/coding-conventions.md) - General coding standards

---

*Last updated: January 21, 2026*
