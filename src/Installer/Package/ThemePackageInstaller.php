<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ComposerPlugin\Installer\Package;

use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\EshopCommunity\Internal\Container\BootstrapContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Theme\Install\DataObject\OxidThemePackage;
use OxidEsales\EshopCommunity\Internal\Framework\Theme\Install\Service\ThemeInstallerInterface;

/**
 * @inheritdoc
 */
class ThemePackageInstaller extends AbstractPackageInstaller
{
    /**
     * @param string $packagePath
     *
     * @return bool
     */
    public function isInstalled(string $packagePath)
    {
        return $this->getBootstrapThemeInstaller()->isInstalled($this->getOxidThemePackage($packagePath));
    }

    /**
     * Registers theme configuration and links theme assets from the package.
     *
     * @param string $packagePath
     */
    public function install($packagePath)
    {
        $this->getIO()->write("Installing theme {$this->getPackageName()} package.");
        $this->getBootstrapThemeInstaller()->install($this->getOxidThemePackage($packagePath));
    }

    /**
     * @param string $packagePath
     */
    public function update($packagePath)
    {
        $package = $this->getOxidThemePackage($packagePath);

        if ($this->getBootstrapThemeInstaller()->isInstalled($package)) {
            $this->getIO()->write("Updating theme {$this->getPackageName()} files...");
            $this->getBootstrapThemeInstaller()->install($package);
        } else {
            $this->install($packagePath);
        }
    }

    /**
     * @param string $packagePath
     */
    public function uninstall(string $packagePath): void
    {
        $this->getThemeInstaller()->uninstall($this->getOxidThemePackage($packagePath));
    }

    private function getThemeInstaller(): ThemeInstallerInterface
    {
        try {
            return ContainerFacade::get(ThemeInstallerInterface::class);
        } catch (\Exception) {
            return $this->getBootstrapThemeInstaller();
        }
    }

    private function getOxidThemePackage(string $packagePath): OxidThemePackage
    {
        return new OxidThemePackage($packagePath);
    }

    private function getBootstrapThemeInstaller(): ThemeInstallerInterface
    {
        return BootstrapContainerFactory::getBootstrapContainer()
            ->get('oxid_esales.theme.install.service.bootstrap_theme_installer');
    }
}
