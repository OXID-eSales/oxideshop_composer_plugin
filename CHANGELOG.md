# Change Log for OXID eSales Composer Plugin

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [4.0.0] - 2019-10-15

### Changed
- Do not copy offline.html if it already present in source [PR-17](https://github.com/OXID-eSales/oxideshop_composer_plugin/pull/17)
- Used OXID eShop compilation v6.2.0 packages

## [3.0.0] - 2019-07-23

### Removed
- Support for PHP 5.6

### Added
- Backwards compatibility break: New installer type - `oxideshop-component`, which will trigger service registration to newly introduced OXID eShop DI container.

### Changed
- Exclude non-essential files from dist package [PR-12](https://github.com/OXID-eSales/oxideshop_composer_plugin/pull/12)
- Clarified and unified CLI messages during composer install and composer update
- Updated version of required PHPUnit and fixed tests
- Backwards compatibility break: Module installation logic was changed and moved to OXID eShop Community Edition Core Component.

## [2.0.4] - 2019-07-16

### Fixed
-  Setup folder is copied on every "composer update" although Setup was already executed [#0006793](https://bugs.oxid-esales.com/view.php?id=6793) [PR-13](https://github.com/OXID-eSales/oxideshop_composer_plugin/pull/13)

## [2.0.3] - 2018-07-18

## [2.0.2] - 2017-12-11

### Fixed
- [Add robot exclusion files filter](https://bugs.oxid-esales.com/view.php?id=6703)

[4.0.0]: https://github.com/OXID-eSales/oxideshop_composer_plugin/compare/v3.0.0...4.0.0
[3.0.0]: https://github.com/OXID-eSales/oxideshop_composer_plugin/compare/v2.0.4...v3.0.0
[2.0.4]: https://github.com/OXID-eSales/oxideshop_composer_plugin/compare/v2.0.3...v2.0.4
[2.0.3]: https://github.com/OXID-eSales/oxideshop_composer_plugin/compare/v2.0.2...v2.0.3
