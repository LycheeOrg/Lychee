# Adding an OAuth Provider to Lychee

This guide explains how to add a new OAuth provider to Lychee, which uses the Laravel Socialite package along with the [Socialite Providers](https://socialiteproviders.com/) extensions.

## Step 1: Add the Provider Package

First, install the required Socialite Provider package via Composer. You can find available providers at [socialiteproviders.com](https://socialiteproviders.com/about/).

```bash
composer require socialiteproviders/[provider-name]
```

Replace `[provider-name]` with the name of the provider you want to add (e.g., `discord`, `gitlab`, etc.).

## Step 2: Update the Enum

Add the new provider to `app/Enum/OauthProvidersType.php`:

```php
enum OauthProvidersType: string
{
    use DecorateBackedEnum;

    // Existing providers
    case AMAZON = 'amazon';
    // ...
    // Add your new provider
    case NEW_PROVIDER = 'new-provider';
}
```

Use uppercase for the case name and lowercase with hyphens for the string value.

## Step 3: Update the Event Service Provider

Add the provider to the `EventServiceProvider.php` in the `$listen` array:

```php
protected $listen = [
    // ...
    SocialiteWasCalled::class => [
        // Existing providers
        AmazonExtendSocialite::class . '@handle',
        // ...
        // Add your new provider
        NewProviderExtendSocialite::class . '@handle',
    ],
];
```

Be sure to import the provider's class at the top of the file:

```php
use SocialiteProviders\NewProvider\NewProviderExtendSocialite;
```

## Step 4: Add Configuration to services.php

Add the configuration for your new provider in `config/services.php`:

```php
'new-provider' => [
    'client_id' => env('NEW_PROVIDER_CLIENT_ID'),
    'client_secret' => env('NEW_PROVIDER_CLIENT_SECRET'),
    'redirect' => env('NEW_PROVIDER_REDIRECT_URI', '/auth/new-provider/redirect'),
    // Add any additional provider-specific settings here
],
```

## Step 5: Update Environment Variables

Add the necessary environment variables to your `.env` file and equally important, the `.env.example`:

```
NEW_PROVIDER_CLIENT_ID=your_client_id
NEW_PROVIDER_CLIENT_SECRET=your_client_secret
NEW_PROVIDER_REDIRECT_URI=/auth/new-provider/redirect
```

You may need additional variables depending on the provider.

## Step 6: Update the Frontend

Add an icon for your provider in `resources/js/services/oauth-service.ts`:

```typescript
providerIcon(provider: App.Enum.OauthProvidersType): string {
    switch (provider) {
        // Existing cases...
        case "new-provider":
            return "fa-brands fa-new-provider"; // Use an appropriate Font Awesome icon
    }
}
```

## Step 7: Double check the Routes

The OAuth routes are already defined in `routes/web_v2.php`, but you need to ensure that your new provider is recognized by the route constraints. If your provider follows the standard pattern, it should work automatically since the routes use the `OauthProvidersType::values()` method to validate the provider name:

```php
Route::get('/auth/{provider}/redirect', [OauthController::class, 'redirected'])
    ->whereIn('provider', OauthProvidersType::values());
Route::get('/auth/{provider}/authenticate', [OauthController::class, 'authenticate'])
    ->name('oauth-authenticate')
    ->whereIn('provider', OauthProvidersType::values());
Route::get('/auth/{provider}/register', [OauthController::class, 'register'])
    ->name('oauth-register')
    ->whereIn('provider', OauthProvidersType::values());
```

## Step 8: Test Your Integration

1. Create an OAuth application at the provider's developer portal
2. Configure the provider with the correct redirect URI (should be `https://your-lychee-url/auth/{provider}/redirect`)
3. Add the credentials to your `.env` file
4. Test the authentication flow by visiting `https://your-lychee-url/auth/{provider}/authenticate`
5. Test the registration flow by visiting your profile page and using the registration link

## Common Issues

- **Redirect URI Issues**: Ensure the redirect URI configured at the provider matches exactly what you've set in your `.env` file
- **Scopes**: Some providers require specific scopes to access user information; check the provider's documentation
- **User Data Mapping**: Different providers return user data in different formats; you may need to adjust how user data is mapped
- **Authentication Flow**: Make sure the provider's class is correctly imported in the `EventServiceProvider.php` file

## Provider-Specific Notes

### Mastodon

Mastodon requires a domain configuration:

```php
'mastodon' => [
    'domain' => env('MASTODON_DOMAIN'),
    // Other settings...
],
```

### Keycloak, Authelia, Authentik

These providers require a base URL:

```php
'keycloak' => [
    'base_url' => env('KEYCLOAK_BASE_URL'),
    'realms' => env('KEYCLOAK_REALM'),
    // Other settings...
],
```

## Internal Implementation Details

For developers who want to understand the OAuth implementation in Lychee:

- The OAuth authentication flow is handled by the `OauthController` class:
  - `authenticate()`: Initiates the OAuth login flow
  - `register()`: Allows a logged-in user to associate an OAuth provider with their account
  - `redirected()`: Handles the callback from the OAuth provider
  - `clear()`: Removes an OAuth association from a user account

- OAuth credentials are stored in the database and managed by the `OauthCredential` model

- The system uses Laravel's Socialite package with the SocialiteProviders extensions to handle the OAuth flows

- Authentication methods are configured in `AuthServiceProvider`, which determines available OAuth providers

---

*Last updated: September 3, 2025*
