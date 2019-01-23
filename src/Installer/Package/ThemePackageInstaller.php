<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ComposerPlugin\Installer\Package;

use Composer\Package\PackageInterface;
use OxidEsales\ComposerPlugin\Utilities\CopyFileManager\CopyGlobFilteredFileManager;
use Webmozart\PathUtil\Path;

/**
 * @inheritdoc
 */
class ThemePackageInstaller extends AbstractPackageInstaller
{
    const METADATA_FILE_NAME = 'theme.php';
    const PATH_TO_THEMES = "Application/views";

    /**
     * @return bool
     */
    public function isInstalled()
    {
        return file_exists($this->formThemeTargetPath().'/'.static::METADATA_FILE_NAME);
    }

    /**
     * Copies theme files to shop directory.
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
     * Overwrites theme files.
     *
     * @param string $packagePath
     */
    public function update($packagePath)
    {
        $this->writeUpdatingMessage($this->getPackageTypeDescription());
        $question = 'All files in the following directories will be overwritten:' . PHP_EOL .
                    '- ' . $this->formThemeTargetPath() . PHP_EOL .
                    '- ' . Path::join($this->getRootDirectory(), $this->formAssetsDirectoryName()) . PHP_EOL .
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
     * @param string $packagePath
     */
    protected function copyPackage($packagePath)
    {
        $filtersToApply = [
            [Path::join($this->formAssetsDirectoryName(), AbstractPackageInstaller::BLACKLIST_ALL_FILES)],
            $this->getBlacklistFilterValue(),
            $this->getVCSFilter(),
        ];

        CopyGlobFilteredFileManager::copy(
            $packagePath,
            $this->formThemeTargetPath(),
            $this->getCombinedFilters($filtersToApply)
        );

        $this->installAssets($packagePath);
    }

    /**
     * @return string
     */
    protected function formThemeTargetPath()
    {
        $package = $this->getPackage();
        $themeDirectoryName = $this->formThemeDirectoryName($package);
        return "{$this->getRootDirectory()}/" . static::PATH_TO_THEMES . "/$themeDirectoryName";
    }

    /**
     * @param string $packagePath
     */
    protected function installAssets($packagePath)
    {
        $package = $this->getPackage();
        $target = $this->getRootDirectory() . '/out/' . $this->formThemeDirectoryName($package);

        $assetsDirectory = $this->formAssetsDirectoryName();
        $source = $packagePath . '/' . $assetsDirectory;

        if (file_exists($source)) {
            CopyGlobFilteredFileManager::copy(
                $source,
                $target,
                $this->getBlacklistFilterValue()
            );
        }
    }

    /**
     * @param PackageInterface $package
     * @return string
     */
    protected function formThemeDirectoryName($package)
    {
        $themePath = $this->getExtraParameterValueByKey(static::EXTRA_PARAMETER_KEY_TARGET);
        if (is_null($themePath)) {
            $themePath = explode('/', $package->getName())[1];
        }
        return $themePath;
    }

    /**
     * @return null|string
     */
    protected function formAssetsDirectoryName()
    {
        $assetsDirectory = $this->getExtraParameterValueByKey(static::EXTRA_PARAMETER_KEY_ASSETS);
        if (is_null($assetsDirectory)) {
            $assetsDirectory = 'out';
        }
        return $assetsDirectory;
    }

    /**
     * @return string
     */
    protected function getPackageTypeDescription(): string
    {
        return 'theme package';
    }
}
