<?php

declare(strict_types=1);

namespace Winter\Packager\Tests\Cases;

use Winter\Packager\Commands\ListCommand;
use Winter\Packager\Enums\ListType;
use Winter\Packager\Tests\ComposerTestCase;

/**
 * @testdox The List command
 * @coversDefaultClass \Winter\Packager\Commands\ListCommand
 */
final class ListCommandTest extends ComposerTestCase
{
    /**
     * @test
     * @testdox can run a (mocked) list and collect a few results
     * @covers ::execute
     */
    public function itCanRunAMockedList(): void
    {
        // Mock the command and replace the "runCommand" method
        $mockCommand = $this->getMockBuilder(ListCommand::class)
            ->setConstructorArgs([
                $this->composer,
            ])
            ->onlyMethods(['queryPackagist'])
            ->getMock();

        $mockCommand
            ->method('queryPackagist')
            ->willReturn([
                'packages' => [
                    'winter/wn-system-module' => [
                        'type' => 'winter-module',
                    ],
                    'winter/wn-cms-module' => [
                        'type' => 'winter-module',
                    ],
                    'winter/wn-backend-module' => [
                        'type' => 'winter-module',
                    ],
                    'winter/wn-blog-plugin' => [
                        'type' => 'winter-plugin',
                    ],
                    'winter/wn-user-plugin' => [
                        'type' => 'winter-plugin',
                    ],
                    'winter/wn-nabu-theme' => [
                        'type' => 'winter-theme',
                    ],
                ]
            ]);

        $this->composer->setCommand('list', $mockCommand);

        $results = $this->composer->list();

        $this->assertEquals(6, $results->count());
        $this->assertContainsOnlyInstancesOf(\Winter\Packager\Package\Package::class, $results);

        // Check packages
        $this->assertEquals('winter', $results['winter/wn-system-module']->getNamespace());
        $this->assertEquals('wn-system-module', $results['winter/wn-system-module']->getName());
        $this->assertEquals('winter-module', $results['winter/wn-system-module']->getType());

        $this->assertEquals('winter', $results['winter/wn-cms-module']->getNamespace());
        $this->assertEquals('wn-cms-module', $results['winter/wn-cms-module']->getName());
        $this->assertEquals('winter-module', $results['winter/wn-cms-module']->getType());

        $this->assertEquals('winter', $results['winter/wn-backend-module']->getNamespace());
        $this->assertEquals('wn-backend-module', $results['winter/wn-backend-module']->getName());
        $this->assertEquals('winter-module', $results['winter/wn-backend-module']->getType());

        $this->assertEquals('winter', $results['winter/wn-blog-plugin']->getNamespace());
        $this->assertEquals('wn-blog-plugin', $results['winter/wn-blog-plugin']->getName());
        $this->assertEquals('winter-plugin', $results['winter/wn-blog-plugin']->getType());

        $this->assertEquals('winter', $results['winter/wn-user-plugin']->getNamespace());
        $this->assertEquals('wn-user-plugin', $results['winter/wn-user-plugin']->getName());
        $this->assertEquals('winter-plugin', $results['winter/wn-user-plugin']->getType());

        $this->assertEquals('winter', $results['winter/wn-nabu-theme']->getNamespace());
        $this->assertEquals('wn-nabu-theme', $results['winter/wn-nabu-theme']->getName());
        $this->assertEquals('winter-theme', $results['winter/wn-nabu-theme']->getType());
    }

    /**
     * @test
     * @testdox can run a (mocked) list from a given vendor and collect a few results
     * @covers ::execute
     */
    public function itCanRunAMockedListOfAVendor(): void
    {
        // Mock the command and replace the "runCommand" method
        $mockCommand = $this->getMockBuilder(ListCommand::class)
            ->setConstructorArgs([
                $this->composer,
                ListType::NAMESPACE,
                'winter'
            ])
            ->onlyMethods(['queryPackagist'])
            ->getMock();

        $mockCommand
            ->method('queryPackagist')
            ->willReturn([
                'packages' => [
                    'winter/wn-system-module' => [
                        'type' => 'winter-module',
                    ],
                    'winter/wn-cms-module' => [
                        'type' => 'winter-module',
                    ],
                    'winter/wn-backend-module' => [
                        'type' => 'winter-module',
                    ],
                    'winter/wn-blog-plugin' => [
                        'type' => 'winter-plugin',
                    ],
                    'winter/wn-user-plugin' => [
                        'type' => 'winter-plugin',
                    ],
                    'winter/wn-nabu-theme' => [
                        'type' => 'winter-theme',
                    ],
                ]
            ]);

        $this->composer->setCommand('list', $mockCommand);

        $results = $this->composer->list(ListType::NAMESPACE, 'winter');

        $this->assertEquals(6, $results->count());
        $this->assertContainsOnlyInstancesOf(\Winter\Packager\Package\Package::class, $results);

        // Check packages
        $this->assertEquals('winter', $results['winter/wn-system-module']->getNamespace());
        $this->assertEquals('wn-system-module', $results['winter/wn-system-module']->getName());
        $this->assertEquals('winter-module', $results['winter/wn-system-module']->getType());

        $this->assertEquals('winter', $results['winter/wn-cms-module']->getNamespace());
        $this->assertEquals('wn-cms-module', $results['winter/wn-cms-module']->getName());
        $this->assertEquals('winter-module', $results['winter/wn-cms-module']->getType());

        $this->assertEquals('winter', $results['winter/wn-backend-module']->getNamespace());
        $this->assertEquals('wn-backend-module', $results['winter/wn-backend-module']->getName());
        $this->assertEquals('winter-module', $results['winter/wn-backend-module']->getType());

        $this->assertEquals('winter', $results['winter/wn-blog-plugin']->getNamespace());
        $this->assertEquals('wn-blog-plugin', $results['winter/wn-blog-plugin']->getName());
        $this->assertEquals('winter-plugin', $results['winter/wn-blog-plugin']->getType());

        $this->assertEquals('winter', $results['winter/wn-user-plugin']->getNamespace());
        $this->assertEquals('wn-user-plugin', $results['winter/wn-user-plugin']->getName());
        $this->assertEquals('winter-plugin', $results['winter/wn-user-plugin']->getType());

        $this->assertEquals('winter', $results['winter/wn-nabu-theme']->getNamespace());
        $this->assertEquals('wn-nabu-theme', $results['winter/wn-nabu-theme']->getName());
        $this->assertEquals('winter-theme', $results['winter/wn-nabu-theme']->getType());
    }

    /**
     * @test
     * @testdox can run a (mocked) list of a given package type and collect a few results
     * @covers ::execute
     */
    public function itCanRunAMockedListOfAType(): void
    {
        // Mock the command and replace the "runCommand" method
        $mockCommand = $this->getMockBuilder(ListCommand::class)
            ->setConstructorArgs([
                $this->composer,
                ListType::TYPE,
                'winter-module'
            ])
            ->onlyMethods(['queryPackagist'])
            ->getMock();

        $mockCommand
            ->method('queryPackagist')
            ->willReturn([
                'packages' => [
                    'winter/wn-system-module' => [
                        'type' => 'winter-module',
                    ],
                    'winter/wn-cms-module' => [
                        'type' => 'winter-module',
                    ],
                    'winter/wn-backend-module' => [
                        'type' => 'winter-module',
                    ],
                ]
            ]);

        $this->composer->setCommand('list', $mockCommand);

        $results = $this->composer->list(ListType::TYPE, 'winter-module');

        $this->assertEquals(3, $results->count());
        $this->assertContainsOnlyInstancesOf(\Winter\Packager\Package\Package::class, $results);

        // Check packages
        $this->assertEquals('winter', $results['winter/wn-system-module']->getNamespace());
        $this->assertEquals('wn-system-module', $results['winter/wn-system-module']->getName());
        $this->assertEquals('winter-module', $results['winter/wn-system-module']->getType());

        $this->assertEquals('winter', $results['winter/wn-cms-module']->getNamespace());
        $this->assertEquals('wn-cms-module', $results['winter/wn-cms-module']->getName());
        $this->assertEquals('winter-module', $results['winter/wn-cms-module']->getType());

        $this->assertEquals('winter', $results['winter/wn-backend-module']->getNamespace());
        $this->assertEquals('wn-backend-module', $results['winter/wn-backend-module']->getName());
        $this->assertEquals('winter-module', $results['winter/wn-backend-module']->getType());
    }

    /**
     * @test
     * @testdox can run a (real) list and collect the results
     * @covers ::execute
     */
    public function itCanRunARealList(): void
    {
        $results = $this->composer->list(ListType::TYPE, 'winter-plugin');

        $this->assertGreaterThan(0, $results->count());
        $this->assertContainsOnlyInstancesOf(\Winter\Packager\Package\Package::class, $results);

        $this->assertEquals('winter', $results['winter/wn-blog-plugin']->getNamespace());
        $this->assertEquals('wn-blog-plugin', $results['winter/wn-blog-plugin']->getName());
        $this->assertEquals('winter-plugin', $results['winter/wn-blog-plugin']->getType());

        $this->assertEquals('winter', $results['winter/wn-user-plugin']->getNamespace());
        $this->assertEquals('wn-user-plugin', $results['winter/wn-user-plugin']->getName());
        $this->assertEquals('winter-plugin', $results['winter/wn-user-plugin']->getType());
    }
}
