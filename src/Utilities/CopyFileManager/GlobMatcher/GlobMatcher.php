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
