


# How to Deploy Lychee with LDAP Demo Server

This guide demonstrates how to set up Lychee with a test LDAP server for development and testing purposes.

## Overview

This setup uses the [docker-test-openldap](https://github.com/rroemhild/docker-test-openldap) image, which provides a pre-configured OpenLDAP server with test data based on Futurama characters from Planet Express.

## Prerequisites

- Docker and Docker Compose installed
- Lychee application running (either via Docker or locally)
- Access to modify environment variables

## Step 1: Start the LDAP Demo Server

Use the provided Docker Compose file to start the test LDAP server:

```bash
docker-compose -f docs/specs/2-how-to/docker-compose/docker-compose-ldap.demo.yaml up -d
```

This will start an OpenLDAP server with:
- **LDAP port**: `10389` (mapped to localhost)
- **LDAPS port**: `10636` (LDAP over SSL, mapped to localhost)
- **Base DN**: `dc=planetexpress,dc=com`
- Pre-loaded test users and groups

## Step 2: Configure Lychee Environment Variables

Add the following configuration to your `.env` file:

```env
# Enable LDAP authentication alongside or instead of basic auth
LDAP_ENABLED=true

# LDAP Server connection settings
LDAP_HOST=localhost
LDAP_PORT=10389
# For LDAPS (LDAP over SSL), use port 636
# LDAP_PORT=636

# Base DN for LDAP searches
LDAP_BASE_DN=dc=planetexpress,dc=com

# Service account credentials for LDAP bind
# Using Professor Farnsworth as the service account
LDAP_BIND_DN="cn=Hubert J. Farnsworth,ou=people,dc=planetexpress,dc=com"
LDAP_BIND_PASSWORD=professor

# LDAP user search filter (%s is replaced with username)
LDAP_USER_FILTER=(&(objectClass=inetOrgPerson)(uid=%s))

# LDAP attribute mapping (maps LDAP attributes to Lychee user fields)
LDAP_ATTR_USERNAME=uid
LDAP_ATTR_EMAIL=mail
LDAP_ATTR_DISPLAY_NAME=displayname

# Admin role mapping via LDAP group
# Users in this group will have may_administrate=true (Professor and Hermes)
LDAP_ADMIN_GROUP_DN=cn=admin_staff,ou=people,dc=planetexpress,dc=com

# Auto-provision users on first LDAP login
LDAP_AUTO_PROVISION=true

# TLS/SSL settings for secure LDAP connections
LDAP_USE_TLS=false
LDAP_TLS_VERIFY_PEER=false

# Connection timeout in seconds
LDAP_CONNECTION_TIMEOUT=5
```

**Important**: Set `LDAP_ENABLED=true` to enable LDAP authentication.

## Step 3: Restart Lychee

After updating the environment variables, restart your Lychee application:

```bash
# If running via Docker Compose
docker-compose restart

# If running locally
php artisan config:clear
php artisan cache:clear
```

## Available Test Users

The test LDAP server comes pre-configured with the following users from Planet Express:

| Username | Password | Email | Display Name | Admin |
|----------|----------|-------|--------------|-------|
| `fry` | `fry` | fry@planetexpress.com | Philip J. Fry | No |
| `leela` | `leela` | leela@planetexpress.com | Turanga Leela | No |
| `bender` | `bender` | bender@planetexpress.com | Bender Bending Rodríguez | No |
| `professor` | `professor` | professor@planetexpress.com | Hubert J. Farnsworth | **Yes** |
| `hermes` | `hermes` | hermes@planetexpress.com | Hermes Conrad | **Yes** |
| `zoidberg` | `zoidberg` | zoidberg@planetexpress.com | John A. Zoidberg | No |
| `amy` | `amy` | amy@planetexpress.com | Amy Wong | No |

**Note**: Users `professor` and `hermes` are members of the `admin_staff` group and will have administrator privileges in Lychee.

## Step 4: Test the Setup

1. Navigate to your Lychee login page
2. Try logging in with one of the test accounts (e.g., username: `fry`, password: `fry`)
3. If `LDAP_AUTO_PROVISION=true`, the user will be automatically created in Lychee on first login
4. Verify that `professor` or `hermes` have admin privileges after logging in

## Troubleshooting

### Cannot connect to LDAP server

- Verify the LDAP container is running: `docker ps | grep openldap`
- Check the container logs: `docker-compose -f docs/specs/2-how-to/docker-compose/docker-compose-ldap.demo.yaml logs`
- Ensure port `10389` is not already in use: `lsof -i :10389`

### Authentication fails

- Verify the bind credentials are correct
- Check Lychee logs for LDAP-related errors
- Test LDAP connection manually using `ldapsearch`:
  ```bash
  ldapsearch -x -H ldap://localhost:10389 \
    -D "cn=Hubert J. Farnsworth,ou=people,dc=planetexpress,dc=com" \
    -w professor \
    -b "dc=planetexpress,dc=com" \
    "(uid=fry)"
  ```

### Users not auto-provisioned

- Ensure `LDAP_AUTO_PROVISION=true` in your `.env` file
- Clear the configuration cache: `php artisan config:clear`
- Check that the LDAP attribute mappings are correct

### Admin privileges not working

- Verify the `LDAP_ADMIN_GROUP_DN` matches the group in LDAP
- Check that the test user is a member of the admin group:
  ```bash
  ldapsearch -x -H ldap://localhost:10389 \
    -D "cn=Hubert J. Farnsworth,ou=people,dc=planetexpress,dc=com" \
    -w professor \
    -b "cn=admin_staff,ou=people,dc=planetexpress,dc=com"
  ```

## Stopping the LDAP Server

When you're done testing:

```bash
docker-compose -f docs/specs/2-how-to/docker-compose/docker-compose-ldap.demo.yaml down
```

To remove volumes as well:

```bash
docker-compose -f docs/specs/2-how-to/docker-compose/docker-compose-ldap.demo.yaml down -v
```

## Using LDAPS (Secure LDAP)

To test with encrypted LDAP connections, update your `.env`:

```env
LDAP_PORT=10636
LDAP_USE_TLS=true
LDAP_TLS_VERIFY_PEER=false  # Set to true in production with valid certificates
```

**Note**: In production environments, always use LDAPS with proper certificate verification.

---

*Last updated: February 16, 2026*