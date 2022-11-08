<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ComposerPlugin\Installer\Package;

use OxidEsales\EshopCommunity\Internal\Container\BootstrapContainerFactory;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\DIContainer\Service\ShopStateServiceInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Install\DataObject\OxidEshopPackage;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Install\Service\ModuleInstallerInterface;

/**
 * @inheritdoc
 */
class ModulePackageInstaller extends AbstractPackageInstaller
{
    /**
     * @param string $packagePath
     *
     * @return bool
     */
    public function isInstalled(string $packagePath)
    {
        return $this->getBootstrapModuleInstaller()->isInstalled($this->getOxidShopPackage($packagePath));
    }

    /**
     * Copies module files to shop directory.
     *
     * @param string $packagePath
     */
    public function install($packagePath): void
    {
        $this->getIO()->write("Installing module {$this->getPackageName()} package.");
        $this->getBootstrapModuleInstaller()->install($this->getOxidShopPackage($packagePath));
    }

    /**
     * @param string $packagePath
     */
    public function uninstall(string $packagePath): void
    {
        $moduleInstaller = $this->getModuleInstaller();
        $moduleInstaller->uninstall($this->getOxidShopPackage($packagePath));
    }

    /**
     * @param string $packagePath
     */
    public function update($packagePath)
    {
        $package = $this->getOxidShopPackage($packagePath);

        if ($this->getBootstrapModuleInstaller()->isInstalled($package)) {
            $this->getIO()->write("Updating module {$this->getPackageName()} files...");
            $this->getBootstrapModuleInstaller()->install($package);
        } else {
            $this->install($packagePath);
        }
    }

    /**
     * @return ModuleInstallerInterface
     */
    private function getModuleInstaller(): ModuleInstallerInterface
    {
        if ($this->isShopLaunched()) {
            return ContainerFactory::getInstance()->getContainer()
                ->get(ModuleInstallerInterface::class);
        } else {
            return $this->getBootstrapModuleInstaller();
        }
    }

    /**
     * @return bool
     */
    private function isShopLaunched(): bool
    {
        $container = BootstrapContainerFactory::getBootstrapContainer();
        $shopStateService = $container->get(ShopStateServiceInterface::class);

        return $shopStateService->isLaunched();
    }

    /**
     * @param string $packagePath
     *
     * @return OxidEshopPackage
     */
    private function getOxidShopPackage(string $packagePath): OxidEshopPackage
    {
        return new OxidEshopPackage($packagePath);
    }

    private function getBootstrapModuleInstaller(): ModuleInstallerInterface
    {
        return BootstrapContainerFactory::getBootstrapContainer()
            ->get('oxid_esales.module.install.service.bootstrap_module_installer');
    }


    /**
     * returns module's installation target direcory
     */
    protected function getModuleTargetDir(): string
    {
        return $this->getPackage()->getExtra()["oxideshop"]["target-directory"] ?? "";
    }
}
