<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ComposerPlugin\Tests\Integration\Installer\Package;

use PHPUnit\Framework\Attributes\DataProvider;

class ShopPackageInstallerHtaccessFilesTest extends AbstractShopPackageInstaller
{
    public static function providerHtaccessFiles(): array
    {
        return [
            ['.htaccess'],
            ['bin/.htaccess'],
            ['cache/.htaccess'],
            ['out/downloads/.htaccess'],
            ['Application/views/admin/tpl/.htaccess'],
            ['test/.htaccess'],
        ];
    }

    #[DataProvider('providerHtaccessFiles')]
    public function testShopInstallProcessCopiesHtaccessFilesIfTheyAreMissing(string $htaccessFile): void
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package/source', [
            'index.php' => '<?php',
            $htaccessFile => 'Original htaccess',
        ]);

        $installer = $this->getPackageInstaller();
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileEquals("vendor/test-vendor/test-package/source/$htaccessFile", "source/$htaccessFile");
    }

    #[DataProvider('providerHtaccessFiles')]
    public function testShopInstallProcessDoesNotCopyHtaccessFilesIfTheyAreAlreadyPresent(string $htaccessFile): void
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package/source', [
            'index.php' => '<?php',
            $htaccessFile => 'Original htaccess',
        ]);
        $this->setupVirtualProjectRoot('source', [
            $htaccessFile => 'Old',
        ]);

        $installer = $this->getPackageInstaller();
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileNotEquals(
            "vendor/test-vendor/test-package/source/$htaccessFile",
            "source/$htaccessFile"
        );
    }
}
