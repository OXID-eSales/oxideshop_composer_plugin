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

use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class is responsible for preparing project structure.
 * It copies necessary files to specific directories.
 */
abstract class AbstractInstaller
{
    const EXTRA_PARAMETER_KEY_ROOT = 'oxideshop';

    /** Used to install third party integrations. */
    const EXTRA_PARAMETER_KEY_TARGET = 'target-directory';

    /** Used to install third party integration assets. */
    const EXTRA_PARAMETER_KEY_ASSETS = 'assets-directory';

    /** Used to decide what the shop source directory is. */
    const EXTRA_PARAMETER_SOURCE_PATH = 'source-path';

    /** @var Filesystem */
    private $fileSystem;

    /** @var IOInterface */
    private $io;

    /** @var string */
    private $rootDirectory;

    /** @var PackageInterface */
    private $package;

    /**
     * AbstractInstaller constructor.
     *
     * @param Filesystem $fileSystem
     * @param IOInterface $io
     * @param string $rootDirectory
     * @param PackageInterface $package
     */
    public function __construct(Filesystem $fileSystem, IOInterface $io, $rootDirectory, PackageInterface $package)
    {
        $this->fileSystem = $fileSystem;
        $this->io = $io;
        $this->rootDirectory = $rootDirectory;
        $this->package = $package;
    }

    /**
     * Check whether given package is already installed.
     * @return mixed
     */
    abstract public function isInstalled();

    /**
     * Run package installation procedure. After installation files should be moved to correct location.
     *
     * @param string $packagePath Path to downloaded package in vendors directory.
     */
    abstract public function install($packagePath);

    /**
     * Run update procedure to keep package files up to date.
     *
     * @param string $packagePath Path to downloaded package in vendors directory.
     */
    abstract public function update($packagePath);

    /**
     * @return Filesystem
     */
    protected function getFileSystem()
    {
        return $this->fileSystem;
    }

    /**
     * @return IOInterface
     */
    protected function getIO()
    {
        return $this->io;
    }

    /**
     * @return string
     */
    protected function getRootDirectory()
    {
        return $this->rootDirectory;
    }

    /**
     * @return PackageInterface
     */
    public function getPackage()
    {
        return $this->package;
    }
}
