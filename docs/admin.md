# Administration

The configuration parameters are accessible in Mercator via :

> **Admin → Configuration → Parameters** (`/admin/config/parameters`)

Unlike environment variables, these settings are stored in the database (defaults are in **config/mercator.php**) and can be modified at runtime by an administrator, without restarting the application or editing `.env`.

The parameters page is organised into nine tabs: Parameters, Perimeters, Certificates, Cleanup, Vulnerabilities, Reminders, Notifications, Documents and Monarc.

---

## Parameters

This tab groups application-level behavioural options.

[<img src="/mercator/images/config-general.png" width="700">](images/config-general.png)

### Applications

| Parameter | Description | Default |
|-----------|-------------|---------|
| **Documents attached to applications** | Allows local files (documents) to be uploaded and associated with applications. | `inactive` |

### Logical Infrastructure

| Parameter | Description | Default |
|-----------|-------------|---------|
| **Authenticity** | Adds the "Authenticity" security criterion to the existing Confidentiality, Integrity, Availability and Traceability criteria (DORA requirement). | `inactive` |

### Cartography

| Parameter | Description | Default |
|-----------|-------------|---------|
| **Maximum number of nodes** | Beyond this number of nodes, graphs are not displayed (`0` = no limit). | 500 |

---

## Perimeters

This tab enables the partitioning of the cartography by establishment and manages the list of perimeters.

[<img src="/mercator/images/config-perimeters.png" width="700">](images/config-perimeters.png)

| Item | Description |
|------|-------------|
| **Enable perimeters** | Enables or disables perimeter management (disabled by default). As long as it is disabled, the application behaves as if a single perimeter existed. Enabling or disabling it does not change any data. |
| **Add** | Creates a new perimeter (name of 2 to 32 characters). |
| **Edit** | Renames a perimeter, including the default one. |
| **Delete** | Deletes a perimeter that contains no object and is not linked to any role. The default perimeter (`#1`) cannot be deleted. |

The full behaviour (roles, working perimeter selector, cross-perimeter flows, import) is described in the [Perimeters](perimeters.md) page.

---

## Certificates

Controls the automated monitoring and notification of TLS/SSL certificate expiry.

[<img src="/mercator/images/config-cert.png" width="700">](images/config-cert.png)

| Parameter | Description | Values |
|-----------|-------------|--------|
| **Message subject** | Subject line of the expiry alert email. | Free text |
| **Sent from** | Sender address for expiry alert emails. | Email address |
| **Recipients** | Destination address(es) for expiry alert emails. | Email address(es) |
| **Alert delay** | How many days before expiry the notification is sent. | 1, 7, 15, 30, 60 or 90 days |
| **Check frequency** | How often Mercator scans for expiring certificates. | Never / Daily / Weekly / Monthly |
| **Grouping** | Send one consolidated email for all expiring certificates, or one email per certificate. | One email / One per certificate |
| **Repeat notifications** | Send a single notification per expiry event, or repeat until the certificate is renewed or removed. | Single / Repeat |

A **Test** button is available to send a test email using the current configuration without saving.

---

## Cleanup

This tab enables the automatic purge of the database. Two purges are available; each one is enabled independently and has its own retention period.

[<img src="/mercator/images/cleanup.png" width="700">](images/cleanup.png)

| Parameter | Description | Values | Default |
|-----------|-------------|--------|---------|
| **Audit log purge** | Deletes audit log entries (history of creations, updates and deletions) older than the retention period. | Enabled / Disabled | `disabled` |
| **Audit log retention period** | Age beyond which a log entry is deleted. | 1, 3, 6, 12, 24, 36 or 60 months | 12 months |
| **Deleted objects purge** | Permanently deletes objects that have been in the trash (soft-deleted) for longer than the retention period. | Enabled / Disabled | `disabled` |
| **Deleted objects retention period** | Time after an object's deletion beyond which it is permanently deleted. | 1, 3, 6, 12, 24, 36 or 60 months | 3 months |

The cleanup runs **every night at 02:00** through the Laravel scheduler (command `mercator:db-cleanup`), so the scheduler must be running (`php artisan schedule:run` launched every minute by cron, or `schedule:work`). The tab footer shows the date and result of the last run.

The deleted objects purge:

* applies to every cartography object, **except users and roles**, which stay in the trash;
* is **irreversible**: a purged object can no longer be restored;
* also deletes the object's links to other objects (pivot tables) and its dependent data (for instance an application's events). For a document, the file is also removed from disk;
* clears the reference to the purged object in objects that still pointed to it (for instance a workstation's building);
* **skips** an object that is still a mandatory reference of another object (for instance the source entity of a relation that is not deleted). It is processed again on the following runs, once the reference is gone.

The purge itself does not create audit log entries.

The command can also be run manually; the `--dry-run` option shows what would be deleted without deleting anything:

```bash
php artisan mercator:db-cleanup --dry-run
```

---

## Vulnerabilities

Controls the automated scanning for known vulnerabilities (CVE) affecting inventory items.

[<img src="/mercator/images/config-cve.png" width="700">](images/config-cve.png)

| Parameter | Description | Values |
|-----------|-------------|--------|
| **Message subject** | Subject line of the CVE alert email. | Free text |
| **Sent from** | Sender address for CVE alert emails. | Email address |
| **Recipients** | Destination address(es) for CVE alert emails. | Email address(es) |
| **Check frequency** | How often Mercator scans for new CVEs affecting the inventory. | Never / Daily / Weekly / Monthly |
| **CPE Guesser URL** | URL of the CPE Guesser service used to infer CPE identifiers from software names. | URL |
| **CVE Provider URL** | URL of the CVE data provider. | URL |

### External services

**CVE Provider** — Mercator queries a [vulnerability-lookup](https://github.com/vulnerability-lookup/vulnerability-lookup) instance to retrieve CVEs affecting the inventory items. A public instance is available at `https://vulnerability.circl.lu`. You can also self-host the service.

**CPE Guesser** — Mercator uses [cpe-guesser](https://github.com/vulnerability-lookup/cpe-guesser) to automatically infer CPE identifiers from software names. A public instance is available at `https://cpe-guesser.cve-search.org`. You can also self-host the service.

Three **Test** buttons are available:

* **Test Mail** — sends a test email with the current configuration.
* **Test Provider** — checks connectivity to the configured CVE provider.
* **Test Guesser** — checks connectivity to the configured CPE Guesser service.

---

## Reminders

This tab configures the email reminders sent to cartographers when objects they are responsible for have not been updated for a given time.

[<img src="/mercator/images/config-reminders.png" width="700">](images/config-reminders.png)

| Parameter | Description | Default |
|-----------|-------------|---------|
| **Enable reminders** | Enables sending reminders. The other fields can only be edited while the option is enabled. | `inactive` |
| **Sender** | Sender address of the reminders. | — |
| **Carbon copy to** | Address(es) receiving a blind copy (Bcc) of every reminder. | — |
| **Reminder subject** | Subject of the email. | `[Mercator] Rappel` |
| **Reminder body** | HTML content of the email. | Provided template |
| **No update since (months)** | An object is reported if it has not been modified for this number of months. | 1 |
| **Send a reminder every (days)** | Minimum delay between two reminder campaigns. | 30 |

Variables available in the reminder body:

| Variable | Replaced by |
|----------|-------------|
| `:name` / `:user` | Cartographer's name |
| `:count` | Number of objects to update |
| `:list` | List of objects, with a link to each one |
| `:months` | Number of months without update |
| `:mercator_url` | Mercator URL |

Reminders are sent by the `mercator:remind-cartographers` command, run daily by the scheduler. The tab footer shows the date of the last sending. The **Test** button sends a test email to the blind-copy address (or, failing that, to the sender).

See also [Cartographers](cartographers.md).

---

## Notifications

This tab configures the email notifications sent to an object's cartographers when the object is modified by another user.

[<img src="/mercator/images/config-notifications.png" width="700">](images/config-notifications.png)

| Parameter | Description | Default |
|-----------|-------------|---------|
| **Enable modification notifications** | Enables sending notifications. The other fields can only be edited while the option is enabled. | `inactive` |
| **Sender** | Sender address of the notifications. | — |
| **Carbon copy to** | Address(es) receiving a blind copy (Bcc) of every notification. | — |
| **Notification subject** | Subject of the email; may contain variables. | `[Mercator] Objet :name modifié` |
| **Notification body** | HTML content of the email. | Provided template |

Variables available in the subject and body:

| Variable | Replaced by |
|----------|-------------|
| `:user` / `:email` | Name and email of the user who made the change |
| `:name` / `:id` | Name and identifier of the object |
| `:object` | Object type |
| `:object_url` | URL of the object |
| `:object_history_url` | URL of the object's history |
| `:fields` | Modified fields |
| `:date` / `:timestamp` | Date (and time) of the change |

The **Test** button sends a test email to the blind-copy address (or, failing that, to the sender).

---

## Documents

This tab is **read-only** and provides an overview of the documents stored in Mercator.

[<img src="/mercator/images/config-documents.png" width="700">](images/config-documents.png)

| Information | Description |
|-------------|-------------|
| **Document count** | Total number of documents stored in the database. |
| **Total size** | Cumulative size of all stored documents (displayed in human-readable units). |

A **Check** button triggers an integrity verification of all documents: Mercator compares each document's recorded SHA-256 hash against the file currently on disk and reports one of three statuses:

| Status | Meaning |
|--------|---------|
| `OK` | File is present and its hash matches the database record. |
| `MISSING` | File is referenced in the database but not found on disk. |
| `HASH FAILS` | File is present but its hash does not match — the file may be corrupted or tampered with. |

---

## Monarc

This tab configures the connection to a MONARC Front Office instance, used to export the cartography as a risk analysis.

[<img src="/mercator/images/config-monarc.png" width="700">](images/config-monarc.png)

| Parameter | Description |
|-----------|-------------|
| **Enable Monarc integration** | Shows the "Monarc" entry in the Tools menu and grants access to the generation screen. |
| **Monarc instance URL** | Base URL of the MONARC Front Office instance. |
| **Login** | Account used to authenticate on the Monarc API. |
| **Password** | Leave empty to keep the password already saved. |

The **Test connection** button checks the authentication. The **Reset link** block shows the currently linked analysis (ANR), the date of the last synchronisation and the number of tracked objects; it removes this local link without changing anything in Monarc.

See the [Monarc integration](monarc.md) page.

---

## Summary Table

| Tab          | Parameter | Description | Default |
|--------------|-----------|-------------|---------|
| Parameters   | Documents attached to applications | Attach files to applications | `inactive` |
| Parameters   | Authenticity | Adds the "Authenticity" security criterion (DORA) | `inactive` |
| Parameters   | Maximum number of nodes | Maximum size of displayed graphs | 500 |
| Perimeters   | Enable perimeters | Partitioning of the cartography by establishment | `inactive` |
| Certificates | Message subject | Subject of expiry alert emails | — |
| Certificates | Sent from | Sender address for certificate alerts | — |
| Certificates | Recipients | Destination address(es) for certificate alerts | — |
| Certificates | Alert delay | Days before expiry to trigger notification | 30 days |
| Certificates | Check frequency | Scan interval for expiring certificates | Never |
| Certificates | Grouping | One consolidated email or one per certificate | One email |
| Certificates | Repeat notifications | Single or repeated notifications until resolved | Single |
| Cleanup | Audit log purge | Deletes log entries older than the retention period | `disabled`, 12 months |
| Cleanup | Deleted objects purge | Permanently deletes objects deleted longer ago than the retention period | `disabled`, 3 months |
| Vulnerabilities | Message subject | Subject of CVE alert emails | — |
| Vulnerabilities | Sent from | Sender address for CVE alerts | — |
| Vulnerabilities | Recipients | Destination address(es) for CVE alerts | — |
| Vulnerabilities | Check frequency | Scan interval for new CVEs | Never |
| Vulnerabilities | CPE Guesser URL | Endpoint of the [cpe-guesser](https://github.com/vulnerability-lookup/cpe-guesser) service | — |
| Vulnerabilities | CVE Provider URL | Endpoint of the [vulnerability-lookup](https://github.com/vulnerability-lookup/vulnerability-lookup) provider | — |
| Documents    | — | Read-only: document count, total size, integrity check | — |
| Reminders | Enable reminders | Reminders to cartographers for outdated objects | `inactive` |
| Notifications | Enable modification notifications | Notify cartographers when an object is modified | `inactive` |
| Monarc | Enable Monarc integration | Export the cartography to a Monarc risk analysis | `inactive` |
