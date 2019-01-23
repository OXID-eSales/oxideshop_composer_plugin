<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ComposerPlugin\Utilities\CopyFileManager\GlobMatcher;

use OxidEsales\ComposerPlugin\Utilities\CopyFileManager\GlobMatcher\Integration\WebmozartGlobMatcher;
use OxidEsales\ComposerPlugin\Utilities\CopyFileManager\GlobMatcher\GlobListMatcher\GlobListMatcher;

/**
 * Class GlobMatcher.
 *
 * Expose multiple glob matching interface for given relative path.
 */
class GlobMatcher
{
    /**
     * @param string $relativePath       Relative path to match against.
     * @param array  $globExpressionList List of glob expressions, e.g. ["*.txt", "*.pdf"].
     *
     * @return bool True if given path matches any of given glob expression.
     */
    public static function matchAny($relativePath, $globExpressionList)
    {
        $globMatcher = new WebmozartGlobMatcher();
        $globListMatcher = new GlobListMatcher($globMatcher);

        return $globListMatcher->matchAny($relativePath, $globExpressionList);
    }
}
