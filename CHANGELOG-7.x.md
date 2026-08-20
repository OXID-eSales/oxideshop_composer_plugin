# Change Log for OXID eSales Composer Plugin

## v7.6.0 - Unreleased

### Added
- Copy the shop's `.env.dist` file to the project root when the shop is installed as a Composer requirement.

## v7.4.0 - 2026-04-08

### Added
- A way to configure extra parameters to answer the update overwrite question. [PR-31](https://github.com/OXID-eSales/oxideshop_composer_plugin/pull/31)
- PHPUnit v12.5 support

### Removed
- PHPUnit v11 support

## v7.3.0 - 2025-04-08

### Added
- PHPUnit v11 support

### Removed
- PHPUnit v10 support

## v7.2.0 - 2024-03-14

### Added
- PHPUnit v10 support

### Removed
- PHPUnit v9 support

### Changed
- License update

## v7.1.1 - 2023-11-16

### Changed
- License update

## v7.1.0 - 2023-04-19

### Fixed
- Filter alias packages to avoid duplicated package installation

### Removed
- Dependency to webmozart/path-util

## v7.0.1 - 2022-11-23

### Fixed
- Install/update logic disregards package dependency weight

## v7.0.0 - 2022-10-06

### Removed
- Support for Composer v1

### Fixed
- Fix failing unit tests
