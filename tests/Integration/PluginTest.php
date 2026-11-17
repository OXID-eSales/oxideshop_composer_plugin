<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ComposerPlugin\Tests\Integration;

use Composer\Composer;
use Composer\Config;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\Installer\PackageEvent;
use Composer\IO\BufferIO;
use Composer\Package\Package;
use Composer\Repository\InstalledArrayRepository;
use OxidEsales\ComposerPlugin\Plugin;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PluginTest extends TestCase
{
    #[DataProvider('shopPackageOperationsDataProvider')]
    public function testClearShopCacheHintIsShownAfterShopPackageChange(OperationInterface $operation): void
    {
        $io = new BufferIO();
        $plugin = $this->getPlugin($io);

        $plugin->registerShopPackageChange($this->getPackageEvent($operation));
        $plugin->showClearShopCacheHint();

        $this->assertStringContainsString('Please clear the shop cache.', $io->getOutput());
    }

    public static function shopPackageOperationsDataProvider(): array
    {
        return [
            [new InstallOperation(self::getPackage('oxideshop-theme'))],
            [new UpdateOperation(self::getPackage('library'), self::getPackage('oxideshop-module'))],
            [new UpdateOperation(self::getPackage('oxideshop-module'), self::getPackage('library'))],
            [new UninstallOperation(self::getPackage('oxideshop-admin-ui'))],
        ];
    }

    public function testClearShopCacheHintIsNotShownAfterOtherPackageChange(): void
    {
        $io = new BufferIO();
        $plugin = $this->getPlugin($io);

        $plugin->registerShopPackageChange($this->getPackageEvent(new InstallOperation(self::getPackage('library'))));
        $plugin->showClearShopCacheHint();

        $this->assertSame('', $io->getOutput());
    }

    public function testClearShopCacheHintIsNotShownWithoutPackageChange(): void
    {
        $io = new BufferIO();

        $this->getPlugin($io)->showClearShopCacheHint();

        $this->assertSame('', $io->getOutput());
    }

    #[DataProvider('packagesEventsDataProvider')]
    public function testClearShopCacheHintIsShownAfterOtherListeners(string $event, string $packagesListener): void
    {
        $listeners = Plugin::getSubscribedEvents()[$event];
        $firstListener = reset($listeners);
        $lastListener = end($listeners);

        $this->assertSame($packagesListener, $firstListener[0]);
        $this->assertSame('showClearShopCacheHint', $lastListener[0]);
        $this->assertLessThan(0, $lastListener[1]);
    }

    public static function packagesEventsDataProvider(): array
    {
        return [
            ['post-install-cmd', 'installPackages'],
            ['post-update-cmd', 'updatePackages'],
        ];
    }

    private function getPlugin(BufferIO $io): Plugin
    {
        $plugin = new Plugin();
        $plugin->activate($this->getComposer(), $io);

        return $plugin;
    }

    private function getComposer(): Composer
    {
        $composer = $this->createStub(Composer::class);
        $composer->method('getConfig')->willReturn(new Config());

        return $composer;
    }

    private function getPackageEvent(OperationInterface $operation): PackageEvent
    {
        return new PackageEvent(
            'post-package-install',
            $this->getComposer(),
            new BufferIO(),
            true,
            new InstalledArrayRepository(),
            [$operation],
            $operation
        );
    }

    private static function getPackage(string $type): Package
    {
        $package = new Package('vendor/package', '1.0.0', '1.0.0');
        $package->setType($type);

        return $package;
    }
}
