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

use Composer\IO\NullIO;
use Composer\Package\Package;
use OxidEsales\ComposerPlugin\Installer\Package\ModulePackageInstaller;
use org\bovigo\vfs\vfsStream;
use OxidEsales\ComposerPlugin\Tests\Integration\Installer\StructurePreparator;
use Webmozart\PathUtil\Path;

class ModulePackageInstallerTest extends \PHPUnit_Framework_TestCase
{
    const PRODUCT_NAME_IN_COMPOSER_FILE = "oxid-esales/paypal-module";

    public function testChecksIfModuleIsNotInstalled()
    {
        $structure = [
            'vendor/'.static::PRODUCT_NAME_IN_COMPOSER_FILE.'/metadata.php' => '<?php',
        ];
        vfsStream::setup('root', 777, ['projectRoot' => $this->getStructurePreparator()->prepareStructure($structure)]);
        $rootPath = vfsStream::url('root/projectRoot/source');

        $shopPreparator = new ModulePackageInstaller(new NullIO, $rootPath, new Package(static::PRODUCT_NAME_IN_COMPOSER_FILE, 'dev', 'dev'));
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

        $shopPreparator = new ModulePackageInstaller(new NullIO, $rootPath, new Package(static::PRODUCT_NAME_IN_COMPOSER_FILE, 'dev', 'dev'));
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
        $shopPreparator = new ModulePackageInstaller(new NullIO(), $eshopRootPath, $package);
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
        $shopPreparator = new ModulePackageInstaller(new NullIO(), $eshopRootPath, $package);
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

    /**
     * @return StructurePreparator
     */
    public function getStructurePreparator()
    {
        return new StructurePreparator();
    }

    public function testBlacklistedFilesArePresentWhenNoBlacklistFilterIsDefined()
    {
        $structure = [
            'vendor' => [
                'test-vendor' => [
                    'test-package' => [
                        'metadata.php' => 'meta data',
                        'module.php' => 'module',
                        'readme.txt' => 'readme',
                    ]
                ]
            ]
        ];

        vfsStream::setup('root', 777, ['projectRoot' => $structure]);

        $rootPath = vfsStream::url('root/projectRoot');
        $shopRootPath = Path::join($rootPath, 'source');
        $installedModulePath = Path::join($shopRootPath, 'modules', 'test-vendor', 'test-package');
        $moduleSourcePath = Path::join($rootPath, 'vendor', 'test-vendor', 'test-package');

        $package = new Package('test-vendor/test-package', 'dev', 'dev');
        $moduleInstaller = new ModulePackageInstaller(new NullIO(), $shopRootPath, $package);
        $moduleInstaller->install($moduleSourcePath);

        $this->assertFileExists(Path::join($installedModulePath, 'metadata.php'));
        $this->assertFileExists(Path::join($installedModulePath, 'module.php'));
        $this->assertFileExists(Path::join($installedModulePath, 'readme.txt'));
    }

    public function testBlacklistedFilesArePresentWhenEmptyBlacklistFilterIsDefined()
    {
        $structure = [
            'vendor' => [
                'test-vendor' => [
                    'test-package' => [
                        'metadata.php' => 'meta data',
                        'module.php' => 'module',
                        'readme.txt' => 'readme',
                    ]
                ]
            ]
        ];

        vfsStream::setup('root', 777, ['projectRoot' => $structure]);

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
        $moduleInstaller = new ModulePackageInstaller(new NullIO(), $shopRootPath, $package);
        $moduleInstaller->install($moduleSourcePath);

        $this->assertFileExists(Path::join($installedModulePath, 'metadata.php'));
        $this->assertFileExists(Path::join($installedModulePath, 'module.php'));
        $this->assertFileExists(Path::join($installedModulePath, 'readme.txt'));
    }

    public function testBlacklistedFilesArePresentWhenDifferentBlacklistFilterIsDefined()
    {
        $structure = [
            'vendor' => [
                'test-vendor' => [
                    'test-package' => [
                        'metadata.php' => 'meta data',
                        'module.php' => 'module',
                        'readme.txt' => 'readme',
                    ]
                ]
            ]
        ];

        vfsStream::setup('root', 777, ['projectRoot' => $structure]);

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
        $moduleInstaller = new ModulePackageInstaller(new NullIO(), $shopRootPath, $package);
        $moduleInstaller->install($moduleSourcePath);

        $this->assertFileExists(Path::join($installedModulePath, 'metadata.php'));
        $this->assertFileExists(Path::join($installedModulePath, 'module.php'));
        $this->assertFileExists(Path::join($installedModulePath, 'readme.txt'));
    }

    public function testBlacklistedFilesAreSkippedWhenABlacklistFilterIsDefined()
    {
        $structure = [
            'vendor' => [
                'test-vendor' => [
                    'test-package' => [
                        'metadata.php' => 'meta data',
                        'module.php' => 'module',
                        'readme.txt' => 'readme',
                    ]
                ]
            ]
        ];

        vfsStream::setup('root', 777, ['projectRoot' => $structure]);

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
        $moduleInstaller = new ModulePackageInstaller(new NullIO(), $shopRootPath, $package);
        $moduleInstaller->install($moduleSourcePath);

        $this->assertFileExists(Path::join($installedModulePath, 'metadata.php'));
        $this->assertFileExists(Path::join($installedModulePath, 'module.php'));
        $this->assertFileNotExists(Path::join($installedModulePath, 'readme.txt'));
    }
}
