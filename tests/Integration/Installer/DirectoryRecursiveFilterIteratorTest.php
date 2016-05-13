<?php
/**
 * Created by PhpStorm.
 * User: aurimas
 * Date: 5/13/16
 * Time: 3:40 PM
 */

namespace Tests\Integration;


use org\bovigo\vfs\vfsStream;
use OxidEsales\ComposerPlugin\Installer\DirectoryRecursiveFilterIterator;
use RecursiveArrayIterator;

class DirectoryRecursiveFilterIteratorTest extends \PHPUnit_Framework_TestCase
{
    public function testFilteringDirectories()
    {
        $structure = [
            'Directory' => [
                'NotSkipped' => [],
                'Skipped' => [
                    'SkippedInside' => [],
                    'Class.php' => 'content'
                ],
                'SkippedNot' => [],
            ]
        ];
        vfsStream::setup('root', 777, ['projectRoot' => $structure]);
        $rootPath = vfsStream::url('root/projectRoot');

        $directoryIterator = new \RecursiveDirectoryIterator($rootPath, \FilesystemIterator::SKIP_DOTS);
        $directoryFilter = new DirectoryRecursiveFilterIterator($directoryIterator, [$rootPath.'/Directory/Skipped']);
        $iterator = new \RecursiveIteratorIterator($directoryFilter, \RecursiveIteratorIterator::SELF_FIRST);

        $result = [];
        foreach ($iterator as $path) {
            $result[] = $path->getPathName();
        }

        $expected = [
            $rootPath.'/Directory',
            $rootPath.'/Directory/NotSkipped',
            $rootPath.'/Directory/SkippedNot'
        ];
        $this->assertEquals($expected, $result);
    }
}
