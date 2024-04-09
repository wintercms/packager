<?php

declare(strict_types=1);

namespace Winter\Packager\Tests\Cases;

use Winter\Packager\Commands\Search;
use Winter\Packager\Tests\ComposerTestCase;

/**
 * @testdox The Search command
 * @coversDefaultClass \Winter\Packager\Commands\Search
 */
final class SearchTest extends ComposerTestCase
{
    /**
     * @test
     * @testdox can run a (mocked) search and show a few results.
     * @covers ::handle
     * @covers ::execute
     * @covers ::getResults
     * @covers ::count
     */
    public function itCanRunAMockedSearch(): void
    {
        $this->mockCommandOutput(
            'search',
            Search::class,
            0,
            json_encode([
                [
                    'name' => 'winter/wn-system-module',
                    'description' => 'System module for Winter CMS',
                    'url' => 'https://packagist.org/packages/winter/wn-system-module',
                    'repository' => 'https://github.com/wintercms/wn-system-module',
                ],
                [
                    'name' => 'winter/wn-cms-module',
                    'description' => 'CMS module for Winter CMS',
                    'url' => 'https://packagist.org/packages/winter/wn-cms-module',
                    'repository' => 'https://github.com/wintercms/wn-cms-module',
                ],
                [
                    'name' => 'winter/wn-backend-module',
                    'description' => 'Backend module for Winter CMS',
                    'url' => 'https://packagist.org/packages/winter/wn-backend-module',
                    'repository' => 'https://github.com/wintercms/wn-backend-module',
                ],
            ], JSON_PRETTY_PRINT),
            'winter',
            'winter-module',
        );

        $results = $this->composer->search('winter', 'winter-module');

        $this->assertEquals(3, $results->count());
        $this->assertContainsOnlyInstancesOf(\Winter\Packager\Package\Package::class, $results);

        // Check packages
        $this->assertEquals('winter', $results['winter/wn-system-module']->getNamespace());
        $this->assertEquals('wn-system-module', $results['winter/wn-system-module']->getName());
        $this->assertEquals('System module for Winter CMS', $results['winter/wn-system-module']->getDescription());

        $this->assertEquals('winter', $results['winter/wn-cms-module']->getNamespace());
        $this->assertEquals('wn-cms-module', $results['winter/wn-cms-module']->getName());
        $this->assertEquals('CMS module for Winter CMS', $results['winter/wn-cms-module']->getDescription());

        $this->assertEquals('winter', $results['winter/wn-backend-module']->getNamespace());
        $this->assertEquals('wn-backend-module', $results['winter/wn-backend-module']->getName());
        $this->assertEquals('Backend module for Winter CMS', $results['winter/wn-backend-module']->getDescription());
    }

    /**
     * @test
     * @testdox can run a real search and show a few results.
     * @covers ::handle
     * @covers ::execute
     * @covers ::getResults
     * @covers ::count
     */
    public function itCanRunRealSearch(): void
    {
        $results = $this->composer->search('winter/', 'winter-module');

        $this->assertContainsOnlyInstancesOf(\Winter\Packager\Package\Package::class, $results);

        // Check packages
        $this->assertEquals('winter', $results['winter/wn-system-module']->getNamespace());
        $this->assertEquals('wn-system-module', $results['winter/wn-system-module']->getName());
        $this->assertEquals('winter/wn-system-module', $results['winter/wn-system-module']->getPackageName());

        $this->assertEquals('winter', $results['winter/wn-cms-module']->getNamespace());
        $this->assertEquals('wn-cms-module', $results['winter/wn-cms-module']->getName());
        $this->assertEquals('winter/wn-cms-module', $results['winter/wn-cms-module']->getPackageName());

        $this->assertEquals('winter', $results['winter/wn-backend-module']->getNamespace());
        $this->assertEquals('wn-backend-module', $results['winter/wn-backend-module']->getName());
        $this->assertEquals('winter/wn-backend-module', $results['winter/wn-backend-module']->getPackageName());
    }
}
