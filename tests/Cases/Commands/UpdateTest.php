<?php

declare(strict_types=1);

namespace BennoThommo\Packager\Tests\Cases;

use BennoThommo\Packager\Exceptions\ComposerJsonException;
use BennoThommo\Packager\Tests\ComposerTestCase;
/**
 * @testdox The Update command
 * @coversDefaultClass \BennoThommo\Packager\Commands\Update
 */
final class UpdateTest extends ComposerTestCase
{
    /**
     * @before
     */
    public function setUpDirectories()
    {
        $this->composer
            ->setHomeDir($this->homeDir)
            ->setWorkDir($this->workDir);
    }

    /**
     * @test
     * @testdox fails when the "composer.json" file is in an invalid format.
     * @covers ::handle
     * @covers ::execute
     */
    public function itFailsWhenComposerJsonIsInvalidFormat(): void
    {
        $this->expectException(ComposerJsonException::class);

        $this->copyToWorkDir($this->testBasePath() . '/fixtures/invalid/composerFile/composer.json');

        $this->composer->update();
    }
}
