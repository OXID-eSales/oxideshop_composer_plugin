# Change Log for OXID eSales Composer Plugin

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

## v6.0.0 - 2021-07-06

### Added
- Improve messages readability by sharpening component names [PR-26](https://github.com/OXID-eSales/oxideshop_composer_plugin/pull/26)

### Changed
- Update symfony components to version 5
- Use bootstrap container for the modulePackageInstaller

### Removed
- Module blacklist-filter functionality
- Ask question to update module package files
- Support for module source-directory and target-directory in composer.json
    - `OxidEsales\ComposerPlugin\Installer\Package\ModulePackageInstaller::MODULES_DIRECTORY`
   
## v5.2.3 - Unreleased

### Fixed
- Install/update logic disregards package dependency weight

## v5.2.2 - 2022-06-22

### Fixed
- Fix autoloading of components during the uninstall process
  [#0007309](https://bugs.oxid-esales.com/view.php?id=7309)
  [#0007123](https://bugs.oxid-esales.com/view.php?id=7123)
  [PR-27](https://github.com/OXID-eSales/oxideshop_composer_plugin/pull/27)

## v5.2.1 - 2022-03-31

### Fixed
- Fix symfony/filesystem copy method use case

## v5.2.0 - 2021-04-12

### Changed
- Support PHP 8.0

## v5.1.1 - 2021-04-12

### Deprecated
- Module blacklist-filter functionality
- `OxidEsales\ComposerPlugin\Installer\Package\ModulePackageInstaller::MODULES_DIRECTORY`

## v5.1.0 - 2020-11-04

### Added
- Support for composer v2

## v5.0.1 - 2020-07-03

### Fixed
- Bootstrap container is used for the module installation

## v5.0.0 - 2020-04-24

### Removed
- `OxidEsales\ComposerPlugin\Plugin::updatePackages()`
- `OxidEsales\ComposerPlugin\Plugin::executeAction()`
- `OxidEsales\ComposerPlugin\Plugin::ACTION_INSTALL`
- `OxidEsales\ComposerPlugin\Plugin::ACTION_UPDATE`

### Changed
- Plugin updates oxid eshop components only if they have any updates
- Skip overwriting favicon.ico file on update [PR-23](https://github.com/OXID-eSales/oxideshop_composer_plugin/pull/23)

## v4.1.1 - 2020-04-14

### Changed
- Component installer uses main container if shop is launched

## v4.1.0 - 2020-02-25

### Removed
- Support for PHP 7.0

### Added
- CodeSniffer as dev dependency [PR-20](https://github.com/OXID-eSales/oxideshop_composer_plugin/pull/20)
- Run CodeSniffer with PSR-2 standard during travis runs [PR-20](https://github.com/OXID-eSales/oxideshop_composer_plugin/pull/20)
- Support uninstall module

### Fixed
- Fix code style to fit PSR-2 standard [PR-20](https://github.com/OXID-eSales/oxideshop_composer_plugin/pull/20)
- Fix code style to fit PSR-12 [PR-21](https://github.com/OXID-eSales/oxideshop_composer_plugin/pull/21)
- Made composer.json compatible with composer v2

## v4.0.0 - 2019-10-15

### Changed
- Do not copy offline.html if it already present in source [PR-17](https://github.com/OXID-eSales/oxideshop_composer_plugin/pull/17)
- Used OXID eShop compilation v6.2.0 packages

## v3.0.0 - 2019-07-23

### Removed
- Support for PHP 5.6

### Added
- Backwards compatibility break: New installer type - `oxideshop-component`, which will trigger service registration to newly introduced OXID eShop DI container.

### Changed
- Exclude non-essential files from dist package [PR-12](https://github.com/OXID-eSales/oxideshop_composer_plugin/pull/12)
- Clarified and unified CLI messages during composer install and composer update
- Updated version of required PHPUnit and fixed tests
- Backwards compatibility break: Module installation logic was changed and moved to OXID eShop Community Edition Core Component.

## v2.0.4 - 2019-07-16

### Fixed
-  Setup folder is copied on every "composer update" although Setup was already executed [#0006793](https://bugs.oxid-esales.com/view.php?id=6793) [PR-13](https://github.com/OXID-eSales/oxideshop_composer_plugin/pull/13)

## v2.0.3 - 2018-07-18

## v2.0.2 - 2017-12-11

### Fixed
- [Add robot exclusion files filter](https://bugs.oxid-esales.com/view.php?id=6703)
