<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
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
        if (null !== $flatFileSystemStructure && false === is_array($flatFileSystemStructure)) {
            throw new \InvalidArgumentException("Given input argument must be an array.");
        }

        if (null === $flatFileSystemStructure) {
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
