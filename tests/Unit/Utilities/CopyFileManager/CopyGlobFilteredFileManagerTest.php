<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ComposerPlugin\Tests\Unit\Utilities\CopyFileManager;

use org\bovigo\vfs\vfsStream;
use OxidEsales\ComposerPlugin\Utilities\CopyFileManager\CopyGlobFilteredFileManager;
use Webmozart\PathUtil\Path;

/**
 * Class CopyGlobFilteredFileManagerTest.
 *
 * @covers \OxidEsales\ComposerPlugin\Utilities\CopyFileManager\CopyGlobFilteredFileManager
 * @covers \OxidEsales\ComposerPlugin\Utilities\CopyFileManager\GlobMatcher\GlobMatcher
 * @covers \OxidEsales\ComposerPlugin\Utilities\CopyFileManager\GlobMatcher\Iteration\BlacklistFilterIterator
 * @covers \OxidEsales\ComposerPlugin\Utilities\CopyFileManager\GlobMatcher\Integration\AbstractGlobMatcher
 * @covers \OxidEsales\ComposerPlugin\Utilities\CopyFileManager\GlobMatcher\Integration\WebmozartGlobMatcher
 * @covers \OxidEsales\ComposerPlugin\Utilities\CopyFileManager\GlobMatcher\GlobListMatcher\GlobListMatcher
 */
class CopyGlobFilteredFileManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var array */
    private $filter = [];

    public function testBasicFileCopyOperation()
    {
        $inputFiles = [
            "module.php" => "PHP_1",
        ];

        $this->prepareVirtualFileSystem($inputFiles, []);

        $this->simulateCopyWithFilter('module.php', 'module.php');

        $this->assertFileCopyIsIdentical(['module.php']);
    }

    public function testNoExceptionThrownWhenSourceFileDoesNotExist()
    {
        $this->prepareVirtualFileSystem([], []);
        $this->simulateCopyWithFilter('module.php', 'module.php');

        $this->assertFilesNotExistInDestination(['module.php']);
    }

    public function testThrowsExceptionWhenSourceValueIsInvalid()
    {
        $inputFiles = [
            "module.php" => "PHP_1",
        ];

        $this->prepareVirtualFileSystem($inputFiles, []);

        $this->setExpectedException(
            \InvalidArgumentException::class,
            "Given value \"1\" is not a valid source path entry. ".
            "Valid entry must be an absolute path to an existing file or directory."
        );

        $destinationPath = $this->getSourcePath('module.php');
        CopyGlobFilteredFileManager::copy(1, $destinationPath);
    }

    public function testThrowsExceptionWhenDestinationValueIsInvalid()
    {
        $inputFiles = [
            "module.php" => "PHP_1",
        ];

        $this->prepareVirtualFileSystem($inputFiles, []);

        $this->setExpectedException(
            \InvalidArgumentException::class,
            "Given value \"1\" is not a valid destination path entry. ".
            "Valid entry must be an absolute path to an existing directory."
        );

        $sourcePath = $this->getSourcePath('module.php');
        CopyGlobFilteredFileManager::copy($sourcePath, 1);
    }

    public function testThrowsExceptionWhenFilterValueIsInvalid()
    {
        $inputFiles = [
            "module.php" => "PHP_1",
        ];

        $this->prepareVirtualFileSystem($inputFiles, []);

        $this->setExpectedException(
            \InvalidArgumentException::class,
            "Given value \"1\" is not a valid glob expression list. ".
            "Valid entry must be a list of glob expressions e.g. [\"*.txt\", \"*.pdf\"]."
        );

        $this->setFilter(1);
        $this->simulateCopyWithFilter('module.php', 'module.php');
    }

    public function testThrowsExceptionWhenFilterItemValueIsInvalid()
    {
        $inputFiles = [
            "module.php" => "PHP_1",
        ];

        $this->prepareVirtualFileSystem($inputFiles, []);

        $this->setExpectedException(
            \InvalidArgumentException::class,
            "Given value \"1\" is not a valid glob expression. ".
            "Valid expression must be a string e.g. \"*.txt\"."
        );

        $this->setFilter([1]);
        $this->simulateCopyWithFilter('module.php', 'module.php');
    }

    public function testThrowsExceptionWhenFilterItemValueIsAbsolutePath()
    {
        $inputFiles = [
            "module.php" => "PHP_1",
        ];

        $this->prepareVirtualFileSystem($inputFiles, []);

        $this->setExpectedException(
            \InvalidArgumentException::class,
            "Given value \"/some/absolute/path/*.*\" is an absolute path. ".
            "Glob expression can only be accepted if it's a relative path."
        );

        $this->setFilter(["/some/absolute/path/*.*"]);
        $this->simulateCopyWithFilter('module.php', 'module.php');
    }

    public function testSingleFileCopyFilteringOperation()
    {
        $inputFiles = [
            "module.txt" => "TXT_1",
        ];

        $this->prepareVirtualFileSystem($inputFiles, []);

        $this->setFilter(["*.txt"]);
        $this->simulateCopyWithFilter('module.txt', 'module.txt');

        $this->assertFilesExistInSource(['module.txt']);
        $this->assertFilesNotExistInDestination(['modules.txt']);
    }

    public function testSingleFileCopyFilteringOperationWhenFilterIsEmpty()
    {
        $inputFiles = [
            "module.txt" => "TXT_1",
        ];

        $this->prepareVirtualFileSystem($inputFiles, []);

        $this->setFilter([]);
        $this->simulateCopyWithFilter('module.txt', 'module.txt');

        $this->assertFilesExistInSource(['module.txt']);
        $this->assertFilesNotExistInDestination(['modules.txt']);
    }

    public function testSingleFileCopyFilteringOperationWhenFilterContainsEmptyValues()
    {
        $inputFiles = [
            "module.txt" => "TXT_1",
        ];

        $this->prepareVirtualFileSystem($inputFiles, []);

        $this->setFilter([null]);
        $this->simulateCopyWithFilter('module.txt', 'module.txt');

        $this->assertFilesExistInSource(['module.txt']);
        $this->assertFilesNotExistInDestination(['modules.txt']);
    }

    public function testBasicDirectoryTreeCopyOperation()
    {
        $inputFiles = [
            "module.php" => "PHP_1",
            "readme.md" => "MD_1",
        ];

        $this->prepareVirtualFileSystem($inputFiles, []);

        $this->simulateCopyWithFilter();

        $this->assertFileCopyIsIdentical([
            "module.php",
            "readme.md",
        ]);
    }

    public function testCopyOverwritesFilesByDefault()
    {
        $inputFiles = [
            "module.php" => "PHP_1",
        ];

        $outputFiles = [
            "module.php" => "PHP_2",
        ];

        $this->prepareVirtualFileSystem($inputFiles, $outputFiles);

        $this->simulateCopyWithFilter();

        $this->assertFileCopyIsIdentical(["module.php"]);
    }

    public function testCopyDoesNotThrowAnErrorWhenSourceIsMissing()
    {
        $this->prepareVirtualFileSystem([], []);

        $this->simulateCopyWithFilter('module.php');

        $this->assertFilesNotExistInDestination(["module.php"]);
    }

    public function testFilteringFileCopyOperation()
    {
        $inputFiles = [
            "module.php" => "PHP_1",
            "readme.md" => "MD_1",
            "documentation.txt" => "TXT_1",
            "src" => [
                "a.php" => "PHP_2",
                "b.php" => "PHP_3",
                "c.php" => "PHP_3",
            ],
            "tests" => [
                "test.php" => "PHP_4",
                "unit" => [
                    "test.php" => "PHP_5",
                ],
                "integration" => [
                    "test.php" => "PHP_6",
                ]
            ],
            "documentation" => [
                "document_a.pdf" => "PDF_1",
                "document_b.pdf" => "PDF_2",
                "index.txt" => "TXT_2",
                "example.php" => "PHP_7",
            ]
        ];

        $this->prepareVirtualFileSystem($inputFiles, []);

        $this->setFilter([
            "**/*.md",
            "**/*.txt",
            "tests/**/*.*",
            "documentation/**/*.pdf",
        ]);
        $this->simulateCopyWithFilter();

        $this->assertFileCopyIsIdentical([
            "module.php",
            "src/a.php",
            "src/b.php",
            "src/c.php",
            "documentation/example.php",
        ]);

        $this->assertFilesNotExistInDestination([
            "readme.md",
            "documentation.txt",
            "tests/test.php",
            "tests/unit/test.php",
            "tests/integration/test.php",
            "documentation/document_a.pdf",
            "documentation/document_b.pdf",
            "documentation/index.txt",
        ]);
    }

    protected function setFilter($filter)
    {
        $this->filter = $filter;
    }

    protected function prepareVirtualFileSystem($inputStructure, $outputStructure)
    {
        vfsStream::setup('root', 777);
        vfsStream::create([
            'src' => $inputStructure,
            'dest' => $outputStructure,
        ]);
    }

    protected function simulateCopyWithFilter($source = null, $destination = null)
    {
        $sourcePath = $this->getSourcePath($source);
        $destinationPath = $this->getDestinationPath($destination);

        CopyGlobFilteredFileManager::copy($sourcePath, $destinationPath, $this->filter);
    }

    protected function getSourcePath($suffixForSource = null)
    {
        return Path::join(vfsStream::url('root/src'), !is_null($suffixForSource) ? $suffixForSource : "");
    }

    protected function getDestinationPath($suffixForDestination = null)
    {
        return Path::join(vfsStream::url('root/dest'), !is_null($suffixForDestination) ? $suffixForDestination : "");
    }

    protected function assertFilesExistInSource($paths)
    {
        foreach ($paths as $path) {
            $this->assertFileExists($this->getSourcePath($path));
        }
    }

    protected function assertFilesExistInDestination($paths)
    {
        foreach ($paths as $path) {
            $this->assertFileExists($this->getDestinationPath($path));
        }
    }

    protected function assertFilesNotExistInSource($paths)
    {
        foreach ($paths as $path) {
            $this->assertFileNotExists($this->getSourcePath($path));
        }
    }

    protected function assertFilesNotExistInDestination($paths)
    {
        foreach ($paths as $path) {
            $this->assertFileNotExists($this->getDestinationPath($path));
        }
    }

    protected function assertFileCopyIsIdentical($paths)
    {
        foreach ($paths as $path) {
            $this->assertFileEquals($this->getSourcePath($path), $this->getDestinationPath($path));
        }
    }

    protected function assertFileCopyIsDifferent($paths)
    {
        foreach ($paths as $path) {
            $this->assertFileNotEquals($this->getSourcePath($path), $this->getDestinationPath($path));
        }
    }
}
