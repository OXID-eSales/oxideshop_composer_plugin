<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ComposerPlugin\Installer\Package;

use Composer\Package\PackageInterface;
use OxidEsales\EshopCommunity\Internal\Container\BootstrapContainerFactory;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\DIContainer\Service\ShopStateServiceInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Dao\ProjectConfigurationDaoInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Install\DataObject\OxidEshopPackage;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Install\Service\ModuleFilesInstallerInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Install\Service\ModuleInstallerInterface;
use Webmozart\PathUtil\Path;

/**
 * @inheritdoc
 */
class ModulePackageInstaller extends AbstractPackageInstaller
{
    /** @var string MODULES_DIRECTORY */
    public const MODULES_DIRECTORY = 'modules';

    /**
     * @param string $packagePath
     *
     * @return bool
     */
    public function isInstalled(string $packagePath)
    {
        $package = $this->getOxidShopPackage($packagePath);

        return $this->getBootstrapModuleInstaller()->isInstalled($package);
    }

    /**
     * Copies module files to shop directory.
     *
     * @param string $packagePath
     */
    public function install($packagePath)
    {
        $this->getIO()->write("Installing module {$this->getPackageName()} package.");
        $this->getBootstrapModuleInstaller()->install($this->getOxidShopPackage($packagePath));
    }

    /**
     * @param PackageInterface $package
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
        $package = new OxidEshopPackage($this->getPackage()->getName(), $packagePath);
        $extraParameters = $this->getPackage()->getExtra();

        return $package;
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
