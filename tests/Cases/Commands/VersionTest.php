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
     * @before
     */
    public function mockVersionOutput(): void
    {
        $this->mockCommandOutput(
            'version',
            Version::class,
            0,
            'Composer version 2.0.12 2021-04-01 10:14:59'
        );
    }

    /**
     * @test
     * @testdox can get the version of Composer installed.
     * @covers ::execute
     */
    public function itCanGetTheVersionOfComposer(): void
    {
        $this->assertEquals('2.0.12', $this->composer->version());
    }
}
