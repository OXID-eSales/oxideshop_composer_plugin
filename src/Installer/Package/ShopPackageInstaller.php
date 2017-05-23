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

    /**
     * @return bool
     */
    public function isInstalled()
    {
        return file_exists(Path::join($this->getRootDirectory(), self::FILE_TO_CHECK_IF_PACKAGE_INSTALLED));
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
     * @param $packagePath
     */
    protected function copyPackage($packagePath)
    {
        $packagePath = Path::join($packagePath, self::SHOP_SOURCE_DIRECTORY);
        $root = $this->getRootDirectory();

        CopyGlobFilteredFileManager::copy(
            $packagePath,
            $root,
            $this->getBlacklistFilterValue()
        );

        $pathToConfig = Path::join($root, self::SHOP_SOURCE_CONFIGURATION_FILE);
        $pathToConfigDist = $pathToConfig . self::DISTRIBUTION_FILE_EXTENSION_MARK;

        if (!file_exists($pathToConfig)) {
            CopyGlobFilteredFileManager::copy($pathToConfigDist, $pathToConfig);
        }
    }
}
