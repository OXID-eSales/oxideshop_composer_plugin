<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ComposerPlugin\Installer\Package;

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
        $package = $this->getOxidShopPackage($packagePath);
        $this->installOrUpdate($package);
    }

    /**
     * @param $package OxidEshopPackage
     */
    private function installOrUpdate($package){
        $moduleInstaller = $this->getModuleInstaller();
        $moduleInstaller->install($package);
        $installationInfo = ['version'=>$this->getPackage()
            ->getVersion()];
        $dataAsJson = json_encode($installationInfo);
        file_put_contents($this->formTargetPath() . DIRECTORY_SEPARATOR . 'installed.json', $data_as_json);
    }

    /**
     * Update module files.
     *
     * @param string $packagePath
     */
    public function update($packagePath)
    {
        $package = $this->getOxidShopPackage($packagePath);

        /**
         * We check only files because during the first composer update modules may not have installed configuration
         * and module files are getting overwritten without asking if ModuleInstallerInterface is used.
         */
        $moduleFilesInstaller = $this->getModuleFilesInstaller();
        if ($moduleFilesInstaller->isInstalled($package)) {

            if ($this->isInstalledInSameVersion()) {
                $this->getIO()->write("Module {$this->getPackageName()} is already up-to-date in version {$this->getPackage()->getVersion()}.");
                return;
            }

            if ($this->askQuestion("Update operation will overwrite {$this->getPackageName()} files in the directory source/modules. Do you want to overwrite them? (y/N) ")) {
                $this->getIO()->write("Updating module {$this->getPackageName()} files...");
                $this->installOrUpdate($package);
            }
        } else {
            $this->install($packagePath);
        }
    }

    public function isInstalledInSameVersion() {
        $targetPath = $this->formTargetPath();
        $filename = $targetPath . '/installed.json';
        if (file_exists($filename)) {
            $data = json_decode(file_get_contents($filename), true);
            if (!$this->getPackage()->isDev() &&
                $data['version'] &&
                $data['version'] == $this->getPackage()->getVersion()
            ){
                return true;
            }
        }
        return false;
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
