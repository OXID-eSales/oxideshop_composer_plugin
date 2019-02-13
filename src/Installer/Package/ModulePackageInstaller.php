<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ComposerPlugin\Installer\Package;

use OxidEsales\EshopCommunity\Internal\Application\BootstrapContainer\BootstrapContainerFactory;
use OxidEsales\EshopCommunity\Internal\Application\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Module\Install\DataObject\OxidEshopPackage;
use OxidEsales\EshopCommunity\Internal\Module\Install\Service\ModuleInstallerInterface;
use Webmozart\PathUtil\Path;

/**
 * @deprecated since v.3.0.0 (2019-01-31); This class will be @internal in future and not public to be used by 3rd party.
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

        if ($moduleInstaller->isInstalled($packagePath)) {
            if ($this->askQuestion("Update operation will overwrite {$this->getPackageName()} files in the directory source/modules. Do you want to overwrite them? (y/N) ")) {
                $this->getIO()->write("Updating module {$this->getPackageName()} files...");
                $moduleInstaller->install($packagePath);
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
        return new OxidEshopPackage($this->getPackage()->getName(), $packagePath, $this->getPackage()->getExtra());
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
