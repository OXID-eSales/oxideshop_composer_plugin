<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ComposerPlugin\Utilities\CopyFileManager\GlobMatcher\Integration;

use InvalidArgumentException;
use Webmozart\PathUtil\Path;

/**
 * Class AbstractGlobMatcher.
 *
 * Abstract which defines API for matching a path against a glob expression.
 */
abstract class AbstractGlobMatcher
{
    /**
     * Returns true if given path matches a glob expression.
     *
     * @param string $relativePath
     * @param string $globExpression Glob filter expressions, e.g. "*.txt" or "*.pdf".
     *
     * @throws \InvalidArgumentException If given $globExpression is not a valid string.
     * @throws \InvalidArgumentException If given $globExpression is an absolute path.
     *
     * @return bool
     */
    public function match($relativePath, $globExpression)
    {
        if (!is_string($globExpression) && !is_null($globExpression)) {
            $message = "Given value \"$globExpression\" is not a valid glob expression. ".
                "Valid expression must be a string e.g. \"*.txt\".";

            throw new InvalidArgumentException($message);
        }

        if (Path::isAbsolute((string)$globExpression)) {
            $message = "Given value \"$globExpression\" is an absolute path. ".
                "Glob expression can only be accepted if it's a relative path.";

            throw new InvalidArgumentException($message);
        }

        if (is_null($globExpression)) {
            return true;
        }

        return static::isGlobMatch($relativePath, $globExpression);
    }

    /**
     * Implementation details for matching a given path against glob expression.
     *
     * @param string $relativePath
     * @param string $globExpression Glob filter expressions, e.g. "*.txt" or "*.pdf".
     */
    abstract protected function isGlobMatch($relativePath, $globExpression);
}
