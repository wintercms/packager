<?php

declare(strict_types=1);

namespace Winter\Packager\Tests\Cases\Package;

use Winter\Packager\Composer;
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
        $package = Composer::newVersionedPackage('winter', 'wn-pages-plugin', '', 'v2.0.3');
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
