<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ComposerPlugin\Installer\Package;

use Composer\Package\PackageInterface;
use OxidEsales\ComposerPlugin\Utilities\CopyFileManager\CopyGlobFilteredFileManager;
use Symfony\Component\Filesystem\Path;

/**
 * @inheritdoc
 */
class ThemePackageInstaller extends AbstractPackageInstaller
{
    public const METADATA_FILE_NAME = 'theme.php';
    public const PATH_TO_THEMES = "Application/views";

    /**
     * @param string $packagePath
     *
     * @return bool
     */
    public function isInstalled(string $packagePath)
    {
        return file_exists($this->formThemeTargetPath() . '/' . static::METADATA_FILE_NAME);
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
        $themeDirectoryName = $this->formThemeDirectoryName($this->getPackage());

        $templatesPath = str_replace(
            $themeDirectoryName,
            $this->highlightMessage($themeDirectoryName),
            $this->formThemeTargetPath()
        );

        $assetsPath = str_replace(
            $themeDirectoryName,
            $this->highlightMessage($themeDirectoryName),
            $this->formAssetsDirectoryName()
        );

        $question = 'All files in the following directories will be overwritten:' . PHP_EOL .
            '- ' . $templatesPath . PHP_EOL .
            '- ' . Path::join($this->getRootDirectory(), $assetsPath) . PHP_EOL .
            'Do you want to overwrite them? (y/N) ';

        if ($this->askQuestionIfNotInstalled($question, $packagePath)) {
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
    public function uninstall(string $packagePath): void
    {
        //not implemented yet
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
