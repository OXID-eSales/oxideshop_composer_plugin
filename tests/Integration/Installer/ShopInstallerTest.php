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

namespace Tests\Integration;

use Composer\IO\NullIO;
use Composer\Package\Package;
use OxidEsales\ComposerPlugin\Installer\ShopInstaller;
use org\bovigo\vfs\vfsStream;
use Symfony\Component\Filesystem\Filesystem;

class ShopInstallerTest extends \PHPUnit_Framework_TestCase
{
    public function testChecksIfPackageIsNotInstalled()
    {
        $structure = [
            'source/vendor/oxideshop_ce/source/index.php' => '<?php',
        ];
        vfsStream::setup('root', 777, ['projectRoot' => $this->prepareStructure($structure)]);
        $rootPath = vfsStream::url('root/projectRoot/source');

        $shopPreparator = new ShopInstaller(new Filesystem(), new NullIO, $rootPath);
        $this->assertFalse($shopPreparator->isInstalled(new Package('oxid-esales/oxideshop-ce', 'dev', 'dev')));
    }

    public function testChecksIfPackageInstalled()
    {
        $structure = [
            'source' => [
                'index.php' => '<?php',
                'vendor/oxideshop_ce/source/index.php' => '<?php',
            ]
        ];
        vfsStream::setup('root', 777, ['projectRoot' => $this->prepareStructure($structure)]);
        $rootPath = vfsStream::url('root/projectRoot/source');

        $shopPreparator = new ShopInstaller(new Filesystem(), new NullIO, $rootPath);
        $this->assertTrue($shopPreparator->isInstalled(new Package('oxid-esales/oxideshop-ce', 'dev', 'dev')));
    }

    public function testInstallationOfPackage()
    {
        $structure = [
            'source/vendor/oxideshop_ce/source' => [
                'index.php' => '<?php',
                'Application/views/template.tpl' => '<?php',
                'config.inc.php.dist' => '<?php',
            ]
        ];
        vfsStream::setup('root', 777, ['projectRoot' => $this->prepareStructure($structure)]);

        $rootPath = vfsStream::url('root/projectRoot/source');
        $shopDirectory = "$rootPath/vendor/oxideshop_ce";

        $shopPreparator = new ShopInstaller(new Filesystem(), new NullIO, $rootPath);
        $shopPreparator->install(new Package('oxid-esales/oxideshop-ce', 'dev', 'dev'), $shopDirectory);

        $this->assertFileExists($rootPath . '/index.php');
        $this->assertFileExists($rootPath . '/Application/views/template.tpl');
        $this->assertFileExists($rootPath . '/config.inc.php.dist');
    }

    public function testInstallCreatesConfigInc()
    {
        $structure = [
            'source/vendor/oxideshop_ce/source' => [
                'config.inc.php.dist' => '<?php',
            ]
        ];
        vfsStream::setup('root', 777, ['projectRoot' => $this->prepareStructure($structure)]);

        $rootPath = vfsStream::url('root/projectRoot/source');
        $shopDirectory = "$rootPath/vendor/oxideshop_ce";

        $shopPreparator = new ShopInstaller(new Filesystem(), new NullIO, $rootPath);
        $shopPreparator->install(new Package('oxid-esales/oxideshop-ce', 'dev', 'dev'), $shopDirectory);

        $this->assertFileExists($rootPath . '/config.inc.php');
    }

    public function testInstallDoesNotCopyClasses()
    {
        $structure = [
            'source/vendor/oxideshop_ce/source' => [
                'Core/Class.php' => '<?php',
                'Application/Model/Class.php' => '<?php',
                'Application/Controller/Class.php' => '<?php',
            ]
        ];
        vfsStream::setup('root', 777, ['projectRoot' => $this->prepareStructure($structure)]);

        $rootPath = vfsStream::url('root/projectRoot/source');
        $shopDirectory = "$rootPath/vendor/oxideshop_ce";

        $shopPreparator = new ShopInstaller(new Filesystem(), new NullIO, $rootPath);
        $shopPreparator->install(new Package('oxid-esales/oxideshop-ce', 'dev', 'dev'), $shopDirectory);

        $this->assertFileNotExists($rootPath . '/Core/Class.php');
        $this->assertFileNotExists($rootPath . '/Application/Model/Class.php');
        $this->assertFileNotExists($rootPath . '/Application/Controller/Class.php');
        $this->assertFileNotExists($rootPath . '/Application/Controller');
    }

    /**
     * @param array $structure
     *
     * @return array
     */
    public function prepareStructure($structure)
    {
        $newStructure = [];
        foreach ($structure as $path => $element) {
            $position = &$newStructure;
            foreach (explode('/', $path) as $part) {
                $position[$part] = [];
                $position = &$position[$part];
            }
            $position = strpos($path, '/') === false ? [] : $position;
            $position = is_array($element) ? $this->prepareStructure($element) : $element;
        }
        return $newStructure;
    }
}
