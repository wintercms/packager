<?php

declare(strict_types=1);

namespace Winter\Packager\Tests\Cases;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Winter\Packager\Commands\Search;
use Winter\Packager\Tests\ComposerTestCase;

/**
 * @testdox The Search command
 * @coversDefaultClass \Winter\Packager\Commands\Search
 */
final class SearchTest extends ComposerTestCase
{
    use ArraySubsetAsserts;

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
            ], JSON_PRETTY_PRINT)
        );

        $search = $this->composer->search('winter', 'winter-module');

        $this->assertArraySubset([
            [
                'name' => 'winter/wn-system-module',
            ],
            [
                'name' => 'winter/wn-cms-module',
            ],
            [
                'name' => 'winter/wn-backend-module',
            ],
        ], $search->getResults());
        $this->assertEquals(3, $search->count());
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
        $search = $this->composer->search('winter/', 'winter-module');

        $this->assertArraySubset([
            [
                'name' => 'winter/wn-system-module',
            ],
            [
                'name' => 'winter/wn-cms-module',
            ],
            [
                'name' => 'winter/wn-backend-module',
            ],
        ], $search->getResults());
        $this->assertEquals(3, $search->count());
    }
}
