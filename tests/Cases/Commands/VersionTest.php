<?php

declare(strict_types=1);

namespace BennoThommo\Packager\Tests\Cases;

use BennoThommo\Packager\Commands\Version;
use BennoThommo\Packager\Tests\ComposerTestCase;
/**
 * @testdox The Version command
 * @coversDefaultClass \BennoThommo\Packager\Commands\Version
 */
final class VersionTest extends ComposerTestCase
{
    /**
     * @test
     * @testdox can get the version of Composer installed.
     * @covers ::handle
     * @covers ::execute
     */
    public function itCanGetTheVersionOfComposer(): void
    {
        $this->mockCommandOutput(
            'version',
            Version::class,
            0,
            'Composer version 2.0.12 2021-04-01 10:14:59'
        );

        $this->assertEquals('2.0.12', $this->composer->version());
    }

    /**
     * @test
     * @testdox can get the release date of the installed Composer version.
     * @covers ::handle
     * @covers ::execute
     */
    public function itCanGetTheReleaseDateOfComposer(): void
    {
        $this->mockCommandOutput(
            'version',
            Version::class,
            0,
            'Composer version 2.0.12 2021-04-01 10:14:59'
        );

        $this->assertEquals('2021-04-01', $this->composer->version('date'));
    }

    /**
     * @test
     * @testdox can get the release date and time of the installed Composer version.
     * @covers ::handle
     * @covers ::execute
     */
    public function itCanGetTheReleaseDateTimeOfComposer(): void
    {
        $this->mockCommandOutput(
            'version',
            Version::class,
            0,
            'Composer version 2.0.12 2021-04-01 10:14:59'
        );

        $this->assertEquals('2021-04-01 10:14:59', $this->composer->version('dateTime'));
    }

    /**
     * @test
     * @testdox can get all release information of the installed Composer version.
     * @covers ::handle
     * @covers ::execute
     */
    public function itCanGetAllReleaseInfoOfComposer(): void
    {
        $this->mockCommandOutput(
            'version',
            Version::class,
            0,
            'Composer version 2.0.12 2021-04-01 10:14:59'
        );

        $this->assertEquals([
            'version' => '2.0.12',
            'date' => '2021-04-01',
            'time' => '10:14:59'
        ], $this->composer->version('all'));
    }
}
