<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ComposerPlugin\Tests\Integration\Installer\Package;

class ShopPackageInstallerEnvDistFileTest extends AbstractShopPackageInstaller
{
    public function testShopInstallProcessCopiesEnvDistFileIfItDoesNotExist(): void
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            '.env.dist' => 'PACKAGE CONTENT',
            'source/index.php' => '<?php'
        ]);

        $installer = $this->getPackageInstaller();
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertFileExists($this->getVirtualFileSystemRootPath('.env.dist'));
        $this->assertStringEqualsFile($this->getVirtualFileSystemRootPath('.env.dist'), 'PACKAGE CONTENT');
    }

    public function testShopInstallProcessDoesNotOverwriteEnvDistFileIfItAlreadyExists(): void
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            '.env.dist' => 'NEW CONTENT',
            'source/index.php' => '<?php'
        ]);

        $this->setupVirtualProjectRoot('', [
            '.env.dist' => 'EXISTING CONTENT'
        ]);

        $installer = $this->getPackageInstaller();
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertStringEqualsFile($this->getVirtualFileSystemRootPath('.env.dist'), 'EXISTING CONTENT');
    }

    public function testShopInstallProcessDoesNotCreateEnvDistFileIfItDoesNotExistInPackage(): void
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'source/index.php' => '<?php'
        ]);

        $installer = $this->getPackageInstaller();
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertFileDoesNotExist($this->getVirtualFileSystemRootPath('.env.dist'));
    }
}
