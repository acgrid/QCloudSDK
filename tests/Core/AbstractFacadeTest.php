<?php

namespace QCloudSDKTests\Core;

use QCloudSDK\Core\AbstractAPI;
use QCloudSDK\Core\AbstractFacade;
use QCloudSDK\Facade\APIs;

class A extends AbstractAPI {}
class B extends AbstractAPI {}

/**
 * Class TestFacade
 * @package QCloudSDKTests\Core
 * @property A $a
 * @property B $b
 */
class TestFacade extends AbstractFacade
{
    protected $map = [
       'a' => A::class,
       'b' => B::class,
    ];
}

class AbstractFacadeTest extends \PHPUnit_Framework_TestCase
{

    public function testFacade()
    {
        $facade = new TestFacade(new APIs([]));
        $this->assertInstanceOf(A::class, $a = $facade->a);
        $this->assertInstanceOf(B::class, $b = $facade->b);
        $this->assertSame($a, $facade->a);
        $this->assertSame($b, $facade->b);
    }
}
