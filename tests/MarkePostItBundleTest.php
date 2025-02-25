<?php

declare(strict_types=1);

/*
 * This file is part of [package name].
 *
 * (c) John Doe
 *
 * @license LGPL-3.0-or-later
 */

namespace Marke\PostItBundle\Tests;

use Marke\PostItBundle\MarkePostItBundle;
use PHPUnit\Framework\TestCase;

class MarkePostItBundleTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $bundle = new MarkePostItBundle();

        $this->assertInstanceOf('Marke\PostItBundle\MarkePostItBundle', $bundle);
    }
}
