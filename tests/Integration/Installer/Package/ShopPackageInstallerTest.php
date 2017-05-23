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
use org\bovigo\vfs\vfsStream;
use OxidEsales\ComposerPlugin\Tests\Integration\Installer\StructurePreparator;
use Webmozart\PathUtil\Path;

class ShopPackageInstallerTest extends \PHPUnit_Framework_TestCase
{
    protected function getSut(IOInterface $io, $rootPath, PackageInterface $package)
    {
        return new ShopPackageInstaller($io, $rootPath, $package);
    }

    public function testChecksIfPackageIsNotInstalled()
    {
        $structure = [
            'source/vendor/oxideshop_ce/source/index.php' => '<?php',
        ];
        vfsStream::setup('root', 777, ['projectRoot' => $this->getStructurePreparator()->prepareStructure($structure)]);
        $rootPath = vfsStream::url('root/projectRoot/source');

        $shopPreparator = $this->getSut(new NullIO, $rootPath, new Package('oxid-esales/oxideshop-ce', 'dev', 'dev'));
        $this->assertFalse($shopPreparator->isInstalled());
    }

    public function testChecksIfPackageInstalled()
    {
        $structure = [
            'source' => [
                'index.php' => '<?php',
                'vendor/oxideshop_ce/source/index.php' => '<?php',
            ]
        ];
        vfsStream::setup('root', 777, ['projectRoot' => $this->getStructurePreparator()->prepareStructure($structure)]);
        $rootPath = vfsStream::url('root/projectRoot/source');

        $shopPreparator = $this->getSut(new NullIO, $rootPath, new Package('oxid-esales/oxideshop-ce', 'dev', 'dev'));
        $this->assertTrue($shopPreparator->isInstalled());
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
        vfsStream::setup('root', 777, ['projectRoot' => $this->getStructurePreparator()->prepareStructure($structure)]);

        $rootPath = vfsStream::url('root/projectRoot/source');
        $shopDirectory = "$rootPath/vendor/oxideshop_ce";

        $shopPreparator = $this->getSut(new NullIO, $rootPath, new Package('oxid-esales/oxideshop-ce', 'dev', 'dev'));
        $shopPreparator->install($shopDirectory);

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
        vfsStream::setup('root', 777, ['projectRoot' => $this->getStructurePreparator()->prepareStructure($structure)]);

        $rootPath = vfsStream::url('root/projectRoot/source');
        $shopDirectory = "$rootPath/vendor/oxideshop_ce";

        $shopPreparator = $this->getSut(new NullIO, $rootPath, new Package('oxid-esales/oxideshop-ce', 'dev', 'dev'));
        $shopPreparator->install($shopDirectory);

        $this->assertFileExists($rootPath . '/config.inc.php');
    }

    public function testInstallDoesNotCopyClasses()
    {
        $structure = [
            'source' => [
                'vendor' => [
                    'oxideshop_ce' => [
                        'source' => [
                            'Class.php' => 'Class',
                            'Core' => [
                                'Class.php' => 'Class',
                            ],
                            'Application' => [
                                'Model' => [
                                    'Class.php' => 'Class',
                                ],
                                'Controller' => [
                                    'Class.php' => 'Class'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        vfsStream::setup('root', 777, ['projectRoot' => $structure]);

        $rootPath = vfsStream::url('root/projectRoot/source');
        $shopDirectory = "$rootPath/vendor/oxideshop_ce";

        $package = new Package('oxid-esales/oxideshop-ce', 'dev', 'dev');
        $package->setExtra([
            'oxideshop' => [
                'blacklist-filter' => [
                    "Application/Component/**/*.*",
                    "Application/Controller/**/*.*",
                    "Application/Model/**/*.*",
                    "Core/**/*.*"
                ]
            ]
        ]);

        $sut = $this->getSut(new NullIO, $rootPath, $package);
        $sut->install($shopDirectory);

        $this->assertFileExists(Path::join($rootPath, 'Class.php'));
        $this->assertFileNotExists(Path::join($rootPath, 'Core'));
        $this->assertFileNotExists(Path::join($rootPath, 'Core', 'Class.php'));
        $this->assertFileNotExists(Path::join($rootPath, 'Application', 'Model'));
        $this->assertFileNotExists(Path::join($rootPath, 'Application', 'Model', 'Class.php'));
        $this->assertFileNotExists(Path::join($rootPath, 'Application', 'Controller'));
        $this->assertFileNotExists(Path::join($rootPath, 'Application', 'Controller', 'Class.php'));
    }

    /**
     * @return StructurePreparator
     */
    public function getStructurePreparator()
    {
        return new StructurePreparator();
    }
}
