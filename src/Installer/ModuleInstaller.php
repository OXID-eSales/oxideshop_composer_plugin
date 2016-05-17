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
    const EXTRA_PARAMETER_KEY_ROOT = 'oxideshop';
    const EXTRA_PARAMETER_KEY_MODULE = 'module-name';
    const EXTRA_PARAMETER_KEY_VENDOR = 'vendor-name';

    /** @var PackageInterface */
    private $package;

    /**
     * @return bool
     */
    public function isInstalled(PackageInterface $package)
    {
        $this->setPackage($package);
        return file_exists($this->formModuleTargetPath() .'/metadata.php');
    }

    /**
     * Copies module files to shop directory.
     *
     * @param PackageInterface $package
     * @param string           $packagePath
     */
    public function install(PackageInterface $package, $packagePath)
    {
        $packageName = $package->getName();
        $this->getIO()->write("Installing $packageName package");
        $this->setPackage($package);

        $packagePath = rtrim($packagePath, '/') ;

        $targetDirectory = $this->formModuleTargetPath();
        $fileSystem = $this->getFileSystem();
        $fileSystem->mirror($packagePath, $targetDirectory);
    }

    /**
     * Update module files.
     *
     * @param PackageInterface $package
     * @param string           $packagePath
     */
    public function update(PackageInterface $package, $packagePath)
    {
    }

    /**
     * @return PackageInterface
     */
    protected function getPackage()
    {
        return $this->package;
    }

    /**
     * @param PackageInterface $package
     */
    protected function setPackage($package)
    {
        $this->package = $package;
    }

    /**
     * @return string
     */
    protected function formModuleTargetPath()
    {
        $package = $this->getPackage();
        $vendorName = $this->getExtraParameterValueByKey(static::EXTRA_PARAMETER_KEY_VENDOR);
        if (is_null($vendorName)) {
            $vendorName = explode('/', $package->getName())[0];
        }
        $moduleName =  $this->getExtraParameterValueByKey(static::EXTRA_PARAMETER_KEY_MODULE);
        if (is_null($moduleName)) {
            $moduleName = explode('/', $package->getName())[1];
        }
        $targetDirectory = $this->getRootDirectory() . "/modules/$vendorName/$moduleName";
        return $targetDirectory;
    }

    /**
     * @param $extraParameterKey
     * @return null|string
     */
    protected function getExtraParameterValueByKey($extraParameterKey)
    {
        $extraParameterValue = null;
        $package = $this->getPackage();
        $extraParameters = $package->getExtra();
        if (isset($extraParameters[static::EXTRA_PARAMETER_KEY_ROOT]) && isset($extraParameters[static::EXTRA_PARAMETER_KEY_ROOT][$extraParameterKey])) {
            $extraParameterValue =  $extraParameters[static::EXTRA_PARAMETER_KEY_ROOT][$extraParameterKey];
        }
        return $extraParameterValue;
    }
}
