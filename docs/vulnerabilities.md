# Vulnerabilities

Mercator includes a vulnerability detection engine based on [Vulnerability-Lookup](https://vulnerability.circl.lu/) and the [NVD](https://nvd.nist.gov/) (National Vulnerability Database). Once the `vendor` and `product` fields are filled in for an asset, Mercator can identify known CVEs associated with that component and display them alongside their CVSS severity score.

This page covers the architecture of the feature, its configuration, initialization, and day-to-day usage.

## Concepts

Three standards underpin the detection system:

| Term | Meaning | Example |
|------|---------|---------|
| **CPE** | *Common Platform Enumeration* — a standardized dictionary of known software, operating systems and hardware | `cpe:2.3:a:apache:log4j:2.14.1:*:*:*:*:*:*:*` |
| **CVE** | *Common Vulnerabilities and Exposures* — the public list of known security flaws | `CVE-2021-44228` (Log4Shell) |
| **NVD** | *National Vulnerability Database* — the US database linking CPE ↔ CVE | The data source queried by Mercator |

The overall workflow is as follows:

```
Mercator inventory
  └─> vendor + product + version filled in for an asset
        └─> Mercator looks up the matching CPE in its local database
              └─> Mercator queries the NVD for CVEs linked to that CPE
```

## Architecture

Mercator stores CPE data in three dedicated tables:

| Table | Contents |
|-------|---------|
| `cpe_vendors` | Publishers and manufacturers (e.g. `apache`, `microsoft`, `cisco`) |
| `cpe_products` | Products by vendor (e.g. `log4j`, `windows_10`) |
| `cpe_versions` | Versions of each product |

These tables are populated by the `mercator:cpe-sync` command.

### Assets supporting CPE fields

The `vendor`, `product` and `version` fields are available on the following asset types:

| View | Object | Available in UI |
|------|--------|----------------|
| Applications | Application | ✅ |
| Applications | Application module | ✅ |
| Logical infrastructure | Security appliance (logical) | ✅ |
| Physical infrastructure | Peripheral | ✅ |
| Physical infrastructure | Physical server | ⚠️ stored in DB, not shown |
| Physical infrastructure | Workstation | ⚠️ stored in DB, not shown |
| Physical infrastructure | Physical switch | ⚠️ stored in DB, not shown |
| Physical infrastructure | Physical router | ⚠️ stored in DB, not shown |
| Physical infrastructure | Wi-Fi access point | ⚠️ stored in DB, not shown |
| Physical infrastructure | Phone | ⚠️ stored in DB, not shown |
| Physical infrastructure | Storage infrastructure | ⚠️ stored in DB, not shown |

> For assets whose fields are not yet visible in the interface, they remain accessible via the REST API or through JSON import.

## Filling in vendor / product / version

Vulnerability detection depends entirely on the quality of the data entered. Without the `vendor`, `product` and `version` fields correctly filled in, no CVE search can return results.

### CPE naming conventions

Values must match exactly the identifiers used in the NVD: **lowercase**, with **underscores** replacing spaces.

| Software | `vendor` | `product` | `version` |
|----------|----------|-----------|-----------|
| Apache Log4j 2.14 | `apache` | `log4j` | `2.14.1` |
| Microsoft Windows 11 | `microsoft` | `windows_11` | `21h2` |
| Cisco IOS 15.4 | `cisco` | `ios` | `15.4` |
| SAP S/4HANA 2022 | `sap` | `s\/4hana` | `2022` |
| Ubuntu 22.04 | `canonical` | `ubuntu_linux` | `22.04` |
| Docker 24.0.5 | `docker` | `docker` | `24.0.5` |

If you are unsure of the exact identifiers, use the **Guess** function (see next section).

### Entering data in the interface

**For an Application:**  
`Applications` → `Applications` → open or create the record → fill in `Vendor`, `Product`, `Version`.

**For an Application module:**  
`Applications` → `Application modules` → same procedure.

### Import via the API (JSON)

```json
{
  "name": "Apache Guacamole",
  "technology": "apache",
  "vendor": "apache",
  "product": "guacamole",
  "version": "1.5.4"
}
```

### Search

The vulnerability search is powered by [Vulnerability-Lookup](https://vulnerability.circl.lu/), the open-source service developed by [CIRCL](https://www.circl.lu/) (Computer Incident Response Center Luxembourg) and available on [GitHub](https://github.com/vulnerability-lookup/vulnerability-lookup).

Unlike a search limited to the NVD/NIST database, Vulnerability-Lookup aggregates multiple sources: GitHub Advisory Database, PySec, and CSAF feeds from Red Hat, CISA, Cisco, Siemens and other CERTs, providing significantly broader coverage of known vulnerabilities.

**Search** performs an exact match: Mercator looks up the CPE in its local database that precisely corresponds to the `vendor` and `product` values entered, then queries Vulnerability-Lookup for the associated CVEs.

**Prerequisite:** both fields must be filled in and present in the local CPE database.

**Usage:**

1. Open an application record or any asset record with `vendor`, `product` and `version` fields
2. Click `CVE Search`
3. CVEs are displayed with their CVSS score and description

> If no results appear, verify that the CPE database is loaded and that the `vendor` and `product` values match the NVD identifiers exactly.

### CPE-Guesser

[CPE-Guesser](https://vulnerability-lookup.github.io/cpe-guesser/) is an open-source tool developed by [CIRCL](https://www.circl.lu/) (Computer Incident Response Center Luxembourg). It identifies the most probable CPEs from one or more keywords, using an inverted index built from the NVD dictionary and ranked by each CPE's frequency of appearance in known CVEs. Mercator queries CPE-Guesser to return a ranked list of candidates, without requiring the `vendor` / `product` / `version` fields to already be aligned with NVD identifiers.

**When to use Guess:**

- To identify the correct CPE to record on the asset afterwards
- The exact NVD identifiers are unknown
- CVE Search returns no results

**Usage:**

1. Open the application record or navigate to the CVE menu
2. Use the `Guess` function
3. Select the matching CPE from the suggested list
4. Copy the `vendor` / `product` / `version` values back to the asset record so that Search works in future

### Comparison

| | Search | Guess |
|--|--------|-------|
| **Prerequisite** | CPE fields filled in and present in the database | None |
| **Speed** | Instant (local database) | Slower (approximate matching) |
| **Accuracy** | Very precise when fields are correct | May return false positives |
| **Typical use** | Well-documented assets | CPE discovery, poorly documented assets |

## Reports

### CVSS score

Each CVE is presented with its **CVSS** (Common Vulnerability Scoring System) score:

| Score | Severity | Priority |
|-------|----------|----------|
| 0.0 | None | — |
| 0.1 – 3.9 | Low | Low |
| 4.0 – 6.9 | Medium | Normal |
| 7.0 – 8.9 | High | High |
| 9.0 – 10.0 | Critical | Immediate |

Detected CVEs appear in the security reports accessible via the **Reports** menu.

## Backing up CVE / CPE data

CPE data is stored **in the database only** (tables `cpe_vendors`, `cpe_products`, `cpe_versions`). There are no separate CVE/CPE files on the filesystem to back up.

Three strategies are available depending on your context:

**Option A — Skip CPE table backups**  
The tables can be regenerated from the NVD at any time. This is the lightest strategy, at the cost of a re-synchronization delay after a restore.

**Option B — Full database backup (recommended)**

```bash
mysqldump -u mercator_user -p mercator > mercator_backup_$(date +%Y%m%d).sql

# Under Docker
docker exec mercator-db mysqldump -u mercator_user -p mercator \
  > mercator_backup_$(date +%Y%m%d).sql
```

**Option C — Lightweight backup excluding CPE tables**

```bash
mysqldump -u mercator_user -p mercator \
  --ignore-table=mercator.cpe_vendors \
  --ignore-table=mercator.cpe_products \
  --ignore-table=mercator.cpe_versions \
  > mercator_backup_no_cpe_$(date +%Y%m%d).sql
```

After a restore, run `mercator:cpe-sync --now` to repopulate the CPE tables.

## Configuration

### NVD API Key

The NVD enforces rate limits depending on whether an API key is used:

| Without key | With key |
|-------------|----------|
| 5 requests / 30 s | 50 requests / 30 s |
| Initial load: 8–24 h | Initial load: 30 min – 2 h |

The key is **free**. To obtain one:

1. Go to [https://nvd.nist.gov/developers/request-an-api-key](https://nvd.nist.gov/developers/request-an-api-key)
2. Enter an e-mail address
3. Receive the key by e-mail (within a few minutes)

### Environment variables

Add the following lines to your `.env` file:

```ini
# NVD API URL (do not change)
NVD_API_URL=https://services.nvd.nist.gov/rest/json/cpes/2.0

# NVD API key — strongly recommended
NVD_API_KEY=your-key-here
```

### Docker deployment

In `docker-compose.yml`, add these variables to the `environment` section of the `app` service:

```yaml
environment:
  - NVD_API_URL=https://services.nvd.nist.gov/rest/json/cpes/2.0
  - NVD_API_KEY=your-key-here
```

Then reload the configuration:

```bash
docker-compose up -d
docker exec -it mercator-app php artisan config:clear
docker exec -it mercator-app php artisan config:cache
```

## Initialization

### Initial CPE database load

The CPE database must be synchronized before any CVE searches can be performed. The NVD contains several hundred thousand entries; the duration depends directly on whether an API key is available (see previous section).

```bash
# Standard installation (VM)
php artisan mercator:cpe-sync --now

# Docker deployment
docker exec -it mercator-app php artisan mercator:cpe-sync --now
```

### Handling interruptions

If the command is interrupted (network outage, timeout, container restart), there is no resume mechanism: the synchronization starts over from scratch.

To avoid this, run the command inside a `screen` or `tmux` session to protect it from SSH disconnections:

```bash
screen -S cpe-sync
docker exec -it mercator-app php artisan mercator:cpe-sync --now
# Press Ctrl+A then D to detach the session
```

It is also useful to monitor the Laravel logs in a separate terminal:

```bash
docker exec -it mercator-app tail -f storage/logs/laravel.log
```

> **Open issue:** The ability to synchronize incrementally, or to limit the sync to applications already present in the CMDB, is under consideration. See the [Open issues](#open-issues) section.

## Automatic updates

### Laravel scheduler

Mercator uses the Laravel task scheduler to keep the CPE database up to date. The synchronization is scheduled **daily at 03:30**, provided the system cron is active.

### Cron setup (standard VM)

```bash
sudo crontab -e
```

Add the following line:

```cron
* * * * * cd /var/www/mercator && php artisan schedule:run >> /dev/null 2>&1
```

This single entry is sufficient: Laravel internally manages the exact execution time for each scheduled task.

### Verification under Docker

To list scheduled tasks and confirm that `cpe-sync` is registered:

```bash
docker exec -it mercator-app php artisan schedule:list
```

To trigger the scheduler manually:

```bash
docker exec -it mercator-app php artisan schedule:run
```

## Verifying the CPE database {#check}

After initialization, or whenever the sync status is uncertain, two methods are available to check the database contents.

### Via MySQL / MariaDB

```sql
-- Row counts per table
SELECT 'vendors'  AS type, COUNT(*) AS count FROM cpe_vendors
UNION ALL
SELECT 'products', COUNT(*) FROM cpe_products
UNION ALL
SELECT 'versions', COUNT(*) FROM cpe_versions;
```

Under Docker:

```bash
docker exec -it mercator-db mysql -u mercator_user -p mercator \
  -e "SELECT 'vendors' AS type, COUNT(*) AS count FROM cpe_vendors
      UNION ALL SELECT 'products', COUNT(*) FROM cpe_products
      UNION ALL SELECT 'versions', COUNT(*) FROM cpe_versions
      UNION ALL SELECT 'last_sync', MAX(updated_at) FROM cpe_versions;"
```

### Via Laravel logs

```bash
# Standard installation
tail -n 100 /var/www/mercator/storage/logs/laravel.log | grep -i cpe

# Docker
docker exec -it mercator-app tail -n 100 storage/logs/laravel.log | grep -i cpe
```

> **Open issue:** The date of the last CPE sync is not yet displayed in the interface or in reports. See the [Open issues](#open-issues) section.

---

## Open issues {#open-issues}

The following points were identified during use and are being tracked. This section will be updated as the project evolves.

---

### Resume after `cpe-sync` interruption

**Question:** If `cpe-sync --now` is interrupted, is it possible to resume from where it stopped rather than starting over?

**Status:** ⏳ Pending. Approach under consideration: incremental synchronization, or limiting the sync to applications already present in the CMDB.

### CPE sync date not shown in the interface

**Observation:** There is currently no way to see from the interface whether the CPE database is loaded or when it was last updated. The current workaround is a direct SQL query (see [Verifying the CPE database](#check)).

**Status:** ⏳ Pending.  
**Proposal:** Display the last CPE sync date on the CVE search screen and in security reports.

### CPE fields not visible for certain assets

**Observation:** The `vendor`, `product` and `version` fields exist in the database for several asset types (physical servers, workstations, switches…) but are not accessible from the UI.

**Status:** ⏳ Clarification requested.  
**Proposal:** Expose these fields in the UI for all CPE-capable assets, to avoid relying on the API or JSON imports for data entry.

### "Version" column missing from the Applications list

**Observation:** The Applications list view does not display the `version` field, even though it is used by the CPE engine.

**Status:** ⏳ Suggestion submitted.  
**Proposal:** Add a `version` column to this view to make asset documentation coverage more visible.

### Search / Guess distinction not visible in the UI

**Observation:** The interface does not clearly indicate that Search requires the CPE fields to be filled in, nor does it explain the difference in behaviour compared to Guess.

**Status:** ⏳ Suggestion submitted.  
**Proposal:** Add tooltips or inline explanatory text next to the Search and Guess functions.

## Appendix A — Quick command reference

```bash
# Initial CPE database load
docker exec -it mercator-app php artisan mercator:cpe-sync --now

# List scheduled tasks
docker exec -it mercator-app php artisan schedule:list

# Manually trigger the scheduler
docker exec -it mercator-app php artisan schedule:run

# Clear configuration cache (after editing .env)
docker exec -it mercator-app php artisan config:clear
docker exec -it mercator-app php artisan config:cache

# Check CPE database status
docker exec -it mercator-db mysql -u mercator_user -p mercator \
  -e "SELECT 'vendors' AS type, COUNT(*) AS count FROM cpe_vendors
      UNION ALL SELECT 'products', COUNT(*) FROM cpe_products
      UNION ALL SELECT 'versions', COUNT(*) FROM cpe_versions
      UNION ALL SELECT 'last_sync', MAX(updated_at) FROM cpe_versions;"
```

## Appendix B — Minimal `.env` example

```ini
APP_NAME=Mercator
APP_ENV=Production
APP_KEY=base64:your_key_generated_by_artisan_key_generate
APP_DEBUG=false
APP_URL=https://mercator.yourdomain.com

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=mercator
DB_USERNAME=mercator_user
DB_PASSWORD=secure_password

# Mail — see the Configuration page for full options
MAIL_MAILER=smtp
MAIL_HOST=smtp.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=smtp_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Mercator"

# API
API_RATE_LIMIT=60
API_RATE_LIMIT_DECAY=1

# CPE / CVE
NVD_API_URL=https://services.nvd.nist.gov/rest/json/cpes/2.0
NVD_API_KEY=your_free_nvd_key
```