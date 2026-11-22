# Keycloak Authentication

## Overview

Mercator can delegate user authentication to **Keycloak** through OpenID Connect (authorization code flow). A
"Keycloak" button is added to the login page; the local login form remains available.

On each SSO login, Mercator:

1. redirects the user to Keycloak (`/realms/<realm>/protocol/openid-connect/auth`, scope `openid`);
2. exchanges the authorization code for a token using the client secret;
3. reads the user's identity from the Keycloak `userinfo` endpoint;
4. finds the matching local account (by login, then by email), or creates it if auto-provisioning is enabled;
5. adds the local roles matching the Keycloak realm roles (if role synchronization is enabled);
6. opens the Mercator session and writes an audit log entry.

To connect Mercator to Microsoft Entra ID through Keycloak, see [ENTRA_ID.fr.md](ENTRA_ID.fr.md).

## Configuration

### Keycloak

In the realm used by Mercator:

1. **Clients → Create client**

   | Field                 | Value                                               |
   |-----------------------|-----------------------------------------------------|
   | Client type           | OpenID Connect                                      |
   | Client ID             | `mercator`                                          |
   | Client authentication | ON (confidential client)                            |
   | Authentication flow   | Standard flow                                       |
   | Valid redirect URIs   | `https://mercator.example.com/login/keycloak/callback` |
   | Web origins           | `https://mercator.example.com`                      |

   Copy the secret from the **Credentials** tab.

2. **Realm roles** (only if role synchronization is used): create roles whose names are **strictly identical** to
   the Mercator role titles (**Administration → Roles**), case included, e.g. `Admin`, `User`, `Auditor`,
   `Cartographer`.

3. **Client scopes → roles → Mappers → realm roles**: enable **Add to userinfo**. Mercator reads roles from the
   `userinfo` endpoint, where Keycloak does not include them by default. Without this setting, login works but no
   role is received.

### Mercator

In the `.env` file:

```env
KEYCLOAK_ENABLED=true
KEYCLOAK_CLIENT_ID=mercator
KEYCLOAK_CLIENT_SECRET="<Keycloak client secret>"
KEYCLOAK_REDIRECT_URI=https://mercator.example.com/login/keycloak/callback
KEYCLOAK_BASE_URL=https://keycloak.example.com
KEYCLOAK_REALM=mercator
KEYCLOAK_BTN_LABEL="SSO login"
KEYCLOAK_FALLBACK_LOCAL=true
KEYCLOAK_AUTO_PROVISION=false
KEYCLOAK_AUTO_PROVISION_ROLE=User
KEYCLOAK_SYNC_ROLES=false
```

| Variable                       | Default    | Description                                                              |
|--------------------------------|------------|--------------------------------------------------------------------------|
| `KEYCLOAK_ENABLED`             | `false`    | Enables SSO and shows the button on the login page                       |
| `KEYCLOAK_CLIENT_ID`           |            | Keycloak client ID                                                       |
| `KEYCLOAK_CLIENT_SECRET`       |            | Keycloak client secret                                                   |
| `KEYCLOAK_REDIRECT_URI`        |            | Callback URL, identical to the one declared in Keycloak                  |
| `KEYCLOAK_BASE_URL`            |            | Keycloak root URL, **without** `/realms/...`                             |
| `KEYCLOAK_REALM`               |            | Realm name                                                               |
| `KEYCLOAK_BTN_LABEL`           | `Keycloak` | Login button label                                                       |
| `KEYCLOAK_FALLBACK_LOCAL`      | `false`    | Message shown on SSO failure (see below)                                 |
| `KEYCLOAK_AUTO_PROVISION`      | `false`    | Creates the Mercator account on first SSO login                          |
| `KEYCLOAK_AUTO_PROVISION_ROLE` | `User`     | Role given to auto-provisioned accounts                                  |
| `KEYCLOAK_SYNC_ROLES`          | `false`    | Adds the Mercator roles matching the Keycloak realm roles                |

Then reload the configuration:

```bash
php artisan config:clear
```

With Docker, restart the container after changing environment variables.

> The former `KEYCLOAK=enable` variable is no longer used: only `KEYCLOAK_ENABLED` matters.

## Features

### 1. Auto-Provisioning (`KEYCLOAK_AUTO_PROVISION`)

Automatically creates a local user upon first successful SSO login.

```env
KEYCLOAK_AUTO_PROVISION=true
KEYCLOAK_AUTO_PROVISION_ROLE=User
```

**Behavior:**

- ✅ **true**: user created with the Keycloak `preferred_username` as login, the Keycloak name and email, the
  default role and a random unusable password
- ❌ **false**: only existing users can log in via SSO

If `KEYCLOAK_AUTO_PROVISION_ROLE` does not match an existing Mercator role, the account is created without a role.

### 2. Local Fallback (`KEYCLOAK_FALLBACK_LOCAL`)

Determines how an SSO failure is reported to the user.

```env
KEYCLOAK_FALLBACK_LOCAL=true
```

**Situations concerned:**

1. Keycloak communication failure, invalid client secret, invalid or expired code
2. User not found locally (if `KEYCLOAK_AUTO_PROVISION=false`)

**Behavior:** in all cases, the user is redirected to `/login`.

- ✅ **true**: an information message invites the user to sign in with local credentials
- ❌ **false**: an error message is shown

> This option does **not** disable the local login form: it remains available regardless of its value. It only
> changes the message displayed after an SSO failure.

### 3. Role Synchronization (`KEYCLOAK_SYNC_ROLES`)

Adds to the user the local roles matching their Keycloak realm roles.

```env
KEYCLOAK_SYNC_ROLES=true
```

**Mechanism:**

- Compares `realm_access.roles` received from `userinfo` with the local `Role::title` (case-sensitive)
- Keycloak roles without a matching local role (e.g. `offline_access`, `default-roles-<realm>`) are ignored
- Uses `syncWithoutDetaching()`: roles are only **added**, never removed. A role withdrawn in Keycloak stays in
  Mercator until it is removed manually.

## Decision Matrix

| Scenario                  | `AUTO_PROVISION` | `FALLBACK_LOCAL` | Result                                                                 |
|---------------------------|------------------|------------------|------------------------------------------------------------------------|
| SSO OK, existing user     | ✓/✗              | ✓/✗              | ✅ SSO login successful                                                 |
| SSO OK, non-existing user | ✓                | ✓/✗              | ✅ Auto-provision + login                                               |
| SSO OK, non-existing user | ✗                | ✓                | ⚠️ Back to `/login`: `User "…" not found. Please use local credentials.` |
| SSO OK, non-existing user | ✗                | ✗                | ❌ Back to `/login`: `User "…" is not a valid Mercator user.`            |
| SSO fails                 | ✓/✗              | ✓                | ⚠️ Back to `/login`: `SSO authentication failed. Please use local credentials.` |
| SSO fails                 | ✓/✗              | ✗                | ❌ Back to `/login`: `Authentication failed.`                            |

## LDAP vs Keycloak Comparison

### Similarities

| Feature           | LDAP                         | Keycloak                         |
|-------------------|------------------------------|----------------------------------|
| Auto-provisioning | ✅ `LDAP_AUTO_PROVISION`      | ✅ `KEYCLOAK_AUTO_PROVISION`      |
| Local fallback    | ✅ `LDAP_FALLBACK_LOCAL`      | ✅ `KEYCLOAK_FALLBACK_LOCAL`      |
| Default role      | ✅ `LDAP_AUTO_PROVISION_ROLE` | ✅ `KEYCLOAK_AUTO_PROVISION_ROLE` |
| Audit logs        | ✅                            | ✅                                |

### Differences

| Aspect             | LDAP                                                       | Keycloak                                      |
|--------------------|------------------------------------------------------------|-----------------------------------------------|
| **Flow**           | Direct bind with credentials                               | OAuth2 / OpenID Connect redirect flow         |
| **Fallback**       | Same request: local password checked if LDAP fails         | Separate redirect: message on the login page  |
| **Access control** | `LDAP_GROUP` restricts login to members of a group         | Keycloak side (client, identity provider)     |
| **Role sync**      | None (default role at provisioning only)                   | Via `realm_access.roles`                      |
| **User search**    | Multiple attributes (`uid`, `sAMAccountName`)              | Login, then email                             |

## Recommended Use Cases

### Strict Configuration (Production)

```env
KEYCLOAK_ENABLED=true
KEYCLOAK_AUTO_PROVISION=false
KEYCLOAK_FALLBACK_LOCAL=false
KEYCLOAK_SYNC_ROLES=true
```

- ✅ Full control over users: only accounts created in Mercator can log in
- ❌ Requires manual pre-provisioning

### Permissive Configuration (Dev/Test)

```env
KEYCLOAK_ENABLED=true
KEYCLOAK_AUTO_PROVISION=true
KEYCLOAK_FALLBACK_LOCAL=true
KEYCLOAK_SYNC_ROLES=true
```

- ✅ Automatic onboarding
- ⚠️ Any Keycloak user of the realm allowed on the client gets a Mercator account

### Hybrid Configuration (Recommended)

```env
KEYCLOAK_ENABLED=true
KEYCLOAK_AUTO_PROVISION=true
KEYCLOAK_FALLBACK_LOCAL=true
KEYCLOAK_SYNC_ROLES=true
KEYCLOAK_AUTO_PROVISION_ROLE=User
```

- ✅ Auto-provisioning with a limited role
- ✅ Explicit message inviting local login in case of SSO issue
- ✅ Roles managed in Keycloak

## Logging and Debugging

The following events are logged in `storage/logs/laravel.log`:

```php
// Keycloak failure (the error contains the Keycloak response, e.g. 401 unauthorized_client)
Log::warning('Keycloak authentication failed', ['error' => ...]);

// User not found
Log::info('Keycloak user not found locally and auto-provision disabled', ['login' => ...]);

// Auto-provision successful
Log::info('Keycloak user auto-provisioned', ['user_id' => ..., 'login' => ...]);

// Login successful
Log::info('Keycloak login successful', ['user_id' => ..., 'login' => ...]);
```

Audit logs (**Administration → Audit log**) record:

- Action: `SSO Login (Keycloak)`
- User agent, IP, URL
- Keycloak roles received (`keycloak_roles`)

## Reference Configuration

Keycloak settings in `config/services.php`:

```php
'keycloak' => [
    'enabled' => env('KEYCLOAK_ENABLED', false),
    'client_id' => env('KEYCLOAK_CLIENT_ID'),
    'client_secret' => env('KEYCLOAK_CLIENT_SECRET'),
    'redirect' => env('KEYCLOAK_REDIRECT_URI'),
    'base_url' => env('KEYCLOAK_BASE_URL'),
    'realms' => env('KEYCLOAK_REALM'),
    'btn_label' => env('KEYCLOAK_BTN_LABEL', 'Keycloak'),
    'fallback_local' => env('KEYCLOAK_FALLBACK_LOCAL', false),
    'auto_provision' => env('KEYCLOAK_AUTO_PROVISION', false),
    'auto_provision_role' => env('KEYCLOAK_AUTO_PROVISION_ROLE', 'User'),
    'sync_roles' => env('KEYCLOAK_SYNC_ROLES', false),
],
```

The `realms` key (plural) is the one expected by the `socialiteproviders/keycloak` driver.

## Tests

### Test the SSO failure message

1. Set an invalid `KEYCLOAK_CLIENT_SECRET` and `KEYCLOAK_FALLBACK_LOCAL=true`
2. Log in via SSO → back to `/login` with `SSO authentication failed. Please use local credentials.`
3. `storage/logs/laravel.log` contains `Keycloak authentication failed`

### Test auto-provisioning

1. Configuration:
   ```env
   KEYCLOAK_ENABLED=true
   KEYCLOAK_AUTO_PROVISION=true
   KEYCLOAK_AUTO_PROVISION_ROLE=User
   ```

2. Log in with a Keycloak user that doesn't exist locally
3. Verify in **Administration → Users** that the account exists with the `User` role

### Test role sync

1. In Keycloak, assign the `Cartographer` realm role to a user
2. Check that **Add to userinfo** is enabled on the `realm roles` mapper
3. Log in via SSO
4. Verify in **Administration → Users** that the user has the `Cartographer` role, and in the audit log that
   `keycloak_roles` contains `Cartographer`

## Troubleshooting

| Symptom                                          | Probable cause                                                             | Solution                                                                    |
|--------------------------------------------------|----------------------------------------------------------------------------|-----------------------------------------------------------------------------|
| The SSO button does not appear                   | `KEYCLOAK_ENABLED` missing or not `true`, cached configuration             | Check the variable, run `php artisan config:clear` (or restart the container) |
| Keycloak shows `Invalid parameter: redirect_uri` | `KEYCLOAK_REDIRECT_URI` not in the client *Valid redirect URIs*            | Align both values (scheme, host, path)                                      |
| Keycloak returns `Realm does not exist`          | Wrong `KEYCLOAK_REALM`                                                     | Fix the `.env`                                                              |
| Keycloak returns `HTTP 404 Not Found`            | `KEYCLOAK_BASE_URL` containing `/realms/...`                               | Keep only the Keycloak root URL                                             |
| Back to login with an authentication error       | Wrong client secret, Keycloak unreachable from Mercator, clock skew        | Check `storage/logs/laravel.log` (`Keycloak authentication failed`)         |
| Login OK but no role assigned                    | Roles missing from `userinfo`, or role names differ                         | Enable *Add to userinfo* and check role name case                           |
| A second account is created for an existing user | Keycloak `preferred_username` and email differ from the Mercator account   | Align the Mercator login with the Keycloak username                         |

## Security

### Recommendations

1. **Backup account**: keep at least one local Mercator administrator account, with a strong password, to
   intervene if Keycloak is unavailable.
2. **Passwords**: auto-provisioned users receive a random unusable password; they can only log in via SSO
   (unless an administrator sets a password).
3. **Roles**: synchronization only adds roles. To revoke access, remove the role in Mercator, or disable the user
   in Keycloak to block any SSO login.
4. **Audit**: all SSO logins are audited with the Keycloak roles received.
5. **Logout**: logging out of Mercator closes the Mercator session but not the Keycloak session. On a shared
   computer, the user must also log out of Keycloak.
6. **Secrets**: never commit the `.env` file.

## Support

For questions or improvements, create an issue on the Mercator GitHub repository.
