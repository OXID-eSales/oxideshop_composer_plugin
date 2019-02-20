<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ComposerPlugin\Tests\Integration;

use Composer\Composer;
use Composer\Config;
use Composer\Installer\InstallationManager;
use Composer\IO\NullIO;
use Composer\Package\Package;
use Composer\Package\RootPackage;
use Composer\Repository\RepositoryManager;
use Composer\Repository\WritableArrayRepository;
use OxidEsales\ComposerPlugin\Plugin;

class PluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Plugin::executeAction
     */
    public function testExecuteAction()
    {
        $composer = new Composer;
        $composer->setConfig(new Config);
        $composer->setInstallationManager(new InstallationManager);
        $composer->setPackage($this->package(RootPackage::class));
        $composer->setRepositoryManager($manager = new RepositoryManager(new NullIO, new Config));
        $manager->setLocalRepository($repo = new WritableArrayRepository);
        $repo->addPackage($this->package());
        $plugin = new Plugin;
        $plugin->activate($composer, new NullIO);
        $plugin->installPackages();
    }

    /**
     * @param string $class
     *
     * @return mixed
     */
    private function package($class = Package::class)
    {
        $mock =  $this->getMockBuilder($class)->disableOriginalConstructor()->getMock();
        $mock->expects($this->once())->method('getType');
        return $mock;
    }
}
