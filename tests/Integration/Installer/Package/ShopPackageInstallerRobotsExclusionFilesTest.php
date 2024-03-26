<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ComposerPlugin\Tests\Integration\Installer\Package;

class ShopPackageInstallerRobotsExclusionFilesTest extends AbstractShopPackageInstaller
{
    public static function providerFiles(): array
    {
        return [
            ['robots.txt'],
            ['bin/robots.txt'],
        ];
    }

    /**
     * @dataProvider providerFiles
     */
    public function testShopInstallProcessCopiesRobotsExclusionFilesIfTheyAreMissing($file): void
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package/source', [
            'index.php' => '<?php',
            $file => 'Disallow: /agb/',
        ]);

        $installer = $this->getPackageInstaller();
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileEquals("vendor/test-vendor/test-package/source/$file", "source/$file");
    }

    /**
     * @dataProvider providerFiles
     */
    public function testShopInstallProcessDoesNotCopyRobotsExclusionIfTheyAreAlreadyPresent($file): void
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
