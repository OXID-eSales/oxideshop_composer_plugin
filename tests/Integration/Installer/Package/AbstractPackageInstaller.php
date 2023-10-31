<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ComposerPlugin\Tests\Integration\Installer\Package;

use OxidEsales\ComposerPlugin\Utilities\VfsFileStructureOperator;
use org\bovigo\vfs\vfsStream;
use Symfony\Component\Filesystem\Path;

abstract class AbstractPackageInstaller extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        $this->setupVirtualFileSystem();
    }

    protected function setupVirtualFileSystem(): void
    {
        vfsStream::setup(
            'root',
            777,
            [
                'vendor' => [],
                'source' => [],
            ]
        );
    }

    protected function setupVirtualProjectRoot($prefix, $input): \org\bovigo\vfs\vfsStreamDirectory
    {
        $updated = [];

        foreach ($input as $path => $contents) {
            $updated[Path::join($prefix, $path)] = $contents;
        }

        return vfsStream::create(VfsFileStructureOperator::nest($updated));
    }

    protected function getVirtualShopSourcePath(): string
    {
        return $this->getVirtualFileSystemRootPath('source');
    }

    protected function getVirtualVendorPath(): string
    {
        return $this->getVirtualFileSystemRootPath('vendor');
    }

    protected function getVirtualFileSystemRootPath($suffix = ''): string
    {
        return Path::join(vfsStream::url('root'), $suffix);
    }

    protected function assertVirtualFileExists($path): void
    {
        $this->assertFileExists($this->getVirtualFileSystemRootPath($path));
    }

    protected function assertVirtualFileNotExists($path): void
    {
        $this->assertFileDoesNotExist($this->getVirtualFileSystemRootPath($path));
    }

    protected function assertVirtualFileEquals($expected, $actual): void
    {
        $this->assertFileEquals(
            $this->getVirtualFileSystemRootPath($expected),
            $this->getVirtualFileSystemRootPath($actual)
        );
    }

    protected function assertVirtualFileNotEquals($expected, $actual): void
    {
        $this->assertFileNotEquals(
            $this->getVirtualFileSystemRootPath($expected),
            $this->getVirtualFileSystemRootPath($actual)
        );
    }
}
