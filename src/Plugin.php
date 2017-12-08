<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ComposerPlugin;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use OxidEsales\ComposerPlugin\Installer\Package\AbstractPackageInstaller;
use OxidEsales\ComposerPlugin\Installer\PackageInstallerTrigger;

/**
 * Class Plugin.
 */
class Plugin implements PluginInterface, EventSubscriberInterface
{
    const ACTION_INSTALL = 'install';

    const ACTION_UPDATE = 'update';

    /** @var Composer */
    private $composer;

    /** @var IOInterface */
    private $io;

    /** @var PackageInstallerTrigger */
    private $packageInstallerTrigger;

    /**
     * Register events.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            'post-install-cmd' => 'installPackages',
            'post-update-cmd' => 'updatePackages'
        );
    }

    /**
     * Register shop packages installer.
     *
     * @param Composer    $composer
     * @param IOInterface $io
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $packageInstallerTrigger = new PackageInstallerTrigger($io, $composer);
        $composer->getInstallationManager()->addInstaller($packageInstallerTrigger);

        $this->composer = $composer;
        $this->io = $io;
        $this->packageInstallerTrigger = $packageInstallerTrigger;

        $extraSettings = $this->composer->getPackage()->getExtra();
        if (isset($extraSettings[AbstractPackageInstaller::EXTRA_PARAMETER_KEY_ROOT])) {
            $this->packageInstallerTrigger->setSettings($extraSettings[AbstractPackageInstaller::EXTRA_PARAMETER_KEY_ROOT]);
        }
    }

    /**
     * Run installation for oxid packages.
     */
    public function installPackages()
    {
        $this->executeAction(static::ACTION_INSTALL);
    }

    /**
     * Run update for oxid packages.
     */
    public function updatePackages()
    {
        $this->executeAction(static::ACTION_UPDATE);
    }

    /**
     * @param string $actionName
     */
    protected function executeAction($actionName)
    {
        $repo = $this->composer->getRepositoryManager()->getLocalRepository();

        foreach ($repo->getPackages() as $package) {
            if ($this->packageInstallerTrigger->supports($package->getType())) {
                if ($actionName === static::ACTION_INSTALL) {
                    $this->packageInstallerTrigger->installPackage($package);
                }
                if ($actionName === static::ACTION_UPDATE) {
                    $this->packageInstallerTrigger->updatePackage($package);
                }
            }
        }
    }
}
