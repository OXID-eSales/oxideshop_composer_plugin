<?php
/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 */

namespace Tests\Integration;

use Composer\IO\NullIO;
use Composer\Package\Package;
use OxidEsales\ComposerPlugin\Installer\ShopInstaller;
use org\bovigo\vfs\vfsStream;
use Symfony\Component\Filesystem\Filesystem;

class ShopInstallerTest extends \PHPUnit_Framework_TestCase
{
    public function testIsInstalledWhenNotInstalled()
    {
        $structure = [
            'source/vendor/oxideshop_ce/source/index.php' => '<?php',
        ];
        vfsStream::setup('root', 777, ['projectRoot' => $this->prepareStructure($structure)]);
        $rootPath = vfsStream::url('root/projectRoot/source');

        $shopPreparator = new ShopInstaller(new Filesystem(), new NullIO, $rootPath);
        $this->assertFalse($shopPreparator->isInstalled(new Package('oxid-esales/oxideshop-ce', 'dev', 'dev')));
    }

    public function testIsInstalledWhenInstalled()
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

    public function testInstall()
    {
        $structure = [
            'source/vendor/oxideshop_ce/source' => [
                'index.php' => '<?php',
                'Application/TestClass.php' => '<?php',
                'Core/TestClass.php' => '<?php'
            ]
        ];
        vfsStream::setup('root', 777, ['projectRoot' => $this->prepareStructure($structure)]);

        $rootPath = vfsStream::url('root/projectRoot/source');
        $shopDirectory = "$rootPath/vendor/oxideshop_ce";

        $shopPreparator = new ShopInstaller(new Filesystem(), new NullIO, $rootPath);
        $shopPreparator->install(new Package('oxid-esales/oxideshop-ce', 'dev', 'dev'), $shopDirectory);

        $this->assertFileExists($rootPath . '/index.php');
        $this->assertFileExists($rootPath . '/Application/TestClass.php');
        $this->assertFileExists($rootPath . '/Core/TestClass.php');
    }

    public function testUpdateOverwritesCoreFiles()
    {
        $structure = [
            'source' => [
                'Core/TestClass.php' => '<?php old content',
                'Application/Model/TestClass.php' => '<?php old content',
                'vendor/oxideshop_ce/source' => [
                    'Core/TestClass.php' => '<?php new content',
                    'Application/Model/TestClass.php' => '<?php new content',
                ]
            ],
        ];
        vfsStream::setup('root', 777, ['projectRoot' => $this->prepareStructure($structure)]);

        $rootPath = vfsStream::url('root/projectRoot/source');
        $shopDirectory = "$rootPath/vendor/oxideshop_ce";

        $shopPreparator = new ShopInstaller(new Filesystem(), new NullIO, $rootPath);
        $shopPreparator->update(new Package('oxid-esales/oxideshop-ce', 'dev', 'dev'), $shopDirectory);

        $this->assertStringEqualsFile("$rootPath/Core/TestClass.php", "<?php new content");
        $this->assertStringEqualsFile("$rootPath/Application/Model/TestClass.php", "<?php new content");
    }

    public function testUpdateDeletesOldFilesInCoreDirectories()
    {
        $structure = [
            'source' => [
                'Core/OldClass.php' => '<?php',
                'Application/Model/OldClass.php' => '<?php',
                'vendor/oxideshop_ce/source' => [
                    'Core/NewClass.php' => '<?php',
                    'Application/Model/NewClass.php' => '<?php',
                ]
            ],
        ];
        vfsStream::setup('root', 777, ['projectRoot' => $this->prepareStructure($structure)]);

        $rootPath = vfsStream::url('root/projectRoot/source');
        $shopDirectory = "$rootPath/vendor/oxideshop_ce";

        $shopPreparator = new ShopInstaller(new Filesystem(), new NullIO, $rootPath);
        $shopPreparator->update(new Package('oxid-esales/oxideshop-ce', 'dev', 'dev'), $shopDirectory);

        $this->assertFileExists("$rootPath/Core/NewClass.php");
        $this->assertFileNotExists("$rootPath/Core/OldClass.php");
        $this->assertFileExists("$rootPath/Application/Model/NewClass.php");
        $this->assertFileNotExists("$rootPath/Application/Model/OldClass.php");
    }

    public function testUpdateDoesNotOverwriteUserConfigurableFiles()
    {
        $structure = [
            'source' => [
                'config.inc.php' => '<?php old content',
                'out/azure/myPicture.jpg' => 'old content',
                'Application/views/azure/template.tpl' => 'old content',
                'vendor/oxideshop_ce/source' => [
                    'config.inc.php' => '<?php new content',
                    'out/azure/myPicture.jpg' => 'new content',
                    'Application/views/azure/template.tpl' => 'new content',
                ],
            ]
        ];
        vfsStream::setup('root', 777, ['projectRoot' => $this->prepareStructure($structure)]);

        $rootPath = vfsStream::url('root/projectRoot/source');
        $shopDirectory = "$rootPath/vendor/oxideshop_ce";

        $shopPreparator = new ShopInstaller(new Filesystem(), new NullIO, $rootPath);
        $shopPreparator->update(new Package('oxid-esales/oxideshop-ce', 'dev', 'dev'), $shopDirectory);

        $this->assertStringEqualsFile("$rootPath/config.inc.php", "<?php old content");
        $this->assertStringEqualsFile("$rootPath/out/azure/myPicture.jpg", "old content");
        $this->assertStringEqualsFile("$rootPath/Application/views/azure/template.tpl", "old content");
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
