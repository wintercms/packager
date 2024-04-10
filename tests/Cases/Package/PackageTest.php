<?php

declare(strict_types=1);

namespace Winter\Packager\Tests\Cases\Package;

use Winter\Packager\Composer;
use Winter\Packager\Package\Packagist;
use Winter\Packager\Tests\ComposerTestCase;

/**
 * @testdox The Package class
 * @coversDefaultClass \Winter\Packager\Package\Package
 */
class PackageTest extends ComposerTestCase
{
    /**
     * @test
     * @testdox it can convert a package to a detailed package
     * @covers \Winter\Packager\Package\Package::toDetailed
     */
    public function itCanConvertAPackageToADetailedPackage()
    {
        $package = Composer::newPackage('winter', 'wn-pages-plugin');
        $package = $package->toDetailed();

        $this->assertInstanceOf(\Winter\Packager\Package\DetailedPackage::class, $package);
        $this->assertEquals('winter', $package->getNamespace());
        $this->assertEquals('wn-pages-plugin', $package->getName());
        $this->assertEquals('winter-plugin', $package->getType());
        $this->assertEquals('https://github.com/wintercms/wn-pages-plugin', $package->getHomepage());
        $this->assertArrayHasKey('installer-name', $package->getExtras());
        $this->assertArrayHasKey('winter', $package->getExtras());
        $this->assertEquals('pages', $package->getExtras()['installer-name']);
    }

    /**
     * @test
     * @testdox it can convert a versioned package to a detailed versioned package
     * @covers \Winter\Packager\Package\VersionedPackage::toDetailed
     */
    public function itCanConvertAVersionedPackageToADetailedPackage()
    {
        $package = Composer::newVersionedPackage('winter', 'wn-pages-plugin', '', 'winter-plugin', 'v2.0.3');
        $package = $package->toDetailed();

        $this->assertInstanceOf(\Winter\Packager\Package\DetailedVersionedPackage::class, $package);
        $this->assertEquals('winter', $package->getNamespace());
        $this->assertEquals('wn-pages-plugin', $package->getName());
        $this->assertEquals('winter-plugin', $package->getType());
        $this->assertEquals('https://github.com/wintercms/wn-pages-plugin', $package->getHomepage());
        $this->assertArrayHasKey('installer-name', $package->getExtras());
        $this->assertArrayNotHasKey('winter', $package->getExtras());
        $this->assertEquals('pages', $package->getExtras()['installer-name']);
        $this->assertEquals('2.0.3.0', $package->getVersionNormalized());
    }

    /**
     * @test
     * @testdox it can store and retrieve a package from storage
     * @covers \Winter\Packager\Package\VersionedPackage::toDetailed
     */
    public function itCanStoreAndRetrieveAPackageFromStorage()
    {
        $proxy = new \Winter\Packager\Storage\Memory;
        $storage = $this->getMockBuilder(\Winter\Packager\Storage\Memory::class)
            ->setProxyTarget($proxy)
            ->enableProxyingToOriginalMethods()
            ->getMock();

        Packagist::setStorage($storage);

        $storage->expects($this->atLeastOnce())
            ->method('set')
            ->with('winter', 'wn-pages-plugin', $this->anything(), $this->anything());

        $package = Composer::newVersionedPackage('winter', 'wn-pages-plugin', '', 'winter-plugin', 'v2.0.3');
        $package = $package->toDetailed();

        $storage->expects($this->once())
            ->method('has')
            ->with('winter', 'wn-pages-plugin', 'v2.0.3');

        $storage->expects($this->once())
            ->method('get')
            ->with('winter', 'wn-pages-plugin', 'v2.0.3');

        $package = Composer::newVersionedPackage('winter', 'wn-pages-plugin', '', 'winter-plugin', 'v2.0.3');
        $package = $package->toDetailed();

        $this->assertInstanceOf(\Winter\Packager\Package\DetailedVersionedPackage::class, $package);
        $this->assertEquals('winter', $package->getNamespace());
        $this->assertEquals('wn-pages-plugin', $package->getName());
        $this->assertEquals('winter-plugin', $package->getType());
        $this->assertEquals('https://github.com/wintercms/wn-pages-plugin', $package->getHomepage());
        $this->assertArrayHasKey('installer-name', $package->getExtras());
        $this->assertArrayNotHasKey('winter', $package->getExtras());
        $this->assertEquals('pages', $package->getExtras()['installer-name']);
        $this->assertEquals('2.0.3.0', $package->getVersionNormalized());
    }
}
