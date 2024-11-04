<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ComposerPlugin\Utilities\CopyFileManager\GlobMatcher\Iteration;

use OxidEsales\ComposerPlugin\Utilities\CopyFileManager\GlobMatcher\GlobMatcher;
use Symfony\Component\Filesystem\Path;

/**
 * An iterator which iterates through given iterator of files/directories and filters out the items described in list of
 * glob filter definitions (black list filtering).
 */
class BlacklistFilterIterator extends \FilterIterator
{
    /** @var array List of glob expressions, e.g. ["*.txt", "*.pdf"]. */
    private $globExpressionList;

    /** @var string Absolute root path from the start of iteration. */
    private $rootPath;

    /**
     * BlacklistFilterIterator constructor.
     *
     * @param \Iterator $iterator           An iterator which iterates through files/directories.
     * @param string    $rootPath           Absolute root path from the start of iteration.
     * @param array     $globExpressionList List of glob expressions, e.g. ["*.txt", "*.pdf"].
     */
    public function __construct(\Iterator $iterator, $rootPath, $globExpressionList)
    {
        parent::__construct($iterator);

        $this->globExpressionList = $globExpressionList;
        $this->rootPath = $rootPath;
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function accept(): bool
    {
        $path = $this->convertFromSplFileInfoToString(parent::current());

        return !GlobMatcher::matchAny($this->getRelativePath($path), $this->globExpressionList);
    }

    /**
     * Get relative path from given item of iteration compared to provided root path.
     *
     * @param string $absolutePath Absolute path from iteration.
     *
     * @return string
     */
    private function getRelativePath($absolutePath)
    {
        return Path::makeRelative($absolutePath, $this->rootPath);
    }

    /**
     * Returns string to absolute path from an entry of SplFileInfo.
     *
     * @param \SplFileInfo $item Item from iteration.
     *
     * @return string
     */
    private function convertFromSplFileInfoToString(\SplFileInfo $item)
    {
        return (string)$item;
    }
}
