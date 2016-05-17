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

namespace OxidEsales\ComposerPlugin\Tests\Integration\Installer;

/**
 * Makes a structure for vfsStream.
 */
class StructurePreparator
{
    /**
     * @param array $structure
     *
     * @return array
     */
    public function prepareStructure($structure)
    {
        $newStructure = [];
        foreach ($structure as $path => $element) {
            $position = &$newStructure;
            foreach (explode('/', $path) as $part) {
                $position[$part] = [];
                $position = &$position[$part];
            }
            $position = strpos($path, '/') === false ? [] : $position;
            $position = is_array($element) ? $this->prepareStructure($element) : $element;
        }
        return $newStructure;
    }
}
