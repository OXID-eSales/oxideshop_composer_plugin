<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ComposerPlugin\Installer;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use OxidEsales\ComposerPlugin\Installer\Package\AbstractPackageInstaller;
use OxidEsales\ComposerPlugin\Installer\Package\ComponentInstaller;
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
    const TYPE_COMPONENT = 'oxideshop-component';

    /** @var array Available installers for packages. */
    private $installers = [
        self::TYPE_ESHOP => ShopPackageInstaller::class,
        self::TYPE_MODULE => ModulePackageInstaller::class,
        self::TYPE_THEME => ThemePackageInstaller::class,
        self::TYPE_COMPONENT => ComponentInstaller::class,
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
