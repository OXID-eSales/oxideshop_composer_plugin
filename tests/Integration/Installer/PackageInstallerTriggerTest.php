<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ComposerPlugin\Tests\Integration\Installer;

use Composer\Composer;
use Composer\Config;
use Composer\IO\NullIO;
use OxidEsales\ComposerPlugin\Installer\PackageInstallerTrigger;
use OxidEsales\ComposerPlugin\Installer\Package\AbstractPackageInstaller;

class PackageInstallerTriggerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return PackageInstallerTrigger
     */
    private function createTrigger()
    {
        $composerConfigMock = $this->getMockBuilder(Config::class)->getMock();
        $composerMock = $this->getMockBuilder(Composer::class)->getMock();
        $composerMock->method('getConfig')->withAnyParameters()->willReturn($composerConfigMock);
        return new PackageInstallerTrigger(new NullIO, $composerMock);
    }

    /**
     * @covers PackageInstallerTrigger::setSettings
     */
    public function testSetSettings()
    {
        $trigger = $this->createTrigger();

        // both empty => ok
        $trigger->setSettings([
            AbstractPackageInstaller::EXTRA_PARAMETER_FILTER_BLACKLIST => [],
            AbstractPackageInstaller::EXTRA_PARAMETER_FILTER_WHITELIST => [],
        ]);

        // only blacklist => ok
        $trigger->setSettings([
            AbstractPackageInstaller::EXTRA_PARAMETER_FILTER_BLACKLIST => ['not-empty'],
            AbstractPackageInstaller::EXTRA_PARAMETER_FILTER_WHITELIST => [],
        ]);

        // only whitelist => ok
        $trigger->setSettings([
            AbstractPackageInstaller::EXTRA_PARAMETER_FILTER_BLACKLIST => [],
            AbstractPackageInstaller::EXTRA_PARAMETER_FILTER_WHITELIST => ['not-empty'],
        ]);

        $exception = new \InvalidArgumentException(sprintf(
            'settings %s and %s should not be used together',
            AbstractPackageInstaller::EXTRA_PARAMETER_FILTER_BLACKLIST,
            AbstractPackageInstaller::EXTRA_PARAMETER_FILTER_WHITELIST
        ));
        $this->expectExceptionObject($exception);

        // both not empty => not ok
        $trigger->setSettings([
            AbstractPackageInstaller::EXTRA_PARAMETER_FILTER_BLACKLIST => ['not-empty'],
            AbstractPackageInstaller::EXTRA_PARAMETER_FILTER_WHITELIST => ['not-empty'],
        ]);
    }

    /**
     * The composer.json file already in source for 5.3.
     */
    public function testGetShopSourcePathByConfiguration()
    {
        $packageInstallerStub = $this->createTrigger();
        $packageInstallerStub->setSettings(['source-path' => 'some/path/to/source']);
        $this->assertEquals('some/path/to/source', $packageInstallerStub->getShopSourcePath());
    }

    /**
     * The composer.json file is taken up from the source directory for 6.0, so we should add source to path.
     */
    public function testGetShopSourcePathFor60()
    {
        $result = $this->createTrigger()->getShopSourcePath();
        $this->assertEquals(getcwd() . '/source', $result);
    }
}
