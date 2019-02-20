<?php declare(strict_types=1);
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
 * @covers \OxidEsales\ComposerPlugin\Utilities\CopyFileManager\GlobMatcher\Iteration\GlobFilterIterator
 * @covers \OxidEsales\ComposerPlugin\Utilities\CopyFileManager\GlobMatcher\Integration\AbstractGlobMatcher
 * @covers \OxidEsales\ComposerPlugin\Utilities\CopyFileManager\GlobMatcher\Integration\WebmozartGlobMatcher
 * @covers \OxidEsales\ComposerPlugin\Utilities\CopyFileManager\GlobMatcher\GlobListMatcher\GlobListMatcher
 */
class CopyGlobFilteredFileManagerTest extends \PHPUnit\Framework\TestCase
{
    /** @var array */
    private $filter = [];

    /**
     * @var bool
     */
    private $isWhiteList = false;

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

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Given value \"1\" is not a valid source path entry. " .
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
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Given value \"1\" is not a valid destination path entry. " .
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

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Given value \"1\" is not a valid glob expression list. " .
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

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Given value \"1\" is not a valid glob expression. " .
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

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Given value \"/some/absolute/path/*.*\" is an absolute path. " .
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
            "readme.md"  => "MD_1",
        ];

        $this->prepareVirtualFileSystem($inputFiles, []);

        $this->simulateCopyWithFilter();

        $this->assertFileCopyIsIdentical(
            [
                "module.php",
                "readme.md",
            ]
        );
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

    /**
     * @return array
     */
    public function providerCopy()
    {
        $structure = [
            'module.php'        => 'PHP_1',
            'readme.md'         => 'MD_1',
            'documentation.txt' => 'TXT_1',
            'src'               => [
                'a.php' => 'PHP_2',
                'b.php' => 'PHP_3',
                'c.php' => 'PHP_3',
            ],
            'tests'             => [
                'test.php'    => 'PHP_4',
                'unit'        => [
                    'test.php' => 'PHP_5',
                ],
                'integration' => [
                    'test.php' => 'PHP_6',
                ]
            ],
            'documentation'     => [
                'document_a.pdf' => 'PDF_1',
                'document_b.pdf' => 'PDF_2',
                'index.txt'      => 'TXT_2',
                'example.php'    => 'PHP_7',
            ]
        ];

        return [
            'blacklist' => [
                [
                    'module.php'                   => true,
                    'src/a.php'                    => true,
                    'src/b.php'                    => true,
                    'src/c.php'                    => true,
                    'documentation/example.php'    => true,

                    'readme.md'                    => false,
                    'documentation.txt'            => false,
                    'tests/test.php'               => false,
                    'tests/unit/test.php'          => false,
                    'tests/integration/test.php'   => false,
                    'documentation/document_a.pdf' => false,
                    'documentation/document_b.pdf' => false,
                    'documentation/index.txt'      => false,
                ],
                $structure,
                [
                    '**/*.md',
                    '**/*.txt',
                    'tests/**/*.*',
                    'documentation/**/*.pdf',
                ],
                false,
            ],

            'whitelist' => [
                [
                    'module.php'                   => false,
                    'src/a.php'                    => false,
                    'src/b.php'                    => false,
                    'src/c.php'                    => false,
                    'documentation/example.php'    => false,

                    'readme.md'                    => true,
                    'documentation.txt'            => true,
                    'tests/test.php'               => true,
                    'tests/unit/test.php'          => true,
                    'tests/integration/test.php'   => true,
                    'documentation/document_a.pdf' => true,
                    'documentation/document_b.pdf' => true,
                    'documentation/index.txt'      => true,
                ],
                $structure,
                [
                    '**/*.md',
                    '**/*.txt',
                    'tests/**/*.*',
                    'documentation/**/*.pdf',
                ],
                true,
            ],
        ];
    }

    /**
     * @dataProvider providerCopy
     *
     * @param array $expected
     * @param array $structure
     * @param array $filter
     * @param bool $isWhiteList
     */
    public function testCopy(array $expected, array $structure, array $filter, $isWhiteList)
    {
        $this->setFilter($filter, $isWhiteList);
        $this->prepareVirtualFileSystem($structure, []);
        $this->simulateCopyWithFilter();
        foreach ($expected as $path => $identical) {
            $identical ? $this->assertFileCopyIsIdentical([$path]) : $this->assertFilesNotExistInDestination([$path]);
        }
    }

    protected function setFilter($filter, $isWhiteList = false)
    {
        $this->filter = $filter;
        $this->isWhiteList = $isWhiteList;
    }

    protected function prepareVirtualFileSystem($inputStructure, $outputStructure)
    {
        vfsStream::setup('root', 777);
        vfsStream::create(
            [
                'src'  => $inputStructure,
                'dest' => $outputStructure,
            ]
        );
    }

    protected function simulateCopyWithFilter($source = null, $destination = null)
    {
        $sourcePath = $this->getSourcePath($source);
        $destinationPath = $this->getDestinationPath($destination);

        CopyGlobFilteredFileManager::copy($sourcePath, $destinationPath, $this->filter, $this->isWhiteList);
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
}
