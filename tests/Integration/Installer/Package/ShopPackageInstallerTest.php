<?php
/**
 * This file is part of OXID eShop Composer plugin.
 *
 * OXID eShop Composer plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eShop Composer plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eShop Composer plugin.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2016
 * @version   OXID eShop Composer plugin
 */

namespace OxidEsales\ComposerPlugin\Tests\Integration\Installer\Package;

class ShopPackageInstallerTest extends AbstractShopPackageInstallerTest
{
    public function testShopNotInstalledByDefault()
    {
        $installer = $this->getPackageInstaller();

        $this->assertFalse($installer->isInstalled());
    }

    public function testShopIsInstalledIfSourceFilesAlreadyExist()
    {
        $this->setupVirtualProjectRoot('source/', [
            'index.php' => '<?php'
        ]);

        $installer = $this->getPackageInstaller();

        $this->assertTrue($installer->isInstalled());
        $this->assertVirtualFileExists('source/index.php');
    }

    public function testShopIsInstalledAfterInstallProcess()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package/source', [
            'index.php' => '<?php',
        ]);

        $installer = $this->getPackageInstaller();
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertTrue($installer->isInstalled());
    }

    public function testShopFilesAreCopiedAfterInstallProcess()
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

    public function testShopInstallProcessCopiesConfigFileIfItDoesNotExist()
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

    public function testShopInstallProcessDoesNotCopyConfigFileIfItAlreadyExists()
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

    public function testShopInstallProcessDoesNotCopyFilteredClasses()
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

    public function testShopInstallProcessDoesNotCopyVCSFiles()
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
