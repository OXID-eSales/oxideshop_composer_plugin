OXID eShop composer plugin
==========================

.. image:: https://app.travis-ci.com/OXID-eSales/oxideshop_composer_plugin.svg?branch=master
  :target: https://app.travis-ci.com/OXID-eSales/oxideshop_composer_plugin

.. image:: https://img.shields.io/packagist/v/oxid-esales/oxideshop-composer-plugin.svg?maxAge=3600
  :target: https://packagist.org/packages/oxid-esales/oxideshop-composer-plugin

This plugin is used to install OXID eShop and OXID eShop third party integrations (modules, themes).

More information how to install OXID eShop using this plugin can be found `here <https://docs.oxid-esales.com/developer/en/6.1/getting_started/installation/index.html>`__.

Supported types
---------------

Packages are recognised by their type, specified in composer.json file.
Available types are:

- oxideshop - Main shop package is installed into source directory.
- oxideshop-module - Modules, which are installed into source directory. Modules depends on main shop package.
- oxideshop-theme - Themes, which are installed into source directory. Themes depends on main shop package.

More information how to create module installable via composer: https://docs.oxid-esales.com/developer/en/6.1/modules/skeleton/composerjson/module_via_composer.html

More information how to create themes installable via composer: https://docs.oxid-esales.com/developer/en/6.1/themes/theme_via_composer.html

Requirements
------------

* master branch is compatible with OXID eShop compilation master
* b-6.x branch is compatible with OXID eShop compilation 6.x (>=6.3.x)
* b-6.2.x branch is compatible with OXID eShop compilation 6.2.x
* b-2.x branch is compatible with OXID eShop compilation 6.0.x and 6.1.x

Bugs and Issues
---------------

If you experience any bugs or issues, please report them in the section **OXID eShop (all versions)** of https://bugs.oxid-esales.com.
