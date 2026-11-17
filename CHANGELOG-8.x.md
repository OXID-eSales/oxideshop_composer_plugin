# Change Log for OXID eSales Composer Plugin

## v8.0.0-alpha.3 - Unreleased
*Compilation release*

### Added
- Install the theme configuration of a theme package on install and update, and remove it on uninstall
- Link the `assets` directory of a theme package to the shop's `out` directory on install and update, and remove the link on uninstall
- Package type `oxideshop-admin-ui` to link the `assets` directory of the admin UI to the shop's `out` directory on install and update, and remove the link on uninstall

### Changed
- Register a component's `services.yaml` via `ProjectYamlImportServiceInterface::addImportFromFilePath()`
- Theme installer checks for `metadata.yaml` instead of `theme.php`
- Theme packages are not copied to `source/Application/views` and `source/out` anymore, the shop reads theme files from the vendor directory
  - Theme packages without a valid `metadata.yaml` are skipped with a warning
  - Updating a theme package does not ask for confirmation anymore, `update-answer-yes` and `update-answer-no` have no effect on theme packages
  - Theme assets are linked from the `assets` directory of the theme package, the `out` directory is not used anymore

### Removed
- `ThemePackageInstaller::METADATA_FILE_NAME`
- `ThemePackageInstaller::PATH_TO_THEMES`
- `ThemePackageInstaller`: `copyPackage()`, `installAssets()`, `formThemeTargetPath()`, `formThemeDirectoryName()` and `formAssetsDirectoryName()`
- `AbstractPackageInstaller::EXTRA_PARAMETER_KEY_TARGET` and `AbstractPackageInstaller::EXTRA_PARAMETER_KEY_ASSETS`
- Support for `target-directory`, `assets-directory` and `blacklist-filter` in the `extra` section of theme packages

## v8.0.0-alpha.2 - 2026-02-12
*Compilation release*

## v8.0.0-alpha.1 - 2025-02-03

### Added
- Copy the `.env.dist` file from the shop package to the project root

### Removed
- Dependency on the `Facts` component
- Functionality used for web-based shop setup
