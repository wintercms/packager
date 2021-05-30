<?php

declare(strict_types=1);

namespace BennoThommo\Packager\Tests\Cases;

use BennoThommo\Packager\Tests\ComposerTestCase;

/**
 * @testdox The RunsComposer trait
 * @coversDefaultClass \BennoThommo\Packager\Commands\Traits\RunsComposer
 */
final class RunsComposerTest extends ComposerTestCase
{
    /** @var RunsComposer */
    protected $traitMock;

    /**
     * @before
     */
    public function createTraitMock(): void
    {
        $this->traitMock = $this->getMockForTrait('BennoThommo\Packager\Commands\Traits\RunsComposer');
    }

    /**
     * @test
     * @testdox can create a Composer application.
     * @covers ::createApplication
     */
    public function itCanCreateAComposerApp(): void
    {

    }
}
