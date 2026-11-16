<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ComposerPlugin\Tests\Integration\Installer;

use Composer\Composer;
use Composer\Config;
use Composer\IO\NullIO;
use Composer\Package\Package;
use OxidEsales\ComposerPlugin\Installer\Package\AdminUiPackageInstaller;
use OxidEsales\ComposerPlugin\Installer\PackageInstallerTrigger;
use PHPUnit\Framework\TestCase;

class PackageInstallerTriggerTest extends TestCase
{
    /**
     * The composer.json file already in source for 5.3.
     */
    public function testGetShopSourcePathByConfiguration(): void
    {
        $composerMock = $this->createStub(Composer::class);
        $composerMock->method('getConfig')->willReturn(new Config());

        $packageInstallerStub = new PackageInstallerTrigger(new NullIO(), $composerMock);
        $packageInstallerStub->setSettings([
            'source-path' => 'some/path/to/source'
        ]);
        $this->assertEquals($packageInstallerStub->getShopSourcePath(), 'some/path/to/source');
    }

    /**
     * The composer.json file is taken up from the source directory for 6.0, so we should add source to path.
     */
    public function testGetShopSourcePathFor60(): void
    {
        $composerMock = $this->createStub(Composer::class);
        $composerMock->method('getConfig')->willReturn(new Config());

        $packageInstallerStub = new PackageInstallerTrigger(new NullIO(), $composerMock);
        $result = $packageInstallerStub->getShopSourcePath();

        $this->assertEquals($result, getcwd() . '/source');
    }

    public function testAdminUiPackageTypeIsSupported(): void
    {
        $this->assertTrue($this->getPackageInstallerTrigger()->supports('oxideshop-admin-ui'));
    }

    public function testAdminUiPackageTypeMapsToAdminUiPackageInstaller(): void
    {
        $package = new Package('test-vendor/test-admin-ui', '1.0.0', '1.0.0');
        $package->setType('oxideshop-admin-ui');
        $packageInstallerTrigger = $this->getPackageInstallerTrigger();

        $installer = (new \ReflectionMethod($packageInstallerTrigger, 'createInstaller'))
            ->invoke($packageInstallerTrigger, $package);

        $this->assertInstanceOf(AdminUiPackageInstaller::class, $installer);
    }

    private function getPackageInstallerTrigger(): PackageInstallerTrigger
    {
        $composer = $this->createStub(Composer::class);
        $composer->method('getConfig')->willReturn(new Config());

        return new PackageInstallerTrigger(new NullIO(), $composer);
    }
}
