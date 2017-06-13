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
 * @copyright (C) OXID eSales AG 2003-2017
 * @version   OXID eShop Composer plugin
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
