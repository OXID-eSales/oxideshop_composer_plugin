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
        $this->getIO()->write("Installing module {$this->getPackage()->getName()} package.");
        $this->getFileSystem()->mirror($this->formSourcePath($packagePath), $this->formTargetPath());
    }

    /**
     * Update module files.
     *
     * @param string $packagePath
     */
    public function update($packagePath)
    {
        if ($this->askQuestionIfNotInstalled("Update operation will overwrite {$this->getPackage()->getName()} files."
            ." Do you want to continue? (Yes/No) ")) {
            $this->getIO()->write("Copying module {$this->getPackage()->getName()} files...");
            $this->getFileSystem()->mirror(
                $this->formSourcePath($packagePath),
                $this->formTargetPath(),
                null,
                ['override' => true]
            );
        }
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

        if (empty($sourceDirectory)) {
            return $packagePath;
        }

        return $packagePath . "/$sourceDirectory";
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
