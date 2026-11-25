<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ComposerPlugin\Installer\Package;

use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\EshopCommunity\Internal\Framework\Theme\Install\Service\ThemeAssetsInstallerInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Theme\Install\Service\ThemeConfigurationInstallerInterface;
use Symfony\Component\Filesystem\Path;

class ThemePackageInstaller extends AbstractPackageInstaller
{
    private const ASSETS_DIRECTORY = 'assets';

    private const OUT_DIRECTORY = 'out';

    public function isInstalled(string $packagePath): bool
    {
        try {
            return $this->getBootstrapThemeConfigurationInstaller()->isInstalled($packagePath)
                && $this->getThemeAssetsInstaller()->isInstalled($packagePath);
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * @param string $packagePath
     */
    public function install($packagePath)
    {
        $this->writeInstallingMessage($this->getPackageTypeDescription());
        $this->installTheme($packagePath);
    }

    /**
     * @param string $packagePath
     */
    public function update($packagePath)
    {
        $this->writeUpdatingMessage($this->getPackageTypeDescription());
        $this->installTheme($packagePath);
    }

    public function uninstall(string $packagePath): void
    {
        try {
            $this->getThemeConfigurationInstaller()->uninstall($packagePath);
            $this->getThemeAssetsInstaller()->uninstall($packagePath);
        } catch (\Exception $exception) {
            $this->writeSkippedWarning($exception);
        }
    }

    protected function getPackageTypeDescription(): string
    {
        return 'theme package';
    }

    private function installTheme(string $packagePath): void
    {
        try {
            $this->getBootstrapThemeConfigurationInstaller()->install($packagePath);
            $this->getThemeAssetsInstaller()->install($packagePath);
            if (!$this->hasAssets($packagePath) && $this->hasOutDirectory($packagePath)) {
                $this->writeOutDirectoryWarning();
            }
            $this->writeDoneMessage();
        } catch (\Exception $exception) {
            $this->writeSkippedWarning($exception);
            $this->writeSkippedMessage();
        }
    }

    private function writeSkippedWarning(\Exception $exception): void
    {
        $this->getIO()->warning(
            sprintf('Theme package %s is skipped: %s', $this->getPackageName(), $exception->getMessage())
        );
    }

    private function writeOutDirectoryWarning(): void
    {
        $this->getIO()->warning(
            sprintf(
                'Theme package %s has no assets directory. Its out directory is not used anymore, '
                . 'move the files to the assets directory.',
                $this->getPackageName()
            )
        );
    }

    private function hasAssets(string $packagePath): bool
    {
        return is_dir(Path::join($packagePath, self::ASSETS_DIRECTORY));
    }

    private function hasOutDirectory(string $packagePath): bool
    {
        return is_dir(Path::join($packagePath, self::OUT_DIRECTORY));
    }

    private function getThemeConfigurationInstaller(): ThemeConfigurationInstallerInterface
    {
        try {
            return ContainerFacade::get(ThemeConfigurationInstallerInterface::class);
        } catch (\Throwable) {
            return $this->getBootstrapThemeConfigurationInstaller();
        }
    }

    private function getBootstrapThemeConfigurationInstaller(): ThemeConfigurationInstallerInterface
    {
        return $this->getBootstrapContainer()->get(ThemeConfigurationInstallerInterface::class);
    }

    private function getThemeAssetsInstaller(): ThemeAssetsInstallerInterface
    {
        return $this->getBootstrapContainer()->get(ThemeAssetsInstallerInterface::class);
    }
}
