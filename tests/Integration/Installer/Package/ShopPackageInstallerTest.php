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

class ShopPackageInstallerTest extends \PHPUnit_Framework_TestCase
{
    protected function getSut(IOInterface $io, $rootPath, PackageInterface $package)
    {
        return new ShopPackageInstaller($io, $rootPath, $package);
    }

    public function testChecksIfPackageIsNotInstalled()
    {
        vfsStream::setup('root', 777, VfsFileStructureOperator::nest([
            'projectRoot/source/vendor/oxideshop_ce/source/index.php' => '<?php',
        ]));

        $rootPath = vfsStream::url('root/projectRoot/source');

        $shopPreparator = $this->getSut(new NullIO, $rootPath, new Package('oxid-esales/oxideshop-ce', 'dev', 'dev'));
        $this->assertFalse($shopPreparator->isInstalled());
    }

    public function testChecksIfPackageInstalled()
    {
        vfsStream::setup('root', 777, VfsFileStructureOperator::nest([
            'projectRoot/source/index.php' => '<?php',
            'projectRoot/source/vendor/oxideshop_ce/source/index.php' => '<?php',
        ]));

        $rootPath = vfsStream::url('root/projectRoot/source');

        $shopPreparator = $this->getSut(new NullIO, $rootPath, new Package('oxid-esales/oxideshop-ce', 'dev', 'dev'));
        $this->assertTrue($shopPreparator->isInstalled());
    }

    public function testInstallationOfPackage()
    {
        vfsStream::setup('root', 777, VfsFileStructureOperator::nest([
            'projectRoot/source/vendor/oxideshop_ce/source/index.php' => '<?php',
            'projectRoot/source/vendor/oxideshop_ce/source/Application/views/template.tpl' => '<?php',
            'projectRoot/source/vendor/oxideshop_ce/source/config.inc.php.dist' => '<?php',
        ]));

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
        vfsStream::setup('root', 777, VfsFileStructureOperator::nest([
            'projectRoot/source/vendor/oxideshop_ce/source/config.inc.php.dist' => '<?php',
        ]));

        $rootPath = vfsStream::url('root/projectRoot/source');
        $shopDirectory = "$rootPath/vendor/oxideshop_ce";

        $shopPreparator = $this->getSut(new NullIO, $rootPath, new Package('oxid-esales/oxideshop-ce', 'dev', 'dev'));
        $shopPreparator->install($shopDirectory);

        $this->assertFileExists($rootPath . '/config.inc.php');
    }

    public function testInstallDoesNotCopyClasses()
    {
        vfsStream::setup('root', 777, VfsFileStructureOperator::nest([
            'projectRoot/source/vendor/oxideshop_ce/source/Class.php' => 'Class',
            'projectRoot/source/vendor/oxideshop_ce/source/Core/Class.php' => 'Class',
            'projectRoot/source/vendor/oxideshop_ce/source/Application/Model/Class.php' => 'Class',
            'projectRoot/source/vendor/oxideshop_ce/source/Application/Controller/Class.php' => 'Class',
        ]));

        $rootPath = vfsStream::url('root/projectRoot/source');
        $shopDirectory = "$rootPath/vendor/oxideshop_ce";

        $sut = $this->getSut(new NullIO, $rootPath, $this->getShopPackage());
        $sut->install($shopDirectory);

        $this->assertFileExists(Path::join($rootPath, 'Class.php'));
        $this->assertFileNotExists(Path::join($rootPath, 'Core'));
        $this->assertFileNotExists(Path::join($rootPath, 'Core', 'Class.php'));
        $this->assertFileNotExists(Path::join($rootPath, 'Application', 'Model'));
        $this->assertFileNotExists(Path::join($rootPath, 'Application', 'Model', 'Class.php'));
        $this->assertFileNotExists(Path::join($rootPath, 'Application', 'Controller'));
        $this->assertFileNotExists(Path::join($rootPath, 'Application', 'Controller', 'Class.php'));
    }

    public function testInstallCopiesHtaccessFilesDuringFirstRun()
    {
        vfsStream::setup('root', 777, VfsFileStructureOperator::nest([
            'projectRoot/vendor/oxid-esales/oxideshop-ce/source/.htaccess' => 'Original htaccess',
            'projectRoot/vendor/oxid-esales/oxideshop-ce/source/bin/.htaccess' => 'Original htaccess',
            'projectRoot/vendor/oxid-esales/oxideshop-ce/source/cache/.htaccess' => 'Original htaccess',
            'projectRoot/vendor/oxid-esales/oxideshop-ce/source/out/downloads/.htaccess' => 'Original htaccess',
            'projectRoot/vendor/oxid-esales/oxideshop-ce/source/Application/views/admin/tpl/.htaccess' => 'Original htaccess',
            'projectRoot/vendor/oxid-esales/oxideshop-ce/source/test/.htaccess' => 'Original htaccess',
        ]));

        $projectRoot = vfsStream::url(Path::join('root', 'projectRoot'));
        $installedShopSourcePath = Path::join($projectRoot, 'source');
        $packageShopRootPath = Path::join($projectRoot, 'vendor', 'oxid-esales', 'oxideshop-ce');
        $packageShopSourcePath = Path::join($packageShopRootPath, 'source');

        $sut = $this->getSut(new NullIO, $installedShopSourcePath, $this->getShopPackage());
        $sut->install($packageShopRootPath);

        $this->assertFileEquals(
            Path::join($packageShopSourcePath, '.htaccess'),
            Path::join($installedShopSourcePath, '.htaccess')
        );
        $this->assertFileEquals(
            Path::join($packageShopSourcePath, 'bin', '.htaccess'),
            Path::join($installedShopSourcePath, 'bin', '.htaccess')
        );
        $this->assertFileEquals(
            Path::join($packageShopSourcePath, 'cache', '.htaccess'),
            Path::join($installedShopSourcePath, 'cache', '.htaccess')
        );
        $this->assertFileEquals(
            Path::join($packageShopSourcePath, 'out', 'downloads', '.htaccess'),
            Path::join($installedShopSourcePath, 'out', 'downloads', '.htaccess')
        );
        $this->assertFileEquals(
            Path::join($packageShopSourcePath, 'Application', 'views', 'admin', 'tpl', '.htaccess'),
            Path::join($installedShopSourcePath, 'Application', 'views', 'admin', 'tpl', '.htaccess')
        );
        $this->assertFileEquals(
            Path::join($packageShopSourcePath, 'test', '.htaccess'),
            Path::join($installedShopSourcePath, 'test', '.htaccess')
        );
    }

    public function testInstallDoesNotCopyHtaccessFilesDuringSecondRun()
    {
        vfsStream::setup('root', 777, VfsFileStructureOperator::nest([
            'projectRoot/vendor/oxid-esales/oxideshop-ce/source/.htaccess' => 'Original htaccess',
            'projectRoot/vendor/oxid-esales/oxideshop-ce/source/bin/.htaccess' => 'Original htaccess',
            'projectRoot/vendor/oxid-esales/oxideshop-ce/source/cache/.htaccess' => 'Original htaccess',
            'projectRoot/vendor/oxid-esales/oxideshop-ce/source/out/downloads/.htaccess' => 'Original htaccess',
            'projectRoot/vendor/oxid-esales/oxideshop-ce/source/Application/views/admin/tpl/.htaccess' => 'Original htaccess',
            'projectRoot/vendor/oxid-esales/oxideshop-ce/source/test/.htaccess' => 'Original htaccess',
        ]));

        $projectRoot = vfsStream::url(Path::join('root', 'projectRoot'));
        $installedShopSourcePath = Path::join($projectRoot, 'source');
        $packageShopRootPath = Path::join($projectRoot, 'vendor', 'oxid-esales', 'oxideshop-ce');
        $packageShopSourcePath = Path::join($packageShopRootPath, 'source');

        $sut = $this->getSut(new NullIO, $installedShopSourcePath, $this->getShopPackage());
        $sut->install($packageShopRootPath);
        file_put_contents(Path::join($installedShopSourcePath, '.htaccess'), 'Updated htaccess');
        file_put_contents(Path::join($installedShopSourcePath, 'bin', '.htaccess'), 'Updated htaccess');
        file_put_contents(Path::join($installedShopSourcePath, 'cache', '.htaccess'), 'Updated htaccess');
        file_put_contents(Path::join($installedShopSourcePath, 'out', 'downloads', '.htaccess'), 'Updated htaccess');
        file_put_contents(
            Path::join($installedShopSourcePath, 'Application', 'views', 'admin', 'tpl', '.htaccess'),
            'Updated htaccess'
        );
        file_put_contents(Path::join($installedShopSourcePath, 'test', '.htaccess'), 'Updated htaccess');
        $sut->install($packageShopRootPath);

        $this->assertFileNotEquals(
            Path::join($packageShopSourcePath, '.htaccess'),
            Path::join($installedShopSourcePath, '.htaccess')
        );
        $this->assertFileNotEquals(
            Path::join($packageShopSourcePath, 'bin', '.htaccess'),
            Path::join($installedShopSourcePath, 'bin', '.htaccess')
        );
        $this->assertFileNotEquals(
            Path::join($packageShopSourcePath, 'cache', '.htaccess'),
            Path::join($installedShopSourcePath, 'cache', '.htaccess')
        );
        $this->assertFileNotEquals(
            Path::join($packageShopSourcePath, 'out', 'downloads', '.htaccess'),
            Path::join($installedShopSourcePath, 'out', 'downloads', '.htaccess')
        );
        $this->assertFileNotEquals(
            Path::join($packageShopSourcePath, 'Application', 'views', 'admin', 'tpl', '.htaccess'),
            Path::join($installedShopSourcePath, 'Application', 'views', 'admin', 'tpl', '.htaccess')
        );
        $this->assertFileNotEquals(
            Path::join($packageShopSourcePath, 'test', '.htaccess'),
            Path::join($installedShopSourcePath, 'test', '.htaccess')
        );
    }

    public function testInstallCopiesSetupFilesIfShopConfigIsMissing()
    {
        vfsStream::setup('root', 777, VfsFileStructureOperator::nest([
            'projectRoot/vendor/oxid-esales/oxideshop-ce/source/config.inc.php.dist' => 'Example config',
            'projectRoot/vendor/oxid-esales/oxideshop-ce/source/Setup/index.php' => 'Setup index.php file',
        ]));

        $projectRoot = vfsStream::url(Path::join('root', 'projectRoot'));
        $installedShopSourcePath = Path::join($projectRoot, 'source');
        $packageShopRootPath = Path::join($projectRoot, 'vendor', 'oxid-esales', 'oxideshop-ce');
        $packageShopSourcePath = Path::join($packageShopRootPath, 'source');

        $sut = $this->getSut(new NullIO, $installedShopSourcePath, $this->getShopPackage());
        $sut->install($packageShopRootPath);

        $this->assertFileEquals(
            Path::join($packageShopSourcePath, Path::join('Setup', 'index.php')),
            Path::join($installedShopSourcePath, Path::join('Setup', 'index.php'))
        );
    }

    public function testInstallOverwritesSetupFilesIfShopConfigIsMissing()
    {
        vfsStream::setup('root', 777, VfsFileStructureOperator::nest([
            'projectRoot/vendor/oxid-esales/oxideshop-ce/source/config.inc.php.dist' => 'Example config',
            'projectRoot/vendor/oxid-esales/oxideshop-ce/source/Setup/index.php' => 'Setup index.php file',
            'projectRoot/source/Setup/index.php' => 'Old Setup index.php file',
        ]));

        $projectRoot = vfsStream::url(Path::join('root', 'projectRoot'));
        $installedShopSourcePath = Path::join($projectRoot, 'source');
        $packageShopRootPath = Path::join($projectRoot, 'vendor', 'oxid-esales', 'oxideshop-ce');
        $packageShopSourcePath = Path::join($packageShopRootPath, 'source');

        $sut = $this->getSut(new NullIO, $installedShopSourcePath, $this->getShopPackage());

        $this->assertFileNotEquals(
            Path::join($packageShopSourcePath, Path::join('Setup', 'index.php')),
            Path::join($installedShopSourcePath, Path::join('Setup', 'index.php'))
        );
        $sut->install($packageShopRootPath);
        $this->assertFileEquals(
            Path::join($packageShopSourcePath, Path::join('Setup', 'index.php')),
            Path::join($installedShopSourcePath, Path::join('Setup', 'index.php'))
        );
    }

    public function testInstallCopiesSetupFilesIfShopConfigIsNotConfigured()
    {
        vfsStream::setup('root', 777, VfsFileStructureOperator::nest([
            'projectRoot/vendor/oxid-esales/oxideshop-ce/source/config.inc.php.dist' => 'Example config',
            'projectRoot/vendor/oxid-esales/oxideshop-ce/source/Setup/index.php' => 'Setup index.php file',
            'projectRoot/source/config.inc.php' => <<<'EOT'
    $this->dbType = 'pdo_mysql';
    $this->dbHost = '<dbHost>';
    $this->dbPort  = 3306;
    $this->dbName = '<dbName>';
    $this->dbUser = '<dbUser>';
    $this->dbPwd  = '<dbPwd>';
    $this->sShopURL     = '<sShopURL>';
    $this->sShopDir     = '<sShopDir>';
    $this->sCompileDir  = '<sCompileDir>';
EOT
        ]));

        $projectRoot = vfsStream::url(Path::join('root', 'projectRoot'));
        $installedShopSourcePath = Path::join($projectRoot, 'source');
        $packageShopRootPath = Path::join($projectRoot, 'vendor', 'oxid-esales', 'oxideshop-ce');
        $packageShopSourcePath = Path::join($packageShopRootPath, 'source');

        $sut = $this->getSut(new NullIO, $installedShopSourcePath, $this->getShopPackage());
        $sut->install($packageShopRootPath);

        $this->assertFileEquals(
            Path::join($packageShopSourcePath, Path::join('Setup', 'index.php')),
            Path::join($installedShopSourcePath, Path::join('Setup', 'index.php'))
        );
    }

    public function testInstallOverwritesSetupFilesIfShopConfigIsNotConfigured()
    {
        vfsStream::setup('root', 777, VfsFileStructureOperator::nest([
            'projectRoot/vendor/oxid-esales/oxideshop-ce/source/config.inc.php.dist' => 'Example config',
            'projectRoot/vendor/oxid-esales/oxideshop-ce/source/Setup/index.php' => 'Setup index.php file',

            'projectRoot/source/Setup/index.php' => 'Old Setup index.php file',
            'projectRoot/source/config.inc.php' => <<<'EOT'
    $this->dbType = 'pdo_mysql';
    $this->dbHost = '<dbHost>';
    $this->dbPort  = 3306;
    $this->dbName = '<dbName>';
    $this->dbUser = '<dbUser>';
    $this->dbPwd  = '<dbPwd>';
    $this->sShopURL     = '<sShopURL>';
    $this->sShopDir     = '<sShopDir>';
    $this->sCompileDir  = '<sCompileDir>';
EOT
        ]));

        $projectRoot = vfsStream::url(Path::join('root', 'projectRoot'));
        $installedShopSourcePath = Path::join($projectRoot, 'source');
        $packageShopRootPath = Path::join($projectRoot, 'vendor', 'oxid-esales', 'oxideshop-ce');
        $packageShopSourcePath = Path::join($packageShopRootPath, 'source');

        $sut = $this->getSut(new NullIO, $installedShopSourcePath, $this->getShopPackage());

        $this->assertFileNotEquals(
            Path::join($packageShopSourcePath, Path::join('Setup', 'index.php')),
            Path::join($installedShopSourcePath, Path::join('Setup', 'index.php'))
        );
        $sut->install($packageShopRootPath);
        $this->assertFileEquals(
            Path::join($packageShopSourcePath, Path::join('Setup', 'index.php')),
            Path::join($installedShopSourcePath, Path::join('Setup', 'index.php'))
        );
    }

    public function testInstallDoesNotCopySetupFilesIfShopConfigIsConfigured()
    {
        vfsStream::setup('root', 777, VfsFileStructureOperator::nest([
            'projectRoot/vendor/oxid-esales/oxideshop-ce/source/config.inc.php.dist' => 'Example config',
            'projectRoot/vendor/oxid-esales/oxideshop-ce/source/Setup/index.php' => 'Setup index.php file',
            'projectRoot/source/config.inc.php' => <<<'EOT'
    $this->dbType = 'pdo_mysql';
    $this->dbHost = 'somehost';
    $this->dbPort  = 3306;
    $this->dbName = 'some_db_name';
    $this->dbUser = 'db_user';
    $this->dbPwd  = 'db_pass';
    $this->sShopURL     = 'http://some_url/';
    $this->sShopDir     = '/var/www/shopdir/source';
    $this->sCompileDir  = '/var/www/shopdir/source/tmp';
EOT
        ]));

        $projectRoot = vfsStream::url(Path::join('root', 'projectRoot'));
        $installedShopSourcePath = Path::join($projectRoot, 'source');
        $packageShopRootPath = Path::join($projectRoot, 'vendor', 'oxid-esales', 'oxideshop-ce');
        $packageShopSourcePath = Path::join($packageShopRootPath, 'source');

        $sut = $this->getSut(new NullIO, $installedShopSourcePath, $this->getShopPackage());
        $sut->install($packageShopRootPath);

        $this->assertFileNotExists(Path::join($installedShopSourcePath, Path::join('Setup', 'index.php')));
    }

    public function testInstallDoesNotOverwriteSetupFilesIfShopConfigIsConfigured()
    {
        vfsStream::setup('root', 777, VfsFileStructureOperator::nest([
            'projectRoot/vendor/oxid-esales/oxideshop-ce/source/config.inc.php.dist' => 'Example config',
            'projectRoot/vendor/oxid-esales/oxideshop-ce/source/Setup/index.php' => 'Setup index.php file',
            'projectRoot/source/Setup/index.php' => 'Old Setup index.php file',
            'projectRoot/source/config.inc.php' => <<<'EOT'
    $this->dbType = 'pdo_mysql';
    $this->dbHost = 'somehost';
    $this->dbPort  = 3306;
    $this->dbName = 'some_db_name';
    $this->dbUser = 'db_user';
    $this->dbPwd  = 'db_pass';
    $this->sShopURL     = 'http://some_url/';
    $this->sShopDir     = '/var/www/shopdir/source';
    $this->sCompileDir  = '/var/www/shopdir/source/tmp';
EOT
        ]));

        $projectRoot = vfsStream::url(Path::join('root', 'projectRoot'));
        $installedShopSourcePath = Path::join($projectRoot, 'source');
        $packageShopRootPath = Path::join($projectRoot, 'vendor', 'oxid-esales', 'oxideshop-ce');
        $packageShopSourcePath = Path::join($packageShopRootPath, 'source');

        $sut = $this->getSut(new NullIO, $installedShopSourcePath, $this->getShopPackage());

        $this->assertFileNotEquals(
            Path::join($packageShopSourcePath, Path::join('Setup', 'index.php')),
            Path::join($installedShopSourcePath, Path::join('Setup', 'index.php'))
        );
        $sut->install($packageShopRootPath);
        $this->assertFileNotEquals(
            Path::join($packageShopSourcePath, Path::join('Setup', 'index.php')),
            Path::join($installedShopSourcePath, Path::join('Setup', 'index.php'))
        );
    }

    /**
     * @return Package
     */
    private function getShopPackage()
    {
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

        return $package;
    }
}
