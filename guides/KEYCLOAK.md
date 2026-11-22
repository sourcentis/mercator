# Keycloak Authentication with Local Fallback

## Overview

This implementation adds a **fallback to local authentication** system for Keycloak, similar to the existing one for
LDAP.

## Features

### 1. Auto-Provisioning (`KEYCLOAK_AUTO_PROVISION`)

Automatically creates a local user upon first successful SSO login.

```env
KEYCLOAK_AUTO_PROVISION=true
KEYCLOAK_AUTO_PROVISION_ROLE=User
```

**Behavior:**

- ✅ **true**: User automatically created with default role
- ❌ **false**: Only existing users can login via SSO

### 2. Local Fallback (`KEYCLOAK_FALLBACK_LOCAL`)

Allows fallback to local authentication in case of SSO failure.

```env
KEYCLOAK_FALLBACK_LOCAL=true
```

**Scenarios triggering fallback:**

1. Keycloak communication failure
2. Invalid or expired token
3. User not provisioned locally (if `AUTO_PROVISION=false`)

**Behavior:**

- ✅ **true**: Redirects to `/login` with informative message
- ❌ **false**: Shows error and blocks access

### 3. Role Synchronization (`KEYCLOAK_SYNC_ROLES`)

Synchronizes Keycloak roles with corresponding local roles.

```env
KEYCLOAK_SYNC_ROLES=true
```

**Mechanism:**

- Compares `realm_access.roles` from Keycloak with local `Role::title`
- Uses `syncWithoutDetaching()` to preserve existing local roles
- Roles missing in Keycloak are preserved

## Decision Matrix

| Scenario                  | `AUTO_PROVISION` | `FALLBACK_LOCAL` | Result                         |
|---------------------------|------------------|------------------|--------------------------------|
| SSO OK, existing user     | ✓/✗              | ✓/✗              | ✅ SSO login successful         |
| SSO OK, non-existing user | ✓                | ✓/✗              | ✅ Auto-provision + Login       |
| SSO OK, non-existing user | ✗                | ✓                | ⚠️ Redirect to local login     |
| SSO OK, non-existing user | ✗                | ✗                | ❌ Error: invalid user          |
| SSO fails                 | ✓/✗              | ✓                | ⚠️ Redirect to local login     |
| SSO fails                 | ✓/✗              | ✗                | ❌ Error: authentication failed |

## LDAP vs Keycloak Comparison

### Similarities

| Feature           | LDAP                         | Keycloak                         |
|-------------------|------------------------------|----------------------------------|
| Auto-provisioning | ✅ `LDAP_AUTO_PROVISION`      | ✅ `KEYCLOAK_AUTO_PROVISION`      |
| Local fallback    | ✅ `LDAP_FALLBACK_LOCAL`      | ✅ `KEYCLOAK_FALLBACK_LOCAL`      |
| Default role      | ✅ `LDAP_AUTO_PROVISION_ROLE` | ✅ `KEYCLOAK_AUTO_PROVISION_ROLE` |
| Audit logs        | ✅                            | ✅                                |

### Differences

| Aspect          | LDAP                                          | Keycloak                          |
|-----------------|-----------------------------------------------|-----------------------------------|
| **Flow**        | Direct bind with credentials                  | OAuth2 redirect flow              |
| **Fallback**    | Same request, credentials available           | Separate redirect, no credentials |
| **Role sync**   | Via LDAP groups                               | Via `realm_access.roles`          |
| **User search** | Multiple attributes (`uid`, `sAMAccountName`) | Login or email                    |

## Recommended Use Cases

### Strict Configuration (Production)

```env
KEYCLOAK_ENABLED=true
KEYCLOAK_AUTO_PROVISION=false
KEYCLOAK_FALLBACK_LOCAL=false
KEYCLOAK_SYNC_ROLES=true
```

- ✅ Maximum security
- ✅ Full control over users
- ❌ Requires manual pre-provisioning

### Permissive Configuration (Dev/Test)

```env
KEYCLOAK_ENABLED=true
KEYCLOAK_AUTO_PROVISION=true
KEYCLOAK_FALLBACK_LOCAL=true
KEYCLOAK_SYNC_ROLES=true
```

- ✅ Automatic onboarding
- ✅ Fallback in case of issues
- ⚠️ Less control

### Hybrid Configuration (Recommended)

```env
KEYCLOAK_ENABLED=true
KEYCLOAK_AUTO_PROVISION=true
KEYCLOAK_FALLBACK_LOCAL=true
KEYCLOAK_SYNC_ROLES=true
KEYCLOAK_AUTO_PROVISION_ROLE=User
```

- ✅ Auto-provisioning with limited role
- ✅ Fallback for service continuity
- ✅ Role sync for governance

## Logging and Debugging

The following events are logged:

```php
// Keycloak failure
Log::warning('Keycloak authentication failed', ['error' => ...]);

// User not found
Log::info('Keycloak user not found locally and auto-provision disabled', ['login' => ...]);

// Auto-provision successful
Log::info('Keycloak user auto-provisioned', ['user_id' => ..., 'login' => ...]);

// Login successful
Log::info('Keycloak login successful', ['user_id' => ..., 'login' => ...]);
```

Audit logs record:

- Action: `SSO Login (Keycloak)`
- User agent, IP, URL
- Received Keycloak roles

## Migration from Old Code

### Required changes in `config/services.php`:

```php
'keycloak' => [
    'enabled' => env('KEYCLOAK_ENABLED', false),
    'client_id' => env('KEYCLOAK_CLIENT_ID'),
    'client_secret' => env('KEYCLOAK_CLIENT_SECRET'),
    'redirect' => env('KEYCLOAK_REDIRECT_URI'),
    'base_url' => env('KEYCLOAK_BASE_URL'),
    'realms' => env('KEYCLOAK_REALM'),
    'btn_label' => env('KEYCLOAK_BTN_LABEL', 'Keycloak'),
    
    // New options
    'auto_provision' => env('KEYCLOAK_AUTO_PROVISION', false),
    'auto_provision_role' => env('KEYCLOAK_AUTO_PROVISION_ROLE', 'User'),
    'fallback_local' => env('KEYCLOAK_FALLBACK_LOCAL', false),
    'sync_roles' => env('KEYCLOAK_SYNC_ROLES', false),
],
```

### Deprecated `KEYCLOAK` variable

The former `KEYCLOAK=enable` variable is no longer used: the login button is shown as soon as
`KEYCLOAK_ENABLED=true`. Fallback messages (`info`, `error`) are displayed natively on the login page.

## Tests

### Test SSO → Local fallback

1. Temporarily disable Keycloak:
   ```env
   KEYCLOAK_ENABLED=false
   KEYCLOAK_FALLBACK_LOCAL=true
   ```

2. Attempt SSO → should redirect to local login

### Test auto-provisioning

1. Configuration:
   ```env
   KEYCLOAK_ENABLED=true
   KEYCLOAK_AUTO_PROVISION=true
   KEYCLOAK_AUTO_PROVISION_ROLE=User
   ```

2. Login with a Keycloak user that doesn't exist locally
3. Verify creation in `users` table with `User` role

### Test role sync

1. Create local roles: `Admin`, `Editor`, `User`
2. Assign `Admin` and `Editor` in Keycloak
3. Login via SSO
4. Verify in `role_user` that roles are synchronized

## Security

### Recommendations

1. **Production**: `FALLBACK_LOCAL=false` to enforce SSO
2. **Passwords**: Auto-provisioned users receive a random unusable password
3. **Roles**: Use `syncWithoutDetaching()` to preserve custom local roles
4. **Audit**: All SSO logins are audited with received Keycloak roles

### Verifications

```php
// Generated password is unusable
Hash::make(Str::random(32))

// Local roles are preserved
$user->roles()->syncWithoutDetaching($keycloakRoles)
```

## Support

For questions or improvements, create an issue on the Mercator GitHub repository.