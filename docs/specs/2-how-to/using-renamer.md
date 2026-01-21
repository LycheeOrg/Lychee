# Using Renamer

This guide shows how to use the Renamer module to add rules, apply transformation patterns, and manage filename transformations during photo import.

---

## Overview

The Renamer module allows you to automatically transform filenames during import. This is useful for:
- Standardizing filenames across your photo collection
- Replacing camera-generated prefixes (e.g., `IMG_`, `DSC_`) with meaningful names
- Applying consistent naming conventions

## Adding Renamer Rules

### Creating a Simple Rule

To create a basic replacement rule:

1. Access the Renamer API or use the admin interface
2. Define the pattern to find (needle) - not needed for case/trim modes
3. Specify the replacement text - not needed for case/trim modes
4. Choose the replacement mode (FIRST, ALL, REGEX, TRIM, LOWER, UPPER, UCWORDS, or UCFIRST)
5. Set the processing order
6. Enable the rule
7. Optionally set `is_photo_rule` and/or `is_album_rule` to control where the rule applies

### Example: Replace Camera Prefix

Replace `IMG_` with `Photo_`:

```php
$rule = new RenamerRule();
$rule->owner_id = Auth::id();
$rule->rule = 'Replace IMG_';
$rule->description = 'Replaces IMG_ with Photo_';
$rule->needle = 'IMG_';
$rule->replacement = 'Photo_';
$rule->mode = RenamerModeType::FIRST;  // Only replace first occurrence
$rule->order = 1;
$rule->is_enabled = true;
$rule->is_photo_rule = true;   // Apply to photo filenames
$rule->is_album_rule = false;  // Don't apply to album titles
$rule->save();
```

**Result**: `IMG_1234.jpg` becomes `Photo_1234.jpg`

### Example: Replace All Underscores

Replace all underscores with spaces:

```php
$rule = new RenamerRule();
$rule->owner_id = Auth::id();
$rule->rule = 'Underscores to Spaces';
$rule->description = 'Replace all underscores with spaces';
$rule->needle = '_';
$rule->replacement = ' ';
$rule->mode = RenamerModeType::ALL;  // Replace all occurrences
$rule->order = 2;
$rule->is_enabled = true;
$rule->save();
```

**Result**: `Photo_from_trip_2024.jpg` becomes `Photo from trip 2024.jpg`

### Example: Regular Expression Pattern

Use regex to add a date prefix:

```php
$rule = new RenamerRule();
$rule->owner_id = Auth::id();
$rule->rule = 'Add Date Prefix';
$rule->description = 'Extract date from filename and move to beginning';
$rule->needle = '/^(.+)_(\d{4}-\d{2}-\d{2})(.+)$/';
$rule->replacement = '$2_$1$3';
$rule->mode = RenamerModeType::REGEX;  // Use regex matching
$rule->order = 3;
$rule->is_enabled = true;
$rule->save();
```

**Result**: `vacation_2024-06-15_beach.jpg` becomes `2024-06-15_vacation_beach.jpg`

### Example: Transform Case

Use case transformation modes (needle/replacement are ignored for these):

```php
// Convert to lowercase
$rule = new RenamerRule();
$rule->owner_id = Auth::id();
$rule->rule = 'Lowercase';
$rule->description = 'Convert filename to lowercase';
$rule->mode = RenamerModeType::LOWER;
$rule->order = 4;
$rule->is_enabled = true;
$rule->save();
```

**Result**: `VACATION_Photo.jpg` becomes `vacation_photo.jpg`

### Example: Trim Whitespace

```php
$rule = new RenamerRule();
$rule->owner_id = Auth::id();
$rule->rule = 'Trim spaces';
$rule->description = 'Remove leading/trailing whitespace';
$rule->mode = RenamerModeType::TRIM;
$rule->order = 5;
$rule->is_enabled = true;
$rule->save();
```

**Result**: `  photo name.jpg  ` becomes `photo name.jpg`

## Applying Patterns

### Single Filename

Transform a single filename:

```php
$renamer = new Renamer($user_id);
$newFilename = $renamer->handle('IMG_1234.jpg');
echo $newFilename;  // Output: Photo_1234.jpg (if rule is enabled)
```

### Multiple Filenames

Transform multiple filenames at once:

```php
$renamer = new Renamer($user_id);
$filenames = ['IMG_1234.jpg', 'DSC_5678.jpg', 'vacation_photo.jpg'];
$newFilenames = $renamer->handleMany($filenames);

foreach ($newFilenames as $filename) {
    echo $filename . "\n";
}
```

### During Import

The Renamer automatically applies during photo import when enabled. No additional code is required - rules are applied automatically based on the user's configuration.

## Managing Rules

### Rule Priority

Rules are processed in order from lowest to highest `order` value:

```php
// This rule runs first (lower order number)
$rule1 = new RenamerRule();
$rule1->order = 1;
$rule1->needle = 'IMG_';
$rule1->replacement = 'Photo_';

// This rule runs second
$rule2 = new RenamerRule();
$rule2->order = 2;
$rule2->needle = '_';
$rule2->replacement = ' ';
```

**Processing**: `IMG_1234_vacation.jpg` → `Photo_1234_vacation.jpg` → `Photo 1234 vacation.jpg`

### Enabling/Disabling Rules

Toggle rules without deleting them:

```php
// Disable a rule temporarily
$rule->is_enabled = false;
$rule->save();

// Re-enable later
$rule->is_enabled = true;
$rule->save();
```

### Deleting Rules

Remove rules permanently:

```php
$rule->delete();
```

## Configuration Options

### Global Settings

Control Renamer behavior system-wide:

- **`renamer_enabled`**: Enable/disable renaming functionality globally
- **`renamer_enforced`**: Force only system owner's rules (overrides user rules)
- **`renamer_enforced_before`**: Apply system owner's rules before user rules
- **`renamer_enforced_after`**: Apply system owner's rules after user rules

### User-specific vs System Rules

When `renamer_enforced` is enabled:
- Only the system owner's rules apply
- Individual user rules are ignored

When `renamer_enforced_before` is enabled:
- System owner's rules run first
- Then user's personal rules apply

When `renamer_enforced_after` is enabled:
- User's personal rules run first
- Then system owner's rules apply

## Common Use Cases

### Standardize Camera Imports

Create rules to replace common camera prefixes:

```php
// Canon
['needle' => 'IMG_', 'replacement' => 'Photo_', 'mode' => FIRST]

// Nikon
['needle' => 'DSC_', 'replacement' => 'Photo_', 'mode' => FIRST]

// Sony
['needle' => 'DSC', 'replacement' => 'Photo', 'mode' => FIRST]
```

### Clean Up Filenames

Remove unwanted characters:

```php
// Remove dashes
['needle' => '-', 'replacement' => '', 'mode' => ALL]

// Replace underscores with spaces
['needle' => '_', 'replacement' => ' ', 'mode' => ALL]
```

### Add Prefixes

Add consistent prefixes to all files:

```php
// Add "Client_" prefix using regex
['needle' => '/^(.+)$/', 'replacement' => 'Client_$1', 'mode' => REGEX]
```

## Tips and Best Practices

1. **Test rules first**: Use the test API endpoint to preview transformations before enabling
2. **Order matters**: Set lower order numbers for rules that should run first
3. **Use FIRST carefully**: Only use FIRST mode when you want to preserve subsequent occurrences
4. **Regex power**: Use REGEX mode for complex pattern matching and capture groups
5. **Backup rules**: Keep a list of your rule configurations for recovery
6. **Start simple**: Begin with simple rules and add complexity as needed

## Troubleshooting

### Rule Not Applied

Check:
- Rule is enabled (`is_enabled = true`)
- Global renaming is enabled (`renamer_enabled = true`)
- User has permission (supporter status)
- Rule order is correct

### Unexpected Results

Verify:
- Pattern matches exactly what you expect
- Mode is correct (FIRST vs ALL vs REGEX)
- Rule processing order
- No conflicting rules

### Regex Not Working

Ensure:
- Pattern syntax is valid PHP regex
- Capture groups are properly referenced (`$1`, `$2`, etc.)
- Delimiters are correct (typically `/pattern/`)

## Related Documentation

- [Renamer System](../3-reference/renamer-system.md) - Technical reference and architecture
- [Backend Architecture](../4-architecture/backend-architecture.md) - Overall backend structure

---

*Last updated: January 21, 2026*
