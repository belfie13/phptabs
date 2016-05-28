<?php

namespace PhpTabsTest\Component;

use PHPUnit_Framework_TestCase;
use PhpTabs\Component\Tablature;

class TablatureTest extends PHPUnit_Framework_TestCase
{
  /**
   * @expectedException PHPUnit_Framework_Error
   */
  public function testUnexistingMethod()
  {
    # Method does not exist
    (new Tablature())->undefinedMethod();
  }

  /**
   * @expectedException PHPUnit_Framework_Error
   */
  public function testException()
  {
    # Not a valid number of params
    (new Tablature())->getChannels('param1', 'param2', 'param3');
  }
}