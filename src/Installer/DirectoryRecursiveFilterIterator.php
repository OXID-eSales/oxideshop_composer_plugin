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

namespace OxidEsales\ComposerPlugin\Installer;

use RecursiveFilterIterator;
use RecursiveIterator;

class DirectoryRecursiveFilterIterator extends RecursiveFilterIterator
{
    /** @var array */
    private $directoriesToSkip = [];

    /**
     * DirectoryRecursiveFilterIterator constructor.
     *
     * @param RecursiveIterator $iterator
     * @param                   $directoriesToSkip
     */
    public function __construct(RecursiveIterator $iterator, $directoriesToSkip)
    {
        $this->directoriesToSkip = $directoriesToSkip;
        parent::__construct($iterator);
    }

    /**
     * If directory start matches the one from provided array, it will be skipped
     *
     * @return bool
     */
    public function accept()
    {
        foreach ($this->directoriesToSkip as $skip) {
            $skip = preg_quote(rtrim($skip, '/'), '/');
            if (preg_match("/^${skip}(\\/|$)/", $this->current()->getPathName())) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return DirectoryRecursiveFilterIterator
     */
    public function getChildren()
    {
        return new self($this->getInnerIterator()->getChildren(), $this->directoriesToSkip);
    }
}
