<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ComposerPlugin\Tests\Integration\Installer\Package;

use Composer\Package\Package;
use Composer\IO\NullIO;
use OxidEsales\ComposerPlugin\Installer\Package\ShopPackageInstaller;

abstract class AbstractShopPackageInstaller extends AbstractPackageInstaller
{
    protected function getPackageInstaller(): ShopPackageInstaller
    {
        $package = new Package(
            'test-vendor/test-package',
            '1.0.0',
            '1.0.0'
        );

        $extra['oxideshop']['blacklist-filter'] = [
            "Application/Component/**/*",
            "Application/Controller/**/*",
            "Application/Model/**/*",
            "Core/**/*"
        ];
        $package->setExtra($extra);

        return new ShopPackageInstaller(
            new NullIO(),
            $this->getVirtualShopSourcePath(),
            $package
        );
    }
}
