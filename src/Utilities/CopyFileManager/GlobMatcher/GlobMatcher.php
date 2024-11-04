<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ComposerPlugin\Utilities\CopyFileManager\GlobMatcher;

use OxidEsales\ComposerPlugin\Utilities\CopyFileManager\GlobMatcher\Integration\WebmozartGlobMatcher;
use OxidEsales\ComposerPlugin\Utilities\CopyFileManager\GlobMatcher\GlobListMatcher\GlobListMatcher;

/**
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
        return (new GlobListMatcher(new WebmozartGlobMatcher()))
            ->matchAny($relativePath, $globExpressionList);
    }
}
