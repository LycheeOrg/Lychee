# LDAP Authentication Setup Guide

This guide explains how to configure Lychee to authenticate users against an LDAP or Active Directory server.

## Table of Contents

- [Overview](#overview)
- [Requirements](#requirements)
- [Quick Start](#quick-start)
- [Configuration](#configuration)
  - [OpenLDAP Configuration](#openldap-configuration)
  - [Active Directory Configuration](#active-directory-configuration)
  - [TLS/SSL Configuration](#tlsssl-configuration)
- [Attribute Mapping](#attribute-mapping)
- [Admin Role Assignment](#admin-role-assignment)
- [User Provisioning](#user-provisioning)
- [Troubleshooting](#troubleshooting)
- [Security Best Practices](#security-best-practices)

## Overview

Lychee supports LDAP authentication for enterprise directory integration. When enabled, users can authenticate using their LDAP/Active Directory credentials. The system supports:

- **Dual authentication**: LDAP-first with automatic fallback to local authentication
- **Auto-provisioning**: Automatically create local user accounts on first LDAP login
- **Attribute synchronization**: Sync email and display name from LDAP
- **Role mapping**: Assign admin privileges based on LDAP group membership
- **Graceful degradation**: Falls back to local auth if LDAP server is unreachable

## Requirements

- **PHP extension**: `php-ldap` must be installed and enabled
- **LDAP server**: OpenLDAP, Active Directory, or compatible directory server
- **Service account**: Read-only LDAP account for searching users and groups
- **Network access**: Lychee server must be able to reach LDAP server on configured port

### Verify PHP LDAP Extension

```bash
php -m | grep ldap
```

If not installed:

```bash
# Debian/Ubuntu
sudo apt-get install php-ldap

# RHEL/CentOS
sudo yum install php-ldap

# Restart web server after installation
sudo systemctl restart apache2  # or nginx/php-fpm
```

## Quick Start

1. Copy LDAP configuration from `.env.example` to `.env`:

```bash
# Enable LDAP authentication
LDAP_ENABLED=true

# Connection settings
LDAP_HOST=ldap.example.com
LDAP_PORT=389
LDAP_BASE_DN=dc=example,dc=com

# Service account
LDAP_BIND_DN=cn=lychee-service,ou=services,dc=example,dc=com
LDAP_BIND_PASSWORD=securepassword

# User search filter
LDAP_USER_FILTER=(&(objectClass=person)(uid=%s))

# Enable TLS
LDAP_USE_TLS=true
LDAP_TLS_VERIFY_PEER=true
```

2. Run database migration to add LDAP user fields:

```bash
php artisan migrate
```

3. Test authentication by logging in with LDAP credentials

## Configuration

### OpenLDAP Configuration

OpenLDAP typically uses:
- **Username attribute**: `uid`
- **Email attribute**: `mail`
- **Display name attribute**: `displayName`
- **User filter**: `(&(objectClass=person)(uid=%s))`

Example `.env` configuration:

```dotenv
LDAP_ENABLED=true
LDAP_HOST=ldap.example.com
LDAP_PORT=389
LDAP_BASE_DN=dc=example,dc=com
LDAP_BIND_DN=cn=lychee-service,ou=services,dc=example,dc=com
LDAP_BIND_PASSWORD=securepassword
LDAP_USER_FILTER=(&(objectClass=person)(uid=%s))
LDAP_ATTR_USERNAME=uid
LDAP_ATTR_EMAIL=mail
LDAP_ATTR_DISPLAY_NAME=displayName
LDAP_USE_TLS=true
LDAP_TLS_VERIFY_PEER=true
LDAP_CONNECTION_TIMEOUT=5
```

### Active Directory Configuration

Active Directory typically uses:
- **Username attribute**: `sAMAccountName`
- **Email attribute**: `userPrincipalName` or `mail`
- **Display name attribute**: `displayName`
- **User filter**: `(&(objectClass=user)(sAMAccountName=%s))`

Example `.env` configuration:

```dotenv
LDAP_ENABLED=true
LDAP_HOST=ad.corp.example.com
LDAP_PORT=389
LDAP_BASE_DN=dc=corp,dc=example,dc=com
LDAP_BIND_DN=cn=lychee-service,ou=ServiceAccounts,dc=corp,dc=example,dc=com
LDAP_BIND_PASSWORD=securepassword
LDAP_USER_FILTER=(&(objectClass=user)(sAMAccountName=%s))
LDAP_ATTR_USERNAME=sAMAccountName
LDAP_ATTR_EMAIL=userPrincipalName
LDAP_ATTR_DISPLAY_NAME=displayName
LDAP_USE_TLS=true
LDAP_TLS_VERIFY_PEER=true
LDAP_CONNECTION_TIMEOUT=5
```

### TLS/SSL Configuration

#### LDAPS (LDAP over SSL) - Port 636

LDAPS encrypts the entire LDAP session from the start using SSL/TLS on port 636:

```dotenv
LDAP_HOST=ldaps.example.com
LDAP_PORT=636
LDAP_USE_TLS=false  # TLS is implicit with port 636
LDAP_TLS_VERIFY_PEER=true
```

#### StartTLS - Port 389

StartTLS upgrades a standard LDAP connection on port 389 to use TLS:

```dotenv
LDAP_HOST=ldap.example.com
LDAP_PORT=389
LDAP_USE_TLS=true
LDAP_TLS_VERIFY_PEER=true
```

**Note**: StartTLS is the recommended approach as it's more flexible and follows modern security practices.

#### Certificate Verification

For production environments, always verify TLS certificates:

```dotenv
LDAP_TLS_VERIFY_PEER=true
```

For development/testing with self-signed certificates, you may disable verification (NOT recommended for production):

```dotenv
LDAP_TLS_VERIFY_PEER=false
```

## Attribute Mapping

Lychee supports flexible attribute mapping to accommodate different LDAP schemas.

### Standard Attributes

| Lychee Field | OpenLDAP Attribute | Active Directory Attribute |
|--------------|-------------------|----------------------------|
| Username | `uid` | `sAMAccountName` |
| Email | `mail` | `userPrincipalName` or `mail` |
| Display Name | `displayName` | `displayName` |

### Custom Attributes

You can map to any LDAP attributes:

```dotenv
# Example: Use 'cn' (common name) as display name
LDAP_ATTR_DISPLAY_NAME=cn

# Example: Use custom email attribute
LDAP_ATTR_EMAIL=corporateEmail
```

### Fallback Behavior

- **Email**: If LDAP attribute is empty, user email will be `null`
- **Display Name**: If LDAP attribute is empty, username is used as fallback

## Admin Role Assignment

Lychee can automatically assign admin privileges to users based on LDAP group membership.

### Configuration

```dotenv
# Users in this group will have may_administrate=true
LDAP_ADMIN_GROUP_DN=cn=lychee-admins,ou=groups,dc=example,dc=com
```

### How It Works

1. User authenticates via LDAP
2. Lychee queries user's group memberships
3. If user is member of `LDAP_ADMIN_GROUP_DN`, sets `may_administrate=true`
4. If user is not in admin group (or no admin group configured), sets `may_administrate=false`

### Group Query

Lychee searches for groups where the user is a member using:

```ldap
(member=USER_DN)
```

This works for most LDAP/AD configurations. If your directory uses a different membership attribute (e.g., `uniqueMember`), please open an issue.

## User Provisioning

### Auto-Provisioning (Default)

When enabled, Lychee automatically creates local user accounts on first LDAP login:

```dotenv
LDAP_AUTO_PROVISION=true
```

**New user defaults:**
- `may_upload=true`
- `may_edit_own_settings=true`
- `may_administrate=false` (unless in admin group)
- Password: random (user authenticates via LDAP, not local password)

### Manual Provisioning

Disable auto-provisioning to require manual user creation:

```dotenv
LDAP_AUTO_PROVISION=false
```

With manual provisioning:
1. Admin pre-creates users in Lychee with matching usernames
2. Users can then log in via LDAP
3. Attributes (email, display name, admin status) sync from LDAP on each login

### Attribute Synchronization

On every LDAP login, Lychee updates:
- Email address from LDAP
- Display name from LDAP
- Admin status from LDAP group membership

This ensures local user attributes stay in sync with directory changes.

## Troubleshooting

### Authentication Failures

#### Check PHP LDAP Extension

```bash
php -m | grep ldap
```

If missing, install `php-ldap` package.

#### Test LDAP Connection

```bash
ldapsearch -x -H ldap://ldap.example.com:389 -D "cn=lychee-service,ou=services,dc=example,dc=com" -w "securepassword" -b "dc=example,dc=com" "(uid=testuser)"
```

**Expected output**: LDAP entry for testuser

**Common errors:**
- `Can't contact LDAP server`: Check `LDAP_HOST` and `LDAP_PORT`, verify network access
- `Invalid credentials`: Check `LDAP_BIND_DN` and `LDAP_BIND_PASSWORD`
- `No such object`: Check `LDAP_BASE_DN`

#### Check Lychee Logs

LDAP operations are logged to Laravel's logging system:

```bash
tail -f storage/logs/laravel.log | grep -i ldap
```

**Log levels:**
- **DEBUG**: Connection attempts, searches, binds, attribute retrieval
- **INFO**: Successful authentication, user provisioning, admin role assignment
- **NOTICE**: User not found in LDAP
- **WARNING**: Authentication failures, group query errors
- **ERROR**: Connection failures, timeout errors

### TLS/SSL Issues

#### Certificate Verification Failures

If you see "TLS certificate verification failed":

1. **Production**: Ensure LDAP server has valid certificate from trusted CA
2. **Development**: Temporarily disable verification (NOT for production):
   ```dotenv
   LDAP_TLS_VERIFY_PEER=false
   ```

#### LDAPS vs StartTLS

**LDAPS (port 636):**
```dotenv
LDAP_PORT=636
LDAP_USE_TLS=false  # TLS is implicit
```

**StartTLS (port 389):**
```dotenv
LDAP_PORT=389
LDAP_USE_TLS=true
```

### Connection Timeouts

If LDAP queries are slow:

```dotenv
# Increase timeout (default: 5 seconds)
LDAP_CONNECTION_TIMEOUT=10
```

### User Not Found

If user exists in LDAP but can't authenticate:

1. Verify `LDAP_USER_FILTER` matches your directory structure
2. Test filter with `ldapsearch`:
   ```bash
   ldapsearch -x -H ldap://ldap.example.com -D "BIND_DN" -w "PASSWORD" -b "BASE_DN" "(&(objectClass=person)(uid=testuser))"
   ```
3. Check `LDAP_ATTR_USERNAME` matches the attribute in your filter

### Admin Status Not Updating

If admin group membership isn't working:

1. Verify group DN is correct:
   ```bash
   ldapsearch -x -H ldap://ldap.example.com -D "BIND_DN" -w "PASSWORD" -b "BASE_DN" "(cn=lychee-admins)"
   ```
2. Check group membership:
   ```bash
   ldapsearch -x -H ldap://ldap.example.com -D "BIND_DN" -w "PASSWORD" -b "GROUP_DN" "(member=USER_DN)"
   ```
3. Verify `LDAP_ADMIN_GROUP_DN` matches exactly (case-insensitive)

### Graceful Degradation Testing

To test fallback to local auth when LDAP is unavailable:

1. Set invalid LDAP host:
   ```dotenv
   LDAP_HOST=unreachable.example.com
   ```
2. Attempt login with local credentials
3. Should succeed with local auth and log warning about LDAP failure

## Security Best Practices

### 1. Use TLS/SSL

Always encrypt LDAP traffic in production:

```dotenv
LDAP_USE_TLS=true
LDAP_TLS_VERIFY_PEER=true
```

### 2. Dedicated Service Account

Create a dedicated read-only service account for Lychee:

```ldap
dn: cn=lychee-service,ou=services,dc=example,dc=com
objectClass: simpleSecurityObject
objectClass: organizationalRole
cn: lychee-service
description: Lychee photo gallery LDAP service account
userPassword: {SSHA}...securepassword...
```

**Permissions needed:**
- Read access to user objects (uid, mail, displayName)
- Read access to group objects (for admin role mapping)
- No write access required

### 3. Restrict Base DN

Use the most specific base DN possible to limit search scope:

```dotenv
# ✅ Good - specific OU
LDAP_BASE_DN=ou=employees,dc=example,dc=com

# ❌ Avoid - entire domain
LDAP_BASE_DN=dc=example,dc=com
```

### 4. Secure Credentials

- Store `LDAP_BIND_PASSWORD` in `.env` (not in version control)
- Use environment-specific `.env` files
- Rotate service account password regularly
- Use strong, unique passwords

### 5. Connection Timeout

Set reasonable timeout to prevent hanging:

```dotenv
LDAP_CONNECTION_TIMEOUT=5
```

### 6. Monitor Logs

Regularly review LDAP authentication logs for:
- Failed authentication attempts
- Connection errors
- Unusual activity patterns

```bash
grep -i "ldap" storage/logs/laravel-*.log | grep -i "error\|warning"
```

### 7. Test Graceful Degradation

Verify fallback to local auth works:
1. Ensure local admin account exists
2. Test authentication when LDAP is unavailable
3. Verify warning is logged but authentication succeeds

### 8. Auto-Provision Security

If disabling auto-provisioning:

```dotenv
LDAP_AUTO_PROVISION=false
```

Pre-create users with appropriate permissions before allowing LDAP login.

### 9. Network Security

- Use firewall rules to restrict LDAP access to Lychee server only
- Consider VPN or private network for LDAP communication
- Disable insecure LDAP (port 389 without TLS) in production

### 10. Regular Updates

Keep Lychee and LdapRecord library updated for security patches:

```bash
composer update directorytree/ldaprecord-laravel
```

## Advanced Configuration

### Multiple LDAP Servers

Currently, Lychee supports one LDAP server. For high availability, consider:
- DNS round-robin for `LDAP_HOST`
- Load balancer in front of LDAP servers
- LDAP replication/clustering

### Custom User Filters

You can customize the user filter for complex requirements:

```dotenv
# Require specific group membership
LDAP_USER_FILTER=(&(objectClass=person)(uid=%s)(memberOf=cn=lychee-users,ou=groups,dc=example,dc=com))

# Multiple object classes
LDAP_USER_FILTER=(&(|(objectClass=person)(objectClass=inetOrgPerson))(uid=%s))

# Exclude disabled accounts (Active Directory)
LDAP_USER_FILTER=(&(objectClass=user)(sAMAccountName=%s)(!(userAccountControl:1.2.840.113556.1.4.803:=2)))
```

### Testing LDAP Configuration

Create a test script to verify configuration:

```php
<?php
// test-ldap.php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$ldapConfig = new \App\DTO\LdapConfiguration();
$ldapService = new \App\Services\Auth\LdapService($ldapConfig);

$username = 'testuser';
$password = 'testpassword';

try {
    $ldapUser = $ldapService->authenticate($username, $password);
    
    if ($ldapUser !== null) {
        echo "✅ Authentication successful!\n";
        echo "Username: {$ldapUser->username}\n";
        echo "DN: {$ldapUser->userDn}\n";
        echo "Email: " . ($ldapUser->email ?? 'N/A') . "\n";
        echo "Display Name: " . ($ldapUser->display_name ?? 'N/A') . "\n";
    } else {
        echo "❌ Authentication failed - invalid credentials\n";
    }
} catch (\App\Exceptions\LdapConnectionException $e) {
    echo "❌ Connection error: " . $e->getMessage() . "\n";
} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
```

Run with:

```bash
php test-ldap.php
```

---

**Need help?** Open an issue on GitHub: https://github.com/LycheeOrg/Lychee/issues

---

*Last updated: January 26, 2026*
