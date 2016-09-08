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
class DemodataInstaller extends AbstractInstaller
{
    const PATH_TO_TARGET_DEMODATA = "Setup/Sql/demodata.sql";
    const PATH_TO_OUT_DIRECTORY = "Out";

    /**
     * Copies module files to shop directory.
     *
     * @param string $packagePath
     */
    public function install($packagePath)
    {
        $package = $this->getPackage();
        $this->getIO()->write("Installing {$package->getName()} package");

        #Try getting installation path instructions from demodata package configuration
        $demodataFileTargetPath = static::PATH_TO_TARGET_DEMODATA;
        if ($this->getExtraParameterValueByKey('demodata-target')) {
            $demodataFileTargetPath = $this->getExtraParameterValueByKey('demodata-target');
        }
        $outDirectoryTargetPath = static::PATH_TO_OUT_DIRECTORY;
        if ($this->getExtraParameterValueByKey('out-target')) {
            $outDirectoryTargetPath = $this->getExtraParameterValueByKey('out-target');
        }

        $fileSystem = $this->getFileSystem();
        $fileSystem->copy($packagePath . "/src/demodata.sql", $this->getRootDirectory() . "/" . $demodataFileTargetPath);
        $fileSystem->mirror($packagePath . "/src/out", $this->getRootDirectory() . "/" . $outDirectoryTargetPath);
    }
}
