<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ComposerPlugin\Tests\Integration\Installer\Package;

class ShopPackageInstallerRobotsExclusionFilesTest extends AbstractShopPackageInstallerTest
{
    public function providerFiles()
    {
        return [
            ['robots.txt'],
            ['bin/robots.txt'],
        ];
    }

    /**
     * @dataProvider providerFiles
     */
    public function testShopInstallProcessCopiesRobotsExclusionFilesIfTheyAreMissing($file)
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
    public function testShopInstallProcessDoesNotCopyRobotsExclusioIfTheyAreAlreadyPresent($file)
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
