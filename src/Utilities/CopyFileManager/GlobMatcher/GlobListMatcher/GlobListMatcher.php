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

namespace OxidEsales\ComposerPlugin\Utilities\CopyFileManager\GlobMatcher\GlobListMatcher;

use OxidEsales\ComposerPlugin\Utilities\CopyFileManager\GlobMatcher\Integration\AbstractGlobMatcher;

/**
 * Class GlobListMatcher.
 *
 * Enables glob matching for a relative path against a list of glob expressions.
 */
class GlobListMatcher
{
    /** @var AbstractGlobMatcher */
    protected $globMatcher;

    /**
     * GlobListMatcher constructor.
     *
     * @param AbstractGlobMatcher $globMatcher Instance of a variant from AbstractGlobMatcher.
     */
    public function __construct($globMatcher)
    {
        $this->globMatcher = $globMatcher;
    }

    /**
     * Returns true if given relative path matches against at least one glob expression from provided list.
     *
     * @param string $relativePath
     * @param array  $globExpressionList List of glob expressions, e.g. ["*.txt", "*.pdf"].
     *
     * @throws \InvalidArgumentException If $globExpressionList is not a \Traversable instance.
     *
     * @return bool
     */
    public function matchAny($relativePath, $globExpressionList)
    {
        if (!is_array($globExpressionList) && (!$globExpressionList instanceof \Traversable)
            && (!is_null($globExpressionList))) {
            $message = "Given value \"$globExpressionList\" is not a valid glob expression list. ".
                "Valid entry must be a list of glob expressions e.g. [\"*.txt\", \"*.pdf\"].";

            throw new \InvalidArgumentException($message);
        }

        if (count($globExpressionList) > 0) {
            return $this->isMatchInList($relativePath, $globExpressionList);
        }

        return false;
    }

    /**
     * Returns true if the supplied globMatcher indicates a match for at least one item in given glob expression list.
     *
     * @param string $relativePath
     * @param array  $globExpressionList List of glob expressions, e.g. ["*.txt", "*.pdf"].
     *
     * @return bool
     */
    private function isMatchInList($relativePath, $globExpressionList)
    {
        foreach ($globExpressionList as $globExpression) {
            if ($this->globMatcher->match($relativePath, $globExpression)) {
                return true;
            }
        }

        return false;
    }
}
