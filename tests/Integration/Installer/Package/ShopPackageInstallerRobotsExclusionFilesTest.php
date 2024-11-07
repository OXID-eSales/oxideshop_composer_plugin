<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ComposerPlugin\Tests\Integration\Installer\Package;

use PHPUnit\Framework\Attributes\DataProvider;

class ShopPackageInstallerRobotsExclusionFilesTest extends AbstractShopPackageInstaller
{
    public static function providerFiles(): array
    {
        return [
            ['robots.txt'],
            ['bin/robots.txt'],
        ];
    }

    #[DataProvider('providerFiles')]
    public function testShopInstallProcessCopiesRobotsExclusionFilesIfTheyAreMissing(string $file): void
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package/source', [
            'index.php' => '<?php',
            $file => 'Disallow: /agb/',
        ]);

        $installer = $this->getPackageInstaller();
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileEquals("vendor/test-vendor/test-package/source/$file", "source/$file");
    }

    #[DataProvider('providerFiles')]
    public function testShopInstallProcessDoesNotCopyRobotsExclusionIfTheyAreAlreadyPresent(string $file): void
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package/source', [
            'index.php' => '<?php',
            $file => 'Disallow: /agb/',
        ]);
        $this->setupVirtualProjectRoot('source', [
            $file => 'Old',
        ]);

        $installer = $this->getPackageInstaller();
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileNotEquals(
            "vendor/test-vendor/test-package/source/$file",
            "source/$file"
        );
    }
}
