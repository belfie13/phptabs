<?php

/*
 * This file is part of the PhpTabs package.
 *
 * Copyright (c) landrok at github.com/landrok
 *
 * For the full copyright and license information, please see
 * <https://github.com/stdtabs/phptabs/blob/master/LICENSE>.
 */

namespace PhpTabsTest;

use Exception;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Error\Error;
use PhpTabs\IOFactory;

/**
 * Tests PhpTabs component
 */
class PhpTabsTest extends TestCase
{
    public function testException()
    {
        $this->expectError();
        # Not a valid number of params
        IOFactory::create()->export('param1', 'param2', 'param3');
    }

  /**
   * Test getVersion()
   */
    public function testGetVersion()
    {
        $this->assertRegExp(
            '/\d.\d.\d/',
            IOFactory::create()->getVersion(),
            'getVersion failed'
        );
    }
}
