<?php

declare(strict_types=1);

/*
 * This file is part of [package name].
 *
 * (c) John Doe
 *
 * @license LGPL-3.0-or-later
 */

namespace Marke\FrontendNoteBundle\Tests;

use Marke\FrontendNoteBundle\MarkeFrontendNoteBundle;
use PHPUnit\Framework\TestCase;

class MarkeFrontendNoteBundleTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $bundle = new MarkeFrontendNoteBundle();

        $this->assertInstanceOf('Marke\FrontendNoteBundle\MarkeFrontendNoteBundle', $bundle);
    }
}
