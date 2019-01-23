<?php declare(strict_types=1);
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
        $this->writeInstallingMessage($this->getPackageTypeDescription());
        $this->writeCopyingMessage();
        $this->copyPackage($packagePath);
        $this->writeDoneMessage();
    }

    /**
     * Update module files.
     *
     * @param string $packagePath
     */
    public function update($packagePath)
    {
        $this->writeUpdatingMessage($this->getPackageTypeDescription());
        $question = 'All files in the following directories will be overwritten:' . PHP_EOL .
                    '- ' . $this->formTargetPath() . PHP_EOL .
                    'Do you want to overwrite them? (y/N) ';

        if ($this->askQuestionIfNotInstalled($question)) {
            $this->writeCopyingMessage();
            $this->copyPackage($packagePath);
            $this->writeDoneMessage();
        } else {
            $this->writeSkippedMessage();
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

    /**
     * @return string
     */
    private function getPackageTypeDescription(): string
    {
        return 'module package';
    }
}
