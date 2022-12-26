<?php

declare(strict_types=1);

namespace Winter\Packager\Tests\Cases\Storage;

use Winter\Packager\Storage\Memory;
use Winter\Packager\Tests\ComposerTestCase;

/**
 * @testdox Memory storage
 * @coversDefaultClass \Winter\Packager\Storage\Memory
 */
final class MemoryStorageTest extends ComposerTestCase
{
    /**
     * @var ?Memory storage instance.
     */
    protected $memory = null;

    /**
     * @after
     */
    public function clearMemoryStorage()
    {
        if (isset($this->memory)) {
            $this->memory->clear();
            $this->memory = null;
        }
    }

    /**
     * @test
     * @covers ::get
     * @covers ::set
     */
    public function canSetAndGetPackageData(): void
    {
        $this->memory = new Memory();
        $packageData = [
            'name' => 'winter/test-package',
            'version' => 'v1.0.1',
            'type' => 'package',
            'description' => 'Test package',
            'keywords' => ['winter', 'plugin'],
        ];

        $this->memory->set('winter/test-package', 'v1.0.1', $packageData);

        $this->assertEquals($packageData, $this->memory->get('winter/test-package', 'v1.0.1'));
    }

    /**
     * @test
     * @covers ::get
     * @covers ::set
     */
    public function canSetAndGetPackageDataWithDifferingVersionDefinitions(): void
    {
        $this->memory = new Memory();
        $packageData = [
            'name' => 'winter/test-package',
            'version' => 'v1.0.1',
            'type' => 'package',
            'description' => 'Test package',
            'keywords' => ['winter', 'plugin'],
        ];

        $this->memory->set('winter/test-package', '1.0.1', $packageData);

        $this->assertEquals($packageData, $this->memory->get('winter/test-package', 'v1.0.1.0'));
    }

    /**
     * @test
     * @covers ::get
     */
    public function returnsNullWhenPackageDoesNotExistInStorage(): void
    {
        $this->memory = new Memory();

        $this->assertNull($this->memory->get('winter/test-package', 'v1.0.1'));
    }

    /**
     * @test
     * @covers ::get
     * @covers ::set
     */
    public function canGetMultipleVersionsOfAPackage(): void
    {
        $this->memory = new Memory();
        $packageData101 = [
            'name' => 'winter/test-package',
            'version' => 'v1.0.1',
            'type' => 'package',
            'description' => 'Test package',
            'keywords' => ['winter', 'plugin'],
        ];
        $packageData102 = [
            'name' => 'winter/test-package',
            'version' => 'v1.0.2',
            'type' => 'package',
            'description' => 'Test package updated',
            'keywords' => ['winter', 'plugin'],
        ];
        $packageData103 = [
            'name' => 'winter/test-package-2',
            'version' => 'v1.0.1',
            'type' => 'package',
            'description' => 'Another test package',
            'keywords' => ['winter', 'plugin'],
        ];

        $this->memory->set('winter/test-package', 'v1.0.1', $packageData101);
        $this->memory->set('winter/test-package', 'v1.0.2', $packageData102);
        $this->memory->set('winter/test-package-2', 'v1.0.1', $packageData103);

        $this->assertEquals($packageData101, $this->memory->get('winter/test-package', 'v1.0.1'));
        $this->assertEquals($packageData102, $this->memory->get('winter/test-package', 'v1.0.2'));
        $this->assertEquals($packageData103, $this->memory->get('winter/test-package-2', 'v1.0.1'));

        $this->assertCount(2, $this->memory->get('winter/test-package'));
        $this->assertEquals([
            '1.0.1.0' => $packageData101,
            '1.0.2.0' => $packageData102,
        ], $this->memory->get('winter/test-package'));

        $this->assertCount(1, $this->memory->get('winter/test-package-2'));
        $this->assertEquals([
            '1.0.1.0' => $packageData103,
        ], $this->memory->get('winter/test-package-2'));
    }

    /**
     * @test
     * @covers ::get
     * @covers ::set
     * @covers ::has
     */
    public function canCheckExistenceOfPackagesAndVersions(): void
    {
        $this->memory = new Memory();
        $packageData101 = [
            'name' => 'winter/test-package',
            'version' => 'v1.0.1',
            'type' => 'package',
            'description' => 'Test package',
            'keywords' => ['winter', 'plugin'],
        ];
        $packageData102 = [
            'name' => 'winter/test-package',
            'version' => 'v1.0.2',
            'type' => 'package',
            'description' => 'Test package updated',
            'keywords' => ['winter', 'plugin'],
        ];
        $packageData103 = [
            'name' => 'winter/test-package',
            'version' => 'v1.0.3',
            'type' => 'package',
            'description' => 'Test package updated again',
            'keywords' => ['winter', 'plugin'],
        ];

        $this->memory->set('winter/test-package', 'v1.0.1', $packageData101);
        $this->memory->set('winter/test-package', 'v1.0.2', $packageData102);
        $this->memory->set('winter/test-package', 'v1.0.3', $packageData103);

        $this->assertTrue($this->memory->has('winter/test-package'));
        $this->assertTrue($this->memory->has('winter/test-package', 'v1.0.1'));
        $this->assertTrue($this->memory->has('winter/test-package', 'v1.0.2'));
        $this->assertTrue($this->memory->has('winter/test-package', 'v1.0.3'));
        $this->assertFalse($this->memory->has('winter/another-package'));
        $this->assertFalse($this->memory->has('winter/test-package', 'v1.0.4'));
        $this->assertFalse($this->memory->has('winter/test-package', '2'));
        $this->assertFalse($this->memory->has('winter/test-package', 'v2.0.1.0'));
    }

    /**
     * @test
     * @covers ::get
     * @covers ::set
     * @covers ::forget
     */
    public function canForgetPackagesAndVersions(): void
    {
        $this->memory = new Memory();
        $packageData101 = [
            'name' => 'winter/test-package',
            'version' => 'v1.0.1',
            'type' => 'package',
            'description' => 'Test package',
            'keywords' => ['winter', 'plugin'],
        ];
        $packageData102 = [
            'name' => 'winter/test-package',
            'version' => 'v1.0.2',
            'type' => 'package',
            'description' => 'Test package updated',
            'keywords' => ['winter', 'plugin'],
        ];
        $packageData103 = [
            'name' => 'winter/test-package-2',
            'version' => 'v1.0.1',
            'type' => 'package',
            'description' => 'Another test package',
            'keywords' => ['winter', 'plugin'],
        ];

        $this->memory->set('winter/test-package', 'v1.0.1', $packageData101);
        $this->memory->set('winter/test-package', 'v1.0.2', $packageData102);
        $this->memory->set('winter/test-package-2', 'v1.0.1', $packageData103);

        $this->memory->forget('winter/test-package', '1.0.1');

        $this->assertCount(1, $this->memory->get('winter/test-package'));
        $this->assertEquals([
            '1.0.2.0' => $packageData102,
        ], $this->memory->get('winter/test-package'));

        $this->memory->forget('winter/test-package-2');

        $this->assertNull($this->memory->get('winter/test-package-2'));
    }

    /**
     * @test
     * @covers ::get
     * @covers ::set
     * @covers ::clear
     */
    public function canClearTheStorageCompletely(): void
    {
        $this->memory = new Memory();
        $packageData101 = [
            'name' => 'winter/test-package',
            'version' => 'v1.0.1',
            'type' => 'package',
            'description' => 'Test package',
            'keywords' => ['winter', 'plugin'],
        ];
        $packageData102 = [
            'name' => 'winter/test-package',
            'version' => 'v1.0.2',
            'type' => 'package',
            'description' => 'Test package updated',
            'keywords' => ['winter', 'plugin'],
        ];
        $packageData103 = [
            'name' => 'winter/test-package-2',
            'version' => 'v1.0.1',
            'type' => 'package',
            'description' => 'Another test package',
            'keywords' => ['winter', 'plugin'],
        ];

        $this->memory->set('winter/test-package', 'v1.0.1', $packageData101);
        $this->memory->set('winter/test-package', 'v1.0.2', $packageData102);
        $this->memory->set('winter/test-package-2', 'v1.0.1', $packageData103);

        $this->memory->clear();

        $this->assertNull($this->memory->get('winter/test-package'));
        $this->assertNull($this->memory->get('winter/test-package', '1.0.2'));
        $this->assertNull($this->memory->get('winter/test-package-2'));
    }
}
