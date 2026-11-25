<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ComposerPlugin\Installer\Package;

use OxidEsales\EshopCommunity\Internal\Framework\AdminUi\Service\AdminUiAssetsInstallerInterface;

class AdminUiPackageInstaller extends AbstractPackageInstaller
{
    public function isInstalled(string $packagePath): bool
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

    public function uninstall(string $packagePath): void
    {
        $this->getAdminUiAssetsInstaller()->uninstall();
    }

    protected function getPackageTypeDescription(): string
    {
        return 'admin UI package';
    }

    private function getAdminUiAssetsInstaller(): AdminUiAssetsInstallerInterface
    {
        return $this->getBootstrapContainer()->get(AdminUiAssetsInstallerInterface::class);
    }
}
