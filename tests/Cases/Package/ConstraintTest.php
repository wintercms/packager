<?php

declare(strict_types=1);

namespace Winter\Packager\Tests\Cases\Package;

use Winter\Packager\Package\Constraint;
use Winter\Packager\Tests\ComposerTestCase;

/**
 * @testdox The Contraint class
 * @coversDefaultClass \Winter\Packager\Package\Constraint
 */
class ConstraintTest extends ComposerTestCase
{
    /**
     * @test
     * @testdox can set and get package names
     * @covers ::package
     * @covers ::php
     * @covers ::extension
     * @covers ::getPackage
     * @covers ::setPackage
     */
    public function itCanSetAndGetPackageNames(): void
    {
        foreach ([
            'bennothommo/packager',
            'wintercms/winter',
            'winter/storm',
            'composer/composer'
        ] as $packageName) {
            $this->assertEquals(
                $packageName,
                Constraint::package($packageName)->getPackage()
            );
        }

        $this->assertEquals(
            'php',
            Constraint::php()->getPackage()
        );

        $this->assertEquals(
            'ext-gd',
            Constraint::extension('gd')->getPackage()
        );
        $this->assertEquals(
            'ext-openssl',
            Constraint::extension('openssl')->getPackage()
        );
        $this->assertEquals(
            'ext-mbstring',
            Constraint::extension('mbstring')->getPackage()
        );
    }

    /**
     * @test
     * @testdox throws on invalid package name and extensions
     * @covers ::package
     * @covers ::extension
     * @covers ::setPackage
     * @dataProvider invalidPackageNames
     */
    public function itThrowsOnInvalidPackageAndExtensionsNames(string $method, string $packageName): void
    {
        $this->expectException(\Winter\Packager\Exceptions\ConstraintException::class);
        $this->expectExceptionMessage('Invalid package name provided for constraint');

        $constraint = Constraint::$method($packageName);
    }

    public static function invalidPackageNames(): array
    {
        return [
            ['package', 'invalid-package-name'],
            ['package', 'invalid/package/name'],
            ['package', 'invalid package name'],
            ['package', 'INVALID/PACKAGE'],
            ['extension', 'ABC'],
            ['extension', 'gd_23!'],
            ['extension', 'my$ql'],
            ['extension', ''],
        ];
    }

    /**
     * @test
     * @testdox can get version constraints as a string
     * @covers ::package
     * @covers ::setPackage
     * @covers ::version
     * @covers ::andVersion
     * @covers ::orVersion
     * @covers ::toString
     */
    public function itCanGetStringConstraints(): void
    {
        $this->assertEquals(
            '"bennothommo/packager *"',
            Constraint::package('bennothommo/packager')->toString()
        );

        $this->assertEquals(
            '"bennothommo/packager 0.1.0"',
            Constraint::package('bennothommo/packager')->version('0.1.0')->toString()
        );

        $this->assertEquals(
            '"bennothommo/packager 0.1.0 || 0.2.0"',
            Constraint::package('bennothommo/packager')->version('0.1.0')->orVersion('0.2.0')->toString()
        );

        $this->assertEquals(
            '"bennothommo/packager >=0.1.0 <0.2.0"',
            Constraint::package('bennothommo/packager')->version('>=', '0.1.0')->andVersion('<', '0.2.0')->toString()
        );

        $this->assertEquals(
            '"bennothommo/packager ^0.1.0 || ~0.2"',
            Constraint::package('bennothommo/packager')->version('^0.1.0')->orVersion('~0.2')->toString()
        );
    }
}
