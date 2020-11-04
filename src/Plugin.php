<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ComposerPlugin;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use OxidEsales\ComposerPlugin\Installer\Package\AbstractPackageInstaller;
use OxidEsales\ComposerPlugin\Installer\PackageInstallerTrigger;
use OxidEsales\EshopCommunity\Internal\Container\BootstrapContainerFactory;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\DIContainer\Service\ShopStateServiceInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Dao\ProjectConfigurationDaoInterface;
use OxidEsales\Facts\Facts;

/**
 * Class Plugin.
 */
class Plugin implements PluginInterface, EventSubscriberInterface
{
    /** @var Composer */
    private $composer;

    /** @var PackageInstallerTrigger */
    private $packageInstallerTrigger;

    /**
     * Register events.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'post-install-cmd'      => 'installPackages',
            'post-update-cmd'       => 'updatePackages',
            'pre-package-uninstall' => 'uninstallPackage',
        ];
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
        $this->packageInstallerTrigger = $packageInstallerTrigger;

        $extraSettings = $this->composer->getPackage()->getExtra();
        if (isset($extraSettings[AbstractPackageInstaller::EXTRA_PARAMETER_KEY_ROOT])) {
            $this->packageInstallerTrigger->setSettings(
                $extraSettings[AbstractPackageInstaller::EXTRA_PARAMETER_KEY_ROOT]
            );
        }
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
    }

    /**
     * Run installation for oxid packages.
     */
    public function installPackages(): void
    {
        $this->autoloadInstalledPackages();
        $this->bootstrapOxidShopComponent();
        $this->generateDefaultProjectConfigurationIfMissing();

        $repo = $this->composer->getRepositoryManager()->getLocalRepository();

        foreach ($repo->getPackages() as $package) {
            if ($this->packageInstallerTrigger->supports($package->getType())) {
                $this->packageInstallerTrigger->installPackage($package);
            }
        }
    }

    public function updatePackages(): void
    {
        $this->autoloadInstalledPackages();
        $this->bootstrapOxidShopComponent();
        $this->generateDefaultProjectConfigurationIfMissing();

        $repo = $this->composer->getRepositoryManager()->getLocalRepository();

        foreach ($repo->getPackages() as $package) {
            if ($this->packageInstallerTrigger->supports($package->getType())) {
                $this->packageInstallerTrigger->updatePackage($package);
            }
        }
    }

    /**
     * @param PackageEvent $event
     */
    public function uninstallPackage(PackageEvent $event): void
    {
        $package = $event->getOperation()->getPackage();
        if ($this->packageInstallerTrigger->supports($package->getType())) {
            $this->autoloadInstalledPackages();
            $this->bootstrapOxidShopComponent();
            $this->packageInstallerTrigger->uninstallPackage($package);
        }
    }

    /**
     * Composer autoloads classes needed for its own tasks only. Classes of other packages installed need to be loaded
     * separately.
     */
    private function autoloadInstalledPackages(): void
    {
        $vendorDir = $this->composer->getConfig()->get('vendor-dir');
        require_once($vendorDir . '/autoload.php');
    }

    private function bootstrapOxidShopComponent(): void
    {
        if ($this->isShopLaunched()) {
            $bootstrapFilePath = (new Facts())->getSourcePath() . DIRECTORY_SEPARATOR . 'bootstrap.php';
            require_once $bootstrapFilePath;
        }
    }

    private function isShopLaunched(): bool
    {
        $container = BootstrapContainerFactory::getBootstrapContainer();
        $shopStateService = $container->get(ShopStateServiceInterface::class);

        return $shopStateService->isLaunched();
    }

    private function generateDefaultProjectConfigurationIfMissing(): void
    {
        $bootstrapContainer = BootstrapContainerFactory::getBootstrapContainer();
        $projectConfigurationDao = $bootstrapContainer->get(ProjectConfigurationDaoInterface::class);

        if ($projectConfigurationDao->isConfigurationEmpty()) {
            if ($this->isShopLaunched()) {
                $container = ContainerFactory::getInstance()->getContainer();
                $container
                    ->get('oxid_esales.module.install.service.launched_shop_project_configuration_generator')
                    ->generate();
            } else {
                $bootstrapContainer
                    ->get('oxid_esales.module.install.service.installed_shop_project_configuration_generator')
                    ->generate();
            }
        }
    }
}
