<?php

declare(strict_types=1);

namespace Winter\Packager\Tests\Cases;

use Winter\Packager\Commands\Update;
use Winter\Packager\Exceptions\ComposerJsonException;
use Winter\Packager\Tests\ComposerTestCase;

/**
 * @testdox The Update command
 * @coversDefaultClass \Winter\Packager\Commands\Update
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
     * @testdox can run a (mocked) update and shows installed packages.
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

        $this->assertEquals(106, $update->getLockInstalledCount());
        $this->assertEquals(0, $update->getLockUpgradedCount());
        $this->assertEquals(0, $update->getLockRemovedCount());

        $this->assertEquals(106, $update->getInstalledCount());
        $this->assertEquals(0, $update->getUpgradedCount());
        $this->assertEquals(0, $update->getRemovedCount());

        // Check a couple of packages in the lock file
        $this->assertArrayHasKey('winter/storm', $update->getLockInstalled());
        $this->assertEquals('dev-develop 0a729ee', $update->getLockInstalled()['winter/storm']);

        $this->assertArrayHasKey('symfony/yaml', $update->getLockInstalled());
        $this->assertEquals('3.4.47.0', $update->getLockInstalled()['symfony/yaml']);

        $this->assertArrayHasKey('laravel/framework', $update->getLockInstalled());
        $this->assertEquals('6.20.27.0', $update->getLockInstalled()['laravel/framework']);

        $this->assertArrayHasKey('league/flysystem', $update->getLockInstalled());
        $this->assertEquals('1.1.3.0', $update->getLockInstalled()['league/flysystem']);

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
     * @testdox can run a (mocked) update, lock-file only, and shows locked packages.
     * @covers ::handle
     * @covers ::execute
     */
    public function itCanRunAnUpdateLockFileOnlyMocked()
    {
        $this->mockCommandOutput(
            'update',
            Update::class,
            0,
            file_get_contents($this->testBasePath() . '/fixtures/valid/validCleanUpdate/lockFileOnly.txt')
        );

        $update = $this->composer->update();

        $this->assertEquals(106, $update->getLockInstalledCount());
        $this->assertEquals(0, $update->getLockUpgradedCount());
        $this->assertEquals(0, $update->getLockRemovedCount());

        $this->assertEquals(0, $update->getInstalledCount());
        $this->assertEquals(0, $update->getUpgradedCount());
        $this->assertEquals(0, $update->getRemovedCount());

        // Check a couple of packages
        $this->assertArrayHasKey('winter/storm', $update->getLockInstalled());
        $this->assertEquals('dev-develop 0a729ee', $update->getLockInstalled()['winter/storm']);

        $this->assertArrayHasKey('symfony/yaml', $update->getLockInstalled());
        $this->assertEquals('3.4.47.0', $update->getLockInstalled()['symfony/yaml']);

        $this->assertArrayHasKey('laravel/framework', $update->getLockInstalled());
        $this->assertEquals('6.20.27.0', $update->getLockInstalled()['laravel/framework']);

        $this->assertArrayHasKey('league/flysystem', $update->getLockInstalled());
        $this->assertEquals('1.1.3.0', $update->getLockInstalled()['league/flysystem']);
    }

    /**
     * @test
     * @testdox can run a (real) update and show an installed package.
     * @covers ::handle
     * @covers ::execute
     */
    public function itCanRunAnUpdateReal()
    {
        $this->copyToWorkDir($this->testBasePath() . '/fixtures/valid/simple/composer.json');

        $update = $this->composer->update();

        $this->assertEquals(2, $update->getLockInstalledCount());
        $this->assertEquals(0, $update->getLockUpgradedCount());
        $this->assertEquals(0, $update->getLockRemovedCount());

        $this->assertEquals(2, $update->getInstalledCount());
        $this->assertEquals(0, $update->getUpgradedCount());
        $this->assertEquals(0, $update->getRemovedCount());

        // Check packages
        $this->assertArrayHasKey('composer/semver', $update->getLockInstalled());
        $this->assertEquals('1.7.1.0', $update->getLockInstalled()['composer/semver']);
        $this->assertArrayHasKey('composer/semver', $update->getInstalled());
        $this->assertEquals('1.7.1.0', $update->getInstalled()['composer/semver']);

        $this->assertArrayHasKey('composer/ca-bundle', $update->getLockInstalled());
        $this->assertEquals('1.2.9.0', $update->getLockInstalled()['composer/ca-bundle']);
        $this->assertArrayHasKey('composer/ca-bundle', $update->getInstalled());
        $this->assertEquals('1.2.9.0', $update->getInstalled()['composer/ca-bundle']);
    }

    /**
     * @test
     * @testdox can run a (real) update and show updated and removed packages.
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
                    'config' => [
                        'allow-plugins' => true,
                        'platform' => [
                            'php' => '7.4.20',
                        ],
                    ],
                ],
                JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK
            )
        );

        $update = $this->composer->update();

        $this->assertEquals(3, $update->getLockInstalledCount());
        $this->assertEquals(1, $update->getLockUpgradedCount());
        $this->assertEquals(1, $update->getLockRemovedCount());

        $this->assertEquals(3, $update->getInstalledCount());
        $this->assertEquals(1, $update->getUpgradedCount());
        $this->assertEquals(1, $update->getRemovedCount());

        // Check packages
        $this->assertArrayHasKey('composer/installers', $update->getLockInstalled());
        $this->assertEquals('1.11.0.0', $update->getLockInstalled()['composer/installers']);
        $this->assertArrayHasKey('composer/installers', $update->getInstalled());
        $this->assertEquals('1.11.0.0', $update->getInstalled()['composer/installers']);

        $this->assertArrayHasKey('themattharris/tmhoauth', $update->getLockInstalled());
        $this->assertEquals('0.8.3.0', $update->getLockInstalled()['themattharris/tmhoauth']);
        $this->assertArrayHasKey('themattharris/tmhoauth', $update->getInstalled());
        $this->assertEquals('0.8.3.0', $update->getInstalled()['themattharris/tmhoauth']);

        $this->assertArrayHasKey('winter/wn-twitter-plugin', $update->getLockInstalled());
        $this->assertEquals('2.0.0.0', $update->getLockInstalled()['winter/wn-twitter-plugin']);
        $this->assertArrayHasKey('winter/wn-twitter-plugin', $update->getInstalled());
        $this->assertEquals('2.0.0.0', $update->getInstalled()['winter/wn-twitter-plugin']);

        $this->assertArrayHasKey('composer/semver', $update->getLockUpgraded());
        $this->assertEquals(['1.7.1.0', '1.7.2.0'], $update->getLockUpgraded()['composer/semver']);
        $this->assertArrayHasKey('composer/semver', $update->getUpgraded());
        $this->assertEquals(['1.7.1.0', '1.7.2.0'], $update->getUpgraded()['composer/semver']);

        $this->assertContains('composer/ca-bundle', $update->getLockRemoved());
        $this->assertContains('composer/ca-bundle', $update->getRemoved());
    }

    /**
     * @test
     * @testdox can run a (real) update, lock-file only, and show updated and removed packages.
     * @covers ::handle
     * @covers ::execute
     */
    public function itCanRunAnUpdateWithUpdatesAndRemovalsLockFileOnly()
    {
        $this->copyToWorkDir($this->testBasePath() . '/fixtures/valid/simple/composer.json');

        $this->composer->update(true, true);

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
                    'config' => [
                        'allow-plugins' => true,
                        'platform' => [
                            'php' => '7.4.20',
                        ],
                    ],
                ],
                JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK
            )
        );

        $update = $this->composer->update(true, true);

        $this->assertEquals(3, $update->getLockInstalledCount());
        $this->assertEquals(1, $update->getLockUpgradedCount());
        $this->assertEquals(1, $update->getLockRemovedCount());

        $this->assertEquals(0, $update->getInstalledCount());
        $this->assertEquals(0, $update->getUpgradedCount());
        $this->assertEquals(0, $update->getRemovedCount());

        // Check packages
        $this->assertArrayHasKey('composer/installers', $update->getLockInstalled());
        $this->assertEquals('1.11.0.0', $update->getLockInstalled()['composer/installers']);

        $this->assertArrayHasKey('themattharris/tmhoauth', $update->getLockInstalled());
        $this->assertEquals('0.8.3.0', $update->getLockInstalled()['themattharris/tmhoauth']);

        $this->assertArrayHasKey('winter/wn-twitter-plugin', $update->getLockInstalled());
        $this->assertEquals('2.0.0.0', $update->getLockInstalled()['winter/wn-twitter-plugin']);

        $this->assertArrayHasKey('composer/semver', $update->getLockUpgraded());
        $this->assertEquals(['1.7.1.0', '1.7.2.0'], $update->getLockUpgraded()['composer/semver']);

        $this->assertContains('composer/ca-bundle', $update->getLockRemoved());
    }

    /**
     * @test
     * @testdox can run a (mocked) update and retrieve problems when a package is in conflict.
     * @covers ::handle
     * @covers ::execute
     */
    public function itCanGetProblemsDueToPackageConflict()
    {
        $this->copyToWorkDir($this->testBasePath() . '/fixtures/invalid/packageConflict/composer.json');

        $update = $this->composer->update();

        $this->assertCount(1, $update->getProblems());
        $this->assertStringContainsString('conflicts with your root composer.json', $update->getProblems()[0]);
    }

    /**
     * @test
     * @testdox can run a (mocked) update and retrieve problems when a PHP extension is missing.
     * @covers ::handle
     * @covers ::execute
     */
    public function itCanGetProblemsDueToMissingExtension()
    {
        $this->copyToWorkDir($this->testBasePath() . '/fixtures/invalid/missingExtension/composer.json');

        $update = $this->composer->update();

        $this->assertCount(1, $update->getProblems());
        $this->assertStringContainsString('requires PHP extension', $update->getProblems()[0]);
    }

    /**
     * @test
     * @testdox can run a (mocked) update and retrieve problems when the PHP version is incompatible.
     * @covers ::handle
     * @covers ::execute
     */
    public function itCanGetProblemsDueToInvalidPhpVersion()
    {
        $this->copyToWorkDir($this->testBasePath() . '/fixtures/invalid/incorrectPhpVersion/composer.json');

        $update = $this->composer->update();

        $this->assertCount(1, $update->getProblems());
        $this->assertStringContainsString('your php version', $update->getProblems()[0]);
        $this->assertStringContainsString('does not satisfy that requirement', $update->getProblems()[0]);
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

    /**
     * @test
     * @testdox can run a (real) update with --dry-run option
     * @covers ::handle
     * @covers ::execute
     */
    public function itCanRunAnUpdateRealWithDryRun()
    {
        $this->copyToWorkDir($this->testBasePath() . '/fixtures/valid/simple/composer.json');

        // call with dryRun = true
        #$update = $this->composer->update(true, false, false, 'none', false, true);
        $update = $this->composer->update(dryRun: true);

        // make sure no lock file gets created
        $this->assertFileDoesNotExist($this->workDir . '/composer.lock');

        // make sure no vendor folder gets created
        $this->assertDirectoryDoesNotExist($this->workDir . '/vendor');

        $this->assertNotEmpty($update->getRawOutput());

        $this->assertEquals(0, $update->getLockInstalledCount());
        $this->assertEquals(0, $update->getInstalledCount());
    }
}
