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

use Composer\IO\IOInterface;
use Composer\IO\NullIO;
use Composer\Package\Package;
use Composer\Package\PackageInterface;
use OxidEsales\ComposerPlugin\Installer\Package\ShopPackageInstaller;
use OxidEsales\ComposerPlugin\Utilities\VfsFileStructureOperator;
use org\bovigo\vfs\vfsStream;
use Webmozart\PathUtil\Path;

class ShopPackageInstallerSetupFilesTest extends AbstractPackageInstallerTest
{
    protected function getPackageInstaller($packageName, $version = '1.0.0', $extra = [])
    {
        $package = new Package($packageName, $version, $version);
        $extra['oxideshop']['blacklist-filter'] = [
            "Application/Component/**/*.*",
            "Application/Controller/**/*.*",
            "Application/Model/**/*.*",
            "Core/**/*.*"
        ];
        $package->setExtra($extra);

        return new ShopPackageInstaller(
            new NullIO(),
            $this->getVirtualShopSourcePath(),
            $package
        );
    }

    public function testShopInstallProcessCopiesSetupFilesIfShopConfigIsMissing()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package/source', [
            'index.php' => '<?php',
            'config.inc.php.dist' => 'dist',
            'Setup/index.php' => '<?php'
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package');
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileEquals(
            "vendor/test-vendor/test-package/source/Setup/index.php",
            "source/Setup/index.php"
        );
    }

    public function testShopInstallProcessOverwritesSetupFilesIfShopConfigIsMissing()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package/source', [
            'index.php' => '<?php',
            'config.inc.php.dist' => 'dist',
            'Setup/index.php' => '<?php'
        ]);
        $this->setupVirtualProjectRoot('source', [
            'Setup/index.php' => 'Old index file'
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package');
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileEquals(
            "vendor/test-vendor/test-package/source/Setup/index.php",
            "source/Setup/index.php"
        );
    }

    public function testShopInstallProcessCopiesSetupFilesIfShopConfigIsNotConfigured()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package/source', [
            'index.php' => '<?php',
            'Setup/index.php' => '<?php'
        ]);
        $this->setupVirtualProjectRoot('source', [
            'config.inc.php' => $this->getNonConfiguredConfigFileContents(),
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package');
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileEquals(
            "vendor/test-vendor/test-package/source/Setup/index.php",
            "source/Setup/index.php"
        );
    }

    public function testShopInstallProcessOverwritesSetupFilesIfShopConfigIsNotConfigured()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package/source', [
            'index.php' => '<?php',
            'Setup/index.php' => '<?php'
        ]);
        $this->setupVirtualProjectRoot('source', [
            'config.inc.php' => $this->getNonConfiguredConfigFileContents(),
            'Setup/index.php' => 'Old index file'
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package');
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileEquals(
            "vendor/test-vendor/test-package/source/Setup/index.php",
            "source/Setup/index.php"
        );
    }

    public function testShopInstallProcessDoesNotCopySetupFilesIfShopConfigIsConfigured()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package/source', [
            'index.php' => '<?php',
            'Setup/index.php' => '<?php'
        ]);
        $this->setupVirtualProjectRoot('source', [
            'config.inc.php' => $this->getConfiguredConfigFileContents(),
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package');
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileNotExists('source/Setup/index.php');
    }

    public function testShopInstallProcessDoesNotOverwriteSetupFilesIfShopConfigIsConfigured()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package/source', [
            'index.php' => '<?php',
            'Setup/index.php' => '<?php'
        ]);
        $this->setupVirtualProjectRoot('source', [
            'config.inc.php' => $this->getConfiguredConfigFileContents(),
            'Setup/index.php' => 'Old index file'
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package');
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileNotEquals(
            "vendor/test-vendor/test-package/source/Setup/index.php",
            "source/Setup/index.php"
        );
    }

    protected function getNonConfiguredConfigFileContents()
    {
        return  <<<'EOT'
    $this->dbType = 'pdo_mysql';
    $this->dbHost = '<dbHost>';
    $this->dbPort  = 3306;
    $this->dbName = '<dbName>';
    $this->dbUser = '<dbUser>';
    $this->dbPwd  = '<dbPwd>';
    $this->sShopURL     = '<sShopURL>';
    $this->sShopDir     = '<sShopDir>';
    $this->sCompileDir  = '<sCompileDir>';
EOT;
    }

    protected function getConfiguredConfigFileContents()
    {
        return <<<'EOT'
    $this->dbType = 'pdo_mysql';
    $this->dbHost = 'test_host';
    $this->dbPort  = 3306;
    $this->dbName = 'test_db';
    $this->dbUser = 'test_user';
    $this->dbPwd  = 'test_password';
    $this->sShopURL     = 'http://test.url/';
    $this->sShopDir     = '/var/www/test/dir';
    $this->sCompileDir  = '/var/www/test/dir/tmp';
EOT;
    }
}
