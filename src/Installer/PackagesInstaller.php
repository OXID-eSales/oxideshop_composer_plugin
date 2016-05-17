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

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class responsible for triggering installation process .
 */
class PackagesInstaller extends LibraryInstaller
{
    /** @var array Available installers for packages. */
    private $installers = [
        'oxideshop' => ShopInstaller::class,
        'oxideshop-module' => ModuleInstaller::class
    ];

    /**
     * Decides if the installer supports the given type
     *
     * @param  string $packageType
     * @return bool
     */
    public function supports($packageType)
    {
        return array_key_exists($packageType, $this->installers);
    }

    /**
     * @param PackageInterface $package
     */
    public function installPackage(PackageInterface $package)
    {
        $installer = $this->createInstaller($package->getType());
        if (!$installer->isInstalled($package)) {
            $installer->install($package, $this->getInstallPath($package));
        } else {
            $installer->update($package, $this->getInstallPath($package));
        }
    }

    /**
     * Creates package installer.
     *
     * @param string $type
     *
     * @return AbstractInstaller
     */
    protected function createInstaller($type)
    {
        return new $this->installers[$type](new Filesystem(), $this->io, getcwd() . '/source');
    }
}
