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
     * @return bool
     */
    public function isInstalled()
    {
        return file_exists($this->formTargetPath());
    }

    /**
     * Copies module files to shop directory.
     *
     * @param string $packagePath
     */
    public function install($packagePath)
    {
        $this->getIO()->write("Installing module {$this->getPackageName()} package.");
        $moduleInstaller = $this->getModuleInstaller();
        $moduleInstaller->install($this->getOxidShopPackage($packagePath));
    }

    /**
     * @param PackageInterface $package
     */
    public function uninstall(PackageInterface $package): void
    {
        $moduleInstaller = $this->getModuleInstaller();
        $moduleInstaller->uninstall($this->getOxidShopPackage($packagePath));
    }

    /**
     * Update module files.
     *
     * @param string $packagePath
     */
    public function update($packagePath)
    {
        $moduleInstaller = $this->getModuleInstaller();
        $package = $this->getOxidShopPackage($packagePath);

        /**
         * We check only files because during the first composer update modules may not have installed configuration
         * and module files are getting overwritten without asking if ModuleInstallerInterface is used.
         */
        if ($this->getModuleFilesInstaller()->isInstalled($package)) {
            $question = "Update operation will overwrite {$this->getPackageName()} files in the directory ";
            $question .= "source/modules. Do you want to overwrite them? (y/N) ";
            if ($this->askQuestion($question)) {
                $this->getIO()->write("Updating module {$this->getPackageName()} files...");
                $moduleInstaller->install($package);
            }
        } else {
            $this->install($packagePath);
        }
    }

    /**
     * @return ModuleInstallerInterface
     */
    private function getModuleInstaller(): ModuleInstallerInterface
    {
        $container = BootstrapContainerFactory::getBootstrapContainer();
        return $container->get(ModuleInstallerInterface::class);
    }

    /**
     * @return ModuleFilesInstallerInterface
     */
    private function getModuleFilesInstaller(): ModuleFilesInstallerInterface
    {
        $container = BootstrapContainerFactory::getBootstrapContainer();
        return $container->get(ModuleFilesInstallerInterface::class);
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

        if (isset($extraParameters['oxideshop']['blacklist-filter'])) {
            $package->setBlackListFilters($extraParameters['oxideshop']['blacklist-filter']);
        }

        if (isset($extraParameters['oxideshop']['source-directory'])) {
            $package->setSourceDirectory($extraParameters['oxideshop']['source-directory']);
        }

        if (isset($extraParameters['oxideshop']['target-directory'])) {
            $package->setTargetDirectory($extraParameters['oxideshop']['target-directory']);
        }

        return $package;
    }

    /**
     * @return string
     */
    protected function formTargetPath()
    {
        $targetDirectory = $this->getExtraParameterValueByKey(
            static::EXTRA_PARAMETER_KEY_TARGET,
            $this->getPackage()->getName()
        );

        return Path::join($this->getRootDirectory(), static::MODULES_DIRECTORY, $targetDirectory);
    }
}
