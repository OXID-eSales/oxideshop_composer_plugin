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

    public function testChecksIfPackageIsNotInstalled()
    {
        $structure = [
            'vendor/'.static::PRODUCT_NAME_IN_COMPOSER_FILE.'/metadata.php' => '<?php',
        ];
        vfsStream::setup('root', 777, ['projectRoot' => $this->getStructurePreparator()->prepareStructure($structure)]);
        $rootPath = vfsStream::url('root/projectRoot/source');

        $shopPreparator = new ModuleInstaller(new Filesystem(), new NullIO, $rootPath);
        $this->assertFalse($shopPreparator->isInstalled(new Package(static::PRODUCT_NAME_IN_COMPOSER_FILE, 'dev', 'dev')));
    }

    public function testChecksIfPackageInstalled()
    {
        $structure = [
            'source/modules/oxid-esales/paypal-module/metadata.php' => '<?php',
            'vendor/oxid-esales/paypal-module/metadata.php' => '<?php'
        ];
        vfsStream::setup('root', 777, ['projectRoot' => $this->getStructurePreparator()->prepareStructure($structure)]);
        $rootPath = vfsStream::url('root/projectRoot/source');

        $shopPreparator = new ModuleInstaller(new Filesystem(), new NullIO, $rootPath);
        $this->assertTrue($shopPreparator->isInstalled(new Package(static::PRODUCT_NAME_IN_COMPOSER_FILE, 'dev', 'dev')));
    }

    public function providerChecksIfModuleIsInstalled()
    {
        return [
            [['oxideshop' => ['vendor-name' => 'oe', 'module-name' => 'paypal']], 'modules/oe/paypal/metadata.php'],
            [['oxideshop' => ['vendor-name' => 'oe']], 'modules/oe/paypal-module/metadata.php'],
            [['oxideshop' => ['module-name' => 'paypal']], 'modules/oxid-esales/paypal/metadata.php'],
            [[], 'modules/oxid-esales/paypal-module/metadata.php']
        ];
    }

    /**
     * @param $composerExtras
     * @param $installedModuleMetadata
     * 
     * @dataProvider providerChecksIfModuleIsInstalled
     */
    public function testChecksIfModuleIsInstalled($composerExtras, $installedModuleMetadata)
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

        $shopPreparator = new ModuleInstaller(new Filesystem(), new NullIO(), $eshopRootPath);
        $package = new Package(static::PRODUCT_NAME_IN_COMPOSER_FILE, 'dev', 'dev');
        $package->setExtra($composerExtras);
        $moduleInVendor = "$rootPath/vendor/" . static::PRODUCT_NAME_IN_COMPOSER_FILE . "";
        $shopPreparator->install($package, $moduleInVendor);

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
