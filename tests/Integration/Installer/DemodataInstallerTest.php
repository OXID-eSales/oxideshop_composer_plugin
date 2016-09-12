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
use org\bovigo\vfs\vfsStream;
use OxidEsales\ComposerPlugin\Installer\DemodataInstaller;
use OxidEsales\ComposerPlugin\Installer\ThemeInstaller;
use Symfony\Component\Filesystem\Filesystem;

class DemodataInstallerTest extends \PHPUnit_Framework_TestCase
{
    const DEMODATA_PACKAGE_NAME = "oxid-esales/oxideshop-demodata";

    public function testChecksIfAlwaysInstalling()
    {
        $package = new Package(static::DEMODATA_PACKAGE_NAME, 'dev', 'dev');
        $demodataInstaller = new DemodataInstaller(new Filesystem(), new NullIO, null, $package);
        $this->assertFalse($demodataInstaller->isInstalled());
    }

    /**
     * @return array
     */
    public function providerChecksIfDemodataFilesExistsAfterInstallation()
    {
        return [
            [
                [],
                DemodataInstaller::PATH_TO_TARGET_DEMODATA,
                'example sql'
            ],
            [
                [],
                DemodataInstaller::PATH_TO_OUT_DIRECTORY . '/custom.txt',
                'example txt'
            ],
            [
                [DemodataInstaller::EXTRA_PARAMETER_KEY_ROOT => [
                    'demodata-target' => 'setup/sql_custom/demodata.sql'
                ]],
                'setup/sql_custom/demodata.sql',
                'example sql'
            ],
            [
                [DemodataInstaller::EXTRA_PARAMETER_KEY_ROOT => [
                    'out-target' => 'custom'
                ]],
                'custom/custom.txt',
                'example txt'
            ],
            [
                [DemodataInstaller::EXTRA_PARAMETER_KEY_ROOT => [
                    'out-target' => 'custom'
                ]],
                'custom/pictures/custom.txt',
                'example pictures directory txt'
            ],
        ];
    }

    /**
     * @param array $composerExtras
     * @param string $fileToCheck
     * @dataProvider providerChecksIfDemodataFilesExistsAfterInstallation
     */
    public function testChecksIfDemodataFilesExistsAfterInstallation($composerExtras, $fileToCheck, $content)
    {
        $rootPath = vfsStream::url('root/projectRoot');
        $eshopRootPath = "$rootPath/source";
        $this->simulateInstallation($composerExtras, $rootPath, $eshopRootPath);

        $assetsFile = "$eshopRootPath/$fileToCheck";
        $this->assertFileExists($assetsFile);
    }

    /**
     * @return StructurePreparator
     */
    protected function getStructurePreparator()
    {
        return new StructurePreparator();
    }

    /**
     * @param $composerExtras
     * @return string
     */
    protected function simulateInstallation($composerExtras, $rootPath, $eshopRootPath)
    {
        $structure = [
            'vendor/' . static::DEMODATA_PACKAGE_NAME => [
                'src' => [
                    'demodata.sql' => 'example sql',
                    'out' => [
                        'custom.txt' => 'example txt',
                        'custom_directory_name/custom.txt' => 'example subdirectory txt',
                        'pictures/custom.txt' => 'example pictures directory txt',
                    ]

                ]
            ]
        ];

        vfsStream::setup('root', 777, ['projectRoot' => $this->getStructurePreparator()->prepareStructure($structure)]);

        $package = new Package(static::DEMODATA_PACKAGE_NAME, 'dev', 'dev');
        $shopPreparator = new DemodataInstaller(new Filesystem(), new NullIO(), $eshopRootPath, $package);
        $package->setExtra($composerExtras);
        $demodataInVendor = "$rootPath/vendor/" . static::DEMODATA_PACKAGE_NAME;

        $shopPreparator->install($demodataInVendor);
    }
}
