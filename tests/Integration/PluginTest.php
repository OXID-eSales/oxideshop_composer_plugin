<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ComposerPlugin\Tests\Integration;

use OxidEsales\ComposerPlugin\Plugin;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PluginTest extends TestCase
{
    #[DataProvider('packagesEventsDataProvider')]
    public function testShopCacheIsClearedAfterOtherListeners(string $event, string $packagesListener): void
    {
        $listeners = Plugin::getSubscribedEvents()[$event];
        $firstListener = reset($listeners);
        $lastListener = end($listeners);

        $this->assertSame($packagesListener, $firstListener[0]);
        $this->assertSame('clearShopCache', $lastListener[0]);
        $this->assertLessThan(0, $lastListener[1]);
    }

    public static function packagesEventsDataProvider(): array
    {
        return [
            ['post-install-cmd', 'installPackages'],
            ['post-update-cmd', 'updatePackages'],
        ];
    }
}
