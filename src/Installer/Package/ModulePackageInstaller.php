<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ComposerPlugin\Installer\Package;

use OxidEsales\ComposerPlugin\Utilities\CopyFileManager\CopyGlobFilteredFileManager;
use Webmozart\PathUtil\Path;

/**
 * @inheritdoc
 */
class ModulePackageInstaller extends AbstractPackageInstaller
{
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
        $this->copyPackage($packagePath);
    }

    /**
     * Update module files.
     *
     * @param string $packagePath
     */
    public function update($packagePath)
    {
        if ($this->askQuestionIfNotInstalled("Update operation will overwrite {$this->getPackageName()} files."
            ." Do you want to continue? (y/N) ")) {
            $this->getIO()->write("Copying module {$this->getPackageName()} files...");
            $this->copyPackage($packagePath);
        }
    }

    /**
     * Copy files from package source to defined target path.
     *
     * @param string $packagePath Absolute path to the package.
     */
    protected function copyPackage($packagePath)
    {
        $filtersToApply = [
            $this->getBlacklistFilterValue(),
            $this->getVCSFilter(),
        ];

        CopyGlobFilteredFileManager::copy(
            $this->formSourcePath($packagePath),
            $this->formTargetPath(),
            $this->getCombinedFilters($filtersToApply)
        );
    }

    /**
     * If module source directory option provided add it's relative path.
     * Otherwise return plain package path.
     *
     * @param string $packagePath
     *
     * @return string
     */
    protected function formSourcePath($packagePath)
    {
        $sourceDirectory = $this->getExtraParameterValueByKey(static::EXTRA_PARAMETER_KEY_SOURCE);

        return !empty($sourceDirectory)?
            Path::join($packagePath, $sourceDirectory):
            $packagePath;
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
