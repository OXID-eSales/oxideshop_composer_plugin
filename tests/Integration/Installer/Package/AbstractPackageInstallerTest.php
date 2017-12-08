<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
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
