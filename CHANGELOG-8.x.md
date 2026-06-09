# Change Log for OXID eSales Composer Plugin

## v8.0.0-alpha.3 - Unreleased
*Compilation release*

### Added
- Install the theme configuration of a theme package on install and update

### Changed
- Register a component's `services.yaml` via `ProjectYamlImportServiceInterface::addImportFromFilePath()`
- Theme installer checks for `metadata.yaml` instead of `theme.php`

### Removed
- `ThemePackageInstaller::METADATA_FILE_NAME`

## v8.0.0-alpha.2 - 2026-02-12
*Compilation release*

## v8.0.0-alpha.1 - 2025-02-03

### Added
- Copy the `.env.dist` file from the shop package to the project root

### Removed
- Dependency on the `Facts` component
- Functionality used for web-based shop setup
