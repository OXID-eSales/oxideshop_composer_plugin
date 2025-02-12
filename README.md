OXID eShop composer plugin
==========================

.. image:: https://img.shields.io/packagist/v/oxid-esales/oxideshop-composer-plugin.svg?maxAge=3600
  :target: https://packagist.org/packages/oxid-esales/oxideshop-composer-plugin

This plugin is used to install OXID eShop and OXID eShop third party integrations (modules, themes).

Supported types
---------------

Packages are recognised by their type, specified in composer.json file.
Available types are:

- oxideshop - Main shop package is installed into source directory.
- oxideshop-module - Modules, which are installed into source directory. Modules depends on main shop package.
- oxideshop-theme - Themes, which are installed into source directory. Themes depends on main shop package.

Configuration
-------------

In your composer.json file you can add the following to help you with the `composer update` prompts. This is useful
for symlinks on installs or possibly other reasons. The packages name in composer is the name you would enter into the 
composer.json file.

> [!NOTE]  
> **update-ask-true** will answer Y for the `composer update` prompts
> **update-ask-false** will answer N for the `composer update` prompts

```json
  "extra": {
    "oxideshop": {
      "update-ask-true": [
        "oxid-esales/oxideshop-ce",
        "ddoe/wysiwyg-editor-module",
        "oxid-esales/wave-theme",
        "ecs/fixsmartyincontent"
      ],
      "update-ask-false": [
        "ecs/adminrights",
        "ecs/ustidcheck"
      ]
    }
  }
```

Bugs and Issues
---------------

If you experience any bugs or issues, please report them in the section **OXID eShop (all versions)** of https://bugs.oxid-esales.com.
