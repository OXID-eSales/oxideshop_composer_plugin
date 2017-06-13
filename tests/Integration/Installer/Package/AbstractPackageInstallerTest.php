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

namespace OxidEsales\ComposerPlugin\Tests\Integration\Installer\Package;

use OxidEsales\ComposerPlugin\Utilities\VfsFileStructureOperator;
use org\bovigo\vfs\vfsStream;
use Webmozart\PathUtil\Path;

abstract class AbstractPackageInstallerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->setupVirtualFileSystem();
    }

    protected function setupVirtualFileSystem()
    {
        vfsStream::setup('root', 777,
            [
                'vendor' => [],
                'source' => [],
            ]
        );
    }

    protected function setupVirtualProjectRoot($prefix, $input)
    {
        $updated = [];

        foreach ($input as $path => $contents) {
            $updated[Path::join($prefix, $path)] = $contents;
        }

        return vfsStream::create(VfsFileStructureOperator::nest($updated));
    }

    protected function getVirtualShopSourcePath()
    {
        return $this->getVirtualFileSystemRootPath('source');
    }

    protected function getVirtualVendorPath()
    {
        return $this->getVirtualFileSystemRootPath('vendor');
    }

    protected function getVirtualFileSystemRootPath($suffix = '')
    {
        return Path::join(vfsStream::url('root'), $suffix);
    }

    protected function assertVirtualFileExists($path)
    {
        $this->assertFileExists($this->getVirtualFileSystemRootPath($path));
    }

    protected function assertVirtualFileNotExists($path)
    {
        $this->assertFileNotExists($this->getVirtualFileSystemRootPath($path));
    }

    protected function assertVirtualFileEquals($expected, $actual)
    {
        $this->assertFileEquals(
            $this->getVirtualFileSystemRootPath($expected),
            $this->getVirtualFileSystemRootPath($actual)
        );
    }

    protected function assertVirtualFileNotEquals($expected, $actual)
    {
        $this->assertFileNotEquals(
            $this->getVirtualFileSystemRootPath($expected),
            $this->getVirtualFileSystemRootPath($actual)
        );
    }
}
