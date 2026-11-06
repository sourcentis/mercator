# Changelog

All notable changes to Mercator are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

### Added

- Author tracking on every audited object: new `created_by`, `updated_by` and
  `deleted_by` columns (references to `users.id`, set to `NULL` when the user is
  purged). They are filled automatically on creation, update and soft delete.
  A `NULL` value means the change was made by the system (console command,
  synchronisation, job).
- The object footer shows who created and who last modified the object, for
  users holding the `audit_log_access` permission.

### Changed

- Bulk relation updates (children buildings, application blocks, gateways,
  VLANs, etc.) now set `updated_by`.
- API `mass-destroy` endpoints now delete objects one by one: the deletions are
  audited and `deleted_by` is recorded.
- The Excel export no longer includes the `created_by`, `updated_by` and
  `deleted_by` columns.

### Upgrade

The migration fills the author columns of existing objects from the audit
history (first creator, last editor, author of the deletion). Objects without
audit history keep empty author columns until their next modification.
