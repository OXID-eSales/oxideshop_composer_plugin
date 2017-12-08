<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ComposerPlugin\Tests\Integration\Installer\Package;

use Composer\IO\NullIO;
use Composer\Package\Package;
use OxidEsales\ComposerPlugin\Installer\Package\ThemePackageInstaller;

class ThemePackageInstallerTest extends AbstractPackageInstallerTest
{
    protected function getPackageInstaller($packageName, $version = '1.0.0', $extra = [])
    {
        $package = new Package($packageName, $version, $version);
        $package->setExtra($extra);

        return new ThemePackageInstaller(
            new NullIO(),
            $this->getVirtualShopSourcePath(),
            $package
        );
    }

    public function testThemeNotInstalledByDefault()
    {
        $installer = $this->getPackageInstaller('test-vendor/test-package');

        $this->assertFalse($installer->isInstalled());
    }

    public function testThemeIsInstalledIfAlreadyExistsInShop()
    {
        $this->setupVirtualProjectRoot('source/Application/views/test-package', [
            'theme.php' => '<?php'
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package');

        $this->assertTrue($installer->isInstalled());
    }

    public function testThemeIsInstalledAfterInstallProcess()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'theme.php' => '<?php'
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package');
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertTrue($installer->isInstalled());
    }

    public function testThemeFilesAreCopiedAfterInstallProcess()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'theme.php' => '<?php'
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package');
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileEquals(
            'vendor/test-vendor/test-package/theme.php',
            'source/Application/views/test-package/theme.php'
        );
    }

    public function testThemeFilesAreCopiedAfterInstallProcessWithSameTargetDirectory()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'theme.php' => '<?php'
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package', '1.0.0', [
            'oxideshop' => [
                'target-directory' => 'test-package',
            ]
        ]);
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileEquals(
            'vendor/test-vendor/test-package/theme.php',
            'source/Application/views/test-package/theme.php'
        );
    }

    public function testThemeFilesAreCopiedAfterInstallProcessWithCustomTargetDirectory()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'theme.php' => '<?php'
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package', '1.0.0', [
            'oxideshop' => [
                'target-directory' => 'custom-package',
            ]
        ]);
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileEquals(
            'vendor/test-vendor/test-package/theme.php',
            'source/Application/views/custom-package/theme.php'
        );
    }

    public function testThemeAssetsAreCopiedAfterInstallProcess()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'theme.php' => '<?php',
            'out/style.css' => 'css',
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package');
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileEquals(
            'vendor/test-vendor/test-package/out/style.css',
            'source/out/test-package/style.css'
        );
    }

    public function testThemeAssetsAreCopiedAfterInstallProcessWithSameAssetsDirectory()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'theme.php' => '<?php',
            'out/style.css' => 'css',
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package', '1.0.0', [
            'oxideshop' => [
                'assets-directory' => 'out',
            ]
        ]);
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileEquals(
            'vendor/test-vendor/test-package/out/style.css',
            'source/out/test-package/style.css'
        );
    }

    public function testThemeAssetsAreCopiedAfterInstallProcessWithSameTargetDirectory()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'theme.php' => '<?php',
            'out/style.css' => 'css',
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package', '1.0.0', [
            'oxideshop' => [
                'target-directory' => 'test-package',
            ]
        ]);
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileEquals(
            'vendor/test-vendor/test-package/out/style.css',
            'source/out/test-package/style.css'
        );
    }

    public function testThemeAssetsAreCopiedAfterInstallProcessWithSameAssetsDirectoryAndSameTargetDirectory()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'theme.php' => '<?php',
            'out/style.css' => 'css',
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package', '1.0.0', [
            'oxideshop' => [
                'assets-directory' => 'out',
                'target-directory' => 'test-package',
            ]
        ]);
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileEquals(
            'vendor/test-vendor/test-package/out/style.css',
            'source/out/test-package/style.css'
        );
    }

    public function testThemeAssetsAreCopiedAfterInstallProcessWithCustomAssetsDirectory()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'theme.php' => '<?php',
            'custom_assets/custom_style.css' => 'css',
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package', '1.0.0', [
            'oxideshop' => [
                'assets-directory' => 'custom_assets',
            ]
        ]);
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileEquals(
            'vendor/test-vendor/test-package/custom_assets/custom_style.css',
            'source/out/test-package/custom_style.css'
        );
    }

    public function testThemeAssetsAreCopiedAfterInstallProcessWithCustomTargetDirectory()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'theme.php' => '<?php',
            'out/style.css' => 'css',
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package', '1.0.0', [
            'oxideshop' => [
                'target-directory' => 'custom-package',
            ]
        ]);
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileEquals(
            'vendor/test-vendor/test-package/out/style.css',
            'source/out/custom-package/style.css'
        );
    }

    public function testThemeAssetsAreCopiedAfterInstallProcessWithCustomAssetsDirectoryAndCustomTargetDirectory()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'theme.php' => '<?php',
            'custom_assets/custom_style.css' => 'css',
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package', '1.0.0', [
            'oxideshop' => [
                'assets-directory' => 'custom_assets',
                'target-directory' => 'custom-package',
            ]
        ]);
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileEquals(
            'vendor/test-vendor/test-package/custom_assets/custom_style.css',
            'source/out/custom-package/custom_style.css'
        );
    }

    public function testThemeAssetsAreNotCopiedAfterInstallProcessWithNonExistingCustomAssetsDirectory()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'theme.php' => '<?php',
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package', '1.0.0', [
            'oxideshop' => [
                'assets-directory' => 'custom_assets',
            ]
        ]);
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileNotExists('source/out/test-package/custom_style.css');
    }

    public function testBlacklistedFilesArePresentWhenNoBlacklistFilterIsDefined()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'theme.php' => '<?php',
            'theme.txt' => 'txt',
            'out/style.css' => 'css',
            'out/style.pdf' => 'PDF',
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package');
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileExists('source/Application/views/test-package/theme.php');
        $this->assertVirtualFileExists('source/Application/views/test-package/theme.txt');
        $this->assertVirtualFileExists('source/out/test-package/style.css');
        $this->assertVirtualFileExists('source/out/test-package/style.pdf');
    }

    public function testBlacklistedFilesArePresentWhenEmptyBlacklistFilterIsDefined()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'theme.php' => '<?php',
            'theme.txt' => 'txt',
            'out/style.css' => 'css',
            'out/style.pdf' => 'PDF',
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package', '1.0.0', [
            'oxideshop' => [
                'blacklist-filter' => []
            ]
        ]);
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileExists('source/Application/views/test-package/theme.php');
        $this->assertVirtualFileExists('source/Application/views/test-package/theme.txt');
        $this->assertVirtualFileExists('source/out/test-package/style.css');
        $this->assertVirtualFileExists('source/out/test-package/style.pdf');
    }

    public function testBlacklistedFilesArePresentWhenDifferentBlacklistFilterIsDefined()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'theme.php' => '<?php',
            'theme.txt' => 'txt',
            'out/style.css' => 'css',
            'out/style.pdf' => 'PDF',
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package', '1.0.0', [
            'oxideshop' => [
                'blacklist-filter' => [
                    '**/*.doc'
                ]
            ]
        ]);
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileExists('source/Application/views/test-package/theme.php');
        $this->assertVirtualFileExists('source/Application/views/test-package/theme.txt');
        $this->assertVirtualFileExists('source/out/test-package/style.css');
        $this->assertVirtualFileExists('source/out/test-package/style.pdf');
    }

    public function testBlacklistedFilesAreSkippedWhenABlacklistFilterIsDefined()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'theme.php' => '<?php',
            'theme.txt' => 'txt',
            'out/style.css' => 'css',
            'out/style.pdf' => 'PDF',
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package', '1.0.0', [
            'oxideshop' => [
                'blacklist-filter' => [
                    '**/*.txt',
                    '**/*.pdf',
                ]
            ]
        ]);
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileExists('source/Application/views/test-package/theme.php');
        $this->assertVirtualFileNotExists('source/Application/views/test-package/theme.txt');
        $this->assertVirtualFileExists('source/out/test-package/style.css');
        $this->assertVirtualFileNotExists('source/out/test-package/style.pdf');
    }

    public function testVCSFilesAreSkippedWhenNoBlacklistFilterIsDefined()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'theme.php' => '<?php',
            '.git/HEAD' => 'HEAD',
            '.git/index' => 'index',
            '.git/objects/ff/fftest' => 'blob',
            '.gitignore' => 'git ignore',
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package', '1.0.0');
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileExists('source/Application/views/test-package/theme.php');
        $this->assertVirtualFileNotExists('source/Application/views/test-package/.git/HEAD');
        $this->assertVirtualFileNotExists('source/Application/views/test-package/.git/index');
        $this->assertVirtualFileNotExists('source/Application/views/test-package/.git/objects/ff/fftest');
        $this->assertVirtualFileNotExists('source/Application/views/test-package/.gitignore');
    }

    public function testVCSFilesAreSkippedWhenABlacklistFilterIsDefined()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'theme.php' => '<?php',
            'theme.txt' => 'txt',
            '.git/HEAD' => 'HEAD',
            '.git/index' => 'index',
            '.git/objects/ff/fftest' => 'blob',
            '.gitignore' => 'git ignore',
            'out/style.css' => 'css',
            'out/style.pdf' => 'PDF',
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package', '1.0.0', [
            'oxideshop' => [
                'blacklist-filter' => [
                    '**/*.txt',
                    '**/*.pdf',
                ]
            ]
        ]);
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertVirtualFileExists('source/Application/views/test-package/theme.php');
        $this->assertVirtualFileNotExists('source/Application/views/test-package/theme.txt');
        $this->assertVirtualFileExists('source/out/test-package/style.css');
        $this->assertVirtualFileNotExists('source/out/test-package/style.pdf');
        $this->assertVirtualFileNotExists('source/Application/views/test-package/.git/HEAD');
        $this->assertVirtualFileNotExists('source/Application/views/test-package/.git/index');
        $this->assertVirtualFileNotExists('source/Application/views/test-package/.git/objects/ff/fftest');
        $this->assertVirtualFileNotExists('source/Application/views/test-package/.gitignore');
    }

    public function testComplexCase()
    {
        $this->setupVirtualProjectRoot('vendor/test-vendor/test-package', [
            'theme.php' => '<?php',
            'theme.txt' => 'txt',
            'out/style.css' => 'css',
            'out/style.pdf' => 'PDF',
            'custom_assets/custom_style.css' => 'css',
            'custom_assets/custom_style.pdf' => 'PDF',
        ]);

        $installer = $this->getPackageInstaller('test-vendor/test-package', '1.0.0', [
            'oxideshop' => [
                'assets-directory' => 'custom_assets',
                'target-directory' => 'custom-package',
                'blacklist-filter' => [
                    '**/*.txt',
                    '**/*.pdf',
                ]
            ]
        ]);
        $installer->install($this->getVirtualFileSystemRootPath('vendor/test-vendor/test-package'));

        $this->assertTrue($installer->isInstalled());
        $this->assertVirtualFileEquals(
            'vendor/test-vendor/test-package/theme.php',
            'source/Application/views/custom-package/theme.php'
        );
        $this->assertVirtualFileEquals(
            'vendor/test-vendor/test-package/custom_assets/custom_style.css',
            'source/out/custom-package/custom_style.css'
        );
        $this->assertVirtualFileNotExists('source/Application/views/custom-package/theme.txt');
        $this->assertVirtualFileNotExists('source/out/custom-package/style.css');
        $this->assertVirtualFileNotExists('source/out/custom-package/style.pdf');
        $this->assertVirtualFileNotExists('source/out/custom-package/custom_style.pdf');
    }
}
