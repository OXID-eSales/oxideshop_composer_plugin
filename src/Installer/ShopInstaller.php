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
use Symfony\Component\Filesystem\Filesystem;

/**  */
class ShopInstaller extends AbstractInstaller
{
    /** @var array Core directories, which are safe to overwrite. */
    private $overwritableDirectories = [
        'Application/Component',
        'Application/Controller',
        'Application/Model',
        'Core'
    ];

    /**
     * @return bool
     */
    public function isInstalled(PackageInterface $package)
    {
        return file_exists($this->getRootDirectory() .'/index.php');
    }

    /**
     * Copies all shop files from vendors to source directory.
     *
     * @param PackageInterface $package
     * @param string           $packagePath
     */
    public function install(PackageInterface $package, $packagePath)
    {
        $this->getIO()->write("Installing shop package");
        $fileSystem = $this->getFileSystem();
        $fileSystem->mirror($packagePath.'/source/', $this->getRootDirectory());
    }

    /**
     * Overwrites files in core directories.
     *
     * @param PackageInterface $package
     * @param string           $packagePath
     */
    public function update(PackageInterface $package, $packagePath)
    {
        $this->getIO()->write("Updating shop package");
        $fileSystem = $this->getFileSystem();
        foreach ($this->overwritableDirectories as $directory) {
            if ($fileSystem->exists("$packagePath/source/$directory")) {
                $fileSystem->mirror("$packagePath/source/$directory", $this->getRootDirectory()."/$directory", null, ['delete' => true]);
            }
        }
    }
}
