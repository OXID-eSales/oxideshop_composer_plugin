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

use Composer\Composer;
use Composer\Config;
use Composer\IO\NullIO;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;
use OxidEsales\ComposerPlugin\Installer\PackagesInstaller;
use Symfony\Component\Filesystem\Filesystem;

class PackagesInstallerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * The composer.json file already in source for 5.3
     */
    public function testGetShopSourcePathFor53()
    {
        $composerConfigMock = $this->getMock(Config::class);
        $composerMock = $this->getMock(Composer::class);
        $composerMock->method('getConfig')->withAnyParameters()->willReturn($composerConfigMock);

        $packageInstallerStub = new PackagesInstaller(new NullIO(), $composerMock);
        $this->assertEquals($packageInstallerStub->getShopSourcePath(), getcwd());
    }

    /**
     * The composer.json file is taken up from the source directory for 6.0, so we should add source to path
     */
    public function testGetShopSourcePathFor60()
    {
        $composerConfigMock = $this->getMock(Config::class);
        $composerMock = $this->getMock(Composer::class);
        $composerMock->method('getConfig')->withAnyParameters()->willReturn($composerConfigMock);

        $packageInstallerStub = new PackagesInstaller(new NullIO(), $composerMock);
        $result = $packageInstallerStub->getShopSourcePath();

        $this->assertEquals($result, getcwd() . '/source');
    }

}