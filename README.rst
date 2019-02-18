OXID eShop composer plugin
==========================

.. image:: https://travis-ci.org/OXID-eSales/oxideshop_composer_plugin.svg?branch=master
  :target: https://travis-ci.org/OXID-eSales/oxideshop_composer_plugin

.. image:: https://img.shields.io/packagist/v/oxid-esales/oxideshop-composer-plugin.svg?maxAge=3600
  :target: https://packagist.org/packages/oxid-esales/oxideshop-composer-plugin

This plugin is used to install OXID eShop and OXID eShop third party integrations (modules, themes).

More information how to install OXID eShop using this plugin can be found `here <http://oxid-eshop-developer-documentation.readthedocs.io/en/latest/getting_started/eshop_installation.html#eshop-installation-via-composer>`__.

Supported types
---------------

Packages are recognised by their type, specified in composer.json file.
Available types are:

- oxideshop - Main shop package is installed into source directory.
- oxideshop-module - Modules, which are installed into source directory. Modules depends on main shop package.
- oxideshop-theme - Themes, which are installed into source directory. Themes depends on main shop package.

More information how to create module installable via composer: http://oxid-eshop-developer-documentation.readthedocs.io/en/latest/modules/module_via_composer.html

More information how to create themes installable via composer: http://oxid-eshop-developer-documentation.readthedocs.io/en/latest/themes/theme_via_composer.html

Requirements
------------

* master branch: OXID eShop compilation >=v6.2.0 (b-6.x CE branch)
* b-2.x branch: OXID eShop compilation v6.0.0 - v6.1.x

Bugs and Issues
---------------

If you experience any bugs or issues, please report them in the section **OXID eShop (all versions)** of https://bugs.oxid-esales.com.
