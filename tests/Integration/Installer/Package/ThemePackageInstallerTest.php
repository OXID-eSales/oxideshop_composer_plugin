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
use org\bovigo\vfs\vfsStream;
use OxidEsales\ComposerPlugin\Installer\Package\ThemePackageInstaller;
use OxidEsales\ComposerPlugin\Utilities\VfsFileStructureOperator;
use Webmozart\PathUtil\Path;

class ThemePackageInstallerTest extends \PHPUnit_Framework_TestCase
{
    protected function getSut(IOInterface $io, $rootPath, PackageInterface $package)
    {
        return new ThemePackageInstaller($io, $rootPath, $package);
    }

    public function testChecksIfThemeIsNotInstalled()
    {
        vfsStream::setup('root', 777, VfsFileStructureOperator::nest([
            'projectRoot/vendor/'."oxid-esales/flow-theme".'/theme.php' => '<?php'
        ]));

        $rootPath = vfsStream::url('root/projectRoot/source');

        $package = new Package("oxid-esales/flow-theme", 'dev', 'dev');
        $themeInstaller = $this->getSut(new NullIO, $rootPath, $package);
        $this->assertFalse($themeInstaller->isInstalled());
    }

    public function testChecksIfThemeIsInstalled()
    {
        vfsStream::setup('root', 777, VfsFileStructureOperator::nest([
            'projectRoot/source/Application/views/flow-theme/theme.php' => '<?php',
            'projectRoot/vendor/oxid-esales/flow-theme/theme.php' => '<?php'
        ]));

        $rootPath = vfsStream::url('root/projectRoot/source');

        $package = new Package("oxid-esales/flow-theme", 'dev', 'dev');
        $shopPreparator = $this->getSut(new NullIO(), $rootPath, $package);
        $this->assertTrue($shopPreparator->isInstalled());
    }

    /**
     * @return array
     */
    public function providerChecksIfThemeFilesExistsAfterInstallation()
    {
        return [
            [
                [ThemePackageInstaller::EXTRA_PARAMETER_KEY_ROOT => [ThemePackageInstaller::EXTRA_PARAMETER_KEY_TARGET => 'flow']],
                'Application/views/flow/theme.php',
                'out/flow/style.css'
            ],
            [
                [],
                'Application/views/flow-theme/theme.php',
                'out/flow-theme/style.css'
            ],
            [
                [ThemePackageInstaller::EXTRA_PARAMETER_KEY_ROOT => [ThemePackageInstaller::EXTRA_PARAMETER_KEY_ASSETS => 'custom_directory_name']],
                'Application/views/flow-theme/theme.php',
                'out/flow-theme/custom_style.css'
            ],
            [
                [ThemePackageInstaller::EXTRA_PARAMETER_KEY_ROOT => [
                    ThemePackageInstaller::EXTRA_PARAMETER_KEY_TARGET => 'flow',
                    ThemePackageInstaller::EXTRA_PARAMETER_KEY_ASSETS => 'custom_directory_name',
                ]],
                'Application/views/flow/theme.php',
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
        $composerExtras = [ThemePackageInstaller::EXTRA_PARAMETER_KEY_ROOT => [
            ThemePackageInstaller::EXTRA_PARAMETER_KEY_TARGET => 'flow',
            ThemePackageInstaller::EXTRA_PARAMETER_KEY_ASSETS => 'non_existing_directory',
        ]];

        $rootPath = vfsStream::url('root/projectRoot');
        $eshopRootPath = "$rootPath/source";
        $this->simulateInstallation($composerExtras, $rootPath, $eshopRootPath);
        $this->assertFileNotExists($eshopRootPath.'/out/non_existing_directory');
    }

    public function testChecksIfAssetsDirectoryWasNotCopied()
    {
        $composerExtras = [ThemePackageInstaller::EXTRA_PARAMETER_KEY_ROOT => [
            ThemePackageInstaller::EXTRA_PARAMETER_KEY_TARGET => 'flow'
        ]];

        $rootPath = vfsStream::url('root/projectRoot');
        $eshopRootPath = "$rootPath/source";
        $this->simulateInstallation($composerExtras, $rootPath, $eshopRootPath);
        $this->assertFileNotExists($eshopRootPath . '/Application/views/flow/out/style.css');
    }

    /**
     * @param $composerExtras
     * @return string
     */
    protected function simulateInstallation($composerExtras, $rootPath, $eshopRootPath)
    {
        vfsStream::setup('root', 777, VfsFileStructureOperator::nest([
            'projectRoot/vendor/oxid-esales/flow-theme/theme.php' => '<?php',
            'projectRoot/vendor/oxid-esales/flow-theme/readme.txt' => 'readme',
            'projectRoot/vendor/oxid-esales/flow-theme/out/style.css' => '.class {}',
            'projectRoot/vendor/oxid-esales/flow-theme/out/readme.pdf' => 'PDF',
            'projectRoot/vendor/oxid-esales/flow-theme/custom_directory_name/custom_style.css' => '.class {}',
        ]));

        $package = new Package("oxid-esales/flow-theme", 'dev', 'dev');
        $shopPreparator = $this->getSut(new NullIO(), $eshopRootPath, $package);
        $package->setExtra($composerExtras);
        $themeInVendor = "$rootPath/vendor/oxid-esales/flow-theme";
        $shopPreparator->install($themeInVendor);
    }

    public function testBlacklistedFilesArePresentWhenNoBlacklistFilterIsDefined()
    {
        $composerExtraParameters = [
            'oxideshop' => [
                'target-directory' => 'flow',
            ]
        ];

        $rootPath = vfsStream::url('root/projectRoot');
        $shopRootPath = Path::join($rootPath, 'source');

        $this->simulateInstallation($composerExtraParameters, $rootPath, $shopRootPath);

        $this->assertFileExists(Path::join($shopRootPath, 'Application', 'views', 'flow', 'readme.txt'));
        $this->assertFileExists(Path::join($shopRootPath, 'out', 'flow', 'readme.pdf'));
    }

    public function testBlacklistedFilesArePresentWhenEmptyBlacklistFilterIsDefined()
    {
        $composerExtraParameters = [
            'oxideshop' => [
                'target-directory' => 'flow',
                'blacklist-filter' => [],
            ]
        ];

        $rootPath = vfsStream::url('root/projectRoot');
        $shopRootPath = Path::join($rootPath, 'source');

        $this->simulateInstallation($composerExtraParameters, $rootPath, $shopRootPath);

        $this->assertFileExists(Path::join($shopRootPath, 'Application', 'views', 'flow', 'readme.txt'));
        $this->assertFileExists(Path::join($shopRootPath, 'out', 'flow', 'readme.pdf'));
    }

    public function testBlacklistedFilesArePresentWhenDifferentBlacklistFilterIsDefined()
    {
        $composerExtraParameters = [
            'oxideshop' => [
                'target-directory' => 'flow',
                'blacklist-filter' => [
                    '**/*.doc'
                ],
            ]
        ];

        $rootPath = vfsStream::url('root/projectRoot');
        $shopRootPath = Path::join($rootPath, 'source');

        $this->simulateInstallation($composerExtraParameters, $rootPath, $shopRootPath);

        $this->assertFileExists(Path::join($shopRootPath, 'Application', 'views', 'flow', 'readme.txt'));
        $this->assertFileExists(Path::join($shopRootPath, 'out', 'flow', 'readme.pdf'));
    }

    public function testBlacklistedFilesAreSkippedWhenABlacklistFilterIsDefined()
    {
        $composerExtraParameters = [
            'oxideshop' => [
                'target-directory' => 'flow',
                'blacklist-filter' => [
                    "**/*.txt",
                    "**/*.pdf",
                ],
            ]
        ];

        $rootPath = vfsStream::url('root/projectRoot');
        $shopRootPath = Path::join($rootPath, 'source');

        $this->simulateInstallation($composerExtraParameters, $rootPath, $shopRootPath);

        $this->assertFileNotExists(Path::join($shopRootPath, 'Application', 'views', 'flow', 'readme.txt'));
        $this->assertFileNotExists(Path::join($shopRootPath, 'out', 'flow', 'readme.pdf'));
    }
}
