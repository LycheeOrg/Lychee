# Keygen License Management

This guide explains how to configure automatic license key rotation and API token monitoring for Lychee Supporter/Pro editions.

## Overview

Lychee uses license keys to unlock Supporter and Pro features. These keys can expire over time. When a **Keygen API token** is configured, Lychee provides two automatic safeguards:

- **Automatic key rotation**: when an admin logs in and the current license key is expired, Lychee attempts to fetch a fresh key from the Keygen server in the background (after the response is sent).
- **Token health check**: the diagnostics page warns administrators if their Keygen API token is expired or about to expire.

## Requirements

- A valid account at [keygen.lycheeorg.dev](https://keygen.lycheeorg.dev)
- An API token generated from your Keygen account

## Configuration

Add the following to your `.env` file:

```env
KEYGEN_API_KEY=<your-api-token>
```

### Docker

Pass the variable through your `docker-compose.yaml`:

```yaml
environment:
  - KEYGEN_API_KEY=${KEYGEN_API_KEY}
```

## How It Works

### Automatic License Rotation

1. An admin logs in (via any method: local, LDAP, OAuth, WebAuthn).
2. After the response is sent, a background job checks whether the current license key is expired.
3. If the key is expired and `KEYGEN_API_KEY` is set, Lychee calls the Keygen API to fetch a new license key.
4. On success the new key is stored in the database and takes effect immediately.
5. On failure a 24-hour cooldown prevents repeated API calls.

The same rotation is also attempted when visiting the diagnostics page with an expired license.

### Diagnostics Token Check

When an admin visits the diagnostics page, Lychee extends the Keygen API token and inspects its expiration date:

- **Error** — the token is invalid or the API returned an error. The token should be regenerated at [keygen.lycheeorg.dev](https://keygen.lycheeorg.dev).
- **Warning** — the token expires within one week. Consider renewing it.

This check is only visible to administrators.

## Troubleshooting

| Symptom | Cause | Fix |
|---|---|---|
| "Your license has expired" on diagnostics | Key expired and no `KEYGEN_API_KEY` set | Set `KEYGEN_API_KEY` in `.env`, or manually retrieve a new key from [keygen.lycheeorg.dev](https://keygen.lycheeorg.dev) |
| "Keygen API token error: …" on diagnostics | API token is invalid or revoked | Generate a new token at [keygen.lycheeorg.dev](https://keygen.lycheeorg.dev) and update `KEYGEN_API_KEY` |
| "Retry timeout active" in logs | A previous rotation attempt failed | Wait 24 hours for the cooldown to expire, or clear the cache (`php artisan cache:forget verify.rotation.next_retry`) |
| Rotation never triggers | User logging in is not an admin | Only admin logins (`may_administrate = true`) trigger rotation |
