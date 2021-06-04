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
     * @testdox run a (mocked) update and shows installed packages.
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

        // Check a couple of packages
        $this->assertArrayHasKey('winter/storm', $update->getInstalled());
        $this->assertEquals('dev-develop 0a729ee', $update->getInstalled()['winter/storm']);

        $this->assertArrayHasKey('symfony/yaml', $update->getInstalled());
        $this->assertEquals('3.4.47.0', $update->getInstalled()['symfony/yaml']);

        $this->assertArrayHasKey('laravel/framework', $update->getInstalled());
        $this->assertEquals('6.20.27.0', $update->getInstalled()['laravel/framework']);

        $this->assertArrayHasKey('league/flysystem', $update->getInstalled());
        $this->assertEquals('1.1.3.0', $update->getInstalled()['league/flysystem']);
    }

    /**
     * @test
     * @testdox run a (real) update and show an installed package.
     * @covers ::handle
     * @covers ::execute
     */
    public function itCanRunAnUpdateReal()
    {
        $this->copyToWorkDir($this->testBasePath() . '/fixtures/valid/simple/composer.json');

        $update = $this->composer->update();

        $this->assertEquals(2, $update->getInstalledCount());
        $this->assertEquals(0, $update->getUpdatedCount());
        $this->assertEquals(0, $update->getRemovedCount());

        // Check packages
        $this->assertArrayHasKey('composer/semver', $update->getInstalled());
        $this->assertEquals('1.7.1.0', $update->getInstalled()['composer/semver']);

        $this->assertArrayHasKey('composer/ca-bundle', $update->getInstalled());
        $this->assertEquals('1.2.9.0', $update->getInstalled()['composer/ca-bundle']);
    }

    /**
     * @test
     * @testdox run a (real) update and show updated and removed packages.
     * @covers ::handle
     * @covers ::execute
     */
    public function itCanRunAnUpdateWithUpdatesAndRemovals()
    {
        $this->copyToWorkDir($this->testBasePath() . '/fixtures/valid/simple/composer.json');

        $this->composer->update();

        // Modify composer.json to simulate an update to one dependency, the removal of another and
        // the installation of some dependent packages.
        file_put_contents(
            $this->workDir . '/composer.json',
            json_encode(
                [
                    'name' => 'packager/simple',
                    'description' => 'Complex Composer test',
                    'type' => 'project',
                    'license' => 'MIT',
                    'authors' => [
                        [
                            'name' => 'Ben Thomson',
                            'email' => 'git@alfreido.com',
                        ],
                    ],
                    'require' => [
                        'composer/semver' => '1.7.2',
                        'composer/installers' => '1.11.0',
                        'winter/wn-twitter-plugin' => '2.0.0',
                    ],
                ],
                JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK
            )
        );

        $update = $this->composer->update();

        $this->assertEquals(3, $update->getInstalledCount());
        $this->assertEquals(1, $update->getUpdatedCount());
        $this->assertEquals(1, $update->getRemovedCount());

        // Check packages
        $this->assertArrayHasKey('composer/installers', $update->getInstalled());
        $this->assertEquals('1.11.0.0', $update->getInstalled()['composer/installers']);

        $this->assertArrayHasKey('themattharris/tmhoauth', $update->getInstalled());
        $this->assertEquals('0.8.3.0', $update->getInstalled()['themattharris/tmhoauth']);

        $this->assertArrayHasKey('winter/wn-twitter-plugin', $update->getInstalled());
        $this->assertEquals('2.0.0.0', $update->getInstalled()['winter/wn-twitter-plugin']);

        $this->assertArrayHasKey('composer/semver', $update->getUpdated());
        $this->assertEquals(['1.7.1.0', '1.7.2.0'], $update->getUpdated()['composer/semver']);

        $this->assertContains('composer/ca-bundle', $update->getRemoved());
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
