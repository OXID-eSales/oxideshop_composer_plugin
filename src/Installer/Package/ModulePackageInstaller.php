<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ComposerPlugin\Installer\Package;

use OxidEsales\ComposerPlugin\Utilities\CopyFileManager\CopyGlobFilteredFileManager;
use OxidEsales\EshopCommunity\Internal\Application\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Module\Install\Service\ModuleInstallerInterface;
use Webmozart\PathUtil\Path;

/**
 * @inheritdoc
 */
class ModulePackageInstaller extends AbstractPackageInstaller
{
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
        $moduleInstaller->install($packagePath);
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

    private function getModuleInstaller(): ModuleInstallerInterface
    {
        $container = ContainerFactory::getInstance()->getContainer();
        return $container->get(ModuleInstallerInterface::class);
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
