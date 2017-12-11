<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ComposerPlugin\Tests\Integration\Installer\Package;

class ShopPackageInstallerHtaccessFilesTest extends AbstractShopPackageInstallerTest
{
    public function providerHtaccessFiles()
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

    /**
     * @dataProvider providerHtaccessFiles
     */
    public function testShopInstallProcessCopiesHtaccessFilesIfTheyAreMissing($htaccessFile)
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package/source', [
            'index.php' => '<?php',
            $htaccessFile => 'Original htaccess',
        ]);

        $installer = $this->getPackageInstaller();
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileEquals("vendor/test-vendor/test-package/source/$htaccessFile", "source/$htaccessFile");
    }

    /**
     * @dataProvider providerHtaccessFiles
     */
    public function testShopInstallProcessDoesNotCopyHtaccessFilesIfTheyAreAlreadyPresent($htaccessFile)
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
