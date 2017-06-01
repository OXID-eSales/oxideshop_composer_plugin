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

class ShopPackageInstallerHtaccessFilesTest extends AbstractPackageInstallerTest
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

    public function HtaccessFilesProvider()
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
     * @dataProvider HtaccessFilesProvider
     */
    public function testShopInstallProcessCopiesHtaccessFilesIfTheyAreMissing($htaccessFile)
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package/source', [
            'index.php' => '<?php',
            $htaccessFile => 'Original htaccess',
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package');
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileEquals("vendor/test-vendor/test-package/source/$htaccessFile", "source/$htaccessFile");
    }

    /**
     * @dataProvider HtaccessFilesProvider
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

        $installer = $this->getPackageInstaller('test-vendor/test-package');
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileNotEquals(
            "vendor/test-vendor/test-package/source/$htaccessFile",
            "source/$htaccessFile"
        );
    }
}
