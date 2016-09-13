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
use OxidEsales\ComposerPlugin\Installer\PackagesInstaller;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    /** @var Composer */
    private $composer;

    /** @var IOInterface */
    private $io;


    /**
     * Register events.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            'post-install-cmd' => 'installPackages',
            'post-update-cmd' => 'installPackages'
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
        $installer = new PackagesInstaller($io, $composer);
        $composer->getInstallationManager()->addInstaller($installer);

        $this->composer = $composer;
        $this->io = $io;
    }

    /**
     * Run installation for oxid packages.
     */
    public function installPackages()
    {
        $repo = $this->composer->getRepositoryManager()->getLocalRepository();
        $extraSettings = $this->composer->getPackage()->getExtra();

        $packagesInstaller = new PackagesInstaller($this->io, $this->composer);
        $packagesInstaller->setSettings($extraSettings);

        foreach ($repo->getPackages() as $package) {
            if ($packagesInstaller->supports($package->getType())) {
                $packagesInstaller->installPackage($package);
            }
        }
    }
}
