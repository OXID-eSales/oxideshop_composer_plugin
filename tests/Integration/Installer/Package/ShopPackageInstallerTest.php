<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ComposerPlugin\Tests\Integration\Installer\Package;

class ShopPackageInstallerTest extends AbstractShopPackageInstaller
{
    public function testShopNotInstalledByDefault(): void
    {
        $installer = $this->getPackageInstaller();

        $this->assertFalse($installer->isInstalled(''));
    }

    public function testShopIsInstalledIfSourceFilesAlreadyExist(): void
    {
        $this->setupVirtualProjectRoot('source/', [
            'index.php' => '<?php'
        ]);

        $installer = $this->getPackageInstaller();

        $this->assertTrue($installer->isInstalled($this->getVirtualFileSystemRootPath('')));
        $this->assertVirtualFileExists('source/index.php');
    }

    public function testShopIsInstalledAfterInstallProcess(): void
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package/source', [
            'index.php' => '<?php',
        ]);

        $installer = $this->getPackageInstaller();

        $packagePath = $this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package');
        $installer->install($packagePath);

        $this->assertTrue($installer->isInstalled($packagePath));
    }

    public function testShopFilesAreCopiedAfterInstallProcess(): void
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package/source', [
            'index.php' => '<?php',
            'Application/views/template.tpl' => 'tpl',
            'config.inc.php.dist' => 'dist',
        ]);

        $installer = $this->getPackageInstaller();
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileEquals(
            'vendor/test-vendor/test-package/source/index.php',
            'source/index.php'
        );
        $this->assertVirtualFileEquals(
            'vendor/test-vendor/test-package/source/Application/views/template.tpl',
            'source/Application/views/template.tpl'
        );
        $this->assertVirtualFileEquals(
            'vendor/test-vendor/test-package/source/config.inc.php.dist',
            'source/config.inc.php.dist'
        );
    }

    public function testShopInstallProcessCopiesConfigFileIfItDoesNotExist(): void
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package/source', [
            'index.php' => '<?php',
            'config.inc.php.dist' => 'dist',
        ]);

        $installer = $this->getPackageInstaller();
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileEquals(
            'vendor/test-vendor/test-package/source/config.inc.php.dist',
            'source/config.inc.php'
        );
    }

    public function testShopInstallProcessDoesNotCopyConfigFileIfItAlreadyExists(): void
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package/source', [
            'index.php' => '<?php',
            'config.inc.php.dist' => 'dist',
        ]);
        $this->setupVirtualProjectRoot('source', [
            'config.inc.php' => 'old',
        ]);

        $installer = $this->getPackageInstaller();
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileNotEquals(
            'vendor/test-vendor/test-package/source/config.inc.php.dist',
            'source/config.inc.php'
        );
    }

    public function testShopInstallProcessDoesNotCopyFilteredClasses(): void
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package/source', [
            'index.php' => '<?php',
            'Class.php' => '<?php',
            'Core/Class.php' => '<?php',
            'Application/Model/Class.php' => '<?php',
            'Application/Controller/Class.php' => '<?php',
            'Application/Component/Class.php' => '<?php',
            'config.inc.php.dist' => 'dist',
        ]);

        $installer = $this->getPackageInstaller();
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileEquals(
            'vendor/test-vendor/test-package/source/Class.php',
            'source/Class.php'
        );
        $this->assertVirtualFileNotExists('source/Core/Class.php');
        $this->assertVirtualFileNotExists('source/Application/Model/Class.php');
        $this->assertVirtualFileNotExists('source/Application/Controller/Class.php');
        $this->assertVirtualFileNotExists('source/Application/Component/Class.php');
    }

    public function testShopInstallProcessDoesNotCopyVCSFiles(): void
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package/source', [
            'index.php' => '<?php',
            '.git/HEAD' => 'HEAD',
            '.git/index' => 'index',
            '.git/objects/ff/fftest' => 'blob',
            '.gitignore' => 'git ignore',
        ]);

        $installer = $this->getPackageInstaller();
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileEquals(
            'vendor/test-vendor/test-package/source/index.php',
            'source/index.php'
        );
        $this->assertVirtualFileNotExists('source/.git/HEAD');
        $this->assertVirtualFileNotExists('source/.git/index');
        $this->assertVirtualFileNotExists('source/.git/objects/ff/fftest');
        $this->assertVirtualFileNotExists('source/.gitignore');
    }
}
