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
 * @copyright (C) OXID eSales AG 2003-2016
 * @version   OXID eShop Composer plugin
 */

namespace OxidEsales\ComposerPlugin\Utilities;

/**
 * Class VfsFileStructureOperator.
 */
class VfsFileStructureOperator
{
    /**
     * Convert given flat file system structure into nested one.
     *
     * @param array|null $flatFileSystemStructure
     *
     * @return array
     */
    public static function nest($flatFileSystemStructure = null)
    {
        if (!is_null($flatFileSystemStructure) && !is_array($flatFileSystemStructure)) {
            throw new \InvalidArgumentException("Given input argument must be an array.");
        }

        if (is_null($flatFileSystemStructure)) {
            return [];
        }

        $nestedFileSystemStructure = [];

        foreach ($flatFileSystemStructure as $pathEntry => $contents) {
            $pathEntries = explode(DIRECTORY_SEPARATOR, $pathEntry);

            $pointerToBranch = &$nestedFileSystemStructure;
            foreach ($pathEntries as $singlePathEntry) {
                $singlePathEntry = trim($singlePathEntry);

                if ($singlePathEntry !== '') {
                    if (!is_array($pointerToBranch)) {
                        $pointerToBranch = [];
                    }

                    if (!key_exists($singlePathEntry, $pointerToBranch)) {
                        $pointerToBranch[$singlePathEntry] = [];
                    }

                    $pointerToBranch = &$pointerToBranch[$singlePathEntry];
                }
            }

            if (substr($pathEntry, -1) !== DIRECTORY_SEPARATOR) {
                $pointerToBranch = $contents;
            }
        }

        return $nestedFileSystemStructure;
    }
}
