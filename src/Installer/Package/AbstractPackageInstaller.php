<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
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
    const BLACKLIST_ALL_FILES = '**/*';

    /** Name of directory to be excluded for VCS */
    const BLACKLIST_VCS_DIRECTORY = '.git';

    /** Name of ignore files to be excluded for VCS */
    const BLACKLIST_VCS_IGNORE_FILE = '.gitignore';

    /** Glob filter expression to exclude VCS files */
    const BLACKLIST_VCS_DIRECTORY_FILTER = self::BLACKLIST_VCS_DIRECTORY . DIRECTORY_SEPARATOR . self::BLACKLIST_ALL_FILES;

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
     * @return string
     */
    protected function getRootDirectory()
    {
        return $this->rootDirectory;
    }

    /**
     * @return string
     */
    protected function getPackageName()
    {
        return $this->getPackage()->getName();
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

        $extraParameterValue = $extraParameters[static::EXTRA_PARAMETER_KEY_ROOT][$extraParameterKey] ?? null;

        return (!empty($extraParameterValue)) ? $extraParameterValue : $defaultValue;
    }

    /**
     * @return PackageInterface
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * Get VCS glob filter expression
     *
     * @return array
     */
    protected function getVCSFilter()
    {
        return [self::BLACKLIST_VCS_DIRECTORY_FILTER, self::BLACKLIST_VCS_IGNORE_FILE];
    }

    /**
     * Combine multiple glob expression lists into one list
     *
     * @param array $listOfGlobExpressionLists E.g. [["*.txt", "*.pdf"], ["*.md"]]
     *
     * @return array
     */
    protected function getCombinedFilters($listOfGlobExpressionLists)
    {
        $filters = [];
        foreach ($listOfGlobExpressionLists as $filter) {
            $filters = array_merge($filters, $filter);
        }

        return $filters;
    }

    /**
     * @param string $messageToAsk
     *
     * @return bool
     */
    protected function askQuestionIfNotInstalled($messageToAsk)
    {
        return $this->isInstalled() ? $this->askQuestion($messageToAsk) : true;
    }

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
     * Returns true if the human answer to the given question was answered with a positive value (Yes/yes/Y/y).
     *
     * @param string $messageToAsk
     *
     * @return bool
     */
    protected function askQuestion($messageToAsk)
    {
        $userInput = $this->getIO()->ask($messageToAsk, 'N');

        return $this->isPositiveUserInput($userInput);
    }

    /**
     * @return IOInterface
     */
    protected function getIO()
    {
        return $this->io;
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

    /**
     * @param string $packageType
     */
    protected function writeInstallingMessage(string $packageType)
    {
        $this->getIO()->write($this->getInstallingMessage($packageType));
    }

    /**
     * @param string $packageType
     *
     * @return string
     */
    protected function getInstallingMessage(string $packageType): string
    {
        return $this->getMessagePrefix() . "Installing {$packageType} {$this->getPackage()->getName()}";
    }

    /**
     * @return string
     */
    protected function getMessagePrefix(): string
    {
        return '<info>oxid-esales/oxideshop-composer-plugin:</info> ';
    }

    /**
     * @param string $packageType
     */
    protected function writeUpdatingMessage(string $packageType)
    {
        $this->getIO()->write($this->getUpdatingMessage($packageType));
    }

    /**
     * @param string $packageType
     *
     * @return string
     */
    protected function getUpdatingMessage(string $packageType): string
    {
        return $this->getMessagePrefix() . "Updating {$packageType} {$this->getPackage()->getName()}";
    }

    /**
     * @return mixed
     */
    protected function writeCopyingMessage()
    {
        return $this->getIO()->write($this->getCopyingMessage());
    }

    /**
     * @return string
     */
    protected function getCopyingMessage(): string
    {
        return 'Copying files ...';
    }

    /**
     * @return mixed
     */
    protected function writeDoneMessage()
    {
        return $this->getIO()->write($this->getDoneMessage());
    }

    /**
     * @return string
     */
    protected function getDoneMessage(): string
    {
        return 'Done';
    }

    /**
     * @return mixed
     */
    protected function writeSkippedMessage()
    {
        return $this->getIO()->write($this->getSkippedMessage());
    }

    /**
     * @return string
     */
    protected function getSkippedMessage(): string
    {
        return 'Skipped';
    }
}
