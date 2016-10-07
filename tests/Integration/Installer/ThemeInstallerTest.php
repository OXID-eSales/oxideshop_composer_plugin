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
use OxidEsales\ComposerPlugin\Installer\ThemeInstaller;
use Symfony\Component\Filesystem\Filesystem;

class ThemeInstallerTest extends \PHPUnit_Framework_TestCase
{
    const THEME_NAME_IN_COMPOSER = "oxid-esales/flow-theme";

    public function testChecksIfThemeIsNotInstalled()
    {
        $structure = [
            'vendor/'.static::THEME_NAME_IN_COMPOSER.'/theme.php' => '<?php',
        ];
        vfsStream::setup('root', 777, ['projectRoot' => $this->getStructurePreparator()->prepareStructure($structure)]);
        $rootPath = vfsStream::url('root/projectRoot/source');

        $package = new Package(static::THEME_NAME_IN_COMPOSER, 'dev', 'dev');
        $themeInstaller = new ThemeInstaller(new Filesystem(), new NullIO, $rootPath, $package);
        $this->assertFalse($themeInstaller->isInstalled());
    }

    public function testChecksIfThemeIsInstalled()
    {
        $structure = [
            'source/application/views/flow-theme/theme.php' => '<?php',
            'vendor/'.static::THEME_NAME_IN_COMPOSER.'/theme.php' => '<?php'
        ];
        vfsStream::setup('root', 777, ['projectRoot' => $this->getStructurePreparator()->prepareStructure($structure)]);
        $rootPath = vfsStream::url('root/projectRoot/source');

        $package = new Package(static::THEME_NAME_IN_COMPOSER, 'dev', 'dev');
        $shopPreparator = new ThemeInstaller(new Filesystem(), new NullIO(), $rootPath, $package);
        $this->assertTrue($shopPreparator->isInstalled());
    }

    /**
     * @return array
     */
    public function providerChecksIfThemeFilesExistsAfterInstallation()
    {
        return [
            [
                [ThemeInstaller::EXTRA_PARAMETER_KEY_ROOT => [ThemeInstaller::EXTRA_PARAMETER_KEY_TARGET => 'flow']],
                'application/views/flow/theme.php',
                'out/flow/style.css'
            ],
            [
                [],
                'application/views/flow-theme/theme.php',
                'out/flow-theme/style.css'
            ],
            [
                [ThemeInstaller::EXTRA_PARAMETER_KEY_ROOT => [ThemeInstaller::EXTRA_PARAMETER_KEY_ASSETS => 'custom_directory_name']],
                'application/views/flow-theme/theme.php',
                'out/flow-theme/custom_style.css'
            ],
            [
                [ThemeInstaller::EXTRA_PARAMETER_KEY_ROOT => [
                    ThemeInstaller::EXTRA_PARAMETER_KEY_TARGET => 'flow',
                    ThemeInstaller::EXTRA_PARAMETER_KEY_ASSETS => 'custom_directory_name',
                ]],
                'application/views/flow/theme.php',
                'out/flow/custom_style.css'
            ],
        ];
    }

    /**
     * @param $composerExtras
     * @param $installedThemeMetadata
     * @param $assetsFile
     * @dataProvider providerChecksIfThemeFilesExistsAfterInstallation
     */
    public function testChecksIfThemeFilesExistsAfterInstallation($composerExtras, $installedThemeMetadata, $assetsFile)
    {
        $rootPath = vfsStream::url('root/projectRoot');
        $eshopRootPath = "$rootPath/source";
        $this->simulateInstallation($composerExtras, $rootPath, $eshopRootPath);

        $installedThemeMetadata = "$eshopRootPath/$installedThemeMetadata";
        $assetsFile = "$eshopRootPath/$assetsFile";
        $this->assertFileExists($installedThemeMetadata);
        $this->assertFileExists($assetsFile);
    }

    public function testChecksIfAssetFileDoesNotExist()
    {
        $composerExtras = [ThemeInstaller::EXTRA_PARAMETER_KEY_ROOT => [
            ThemeInstaller::EXTRA_PARAMETER_KEY_TARGET => 'flow',
            ThemeInstaller::EXTRA_PARAMETER_KEY_ASSETS => 'non_existing_directory',
        ]];

        $rootPath = vfsStream::url('root/projectRoot');
        $eshopRootPath = "$rootPath/source";
        $this->simulateInstallation($composerExtras, $rootPath, $eshopRootPath);
        $this->assertFileNotExists($eshopRootPath.'/out/non_existing_directory');
    }

    public function testChecksIfAssetsDirectoryWasNotCopied()
    {
        $composerExtras = [ThemeInstaller::EXTRA_PARAMETER_KEY_ROOT => [
            ThemeInstaller::EXTRA_PARAMETER_KEY_TARGET => 'flow'
        ]];

        $rootPath = vfsStream::url('root/projectRoot');
        $eshopRootPath = "$rootPath/source";
        $this->simulateInstallation($composerExtras, $rootPath, $eshopRootPath);
        $this->assertFileNotExists($eshopRootPath . '/application/views/flow/out/style.css');
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
            'vendor/' . static::THEME_NAME_IN_COMPOSER => [
                'theme.php' => '<?php',
                'out/style.css' => '.class {}',
                'custom_directory_name/custom_style.css' => '.class {}',
            ]
        ];
        vfsStream::setup('root', 777, ['projectRoot' => $this->getStructurePreparator()->prepareStructure($structure)]);

        $package = new Package(static::THEME_NAME_IN_COMPOSER, 'dev', 'dev');
        $shopPreparator = new ThemeInstaller(new Filesystem(), new NullIO(), $eshopRootPath, $package);
        $package->setExtra($composerExtras);
        $themeInVendor = "$rootPath/vendor/" . static::THEME_NAME_IN_COMPOSER;
        $shopPreparator->install($themeInVendor);
    }
}
