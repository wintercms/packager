<?php

declare(strict_types=1);

namespace BennoThommo\Packager\Tests\Cases;

use BennoThommo\Packager\Commands\Update;
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
    public function itCanRunAnUpdateMocked()
    {
        $this->mockCommandOutput(
            'update',
            Update::class,
            0,
            file_get_contents($this->testBasePath() . '/fixtures/valid/validCleanUpdate/output.txt')
        );

        $update = $this->composer->update();

        $this->assertEquals(106, $update->getInstalledCount());
        $this->assertEquals(0, $update->getUpdatedCount());
        $this->assertEquals(0, $update->getRemovedCount());
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
