<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
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
    const OFFLINE_FILE = 'offline.html';
    const DISTRIBUTION_FILE_EXTENSION_MARK = '.dist';
    const SHOP_SOURCE_DIRECTORY = 'source';
    const SHOP_SOURCE_SETUP_DIRECTORY = 'Setup';
    const HTACCESS_FILTER = '**/.htaccess';
    const ROBOTS_EXCLUSION_FILTER = '**/robots.txt';
    const SETUP_FILES_FILTER = self::SHOP_SOURCE_SETUP_DIRECTORY . DIRECTORY_SEPARATOR . AbstractPackageInstaller::BLACKLIST_ALL_FILES;

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
        $this->writeInstallingMessage($this->getPackageTypeDescription());
        $this->writeCopyingMessage();
        $this->copyPackage($packagePath);
        $this->writeDoneMessage();
    }

    /**
     * Overwrites files in core directories.
     *
     * @param string $packagePath
     */
    public function update($packagePath)
    {
        $this->writeUpdatingMessage($this->getPackageTypeDescription());
        $question = 'All files in the following directories will be overwritten:' . PHP_EOL .
                    '- ' . $this->getTargetDirectoryOfShopSource() . PHP_EOL .
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
    private function copyPackage($packagePath)
    {
        $this->copyShopSourceFromPackageToTarget($packagePath);
        $this->copySetupFiles($packagePath);
        $this->copyConfigurationDistFileWithinTarget();
        $this->copyHtaccessFiles($packagePath);
        $this->copyOfflineFile($packagePath);
        $this->copyRobotsExclusionFiles($packagePath);
    }

    /**
     * Copy shop source files from package source to defined target path.
     *
     * @param string $packagePath
     */
    private function copyShopSourceFromPackageToTarget($packagePath)
    {
        $filtersToApply = [
            $this->getBlacklistFilterValue(),
            [self::HTACCESS_FILTER],
            [self::ROBOTS_EXCLUSION_FILTER],
            [self::SETUP_FILES_FILTER],
            [self::OFFLINE_FILE],
            $this->getVCSFilter(),
        ];

        CopyGlobFilteredFileManager::copy(
            $this->getPackageDirectoryOfShopSource($packagePath),
            $this->getTargetDirectoryOfShopSource(),
            $this->getCombinedFilters($filtersToApply)
        );
    }

    /**
     * Copy shop's configuration file from distribution file.
     */
    private function copyConfigurationDistFileWithinTarget()
    {
        $pathToConfig       = Path::join($this->getTargetDirectoryOfShopSource(), self::SHOP_SOURCE_CONFIGURATION_FILE);
        $pathToConfigDist   = $pathToConfig . self::DISTRIBUTION_FILE_EXTENSION_MARK;

        $this->copyFileIfIsMissing($pathToConfigDist, $pathToConfig);
    }

    /**
     * Copy shop's htaccess files from package.
     *
     * @param string $packagePath Absolute path which points to shop's package directory.
     */
    private function copyHtaccessFiles($packagePath)
    {
        $this->copyFilesFromSourceToInstallationByFilter(
            $packagePath,
            self::HTACCESS_FILTER
        );
    }

    /**
     * Copy shop's offline/maintenange page from package.
     *
     * @param string $packagePath Absolute path which points to shop's package directory.
     */
    private function copyOfflineFile($packagePath)
    {
        $this->copyFilesFromSourceToInstallationByFilter(
            $packagePath,
            self::OFFLINE_FILE
        );
    }

    /**
     * Copy shop's robots exclusion files from package.
     *
     * @param string $packagePath Absolute path which points to shop's package directory.
     */
    private function copyRobotsExclusionFiles($packagePath)
    {
        $this->copyFilesFromSourceToInstallationByFilter(
            $packagePath,
            self::ROBOTS_EXCLUSION_FILTER
        );
    }

    /**
     * Copy shop's setup files from package.
     *
     * @param string $packagePath Absolute path which points to shop's package directory.
     */
    private function copySetupFiles($packagePath)
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

    /**
     * Copy files from source to installation by filter.
     *
     * @param string $packagePath
     * @param string $filter
     */
    private function copyFilesFromSourceToInstallationByFilter($packagePath, $filter)
    {
        $sourceDirectory    = $this->getPackageDirectoryOfShopSource($packagePath);
        $filteredFiles      = $this->getFilteredFiles($sourceDirectory, $filter);

        foreach ($filteredFiles as $packageFilePath) {
            $installationFilePath = $this->getAbsoluteFilePathFromInstallation(
                $sourceDirectory,
                $packageFilePath
            );

            $this->copyFileIfIsMissing($packageFilePath, $installationFilePath);
        }
    }

    /**
     * Copy file if is missing.
     *
     * @param string $sourcePath
     * @param string $destinationPath
     */
    private function copyFileIfIsMissing($sourcePath, $destinationPath)
    {
        if (!file_exists($destinationPath)) {
            CopyGlobFilteredFileManager::copy(
                $sourcePath,
                $destinationPath
            );
        }
    }

    /**
     * Return filtered files.
     *
     * @param   string $directory
     * @param   string $filter
     *
     * @return  GlobIterator
     */
    private function getFilteredFiles($directory, $filter)
    {
        $glob = Path::join($directory, $filter);

        return new GlobIterator($glob);
    }

    /**
     * Return absolute path to file from installation.
     *
     * @param   string  $sourcePackageDirectory
     * @param   string  $absolutePathToFileFromPackage
     *
     * @return  string
     */
    private function getAbsoluteFilePathFromInstallation(
        $sourcePackageDirectory,
        $absolutePathToFileFromPackage
    ) {
        $installationDirectoryOfShopSource = $this->getTargetDirectoryOfShopSource();

        $relativePathOfSourceFromPackage = Path::makeRelative(
            $absolutePathToFileFromPackage,
            $sourcePackageDirectory
        );

        $absolutePathToFileFromInstallation = Path::join(
            $installationDirectoryOfShopSource,
            $relativePathOfSourceFromPackage
        );

        return $absolutePathToFileFromInstallation;
    }

    /**
     * @return string
     */
    private function getPackageTypeDescription(): string
    {
        return 'OXID eShop package';
    }
}
