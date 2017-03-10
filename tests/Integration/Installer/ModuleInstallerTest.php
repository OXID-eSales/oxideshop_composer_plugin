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

namespace OxidEsales\ComposerPlugin\Tests\Integration\Installer;

use Composer\IO\NullIO;
use Composer\Package\Package;
use OxidEsales\ComposerPlugin\Installer\ModuleInstaller;
use org\bovigo\vfs\vfsStream;
use Symfony\Component\Filesystem\Filesystem;

class ModuleInstallerTest extends \PHPUnit_Framework_TestCase
{
    const PRODUCT_NAME_IN_COMPOSER_FILE = "oxid-esales/paypal-module";

    public function testChecksIfModuleIsNotInstalled()
    {
        $structure = [
            'vendor/'.static::PRODUCT_NAME_IN_COMPOSER_FILE.'/metadata.php' => '<?php',
        ];
        vfsStream::setup('root', 777, ['projectRoot' => $this->getStructurePreparator()->prepareStructure($structure)]);
        $rootPath = vfsStream::url('root/projectRoot/source');

        $shopPreparator = new ModuleInstaller(new Filesystem(), new NullIO, $rootPath, new Package(static::PRODUCT_NAME_IN_COMPOSER_FILE, 'dev', 'dev'));
        $this->assertFalse($shopPreparator->isInstalled());
    }

    public function testChecksIfModuleIsInstalled()
    {
        $structure = [
            'source/modules/oxid-esales/paypal-module/metadata.php' => '<?php',
            'vendor/oxid-esales/paypal-module/metadata.php' => '<?php'
        ];
        vfsStream::setup('root', 777, ['projectRoot' => $this->getStructurePreparator()->prepareStructure($structure)]);
        $rootPath = vfsStream::url('root/projectRoot/source');

        $shopPreparator = new ModuleInstaller(new Filesystem(), new NullIO, $rootPath, new Package(static::PRODUCT_NAME_IN_COMPOSER_FILE, 'dev', 'dev'));
        $this->assertTrue($shopPreparator->isInstalled());
    }

    public function providerChecksIfModuleFilesExistsAfterInstallation()
    {
        return [
            [[ModuleInstaller::EXTRA_PARAMETER_KEY_ROOT => [ModuleInstaller::EXTRA_PARAMETER_KEY_TARGET => 'oe/paypal']], 'modules/oe/paypal/metadata.php'],
            [[ModuleInstaller::EXTRA_PARAMETER_KEY_ROOT => [ModuleInstaller::EXTRA_PARAMETER_KEY_TARGET => 'paypal']], 'modules/paypal/metadata.php'],
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
        $structure = [
            'vendor/oxid-esales/paypal-module' => [
                'metadata.php' => '<?php',
            ]
        ];
        vfsStream::setup('root', 777, ['projectRoot' => $this->getStructurePreparator()->prepareStructure($structure)]);

        $rootPath = vfsStream::url('root/projectRoot');
        $eshopRootPath = "$rootPath/source";
        $installedModuleMetadata = "$eshopRootPath/$installedModuleMetadata";

        $package = new Package(static::PRODUCT_NAME_IN_COMPOSER_FILE, 'dev', 'dev');
        $shopPreparator = new ModuleInstaller(new Filesystem(), new NullIO(), $eshopRootPath, $package);
        $package->setExtra($composerExtras);
        $moduleInVendor = "$rootPath/vendor/" . static::PRODUCT_NAME_IN_COMPOSER_FILE . "";
        $shopPreparator->install($moduleInVendor);

        $this->assertFileExists($installedModuleMetadata);
    }

    public function testCheckIfModuleIsInstalledFromProvidedSourceDirectory()
    {
        $structure = [
            'vendor/oxid-esales/erp/copy_this/modules/erp' => [
                'metadata.php' => '<?php',
            ]
        ];
        vfsStream::setup('root', 777, ['projectRoot' => $this->getStructurePreparator()->prepareStructure($structure)]);

        $rootPath = vfsStream::url('root/projectRoot');
        $eshopRootPath = "$rootPath/source";
        $installedModuleMetadata = "$eshopRootPath/modules/erp/metadata.php";

        $package = new Package('oxid-esales/erp', 'dev', 'dev');
        $shopPreparator = new ModuleInstaller(new Filesystem(), new NullIO(), $eshopRootPath, $package);
        $package->setExtra(
            [ModuleInstaller::EXTRA_PARAMETER_KEY_ROOT => [
                ModuleInstaller::EXTRA_PARAMETER_KEY_SOURCE => 'copy_this/modules/erp',
                ModuleInstaller::EXTRA_PARAMETER_KEY_TARGET => 'erp',
            ]]
        );
        $moduleInVendor = "$rootPath/vendor/oxid-esales/erp";
        $shopPreparator->install($moduleInVendor);

        $this->assertFileExists($installedModuleMetadata);
    }

    /**
     * @return StructurePreparator
     */
    public function getStructurePreparator()
    {
        return new StructurePreparator();
    }
}
