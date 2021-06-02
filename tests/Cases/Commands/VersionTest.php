<?php

declare(strict_types=1);

namespace BennoThommo\Packager\Tests\Cases;

use BennoThommo\Packager\Composer;
use BennoThommo\Packager\Tests\ComposerTestCase;
use Composer\Semver\Comparator;

/**
 * @testdox The Version command
 * @coversDefaultClass \BennoThommo\Packager\Commands\Version
 */
final class VersionTest extends ComposerTestCase
{
    /** @var Composer */
    protected $composer;

    /**
     * @before
     */
    public function createComposerClass(): void
    {
        $this->composer = new Composer();
    }

    /**
     * @test
     * @testdox can get the version of Composer installed.
     * @covers ::execute
     */
    public function itCanGetTheVersionOfComposer(): void
    {
        $this->composer->setWorkDir($this->homeDir);

        // Get installed Composer version
        $composerDeps = include dirname(dirname(dirname(__DIR__))) . '/vendor/composer/installed.php';
        $installedVersion = $composerDeps['versions']['composer/composer']['pretty_version'];
;
        $this->assertTrue(Comparator::equalTo($this->composer->version(), $installedVersion));
    }
}
