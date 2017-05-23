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

namespace OxidEsales\ComposerPlugin\Installer\Package;

use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;

/**
 * Class is responsible for preparing project structure.
 * It copies necessary files to specific directories.
 */
abstract class AbstractPackageInstaller
{
    const EXTRA_PARAMETER_KEY_ROOT = 'oxideshop';

    /** Used to determine third party package internal source path. */
    const EXTRA_PARAMETER_KEY_SOURCE = 'source-directory';

    /** Used to install third party integrations. */
    const EXTRA_PARAMETER_KEY_TARGET = 'target-directory';

    /** Used to install third party integration assets. */
    const EXTRA_PARAMETER_KEY_ASSETS = 'assets-directory';

    /** Used to decide what the shop source directory is. */
    const EXTRA_PARAMETER_SOURCE_PATH = 'source-path';

    /** List of glob expressions used to blacklist files being copied. */
    const EXTRA_PARAMETER_FILTER_BLACKLIST = 'blacklist-filter';

    /** Glob expression to filter all files, might be used to filter whole directory. */
    const BLACKLIST_ALL_FILES = '**/*.*';

    /** @var IOInterface */
    private $io;

    /** @var string */
    private $rootDirectory;

    /** @var PackageInterface */
    private $package;

    /**
     * AbstractInstaller constructor.
     *
     * @param IOInterface      $io
     * @param string           $rootDirectory
     * @param PackageInterface $package
     */
    public function __construct(IOInterface $io, $rootDirectory, PackageInterface $package)
    {
        $this->io = $io;
        $this->rootDirectory = $rootDirectory;
        $this->package = $package;
    }

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
     * Check whether given package is already installed.
     *
     * @return bool
     */
    public function isInstalled()
    {
        return false;
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

    /**
     * @return string
     */
    protected function getPackageName()
    {
        return $this->package->getName();
    }

    /**
     * Search for parameter with specific key in "extra" composer configuration block
     *
     * @param string $extraParameterKey
     * @param string $defaultValue
     *
     * @return array|string|null
     */
    protected function getExtraParameterValueByKey($extraParameterKey, $defaultValue = null)
    {
        $extraParameters = $this->getPackage()->getExtra();

        $extraParameterValue = isset($extraParameters[static::EXTRA_PARAMETER_KEY_ROOT][$extraParameterKey])?
            $extraParameters[static::EXTRA_PARAMETER_KEY_ROOT][$extraParameterKey]:
            null;

        return (!empty($extraParameterValue)) ? $extraParameterValue : $defaultValue;
    }

    /**
     * Return the value defined in composer extra parameters for blacklist filtering.
     *
     * @return array
     */
    protected function getBlacklistFilterValue()
    {
        return $this->getExtraParameterValueByKey(static::EXTRA_PARAMETER_FILTER_BLACKLIST, []);
    }

    /**
     * @param string $messageToAsk
     * @return bool
     */
    protected function askQuestionIfNotInstalled($messageToAsk)
    {
        return $this->isInstalled() ? $this->askQuestion($messageToAsk) : true;
    }

    /**
     * Returns true if the human answer to the given question was answered with a positive value (Yes/yes/Y/y).
     *
     * @param string $messageToAsk
     * @return bool
     */
    protected function askQuestion($messageToAsk)
    {
        $userInput = $this->getIO()->ask($messageToAsk, 'No');

        return $this->isPositiveUserInput($userInput);
    }

    /**
     * Return true if the input from user is a positive answer (Yes/yes/Y/y)
     *
     * @param string $userInput Raw user input
     *
     * @return bool
     */
    private function isPositiveUserInput($userInput)
    {
        $positiveAnswers = ['yes', 'y'];

        return in_array(strtolower(trim($userInput)), $positiveAnswers, true);
    }
}
