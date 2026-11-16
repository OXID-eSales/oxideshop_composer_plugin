# OXID eShop composer plugin

[![image](https://img.shields.io/packagist/v/oxid-esales/oxideshop-composer-plugin.svg?maxAge=3600)](https://packagist.org/packages/oxid-esales/oxideshop-composer-plugin)

This plugin is used to install OXID eShop and OXID eShop third party
integrations (modules, themes).

## Supported types

Packages are recognised by their type, specified in composer.json file.
Available types are:

- oxideshop - Main shop package is installed into source directory.
- oxideshop-module - Modules depend on main shop package.
- oxideshop-theme - Themes depend on main shop package. Theme files are not copied, the shop reads them from the
  vendor directory. The plugin installs the theme configuration and links the `assets` directory of the theme to
  `source/out/<theme id>`. A theme package needs a valid `metadata.yaml` file, otherwise it is skipped
  with a warning.
- oxideshop-admin-ui - Admin UI depends on main shop package. Its files are not copied, the shop reads them from
  the vendor directory. The plugin links the `assets` directory of the admin UI to `source/out/<admin UI name>`.

## Shop cache

The shop cache is cleared after `composer install` and `composer update`. If this is not
possible, a warning is shown and the cache has to be cleared with
`vendor/bin/oe-console oe:cache:clear`.

## Configuration

In your composer.json file you can add the following to help you with the `composer update` prompts. This is useful
for symlinks on installs or possibly other reasons. The packages name in composer is the name you would enter into the
composer.json file.

> [!NOTE]  
> **update-answer-yes** will answer Y for the `composer update` prompts for specific package  
> **update-answer-no** will answer N for the `composer update` prompts for specific package

```json
  "extra": {
    "oxideshop": {
      "update-answer-yes": [
        "oxid-esales/oxideshop-ce"
      ],
      "update-answer-no": [
        "my-vendor/other-example"
      ]
    }
  }
```

## Bugs and Issues

If you experience any bugs or issues, please report them in the section
**OXID eShop (all versions)** of <https://bugs.oxid-esales.com>.