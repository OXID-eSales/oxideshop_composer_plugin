<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ComposerPlugin\Utilities\CopyFileManager\GlobMatcher\Integration;

use Webmozart\Glob\Glob;

/**
 * Class WebmozartGlobMatcher.
 *
 * An integration of "webmozart/glob" package to match AbstractGlobMatcher.
 */
class WebmozartGlobMatcher extends AbstractGlobMatcher
{
    /**
     * Check if given path matches provided glob expression using "webmozart/glob" package.
     *
     * @param string $relativePath
     * @param string $globExpression Glob filter expressions, e.g. "*.txt" or "*.pdf".
     *
     * @return bool True in case the path matches the given glob expression.
     */
    protected function isGlobMatch($relativePath, $globExpression)
    {
        return Glob::match("/$relativePath", "/$globExpression");
    }
}
