# Administration

The configuration parameters are accessible in Mercator via :

> **Admin → Configuration → Parameters** (`/admin/config/parameters`)

Unlike environment variables, these settings are stored in the database (defaults are in **config/mercator.php**) and can be modified at runtime by an administrator, without restarting the application or editing `.env`.

The parameters page is organised into tabs.

---

## Parameters

This tab groups application-level behavioural options.

### Applications

| Parameter | Description | Default |
|-----------|-------------|---------|
| **Documents attached to applications** | Allows local files (documents) to be uploaded and associated with applications. | `inactive` |

### Logical Infrastructure

| Parameter | Description | Default |
|-----------|-------------|---------|
| **Security need authentication** | When enabled, the security-need fields on logical servers require prior authentication before being displayed. | `false` |

---

## Certificates

Controls the automated monitoring and notification of TLS/SSL certificate expiry.

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

## CVE

Controls the automated scanning for known vulnerabilities (CVE) affecting inventory items.

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

## Documents

This tab is **read-only** and provides an overview of the documents stored in Mercator.

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

## Summary Table

| Tab          | Parameter | Description | Default |
|--------------|-----------|-------------|---------|
| General      | Security need authentication | Requires authentication to display security-need fields | `false` |
| Certificates | Message subject | Subject of expiry alert emails | — |
| Certificates | Sent from | Sender address for certificate alerts | — |
| Certificates | Recipients | Destination address(es) for certificate alerts | — |
| Certificates | Alert delay | Days before expiry to trigger notification | 30 days |
| Certificates | Check frequency | Scan interval for expiring certificates | Never |
| Certificates | Grouping | One consolidated email or one per certificate | One email |
| Certificates | Repeat notifications | Single or repeated notifications until resolved | Single |
| CVE          | Message subject | Subject of CVE alert emails | — |
| CVE          | Sent from | Sender address for CVE alerts | — |
| CVE          | Recipients | Destination address(es) for CVE alerts | — |
| CVE          | Check frequency | Scan interval for new CVEs | Never |
| CPE          | CPE Guesser URL | Endpoint of the [cpe-guesser](https://github.com/vulnerability-lookup/cpe-guesser) service | — |
| CVE          | CVE Provider URL | Endpoint of the [vulnerability-lookup](https://github.com/vulnerability-lookup/vulnerability-lookup) provider | — |
| Documents    | — | Read-only: document count, total size, integrity check | — |
| Cleanup | Audit log purge | Deletes log entries older than the retention period | `disabled`, 12 months |
| Cleanup | Deleted objects purge | Permanently deletes objects deleted longer ago than the retention period | `disabled`, 3 months |
