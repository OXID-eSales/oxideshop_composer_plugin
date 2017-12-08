<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ComposerPlugin\Tests\Integration\Installer\Package;

use Composer\IO\NullIO;
use Composer\Package\Package;
use OxidEsales\ComposerPlugin\Installer\Package\ModulePackageInstaller;

class ModulePackageInstallerTest extends AbstractPackageInstallerTest
{
    protected function getPackageInstaller($packageName, $version = '1.0.0', $extra = [])
    {
        $package = new Package($packageName, $version, $version);
        $package->setExtra($extra);

        return new ModulePackageInstaller(
            new NullIO(),
            $this->getVirtualShopSourcePath(),
            $package
        );
    }
    
    public function testModuleNotInstalledByDefault()
    {
        $installer = $this->getPackageInstaller('test-vendor/test-package');

        $this->assertFalse($installer->isInstalled());
    }

    public function testModuleIsInstalledIfAlreadyExistsInShop()
    {
        $this->setupVirtualProjectRoot('source/modules/test-vendor/test-package', [
            'metadata.php' => '<?php'
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package');

        $this->assertTrue($installer->isInstalled());
    }

    public function testModuleIsInstalledAfterInstallProcess()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'metadata.php' => '<?php'
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package');
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertTrue($installer->isInstalled());
    }

    public function testModuleFilesAreCopiedAfterInstallProcess()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'metadata.php' => '<?php'
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package');
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileEquals(
            'vendor/test-vendor/test-package/metadata.php',
            'source/modules/test-vendor/test-package/metadata.php'
        );
    }

    public function testModuleFilesAreCopiedAfterInstallProcessWithSameSourceDirectory()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'metadata.php' => '<?php'
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package', '1.0.0', [
            'oxideshop' => [
                'source-directory' => ''
            ]
        ]);
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileEquals(
            'vendor/test-vendor/test-package/metadata.php',
            'source/modules/test-vendor/test-package/metadata.php'
        );
    }

    public function testModuleFilesAreCopiedAfterInstallProcessWithSameTargetDirectory()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'metadata.php' => '<?php'
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package', '1.0.0', [
            'oxideshop' => [
                'target-directory' => 'test-vendor/test-package'
            ]
        ]);
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileEquals(
            'vendor/test-vendor/test-package/metadata.php',
            'source/modules/test-vendor/test-package/metadata.php'
        );
    }

    public function testModuleFilesAreCopiedAfterInstallProcessWithSameSourceDirectoryAndSameTargetDirectory()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'metadata.php' => '<?php'
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package', '1.0.0', [
            'oxideshop' => [
                'source-directory' => '',
                'target-directory' => 'test-vendor/test-package'
            ]
        ]);
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileEquals(
            'vendor/test-vendor/test-package/metadata.php',
            'source/modules/test-vendor/test-package/metadata.php'
        );
    }

    public function testModuleFilesAreCopiedAfterInstallProcessWithCustomSourceDirectory()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package/custom-root', [
            'metadata.php' => '<?php'
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package', '1.0.0', [
            'oxideshop' => [
                'source-directory' => 'custom-root',
            ]
        ]);
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileEquals(
            'vendor/test-vendor/test-package/custom-root/metadata.php',
            'source/modules/test-vendor/test-package/metadata.php'
        );
    }

    public function testModuleFilesAreCopiedAfterInstallProcessWithCustomTargetDirectory()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'metadata.php' => '<?php'
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package', '1.0.0', [
            'oxideshop' => [
                'target-directory' => 'custom-vendor/custom-package',
            ]
        ]);
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileEquals(
            'vendor/test-vendor/test-package/metadata.php',
            'source/modules/custom-vendor/custom-package/metadata.php'
        );
    }

    public function testModuleFilesAreCopiedAfterInstallProcessWithCustomSourceDirectoryAndCustomTargetDirectory()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package/custom-root', [
            'metadata.php' => '<?php'
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package', '1.0.0', [
            'oxideshop' => [
                'source-directory' => 'custom-root',
                'target-directory' => 'custom-vendor/custom-package',
            ]
        ]);
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileEquals(
            'vendor/test-vendor/test-package/custom-root/metadata.php',
            'source/modules/custom-vendor/custom-package/metadata.php'
        );
    }

    public function testBlacklistedFilesArePresentWhenNoBlacklistFilterIsDefined()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'metadata.php' => '<?php',
            'module.php' => '<?php',
            'readme.txt' => 'readme',
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package', '1.0.0');
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileExists('source/modules/test-vendor/test-package/metadata.php');
        $this->assertVirtualFileExists('source/modules/test-vendor/test-package/module.php');
        $this->assertVirtualFileExists('source/modules/test-vendor/test-package/readme.txt');
    }

    public function testBlacklistedFilesArePresentWhenEmptyBlacklistFilterIsDefined()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'metadata.php' => '<?php',
            'module.php' => '<?php',
            'readme.txt' => 'readme',
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package', '1.0.0', [
            'oxideshop' => [
                'blacklist-filter' => []
            ]
        ]);
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileExists('source/modules/test-vendor/test-package/metadata.php');
        $this->assertVirtualFileExists('source/modules/test-vendor/test-package/module.php');
        $this->assertVirtualFileExists('source/modules/test-vendor/test-package/readme.txt');
    }

    public function testBlacklistedFilesArePresentWhenDifferentBlacklistFilterIsDefined()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'metadata.php' => '<?php',
            'module.php' => '<?php',
            'readme.txt' => 'readme',
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package', '1.0.0', [
            'oxideshop' => [
                'blacklist-filter' => [
                    '**/*.pdf'
                ]
            ]
        ]);
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileExists('source/modules/test-vendor/test-package/metadata.php');
        $this->assertVirtualFileExists('source/modules/test-vendor/test-package/module.php');
        $this->assertVirtualFileExists('source/modules/test-vendor/test-package/readme.txt');
    }

    public function testBlacklistedFilesAreSkippedWhenABlacklistFilterIsDefined()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'metadata.php' => '<?php',
            'module.php' => '<?php',
            'readme.txt' => 'readme',
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package', '1.0.0', [
            'oxideshop' => [
                'blacklist-filter' => [
                    '**/*.txt'
                ]
            ]
        ]);
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileExists('source/modules/test-vendor/test-package/metadata.php');
        $this->assertVirtualFileExists('source/modules/test-vendor/test-package/module.php');
        $this->assertVirtualFileNotExists('source/modules/test-vendor/test-package/readme.txt');
    }

    public function testVCSFilesAreSkippedWhenNoBlacklistFilterIsDefined()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'metadata.php' => '<?php',
            '.git/HEAD' => 'HEAD',
            '.git/index' => 'index',
            '.git/objects/ff/fftest' => 'blob',
            '.gitignore' => 'git ignore',
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package', '1.0.0');
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileExists('source/modules/test-vendor/test-package/metadata.php');
        $this->assertVirtualFileNotExists('source/modules/test-vendor/test-package/.git/HEAD');
        $this->assertVirtualFileNotExists('source/modules/test-vendor/test-package/.git/index');
        $this->assertVirtualFileNotExists('source/modules/test-vendor/test-package/.git/objects/ff/fftest');
        $this->assertVirtualFileNotExists('source/modules/test-vendor/test-package/.gitignore');
    }

    public function testVCSFilesAreSkippedWhenABlacklistFilterIsDefined()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'metadata.php' => '<?php',
            'module.php' => '<?php',
            'readme.txt' => 'readme',
            '.git/HEAD' => 'HEAD',
            '.git/index' => 'index',
            '.git/objects/ff/fftest' => 'blob',
            '.gitignore' => 'git ignore',
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package', '1.0.0', [
            'oxideshop' => [
                'blacklist-filter' => [
                    '**/*.txt'
                ]
            ]
        ]);
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileExists('source/modules/test-vendor/test-package/metadata.php');
        $this->assertVirtualFileExists('source/modules/test-vendor/test-package/module.php');
        $this->assertVirtualFileNotExists('source/modules/test-vendor/test-package/readme.txt');
        $this->assertVirtualFileNotExists('source/modules/test-vendor/test-package/.git/HEAD');
        $this->assertVirtualFileNotExists('source/modules/test-vendor/test-package/.git/index');
        $this->assertVirtualFileNotExists('source/modules/test-vendor/test-package/.git/objects/ff/fftest');
        $this->assertVirtualFileNotExists('source/modules/test-vendor/test-package/.gitignore');
    }

    public function testComplexCase()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package/custom-root', [
            'metadata.php' => '<?php',
            'module.php' => '<?php',
            'readme.txt' => 'readme',
            'readme.pdf' => 'PDF',
            'documentation/readme.txt' => 'readme',
            'documentation/example.php' => '<?php',
            'model/model.php' => '<?php',
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package', '1.0.0', [
            'oxideshop' => [
                'source-directory' => 'custom-root',
                'target-directory' => 'custom-out',
                'blacklist-filter' => [
                    '**/*.txt',
                    '**/*.pdf',
                    'documentation/**/*.*',
                ]
            ]
        ]);
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertTrue($installer->isInstalled());
        $this->assertVirtualFileEquals(
            'vendor/test-vendor/test-package/custom-root/metadata.php',
            'source/modules/custom-out/metadata.php'
        );
        $this->assertVirtualFileEquals(
            'vendor/test-vendor/test-package/custom-root/module.php',
            'source/modules/custom-out/module.php'
        );
        $this->assertVirtualFileEquals(
            'vendor/test-vendor/test-package/custom-root/model/model.php',
            'source/modules/custom-out/model/model.php'
        );
        $this->assertVirtualFileNotExists('source/modules/custom-out/readme.txt');
        $this->assertVirtualFileNotExists('source/modules/custom-out/readme.pdf');
        $this->assertVirtualFileNotExists('source/modules/custom-out/documentation');
        $this->assertVirtualFileNotExists('source/modules/custom-out/documentation/readme.txt');
        $this->assertVirtualFileNotExists('source/modules/custom-out/documentation/example.php');
    }
}
