# Renamer System

This document provides technical reference for the Renamer module in Lychee, which transforms filenames during import using customizable rules.

---

## Overview

The Renamer module provides functionality to create, manage, and apply rules for renaming files during the import process. Users can define patterns and their replacements to transform filenames based on custom rules. This is particularly useful for standardizing filenames across a collection or replacing camera-generated prefixes with more meaningful names.

## Architecture

### Core Components

1. **Renamer Class**: Located in `App\Metadata\Renamer`, this is the main class that handles the application of renamer rules to strings.
2. **RenamerRule Model**: Represents a single renaming rule in the database.
3. **RenamerModeType Enum**: Defines the available modes (FIRST, ALL, REGEX, TRIM, LOWER, UPPER, UCWORDS, UCFIRST).
4. **RenamerController**: Handles API requests for managing renaming rules.

### Database Schema

The Renamer module uses a `renamer_rules` table with the following structure:

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| owner_id | bigint | Foreign key to the user who owns this rule |
| order | int | Processing order (lower numbers processed first) |
| rule | string | Name/identifier of the rule |
| description | string | Optional description of what the rule does |
| needle | string | The string to find (pattern to match) - ignored for case/trim modes |
| replacement | string | The replacement text - ignored for case/trim modes |
| mode | enum | Mode of operation (FIRST, ALL, REGEX, TRIM, LOWER, UPPER, UCWORDS, UCFIRST) |
| is_enabled | boolean | Whether the rule is active |
| is_photo_rule | boolean | Whether to apply rule to photo filenames |
| is_album_rule | boolean | Whether to apply rule to album titles |

## Mode Types

The Renamer module supports eight modes of operation (defined in the `RenamerModeType` enum):

**Replacement Modes** (use needle/replacement fields):
1. **First occurrence** (`FIRST`): Replaces only the first occurrence of the pattern.
2. **All occurrences** (`ALL`): Replaces all occurrences of the pattern.
3. **Regular expression** (`REGEX`): Uses regular expressions for pattern matching and replacement.

**Transformation Modes** (ignore needle/replacement fields):
4. **Trim** (`TRIM`): Removes leading and trailing whitespace.
5. **Lowercase** (`LOWER`): Converts entire string to lowercase.
6. **Uppercase** (`UPPER`): Converts entire string to uppercase.
7. **Uppercase words** (`UCWORDS`): Capitalizes the first letter of each word.
8. **Uppercase first** (`UCFIRST`): Capitalizes only the first letter of the string.

## Processing Order

Rules are processed in order from lowest to highest `order` value, meaning rules with lower order numbers have higher priority. Only enabled rules are applied.

## Global Configuration

The Renamer functionality can be controlled through several configuration settings:

1. `renamer_enabled`: Global switch to enable/disable renaming functionality.
2. `renamer_enforced`: If enabled, only the system owner's rules are used, overriding user-specific rules.
3. `renamer_enforced_before`: Apply system owner's rules before user-specific rules.
4. `renamer_enforced_after`: Apply system owner's rules after user-specific rules.

## Integration Points

1. **Import Process**: The Renamer module is integrated with Lychee's import process to rename files upon upload or during server-side imports.
2. **Permission System**: The renamer functionality checks if a user has supporter status through the `Verify` class.

## API Endpoints

The Renamer module exposes API endpoints for managing rules and testing filename transformations. Refer to the `api_v2.php` routes file for the specific implementation details of these endpoints.

## Usage in Code

### Applying Renamer Rules

```php
// Create a Renamer instance for a specific user
$renamer = new Renamer($user_id);

// Apply renamer rules to a single filename
$newFilename = $renamer->handle('IMG_1234.jpg');

// Apply renamer rules to multiple filenames
$newFilenames = $renamer->handleMany(['IMG_1234.jpg', 'DSC_5678.jpg']);
```

### Saving a New Rule

```php
$rule = new RenamerRule();
$rule->owner_id = Auth::id(); // Current user's ID
$rule->rule = 'Replace IMG_';
$rule->description = 'Replaces IMG_ with Photo_';
$rule->needle = 'IMG_';
$rule->replacement = 'Photo_';
$rule->mode = RenamerModeType::FIRST;
$rule->order = 1;
$rule->is_enabled = true;
$rule->is_photo_rule = true;   // Apply to photo filenames
$rule->is_album_rule = false;  // Don't apply to album titles
$rule->save();
```

## Data Model

### RenamerRule Model

```php
class RenamerRule extends Model
{
    public int $id;
    public int $owner_id;           // User who owns this rule
    public int $order;              // Processing priority
    public string $rule;            // Rule name/identifier
    public ?string $description;    // Optional description
    public string $needle;          // Pattern to match (ignored for transformation modes)
    public string $replacement;     // Replacement text (ignored for transformation modes)
    public RenamerModeType $mode;   // FIRST, ALL, REGEX, TRIM, LOWER, UPPER, UCWORDS, or UCFIRST
    public bool $is_enabled;        // Active status
    public bool $is_photo_rule;     // Apply to photo filenames
    public bool $is_album_rule;     // Apply to album titles
}
```

## Related Documentation

- [Using Renamer](../../2-how-to/using-renamer.md) - How-to guide for adding rules and applying patterns
- [Backend Architecture](../4-architecture/backend-architecture.md) - Overall backend structure

---

*Last updated: January 21, 2026*
