<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ComposerPlugin\Installer\Package;

use OxidEsales\EshopCommunity\Core\Di\ContainerFacade;
use OxidEsales\EshopCommunity\Internal\Container\BootstrapContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Theme\Install\Service\ThemeAssetsInstallerInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Theme\Install\Service\ThemeConfigurationInstallerInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Theme\MetaData\Exception\InvalidThemeMetaDataException;
use Psr\Container\ContainerInterface;
use Symfony\Component\Filesystem\Path;

/**
 * @inheritdoc
 */
class ThemePackageInstaller extends AbstractPackageInstaller
{
    private const ASSETS_DIRECTORY = 'assets';

    private ?ContainerInterface $bootstrapContainer = null;

    /**
     * @param string $packagePath
     *
     * @return bool
     */
    public function isInstalled(string $packagePath)
    {
        try {
            return $this->getBootstrapThemeConfigurationInstaller()->isInstalled($packagePath)
                && (!$this->hasAssets($packagePath) || $this->getThemeAssetsInstaller()->isInstalled($packagePath));
        } catch (InvalidThemeMetaDataException) {
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

    /**
     * @param string $packagePath
     */
    public function uninstall(string $packagePath): void
    {
        try {
            $this->getThemeConfigurationInstaller()->uninstall($packagePath);
            $this->getThemeAssetsInstaller()->uninstall($packagePath);
        } catch (InvalidThemeMetaDataException $exception) {
            $this->writeInvalidMetadataWarning($exception);
        }
    }

    /**
     * @return string
     */
    protected function getPackageTypeDescription(): string
    {
        return 'theme package';
    }

    private function installTheme(string $packagePath): void
    {
        try {
            $this->getBootstrapThemeConfigurationInstaller()->install($packagePath);
            $this->getThemeAssetsInstaller()->install($packagePath);
            $this->writeDoneMessage();
        } catch (InvalidThemeMetaDataException $exception) {
            $this->writeInvalidMetadataWarning($exception);
            $this->writeSkippedMessage();
        }
    }

    private function writeInvalidMetadataWarning(InvalidThemeMetaDataException $exception): void
    {
        $this->getIO()->warning(
            sprintf('Theme package %s is skipped: %s', $this->getPackageName(), $exception->getMessage())
        );
    }

    private function hasAssets(string $packagePath): bool
    {
        return is_dir(Path::join($packagePath, self::ASSETS_DIRECTORY));
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

    private function getBootstrapContainer(): ContainerInterface
    {
        if ($this->bootstrapContainer === null) {
            $this->bootstrapContainer = BootstrapContainerFactory::getBootstrapContainer();
        }

        return $this->bootstrapContainer;
    }
}
