<?php declare(strict_types=1);
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
 * @copyright (C) OXID eSales AG 2003-2017
 * @version   OXID eShop Composer plugin
 */

namespace OxidEsales\ComposerPlugin\Utilities\CopyFileManager\GlobMatcher\Iteration;

use OxidEsales\ComposerPlugin\Utilities\CopyFileManager\GlobMatcher\GlobMatcher;
use Webmozart\PathUtil\Path;

/**
 * Class BlacklistFilterIterator.
 *
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
    public function accept()
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
