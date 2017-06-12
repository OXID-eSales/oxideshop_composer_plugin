<?php
/**
 * This file is part of OXID eShop Composer plugin.
 *
 * OXID eShop Composer plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eShop Composer plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eShop Composer plugin.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2016
 * @version   OXID eShop Composer plugin
 */

namespace OxidEsales\ComposerPlugin\Installer\Package;

use OxidEsales\ComposerPlugin\Utilities\CopyFileManager\CopyGlobFilteredFileManager;
use Webmozart\PathUtil\Path;
use Composer\Package\PackageInterface;

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
        $this->getIO()->write("Installing {$this->getPackage()->getName()} package");
        $this->copyPackage($packagePath);
    }

    /**
     * Overwrites theme files.
     *
     * @param string $packagePath
     */
    public function update($packagePath)
    {
        if ($this->askQuestionIfNotInstalled("Update operation will overwrite {$this->getPackage()->getName()} files."
            ." Do you want to continue? (Yes/No) ")) {
            $this->getIO()->write("Copying theme {$this->getPackage()->getName()} files...");
            $this->copyPackage($packagePath);
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
}
