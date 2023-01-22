<?php

declare(strict_types=1);

namespace Winter\Packager\Tests\Cases;

use Winter\Packager\Commands\Install;
use Winter\Packager\Tests\ComposerTestCase;

/**
 * @testdox The Install command
 * @coversDefaultClass \Winter\Packager\Commands\Install
 */
final class InstallTest extends ComposerTestCase
{
    /**
     * @before
     */
    public function setUpDirectories(): void
    {
        $this->composer
            ->setHomeDir($this->homeDir)
            ->setWorkDir($this->workDir);
    }

    /**
     * @test
     * @testdox can run a (mocked) install and shows installed packages.
     * @covers ::handle
     * @covers ::execute
     */
    public function itCanRunAnInstallMocked(): void
    {
        $this->mockCommandOutput(
            'install',
            Install::class,
            0,
            file_get_contents($this->testBasePath() . '/fixtures/valid/validCleanUpdate/output.txt')
        );

        $install = $this->composer->install();

        $this->assertEquals(106, $install->getLockInstalledCount());
        $this->assertEquals(0, $install->getLockUpgradedCount());
        $this->assertEquals(0, $install->getLockRemovedCount());

        $this->assertEquals(106, $install->getInstalledCount());
        $this->assertEquals(0, $install->getUpgradedCount());
        $this->assertEquals(0, $install->getRemovedCount());

        // Check a couple of packages in the lock file
        $this->assertArrayHasKey('winter/storm', $install->getLockInstalled());
        $this->assertEquals('dev-develop', $install->getLockInstalled()['winter/storm']);

        $this->assertArrayHasKey('symfony/yaml', $install->getLockInstalled());
        $this->assertEquals('3.4.47.0', $install->getLockInstalled()['symfony/yaml']);

        $this->assertArrayHasKey('laravel/framework', $install->getLockInstalled());
        $this->assertEquals('6.20.27.0', $install->getLockInstalled()['laravel/framework']);

        $this->assertArrayHasKey('league/flysystem', $install->getLockInstalled());
        $this->assertEquals('1.1.3.0', $install->getLockInstalled()['league/flysystem']);

        $this->assertArrayHasKey('nesbot/carbon', $install->getLockInstalled());
        $this->assertEquals('2.48.9999999.9999999-dev', $install->getLockInstalled()['nesbot/carbon']);

        // Check a couple of packages
        $this->assertArrayHasKey('winter/storm', $install->getInstalled());
        $this->assertEquals('dev-develop', $install->getInstalled()['winter/storm']);

        $this->assertArrayHasKey('symfony/yaml', $install->getInstalled());
        $this->assertEquals('3.4.47.0', $install->getInstalled()['symfony/yaml']);

        $this->assertArrayHasKey('laravel/framework', $install->getInstalled());
        $this->assertEquals('6.20.27.0', $install->getInstalled()['laravel/framework']);

        $this->assertArrayHasKey('league/flysystem', $install->getInstalled());
        $this->assertEquals('1.1.3.0', $install->getInstalled()['league/flysystem']);

        $this->assertArrayHasKey('league/flysystem', $install->getInstalled());
        $this->assertEquals('1.1.3.0', $install->getInstalled()['league/flysystem']);

        $this->assertArrayHasKey('nesbot/carbon', $install->getInstalled());
        $this->assertEquals('2.48.9999999.9999999-dev', $install->getInstalled()['nesbot/carbon']);
    }

    /**
     * @test
     * @testdox can run a (real) install and show installed packages.
     * @covers ::handle
     * @covers ::execute
     */
    public function itCanRunAnInstallReal()
    {
        $this->copyToWorkDir($this->testBasePath() . '/fixtures/valid/simple/composer.json');

        $install = $this->composer->install();

        $this->assertEquals(2, $install->getLockInstalledCount());
        $this->assertEquals(0, $install->getLockUpgradedCount());
        $this->assertEquals(0, $install->getLockRemovedCount());

        $this->assertEquals(2, $install->getInstalledCount());
        $this->assertEquals(0, $install->getUpgradedCount());
        $this->assertEquals(0, $install->getRemovedCount());

        // Check packages
        $this->assertArrayHasKey('composer/semver', $install->getLockInstalled());
        $this->assertEquals('1.7.1.0', $install->getLockInstalled()['composer/semver']);
        $this->assertArrayHasKey('composer/semver', $install->getInstalled());
        $this->assertEquals('1.7.1.0', $install->getInstalled()['composer/semver']);

        $this->assertArrayHasKey('composer/ca-bundle', $install->getLockInstalled());
        $this->assertEquals('1.2.9.0', $install->getLockInstalled()['composer/ca-bundle']);
        $this->assertArrayHasKey('composer/ca-bundle', $install->getInstalled());
        $this->assertEquals('1.2.9.0', $install->getInstalled()['composer/ca-bundle']);
    }

    /**
     * @test
     * @testdox can run a (real) install with a lock file present and show installed packages.
     * @covers ::handle
     * @covers ::execute
     */
    public function itCanRunAnInstallWithALockFilePresent()
    {
        $this->copyToWorkDir($this->testBasePath() . '/fixtures/valid/installed/composer.json');
        $this->copyToWorkDir($this->testBasePath() . '/fixtures/valid/installed/composer.lock');

        $install = $this->composer->install();

        // There should be no lock file changes
        $this->assertEquals(0, $install->getLockInstalledCount());
        $this->assertEquals(0, $install->getLockUpgradedCount());
        $this->assertEquals(0, $install->getLockRemovedCount());

        $this->assertEquals(2, $install->getInstalledCount());
        $this->assertEquals(0, $install->getUpgradedCount());
        $this->assertEquals(0, $install->getRemovedCount());

        // Check packages
        $this->assertArrayHasKey('composer/semver', $install->getInstalled());
        $this->assertEquals('1.7.1.0', $install->getInstalled()['composer/semver']);

        $this->assertArrayHasKey('composer/ca-bundle', $install->getInstalled());
        $this->assertEquals('1.2.9.0', $install->getInstalled()['composer/ca-bundle']);
    }
}
