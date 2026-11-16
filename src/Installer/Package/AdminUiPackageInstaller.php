<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ComposerPlugin\Installer\Package;

use OxidEsales\EshopCommunity\Internal\Container\BootstrapContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\AdminUi\Service\AdminUiAssetsInstallerInterface;
use Psr\Container\ContainerInterface;

/**
 * @inheritdoc
 */
class AdminUiPackageInstaller extends AbstractPackageInstaller
{
    private ?ContainerInterface $bootstrapContainer = null;

    /**
     * @param string $packagePath
     *
     * @return bool
     */
    public function isInstalled(string $packagePath)
    {
        return $this->getAdminUiAssetsInstaller()->isInstalled();
    }

    /**
     * @param string $packagePath
     */
    public function install($packagePath)
    {
        $this->writeInstallingMessage($this->getPackageTypeDescription());
        $this->getAdminUiAssetsInstaller()->install();
        $this->writeDoneMessage();
    }

    /**
     * @param string $packagePath
     */
    public function update($packagePath)
    {
        $this->writeUpdatingMessage($this->getPackageTypeDescription());
        $this->getAdminUiAssetsInstaller()->install();
        $this->writeDoneMessage();
    }

    /**
     * @param string $packagePath
     */
    public function uninstall(string $packagePath): void
    {
        $this->getAdminUiAssetsInstaller()->uninstall();
    }

    /**
     * @return string
     */
    protected function getPackageTypeDescription(): string
    {
        return 'admin UI package';
    }

    private function getAdminUiAssetsInstaller(): AdminUiAssetsInstallerInterface
    {
        return $this->getBootstrapContainer()->get(AdminUiAssetsInstallerInterface::class);
    }

    private function getBootstrapContainer(): ContainerInterface
    {
        if ($this->bootstrapContainer === null) {
            $this->bootstrapContainer = BootstrapContainerFactory::getBootstrapContainer();
        }

        return $this->bootstrapContainer;
    }
}
