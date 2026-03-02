# EXT:kula_audit — TYPO3 Extension Audit

Audit your TYPO3 installation directly from the backend: upgrade readiness, known vulnerabilities, and CycloneDX SBOM — powered by the [Kula](https://github.com/dkd/kula-graph) API.

## Features

- **CLI Command** — `vendor/bin/typo3 kula:audit` reads `composer.lock` and checks against the Kula API
- **Dashboard Widget** — Traffic light overview (green/yellow/red) with package stats
- **Backend Module** — Full detail tables under Admin Tools with upgrade readiness and vulnerability data

## Requirements

- TYPO3 12.4 or 13.x
- PHP 8.1+
- Access to a running Kula instance (default: `https://kula.dkd.de/api/audit`)

## Installation

```bash
composer require dkd/kula-audit
```

Then activate the extension in the TYPO3 Extension Manager or via CLI:

```bash
vendor/bin/typo3 extension:activate kula_audit
```

## Configuration

Set the API URL and target TYPO3 version in `config/system/settings.php` or via the Settings module:

```php
$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['kula_audit'] = [
    'apiUrl' => 'https://kula.dkd.de/api/audit',
    'targetMajor' => 13,
];
```

## Usage

### CLI

```bash
# Run audit (uses 24h cache)
vendor/bin/typo3 kula:audit

# Force fresh check
vendor/bin/typo3 kula:audit --force

# JSON output (for CI/CD pipelines)
vendor/bin/typo3 kula:audit --json
```

### Scheduler

The `kula:audit` command is schedulable via the TYPO3 Scheduler. Recommended frequency: once daily.

### Dashboard

Add the "Extension Audit" widget to your TYPO3 Dashboard for a quick overview.

### Backend Module

Find the full audit report under **Admin Tools > Kula Audit**.

## License

GPL-2.0-or-later
