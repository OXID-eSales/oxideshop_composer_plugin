<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ComposerPlugin\Installer\Package;

use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use OxidEsales\EshopCommunity\Internal\Container\BootstrapContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\DIContainer\Service\ProjectYamlImportServiceInterface;

class ComponentInstaller extends AbstractPackageInstaller
{
    public function install($packagePath)
    {
        $this->writeInstallingMessage("component");
        $this->importServiceFile($packagePath);
    }

    public function update($packagePath)
    {
        $this->writeUpdatingMessage("component");
        $this->importServiceFile($packagePath);
    }

    /**
     * @param $packagePath
     */
    protected function importServiceFile($packagePath)
    {
        $projectYamlImportService = BootstrapContainerFactory::getBootstrapContainer()->get(ProjectYamlImportServiceInterface::class);
        $projectYamlImportService->removeNonExistingImports();
        $projectYamlImportService->addImport($packagePath);
    }
}
