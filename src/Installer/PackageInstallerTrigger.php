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
use OxidEsales\ComposerPlugin\Installer\Package\AbstractPackageInstaller;
use OxidEsales\ComposerPlugin\Installer\Package\ShopPackageInstaller;
use OxidEsales\ComposerPlugin\Installer\Package\ModulePackageInstaller;
use OxidEsales\ComposerPlugin\Installer\Package\ThemePackageInstaller;
use Webmozart\PathUtil\Path;

/**
 * Class responsible for triggering installation process.
 */
class PackageInstallerTrigger extends LibraryInstaller
{
    const TYPE_ESHOP = 'oxideshop';
    const TYPE_MODULE = 'oxideshop-module';
    const TYPE_THEME = 'oxideshop-theme';
    const TYPE_DEMODATA = 'oxideshop-demodata';

    /** @var array Available installers for packages. */
    private $installers = [
        self::TYPE_ESHOP => ShopPackageInstaller::class,
        self::TYPE_MODULE => ModulePackageInstaller::class,
        self::TYPE_THEME => ThemePackageInstaller::class,
    ];

    /**
     * @var array configurations
     */
    protected $settings = [];

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
     * @param array $settings Set additional settings.
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;
    }

    /**
     * @param PackageInterface $package
     */
    public function installPackage(PackageInterface $package)
    {
        $installer = $this->createInstaller($package);
        if (!$installer->isInstalled()) {
            $installer->install($this->getInstallPath($package));
        }
    }

    /**
     * @param PackageInterface $package
     */
    public function updatePackage(PackageInterface $package)
    {
        $installer = $this->createInstaller($package);
        $installer->update($this->getInstallPath($package));
    }

    /**
     * Get the path to shop's source directory.
     *
     * @return string
     */
    public function getShopSourcePath()
    {
        $shopSource = Path::join(getcwd(), ShopPackageInstaller::SHOP_SOURCE_DIRECTORY);

        if (isset($this->settings[AbstractPackageInstaller::EXTRA_PARAMETER_SOURCE_PATH])) {
            $shopSource = $this->settings[AbstractPackageInstaller::EXTRA_PARAMETER_SOURCE_PATH];
        }

        return $shopSource;
    }

    /**
     * Creates package installer.
     *
     * @param PackageInterface $package
     * @return AbstractPackageInstaller
     */
    protected function createInstaller(PackageInterface $package)
    {
        return new $this->installers[$package->getType()]($this->io, $this->getShopSourcePath(), $package);
    }
}
