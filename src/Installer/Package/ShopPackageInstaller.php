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
use Webmozart\Glob\Iterator\GlobIterator;
use Webmozart\PathUtil\Path;

/**
 * @inheritdoc
 */
class ShopPackageInstaller extends AbstractPackageInstaller
{
    const FILE_TO_CHECK_IF_PACKAGE_INSTALLED = 'index.php';
    const SHOP_SOURCE_CONFIGURATION_FILE = 'config.inc.php';
    const DISTRIBUTION_FILE_EXTENSION_MARK = '.dist';
    const SHOP_SOURCE_DIRECTORY = 'source';
    const SHOP_SOURCE_SETUP_DIRECTORY = 'Setup';
    const HTACCESS_FILTER = '**/.htaccess';
    const SETUP_FILES_FILTER = self::SHOP_SOURCE_SETUP_DIRECTORY . '/**/*.*';

    /**
     * @return bool
     */
    public function isInstalled()
    {
        return file_exists(
            Path::join($this->getTargetDirectoryOfShopSource(), self::FILE_TO_CHECK_IF_PACKAGE_INSTALLED)
        );
    }

    /**
     * Copies all shop files from vendors to source directory.
     *
     * @param string $packagePath
     */
    public function install($packagePath)
    {
        $this->getIO()->write("Installing shop package.");
        $this->copyPackage($packagePath);
    }

    /**
     * Overwrites files in core directories.
     *
     * @param string $packagePath
     */
    public function update($packagePath)
    {
        $this->getIO()->write("Installing shop package.");
        if ($this->askQuestionIfNotInstalled('Do you want to overwrite existing OXID eShop files? (Yes/No) ')) {
            $this->getIO()->write("Copying shop files to source directory...");
            $this->copyPackage($packagePath);
        }
    }

    /**
     * @param string $packagePath
     */
    private function copyPackage($packagePath)
    {
        $this->copyShopSourceFromPackageToTarget($packagePath);
        $this->copySetupFilesIfNecessary($packagePath);
        $this->copyConfigurationDistFileWithinTarget();
        $this->copyHtaccessFilesIfNecessary($packagePath);
    }

    /**
     * Copy shop source files from package source to defined target path.
     *
     * @param string $packagePath
     */
    private function copyShopSourceFromPackageToTarget($packagePath)
    {
        $blacklistFilterWithHtAccess = array_merge(
            $this->getBlacklistFilterValue(),
            [self::HTACCESS_FILTER, self::SETUP_FILES_FILTER]
        );

        CopyGlobFilteredFileManager::copy(
            $this->getPackageDirectoryOfShopSource($packagePath),
            $this->getTargetDirectoryOfShopSource(),
            $blacklistFilterWithHtAccess
        );
    }

    /**
     * Copy shop's configuration file from distribution file if necessary.
     */
    private function copyConfigurationDistFileWithinTarget()
    {
        $pathToConfig = Path::join($this->getTargetDirectoryOfShopSource(), self::SHOP_SOURCE_CONFIGURATION_FILE);
        $pathToConfigDist = $pathToConfig . self::DISTRIBUTION_FILE_EXTENSION_MARK;

        if (!file_exists($pathToConfig)) {
            CopyGlobFilteredFileManager::copy($pathToConfigDist, $pathToConfig);
        }
    }

    /**
     * Copy shop's htaccess files from package if necessary.
     *
     * @param string $packagePath Absolute path which points to shop's package directory.
     */
    private function copyHtaccessFilesIfNecessary($packagePath)
    {
        $packageDirectoryOfShopSource = $this->getPackageDirectoryOfShopSource($packagePath);
        $installationDirectoryOfShopSource = $this->getTargetDirectoryOfShopSource();

        $htAccessFilesIterator = new GlobIterator(Path::join($packageDirectoryOfShopSource, self::HTACCESS_FILTER));

        foreach ($htAccessFilesIterator as $absolutePathToHtAccessFromPackage) {
            $relativePathOfSourceFromPackage = Path::makeRelative(
                $absolutePathToHtAccessFromPackage,
                $packageDirectoryOfShopSource
            );
            $absolutePathToHtAccessFromInstallation = Path::join(
                $installationDirectoryOfShopSource,
                $relativePathOfSourceFromPackage
            );

            if (!file_exists($absolutePathToHtAccessFromInstallation)) {
                CopyGlobFilteredFileManager::copy(
                    $absolutePathToHtAccessFromPackage,
                    $absolutePathToHtAccessFromInstallation
                );
            }
        }
    }

    /**
     * Copy shop's setup files from package if necessary.
     *
     * @param string $packagePath Absolute path which points to shop's package directory.
     */
    private function copySetupFilesIfNecessary($packagePath)
    {
        $packageDirectoryOfShopSource = $this->getPackageDirectoryOfShopSource($packagePath);
        $installationDirectoryOfShopSource = $this->getTargetDirectoryOfShopSource();

        $shopConfigFileName = Path::join($installationDirectoryOfShopSource, self::SHOP_SOURCE_CONFIGURATION_FILE);

        if ($this->isConfigFileNotConfiguredOrMissing($shopConfigFileName)) {
            CopyGlobFilteredFileManager::copy(
                Path::join($packageDirectoryOfShopSource, self::SHOP_SOURCE_SETUP_DIRECTORY),
                Path::join($installationDirectoryOfShopSource, self::SHOP_SOURCE_SETUP_DIRECTORY)
            );
        }
    }

    /**
     * Return true if config file is not configured or missing.
     *
     * @param string $shopConfigFileName Absolute path to shop configuration file to check.
     *
     * @return bool
     */
    private function isConfigFileNotConfiguredOrMissing($shopConfigFileName)
    {
        if (!file_exists($shopConfigFileName)) {
            return true;
        }

        $shopConfigFileContents = file_get_contents($shopConfigFileName);
        $wordsIndicatingNotConfigured = [
            '<dbHost>',
            '<dbName>',
            '<dbUser>',
            '<dbPwd>',
            '<sShopURL>',
            '<sShopDir>',
            '<sCompileDir>',
        ];

        foreach ($wordsIndicatingNotConfigured as $word) {
            if (strpos($shopConfigFileContents, $word) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return package directory which points to shop's source directory.
     *
     * @param string $packagePath Absolute path which points to shop's package directory.
     *
     * @return string
     */
    private function getPackageDirectoryOfShopSource($packagePath)
    {
        return Path::join($packagePath, self::SHOP_SOURCE_DIRECTORY);
    }

    /**
     * Return target directory where shop's source files needs to be copied.
     *
     * @return string
     */
    private function getTargetDirectoryOfShopSource()
    {
        return $this->getRootDirectory();
    }
}
