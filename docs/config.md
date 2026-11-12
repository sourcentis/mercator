# Configuration

This document describes all configuration options available in Mercator, including LDAP integration, Active Directory nested group support, mail settings, caching, and optional features.

Mercator relies on environment variables (`.env`) to configure core features such as authentication, LDAP, auto-provisioning, mail, and security.

---

## LDAP Configuration

Mercator supports both **local authentication** and **LDAP authentication**.
Local accounts always remain available for fallback if configured.

### Enable / Disable LDAP Authentication

```
LDAP_ENABLED=true
```

### Allow local login when LDAP authentication fails

```
LDAP_FALLBACK_LOCAL=true
```

Default: `true` (applies when the variable is missing from `.env`).

When enabled, Mercator tries the **local password** stored in its database whenever LDAP authentication fails, **whatever the reason**:

* LDAP server unreachable, service account bind failure, TLS error
* user not found in the search base, or not a member of `LDAP_GROUP`
* several LDAP entries match the identifier
* **wrong LDAP password** (the directory refused the bind)

A valid local password is therefore always an alternative way to log in, even when the directory rejects the password. Set `LDAP_FALLBACK_LOCAL=false` if LDAP accounts must only authenticate against the directory (local-only accounts such as `admin` can then no longer log in while LDAP is enabled).

The only case where the local password is **not** tried: the directory accepts the password but no Mercator user has this `login` and auto-provisioning is disabled. The login is then refused.

#### Login flow

1. `LDAP_ENABLED=false` → local authentication only.
2. LDAP search for the identifier, then bind with the user's DN and password.
3. LDAP success → the Mercator user with the same `login` is logged in (created if `LDAP_AUTO_PROVISION=true`, refused otherwise).
4. LDAP failure → local authentication if `LDAP_FALLBACK_LOCAL=true`, refused otherwise.

### Automatically create Mercator users from LDAP

If the LDAP user exists but no matching Mercator user is found, Mercator can auto-create the corresponding local account.
The match is made **only** on the Mercator `login` field, which must equal the identifier typed on the login page.

```
LDAP_AUTO_PROVISION=true
```

The local account will be created with the following role:

```
LDAP_AUTO_PROVISION_ROLE=user
```

The value must match the role title exactly (case-sensitive). If the role does not exist, the user is created without a role and a warning is logged.
Auto-provisioned accounts get a random local password: they cannot use the local fallback.

---

### LDAP Connection Settings

```
LDAP_HOST=ldap.example.com
LDAP_USERNAME="CN=ldap-reader,OU=Service Accounts,DC=example,DC=com"
LDAP_PASSWORD="secret"
LDAP_PORT=389
LDAP_BASE_DN="DC=example,DC=com"
LDAP_TIMEOUT=5
LDAP_SSL=false
LDAP_TLS=false
```

These values are passed directly to Laravel's LDAPRecord connection layer.

Passwords are checked against the single server set in `LDAP_HOST`. With Active Directory, if this domain controller has not yet received a password change (replication delay or failure), it only accepts the user's **previous** password.

---

### LDAP User Search Base

Define where users should be searched:

```
LDAP_USERS_BASE_DN="OU=Users,DC=example,DC=com"
```

If empty, Mercator searches from `LDAP_BASE_DN`.

---

### LDAP Login Attributes

Defines which LDAP attributes can be used as a login identifier:

```
LDAP_LOGIN_ATTRIBUTES=sAMAccountName,uid,mail
```

Default: `uid,cn,mail,sAMAccountName,userPrincipalName`.

Mercator will try these attributes with an OR filter. Exactly **one** LDAP entry must match: if several entries match (for example a user and a contact sharing the same `mail` or `cn`), the login is refused. Keep this list as short as possible (e.g. `sAMAccountName` on Active Directory).

---

### LDAP Group Restriction

You can restrict access to Mercator to members of a specific LDAP group.

```
LDAP_GROUP="CN=Mercator-Users,OU=Groups,DC=example,DC=com"
```

If empty, all LDAP-authenticated users may log in.

---

## Nested Group Support (Active Directory Only)

Mercator can check recursive (nested) group membership when using **Microsoft Active Directory**.

This is disabled by default.

### Enable nested group lookups

```
LDAP_NESTED_GROUPS=true
```

### How it works

When enabled, Mercator uses the AD-specific matching rule:

```
1.2.840.113556.1.4.1941   (LDAP_MATCHING_RULE_IN_CHAIN)
```

Example LDAP filter generated:

```
(memberOf:1.2.840.113556.1.4.1941:=CN=Mercator-Users,OU=Groups,DC=example,DC=com)
```

This allows recognition of:

* Direct membership
* Indirect membership
* Deeply nested groups (multi-level)

### Important limitations

* **Supported only on Microsoft Active Directory**
* Will **not** work on OpenLDAP or other LDAP servers
* If enabled on non-AD systems, LDAP authentication will fail

---

## Email Configuration

Mercator sends notifications, password resets (for local accounts), and system emails.

```
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=mailer@example.com
MAIL_PASSWORD=secret
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="Mercator"
```

---

## Application-Level Settings

### Application Name

This name is displayed in the top left corner of each page of the application.

```
APP_NAME=Mercator
```

### Mercator Instance Environment

Used to specify the type of the Mercator instance: Production, Development, Integration, Pre-production, Prototype, Mockup…

```
APP_ENV=Production
```

📢 *Note: `APP_ENV=Production` is mandatory to allow HTTPS to work.*

### API Rate Limit

Limits API requests to protect server resources.
Format: `API_RATE_LIMIT` requests per `API_RATE_LIMIT_DECAY` minute(s).

```
    60,1       = 60 req/min    (default - normal usage)
    120,1      = 120 req/min   (development/testing)
    1000,60    = 1000 req/hour (public API)
    10000,1440 = 10000 req/day (third-party integrations)
```

Returns HTTP 429 (Too Many Requests) when exceeded.

```
API_RATE_LIMIT=60
API_RATE_LIMIT_DECAY=1
```

### Application URL

```
APP_URL=https://mercator.example.com
```

Used in links, notifications, export URLs, etc.

### Debug Mode

```
APP_DEBUG=false
```

When enabled, errors will be shown on screen. **Do not enable in production.**

### Session Lifetime

```
SESSION_LIFETIME=120
```

### Content Security Policy

```
CSP_MODE=enforce
```

Mercator sends a Content Security Policy (CSP) header that only allows the application's own scripts, which carry a nonce renewed on every request. A script injected into a page (XSS attack) is therefore not executed by the browser.

| Value | Effect |
|-------|--------|
| `enforce` (default) | The policy is applied (`Content-Security-Policy`): anything not allowed is blocked |
| `report-only` | The policy is only monitored (`Content-Security-Policy-Report-Only`): nothing is blocked, violations are logged to `storage/logs/security-YYYY-MM-DD.log` |
| `off` | No CSP header |

Any other value disables the CSP, like `off`. The `X-Content-Type-Options`, `X-Frame-Options` and `Referrer-Policy` headers are sent in every case.

If a page stops working properly (a button does nothing, a chart is missing), temporarily switch to `report-only`: the application works again immediately and the `security` log shows the blocked resource, to be reported for a fix.

The active mode is shown on the [information page](#information-page).

---

## Logging

Mercator uses Laravel's logging system. Application logs are written to:

```
storage/logs/laravel.log
```

All levels (including `debug`) are already written to this file: there is no level to raise.

### Login troubleshooting

Every login attempt is traced in `laravel.log` with messages prefixed by `[auth]`, the identifier typed and the client IP (never the password):

| Message | Meaning |
|---------|---------|
| `Login succeeded.` | Login accepted (roles and number of permissions) |
| `Login succeeded but user has no role.` | Login accepted, but every page will return 403 |
| `LDAP user not found (or not member of LDAP_GROUP).` | No entry found: check search base, filter and group |
| `LDAP identifier collision: multiple entries match.` | Several entries match: the DNs are listed |
| `LDAP bind refused for user.` | The directory refused the password (see `ad_reason` and `host`) |
| `LDAP service bind / connection failed.` | Server unreachable, TLS error or service account (`LDAP_USERNAME`) refused |
| `LDAP OK but no local user with this login.` | Directory OK but no Mercator user with this `login` |
| `Login refused: local authentication failed.` | Local fallback failed (`unknown login` or `wrong local password`) |
| `Login locked out: too many attempts.` | Too many attempts for this identifier from this IP |

For Active Directory, `ad_reason` decodes the sub-code of the diagnostic message: `52e` invalid credentials, `525` user not found, `530`/`531` logon not permitted (time/workstation), `532` password expired, `533` account disabled, `701` account expired, `773` user must reset password, `775` account locked out.

### LDAPRecord logging

To also trace every LDAP operation (search, bind):

```
LDAP_LOGGING=true
```

Logs will appear in:

```
storage/logs/ldap.log
```

If no file appears, ensure directory permissions are correct.

📢 *Note: if the configuration is cached (`php artisan config:cache`), changes to `.env` only apply after `php artisan config:clear` (or a new `config:cache`).*

## Information page

The information page gathers the instance configuration and the situation of the logged-in user. It is available to every user via:

> **Help → Information** (`/admin/doc/info`)

It is used to check that a `.env` setting has been taken into account, to understand why a user cannot see an object or lacks a right, and to pass this information on to support.

### Mercator

| Row | Content |
|-----|---------|
| Version | Mercator version (`version.txt`) |
| Environment | Value of `APP_ENV` |
| Timezone / Language | Default timezone and language of the application |
| Date / time | Server time, useful to check the timezone |
| Database / Database server version | Driver in use and database server version |
| PHP / Laravel | PHP and framework versions |
| Authentication methods | `Local`, `LDAP` and/or `Keycloak`, depending on the configuration |
| Drivers | Cache, queue and mail drivers |
| PHP memory | Memory used by the request, `memory_limit` and available memory |
| Content Security Policy (CSP_MODE) | Active CSP mode (see [Content Security Policy](#content-security-policy)) |

The Content Security Policy row shows a colored badge:

| Badge | Meaning |
|-------|---------|
| 🟢 `enforce` | Policy enforced |
| 🟠 `report-only` | Monitoring: violations logged, nothing blocked |
| 🔴 `off` or any other value | CSP disabled |

A mistyped value in `.env` (for example `CSP_MODE=enforced`) shows up in red: it does disable the CSP.

### User

Login, name, email address, language and granularity of the logged-in user, along with their roles and whether they are a [cartographer](cartographers.md) (with the object types assigned to them). When [perimeters](perimeters.md) are enabled, the card also shows their perimeters and the active perimeter.

### Perimeters

Only shown when perimeters are enabled. For each of the user's perimeters, the table gives their roles in that perimeter, the number of objects they can see there and, for each right (Read, Create, Edit, Delete), the number of object types concerned. The active perimeter's row is highlighted.

### Mapped objects

Number of objects visible from the active perimeter, by cartography view. The **Rights by object type** button expands the details: for each object type, the number of visible objects and the Read / Create / Edit / Delete rights, per perimeter. An administrator has all rights in every perimeter.

### Copy for helpdesk

The **Copy for helpdesk** button puts a text summary of the page (configuration, user, perimeters, number of visible objects) on the clipboard, ready to paste into a support request.

📢 *Note: this summary contains the user's login and email address. Review it before sending it outside the organization.*

📢 *Note: the page shows the configuration actually loaded. If the configuration is cached, a `.env` change only appears after `php artisan config:clear`.*

---

## File Export / Import

Mercator supports Excel and PDF export via:

* `maatwebsite/excel`
* `phpoffice/phpword`

Ensure `storage/` and `bootstrap/cache/` are writable.

---

## Docker Configuration

### Override environment variables in `docker-compose.yml`

```yaml
environment:
  - LDAP_ENABLED=true
  - LDAP_HOST=ad.example.com
  - LDAP_GROUP=CN=Mercator-Users,OU=Groups,DC=example,DC=com
  - LDAP_NESTED_GROUPS=true
```

### Volumes required

```yaml
volumes:
  - ./storage:/var/www/mercator/storage
  - ./bootstrap/cache:/var/www/mercator/bootstrap/cache
```

---

## Tips for Production Deployment

* Disable `APP_DEBUG`
* Enable HTTPS
* Keep the Content Security Policy enforced (`CSP_MODE=enforce`)
* Use a reverse proxy (Traefik, Nginx)
* Configure automatic backups of the database
* Protect `.env` and `storage/` with proper permissions
* Use LDAP nested groups only if you're on **Active Directory**

---

## Summary Table

| Feature | Variable | Default | Notes |
|---------|----------|---------|-------|
| Enable LDAP | `LDAP_ENABLED` | false | Activates LDAP login |
| Local fallback | `LDAP_FALLBACK_LOCAL` | true | Tries the local password whenever LDAP fails, including a wrong LDAP password |
| Auto-provision | `LDAP_AUTO_PROVISION` | false | Creates user in DB on first LDAP login |
| Auto-provision role | `LDAP_AUTO_PROVISION_ROLE` | null | Role assigned to newly created users |
| LDAP Server | `LDAP_HOST` | ldap.example.com | For connecting to the LDAP server |
| LDAP User | `LDAP_USERNAME` | CN=ldap-reader,… | For connecting to the LDAP server |
| LDAP Password | `LDAP_PASSWORD` | secret | For connecting to the LDAP server |
| LDAP Server Port | `LDAP_PORT` | 389 | For connecting to the LDAP server |
| SSL Encryption | `LDAP_SSL` | false | For connecting to the LDAP server |
| TLS Encryption | `LDAP_TLS` | false | For connecting to the LDAP server |
| LDAP base DN | `LDAP_BASE_DN` | dc=local,dc=com | Default search base |
| LDAP timeout | `LDAP_TIMEOUT` | 5 | Connection timeout in seconds |
| Nested groups | `LDAP_NESTED_GROUPS` | false | AD only |
| Required group | `LDAP_GROUP` | "" | Restricts login |
| LDAP user base | `LDAP_USERS_BASE_DN` | "" | Search base |
| Login attributes | `LDAP_LOGIN_ATTRIBUTES` | `uid,cn,mail,sAMAccountName,userPrincipalName` | CSV list |
| LDAP logging | `LDAP_LOGGING` | false | Writes to `ldap.log` |
| API call limit | `API_RATE_LIMIT` | 60 | Number of API requests allowed within the decay interval |
| API call interval | `API_RATE_LIMIT_DECAY` | 1 | Duration in minutes for the rate limit window |
| Session duration | `SESSION_LIFETIME` | 120 | Session duration in minutes |
| Debug mode | `APP_DEBUG` | false | Enables or disables debug mode |
| Application URL | `APP_URL` | https://mercator.example.com | Base URL used in links and exports |
| Content Security Policy | `CSP_MODE` | enforce | `enforce`, `report-only` or `off` |
| Instance environment | `APP_ENV` | Production | Defines the environment of this Mercator instance |
| Application name | `APP_NAME` | Mercator | Displayed name, useful when running multiple instances |
| Mailer type | `MAIL_MAILER` | smtp | Mail server connection |
| Mail server | `MAIL_HOST` | smtp.example.com | Mail server connection |
| Mail server port | `MAIL_PORT` | 587 | Mail server connection |
| Mail address | `MAIL_USERNAME` | mailer@example.com | Mail server connection |
| Mail password | `MAIL_PASSWORD` | secret | Mail server connection |
| Encryption type | `MAIL_ENCRYPTION` | tls | Mail server connection |
| Sender address | `MAIL_FROM_ADDRESS` | noreply@example.com | Mail server connection |
| Sender name | `MAIL_FROM_NAME` | Mercator | Mail server connection |