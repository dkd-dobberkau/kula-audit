# Changelog

All notable changes to the `kula_audit` extension are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2026-08-10

### Fixed

- A failed vulnerability scan is no longer shown as an all-clear. The Kula API
  reports zero vulnerabilities when it cannot reach OSV; the Dashboard widget lit
  up green, the backend module printed "No known vulnerabilities found." and the
  CLI reported success. All three now surface the failure and its cause.
- `kula:audit` exits with code `1` when the audit could not be completed, so a
  broken scan fails a CI pipeline instead of passing it. Found vulnerabilities
  still exit `0` — that is a finding, not a command failure.

### Added

- Dev dependencies from `packages-dev` appear in the audit and are marked with a
  `dev` badge; the package count shows how many of them are dev-only.
- The overview and widget read `security.scan_ok` / `security.scan_error` from the
  Kula API. Reports from an older API, or cached before this update, are treated
  as scanned, so upgrading does not retroactively flag old results as failed.

## [1.0.0] - 2026-03-02

### Added

- Initial release: Dashboard widget, backend module and `kula:audit` CLI command
  for upgrade readiness, vulnerability scanning and SBOM via the Kula API.
