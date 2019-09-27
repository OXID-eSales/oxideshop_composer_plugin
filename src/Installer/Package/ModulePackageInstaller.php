<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ComposerPlugin\Installer\Package;

use OxidEsales\EshopCommunity\Internal\Container\BootstrapContainerFactory;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Install\DataObject\OxidEshopPackage;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Install\Service\ModuleInstallerInterface;
use Webmozart\PathUtil\Path;

/**
 * @inheritdoc
 */
class ModulePackageInstaller extends AbstractPackageInstaller
{
    /** @var string MODULES_DIRECTORY */
    const MODULES_DIRECTORY = 'modules';

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
     * Update module files.
     *
     * @param string $packagePath
     */
    public function update($packagePath)
    {
        $moduleInstaller = $this->getModuleInstaller();
        $package = $this->getOxidShopPackage($packagePath);

        if ($moduleInstaller->isInstalled($package)) {
            if ($this->askQuestion("Update operation will overwrite {$this->getPackageName()} files in the directory source/modules. Do you want to overwrite them? (y/N) ")) {
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
