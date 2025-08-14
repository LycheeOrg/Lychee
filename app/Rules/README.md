# Lychee Validation Rules Documentation

This document explains the custom validation rules in Lychee, their purposes, and implementation patterns. Lychee uses Laravel's validation system with extensive custom rules to ensure data integrity and security.

## Overview

Lychee implements comprehensive validation through custom Laravel validation rules. These rules handle domain-specific validation requirements that go beyond Laravel's built-in validation rules, ensuring data consistency, security, and proper business logic enforcement.

## Rule Categories

### 1. ID Validation Rules

**Purpose**: Validate various identifier formats used throughout Lychee

- **`RandomIDRule.php`** - Validates Lychee's random ID format (base64-like strings)
- **`RandomIDListRule.php`** - Validates arrays of random IDs
- **`AlbumIDRule.php`** - Validates album IDs (random IDs + smart album types)
- **`AlbumIDListRule.php`** - Validates arrays of album IDs
- **`IntegerIDRule.php`** - Validates integer-based IDs
- **`OwnerIdRule.php`** - Validates user/owner ID references

### 2. Configuration Rules

**Purpose**: Validate configuration values and keys with type safety

- **`ConfigKeyRule.php`** - Validates configuration key names
- **`ConfigValueRule.php`** - Validates configuration values with type checking
- **`OwnerConfigRule.php`** - Validates owner-specific configuration settings

### 3. Content Validation Rules

**Purpose**: Validate user-provided content with security considerations

- **`TitleRule.php`** - Validates album/photo titles
- **`DescriptionRule.php`** - Validates descriptions with length and content rules
- **`CopyrightRule.php`** - Validates copyright information format
- **`UsernameRule.php`** - Validates username format and restrictions
- **`PasswordRule.php`** - Validates password strength and requirements
- **`CurrentPasswordRule.php`** - Validates current password for sensitive operations

### 4. File and Media Rules

**Purpose**: Validate file-related operations and formats

- **`ExtensionRule.php`** - Validates file extensions against allowed types
- **`FileUuidRule.php`** - Validates file UUID format for uploads
- **`PhotoUrlRule.php`** - Validates photo URL format and accessibility

### 5. Type and Support Rules

**Purpose**: Handle conditional validation based on system support

- **`StringRule.php`** - Basic string validation with custom constraints
- **`StringRequireSupportRule.php`** - String validation requiring license verification
- **`BooleanRequireSupportRule.php`** - Boolean validation with support requirements
- **`IntegerRequireSupportRule.php`** - Integer validation with support requirements
- **`EnumRequireSupportRule.php`** - Enum validation with support requirements
- **`ConfigKeyRequireSupportRule.php`** - Configuration key validation with support requirements

## Implementation Patterns

### Legacy Pattern (ValidateTrait)

📝 **Note**: Some rules use `ValidateTrait.php` as a legacy mechanism for compatibility with Laravel's validation system evolution from version 9 to 10.

```php
// ValidateTrait.php - Legacy compatibility
trait ValidateTrait
{
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        if (!$this->passes($attribute, $value)) {
            $fail($this->message());
        }
    }
}
```

**Rules using ValidateTrait:**
- `AlbumIDRule`
- `RandomIDRule`
- `ConfigValueRule`
- Most ID and basic validation rules

**Pattern Usage:**
```php
final class RandomIDRule implements ValidationRule
{
    use ValidateTrait;  // Legacy compatibility
    
    public function passes(string $attribute, mixed $value): bool
    {
        // Validation logic
    }
    
    public function message(): string
    {
        // Error message
    }
}
```

### Modern Pattern (Direct Implementation)

Newer rules implement `ValidationRule` directly:

```php
final class StringRequireSupportRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        // Direct validation implementation
        if (!$this->isValid($value)) {
            $fail('Validation failed');
        }
    }
}
```

## Advanced Rule Example: ConfigValueRule

The `ConfigValueRule` demonstrates advanced validation patterns including data awareness and complex validation logic:

```php
final class ConfigValueRule implements DataAwareRule, ValidationRule
{
    use ValidateTrait;

    /** @var Collection<int,Configs> */
    private Collection $configs;

    /**
     * All of the data under validation.
     *
     * @var array<string,mixed>
     */
    protected $data = [];

    public function __construct()
    {
        $this->configs = Configs::all();
    }

    /**
     * Set the data under validation.
     *
     * @param array<string,mixed> $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    public function passes(string $attribute, mixed $value): bool
    {
        // Parse nested attribute path (e.g., "settings.0.value")
        $path = explode('.', $attribute);
        if (count($path) !== 3) {
            throw new LycheeLogicException('ConfigValueRule: attribute must be in the form of "xxx.*.value"');
        }

        // Extract configuration key from related data
        $array_key = $this->data[$path[0]][intval($path[1])]['key'];
        
        // Validate value against configuration schema
        $template = 'Error: Expected %s, got ' . ($value ?? 'NULL') . '.';
        return '' === $this->configs->first(fn (Configs $c) => $c->key === $array_key)->sanity($value, $template);
    }

    public function message(): string
    {
        return ':attribute is not a valid configuration value.';
    }
}
```

### Key Features of ConfigValueRule:

**1. Data Awareness (`DataAwareRule`)**
- Implements `setData()` to access all form data
- Enables cross-field validation logic
- Allows validation based on related field values

**2. Complex Attribute Parsing**
- Handles nested array attributes (`settings.0.value`)
- Extracts related configuration keys from sibling fields
- Provides meaningful error context

**3. Dynamic Validation Logic**
- Loads all configuration schemas at runtime
- Validates values against specific configuration type requirements
- Uses configuration's built-in `sanity()` method for type checking

**4. Usage in Nested Forms**
```php
// Example form data structure:
[
    'settings' => [
        0 => ['key' => 'gallery_title', 'value' => 'My Gallery'],
        1 => ['key' => 'upload_chunk_size', 'value' => '1024'],
    ]
]

// ConfigValueRule validates that 'value' matches the expected type for 'key'
```

## Usage in Request Classes

Validation rules are used in Request classes:

```php
class UpdateAlbumRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'id' => ['required', new AlbumIDRule(false)],
            'title' => ['sometimes', new TitleRule()],
            'description' => ['sometimes', new DescriptionRule()],
            'parent_id' => ['sometimes', new AlbumIDRule(true)],
        ];
    }
}
```

This validation rule system ensures that Lychee maintains high data quality and security standards while providing clear, maintainable validation logic throughout the application.

---

*Last updated: August 14, 2025*