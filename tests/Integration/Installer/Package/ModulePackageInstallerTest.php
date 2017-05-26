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
use OxidEsales\ComposerPlugin\Installer\Package\ModulePackageInstaller;
use OxidEsales\ComposerPlugin\Utilities\VfsFileStructureOperator;
use org\bovigo\vfs\vfsStream;
use Webmozart\PathUtil\Path;

class ModulePackageInstallerTest extends \PHPUnit_Framework_TestCase
{
    protected function getSut(IOInterface $io, $rootPath, PackageInterface $package)
    {
        return new ModulePackageInstaller($io, $rootPath, $package);
    }

    public function testChecksIfModuleIsNotInstalled()
    {
        vfsStream::setup('root', 777, VfsFileStructureOperator::nest([
            'projectRoot/vendor/oxid-esales/paypal-module/metadata.php' => '<?php',
        ]));

        $rootPath = vfsStream::url('root/projectRoot/source');

        $shopPreparator = $this->getSut(new NullIO, $rootPath, new Package('oxid-esales/paypal-module', 'dev', 'dev'));
        $this->assertFalse($shopPreparator->isInstalled());
    }

    public function testChecksIfModuleIsInstalled()
    {
        vfsStream::setup('root', 777, VfsFileStructureOperator::nest([
            'projectRoot/source/modules/oxid-esales/paypal-module/metadata.php' => '<?php',
            'projectRoot/vendor/oxid-esales/paypal-module/metadata.php' => '<?php'
        ]));

        $rootPath = vfsStream::url('root/projectRoot/source');

        $shopPreparator = $this->getSut(new NullIO, $rootPath, new Package('oxid-esales/paypal-module', 'dev', 'dev'));
        $this->assertTrue($shopPreparator->isInstalled());
    }

    public function providerChecksIfModuleFilesExistsAfterInstallation()
    {
        return [
            [[ModulePackageInstaller::EXTRA_PARAMETER_KEY_ROOT => [ModulePackageInstaller::EXTRA_PARAMETER_KEY_TARGET => 'oe/paypal']], 'modules/oe/paypal/metadata.php'],
            [[ModulePackageInstaller::EXTRA_PARAMETER_KEY_ROOT => [ModulePackageInstaller::EXTRA_PARAMETER_KEY_TARGET => 'paypal']], 'modules/paypal/metadata.php'],
            [[], 'modules/oxid-esales/paypal-module/metadata.php']
        ];
    }

    /**
     * @param $composerExtras
     * @param $installedModuleMetadata
     *
     * @dataProvider providerChecksIfModuleFilesExistsAfterInstallation
     */
    public function testChecksIfModuleFilesExistsAfterInstallation($composerExtras, $installedModuleMetadata)
    {
        vfsStream::setup('root', 777, VfsFileStructureOperator::nest([
            'projectRoot/vendor/oxid-esales/paypal-module/metadata.php' => '<?php',
        ]));

        $rootPath = vfsStream::url('root/projectRoot');
        $eshopRootPath = "$rootPath/source";
        $installedModuleMetadata = "$eshopRootPath/$installedModuleMetadata";

        $package = new Package('oxid-esales/paypal-module', 'dev', 'dev');
        $shopPreparator = $this->getSut(new NullIO(), $eshopRootPath, $package);
        $package->setExtra($composerExtras);
        $moduleInVendor = "$rootPath/vendor/oxid-esales/paypal-module";
        $shopPreparator->install($moduleInVendor);

        $this->assertFileExists($installedModuleMetadata);
    }

    public function testCheckIfModuleIsInstalledFromProvidedSourceDirectory()
    {
        vfsStream::setup('root', 777, VfsFileStructureOperator::nest([
            'projectRoot/vendor/oxid-esales/erp/copy_this/modules/erp/metadata.php' => '<?php',
        ]));

        $rootPath = vfsStream::url('root/projectRoot');
        $eshopRootPath = "$rootPath/source";
        $installedModuleMetadata = "$eshopRootPath/modules/erp/metadata.php";

        $package = new Package('oxid-esales/erp', 'dev', 'dev');
        $shopPreparator = $this->getSut(new NullIO(), $eshopRootPath, $package);
        $package->setExtra(
            [ModulePackageInstaller::EXTRA_PARAMETER_KEY_ROOT => [
                ModulePackageInstaller::EXTRA_PARAMETER_KEY_SOURCE => 'copy_this/modules/erp',
                ModulePackageInstaller::EXTRA_PARAMETER_KEY_TARGET => 'erp',
            ]]
        );
        $moduleInVendor = "$rootPath/vendor/oxid-esales/erp";
        $shopPreparator->install($moduleInVendor);

        $this->assertFileExists($installedModuleMetadata);
    }

    public function testBlacklistedFilesArePresentWhenNoBlacklistFilterIsDefined()
    {
        vfsStream::setup('root', 777, VfsFileStructureOperator::nest([
            'projectRoot/vendor/test-vendor/test-package/metadata.php' => 'meta data',
            'projectRoot/vendor/test-vendor/test-package/module.php' => 'module',
            'projectRoot/vendor/test-vendor/test-package/readme.txt' => 'readme',
        ]));

        $rootPath = vfsStream::url('root/projectRoot');
        $shopRootPath = Path::join($rootPath, 'source');
        $installedModulePath = Path::join($shopRootPath, 'modules', 'test-vendor', 'test-package');
        $moduleSourcePath = Path::join($rootPath, 'vendor', 'test-vendor', 'test-package');

        $package = new Package('test-vendor/test-package', 'dev', 'dev');
        $sut = $this->getSut(new NullIO(), $shopRootPath, $package);
        $sut->install($moduleSourcePath);

        $this->assertFileExists(Path::join($installedModulePath, 'metadata.php'));
        $this->assertFileExists(Path::join($installedModulePath, 'module.php'));
        $this->assertFileExists(Path::join($installedModulePath, 'readme.txt'));
    }

    public function testBlacklistedFilesArePresentWhenEmptyBlacklistFilterIsDefined()
    {
        vfsStream::setup('root', 777, VfsFileStructureOperator::nest([
            'projectRoot/vendor/test-vendor/test-package/metadata.php' => 'meta data',
            'projectRoot/vendor/test-vendor/test-package/module.php' => 'module',
            'projectRoot/vendor/test-vendor/test-package/readme.txt' => 'readme',
        ]));

        $rootPath = vfsStream::url('root/projectRoot');
        $shopRootPath = Path::join($rootPath, 'source');
        $installedModulePath = Path::join($shopRootPath, 'modules', 'test-vendor', 'test-package');
        $moduleSourcePath = Path::join($rootPath, 'vendor', 'test-vendor', 'test-package');

        $package = new Package('test-vendor/test-package', 'dev', 'dev');
        $package->setExtra([
            'oxideshop' => [
                'blacklist-filter' => [],
            ]
        ]);
        $sut = $this->getSut(new NullIO(), $shopRootPath, $package);
        $sut->install($moduleSourcePath);

        $this->assertFileExists(Path::join($installedModulePath, 'metadata.php'));
        $this->assertFileExists(Path::join($installedModulePath, 'module.php'));
        $this->assertFileExists(Path::join($installedModulePath, 'readme.txt'));
    }

    public function testBlacklistedFilesArePresentWhenDifferentBlacklistFilterIsDefined()
    {
        vfsStream::setup('root', 777, VfsFileStructureOperator::nest([
            'projectRoot/vendor/test-vendor/test-package/metadata.php' => 'meta data',
            'projectRoot/vendor/test-vendor/test-package/module.php' => 'module',
            'projectRoot/vendor/test-vendor/test-package/readme.txt' => 'readme',
        ]));

        $rootPath = vfsStream::url('root/projectRoot');
        $shopRootPath = Path::join($rootPath, 'source');
        $installedModulePath = Path::join($shopRootPath, 'modules', 'test-vendor', 'test-package');
        $moduleSourcePath = Path::join($rootPath, 'vendor', 'test-vendor', 'test-package');

        $package = new Package('test-vendor/test-package', 'dev', 'dev');
        $package->setExtra([
            'oxideshop' => [
                'blacklist-filter' => [
                    "**/*.pdf",
                ],
            ]
        ]);
        $sut = $this->getSut(new NullIO(), $shopRootPath, $package);
        $sut->install($moduleSourcePath);

        $this->assertFileExists(Path::join($installedModulePath, 'metadata.php'));
        $this->assertFileExists(Path::join($installedModulePath, 'module.php'));
        $this->assertFileExists(Path::join($installedModulePath, 'readme.txt'));
    }

    public function testBlacklistedFilesAreSkippedWhenABlacklistFilterIsDefined()
    {
        vfsStream::setup('root', 777, VfsFileStructureOperator::nest([
            'projectRoot/vendor/test-vendor/test-package/metadata.php' => 'meta data',
            'projectRoot/vendor/test-vendor/test-package/module.php' => 'module',
            'projectRoot/vendor/test-vendor/test-package/readme.txt' => 'readme',
        ]));

        $rootPath = vfsStream::url('root/projectRoot');
        $shopRootPath = Path::join($rootPath, 'source');
        $installedModulePath = Path::join($shopRootPath, 'modules', 'test-vendor', 'test-package');
        $moduleSourcePath = Path::join($rootPath, 'vendor', 'test-vendor', 'test-package');

        $package = new Package('test-vendor/test-package', 'dev', 'dev');
        $package->setExtra([
            'oxideshop' => [
                'blacklist-filter' => [
                    "**/*.txt",
                ],
            ]
        ]);
        $sut = $this->getSut(new NullIO(), $shopRootPath, $package);
        $sut->install($moduleSourcePath);

        $this->assertFileExists(Path::join($installedModulePath, 'metadata.php'));
        $this->assertFileExists(Path::join($installedModulePath, 'module.php'));
        $this->assertFileNotExists(Path::join($installedModulePath, 'readme.txt'));
    }
}
