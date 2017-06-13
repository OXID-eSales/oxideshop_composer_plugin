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
