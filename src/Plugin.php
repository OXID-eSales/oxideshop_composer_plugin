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

namespace OxidEsales\ComposerPlugin;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use OxidEsales\ComposerPlugin\Installer\Package\AbstractPackageInstaller;
use OxidEsales\ComposerPlugin\Installer\PackageInstallerTrigger;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    const ACTION_INSTALL = 'install';

    const ACTION_UPDATE = 'update';

    /** @var Composer */
    private $composer;

    /** @var IOInterface */
    private $io;

    /** @var PackageInstallerTrigger */
    private $packageInstallerTrigger;

    /**
     * Register events.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            'post-install-cmd' => 'installPackages',
            'post-update-cmd' => 'updatePackages'
        );
    }

    /**
     * Register shop packages installer.
     *
     * @param Composer    $composer
     * @param IOInterface $io
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $installer = new PackageInstallerTrigger($io, $composer);
        $composer->getInstallationManager()->addInstaller($installer);

        $this->composer = $composer;
        $this->io = $io;
        $this->packageInstallerTrigger = $installer;

        $extraSettings = $this->composer->getPackage()->getExtra();
        if (isset($extraSettings[AbstractPackageInstaller::EXTRA_PARAMETER_KEY_ROOT])) {
            $this->packageInstallerTrigger->setSettings($extraSettings[AbstractPackageInstaller::EXTRA_PARAMETER_KEY_ROOT]);
        }
    }

    /**
     * Run installation for oxid packages.
     */
    public function installPackages()
    {
        $this->executeAction(static::ACTION_INSTALL);
    }

    /**
     * Run update for oxid packages.
     */
    public function updatePackages()
    {
        $this->executeAction(static::ACTION_UPDATE);
    }

    protected function executeAction($actionName)
    {
        $repo = $this->composer->getRepositoryManager()->getLocalRepository();

        foreach ($repo->getPackages() as $package) {
            if ($this->packageInstallerTrigger->supports($package->getType())) {
                if ($actionName === static::ACTION_INSTALL) {
                    $this->packageInstallerTrigger->installPackage($package);
                }
                if ($actionName === static::ACTION_UPDATE) {
                    $this->packageInstallerTrigger->updatePackage($package);
                }
            }
        }
    }
}
