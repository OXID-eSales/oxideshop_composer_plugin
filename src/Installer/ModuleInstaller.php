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

namespace OxidEsales\ComposerPlugin\Installer;

use Composer\Package\PackageInterface;

/**
 * @inheritdoc
 */
class ModuleInstaller extends AbstractInstaller
{
    const METADATA_FILE_NAME = 'metadata.php';

    /**
     * @return bool
     */
    public function isInstalled()
    {
        return file_exists($this->formTargetPath() . '/' . static::METADATA_FILE_NAME . '');
    }

    /**
     * Copies module files to shop directory.
     *
     * @param string $packagePath
     */
    public function install($packagePath)
    {
        $package = $this->getPackage();
        $this->getIO()->write("Installing {$package->getName()} package");
        
        $fileSystem = $this->getFileSystem();
        $fileSystem->mirror($packagePath, $this->formTargetPath());
    }

    /**
     * Update module files.
     *
     * @param string $packagePath
     */
    public function update($packagePath)
    {
    }

    /**
     * @return string
     */
    protected function formTargetPath()
    {
        $package = $this->getPackage();
        $targetDirectory =  $this->getExtraParameterValueByKey(static::EXTRA_PARAMETER_KEY_TARGET);
        if (is_null($targetDirectory)) {
            $targetDirectory = $package->getName();
        }
        $targetDirectory = $this->getRootDirectory() . "/modules/$targetDirectory";
        return $targetDirectory;
    }
}
