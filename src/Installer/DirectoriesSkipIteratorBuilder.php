<?php
/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 */

namespace OxidEsales\ComposerPlugin\Installer;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class DirectoriesSkipIteratorBuilder
{
    /**
     * @param string $packagePath
     * @param array  $directoriesToSkip
     *
     * @return RecursiveIteratorIterator
     */
    public function build($packagePath, $directoriesToSkip)
    {
        foreach ($directoriesToSkip as &$directory) {
            $directory = "$packagePath/$directory";
        }
        $directoryIterator = new RecursiveDirectoryIterator($packagePath, FilesystemIterator::SKIP_DOTS);
        $directoryFilter = new DirectoryRecursiveFilterIterator($directoryIterator, $directoriesToSkip);
        return new RecursiveIteratorIterator($directoryFilter, RecursiveIteratorIterator::SELF_FIRST);
    }
}
