<?php

namespace QCloudSDKTests\Image;


use QCloudSDK\Image\Processor;
use QCloudSDKTests\TestCase;

class SignatureTest extends TestCase
{
    /**
     * @var Processor
     */
    protected $api;

    protected $time = 1436077115;

    protected $rand = '11162';

    protected $file = 'tencentyunSignTest';

    public function testSignature()
    {
        $this->assertSame('p2Y5iIYyBmQNfUvPe3e1sxEN/rZhPTEyNTI4MjE4NzEmYj10ZW5jZW50eXVuJms9QUtJRGdhb09ZaDJrT21KZldWZEg0bHBmeFNjRzJ6UExQR29LJmU9MTQzODY2OTExNSZ0PTE0MzYwNzcxMTUmcj0xMTE2MiZ1PTAmZj0=', $this->api->signMultiEffect(2592000, '', $this->time, $this->rand));
        $this->assertSame('Tt9IYBG4j1TpO/9M6M9TokVJrKhhPTEyNTI4MjE4NzEmYj10ZW5jZW50eXVuJms9QUtJRGdhb09ZaDJrT21KZldWZEg0bHBmeFNjRzJ6UExQR29LJmU9MTQzODY2OTExNSZ0PTE0MzYwNzcxMTUmcj0xMTE2MiZ1PTAmZj10ZW5jZW50eXVuU2lnblRlc3Q=', $this->api->signMultiEffect(2592000, $this->file, $this->time, $this->rand));
        $this->assertSame('ewXflzgpQON2bmrX6uJ5Yr0zuOphPTEyNTI4MjE4NzEmYj10ZW5jZW50eXVuJms9QUtJRGdhb09ZaDJrT21KZldWZEg0bHBmeFNjRzJ6UExQR29LJmU9MCZ0PTE0MzYwNzcxMTUmcj0xMTE2MiZ1PTAmZj10ZW5jZW50eXVuU2lnblRlc3Q=', $this->api->signOnce($this->file, $this->time, $this->rand));
    }

    protected function setUp()
    {
        parent::setUp();
        $this->api = new Processor($this->configForTest(), $this->http);
    }

}
