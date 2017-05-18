<?php

namespace QCloudSDKTests\TIM;

use GuzzleHttp\Psr7\Uri;
use QCloudSDK\TIM\Signature;
use QCloudSDKTests\TestCase;

class SignatureTest extends TestCase
{
    /**
     * @var Signature
     */
    protected $signature;

    protected function setUp()
    {
        parent::setUp();
        $this->signature = new Signature($this->configForTest(), $this->http);
    }

    public function testAdd()
    {
        $this->signature->add('foo', 'bar');
        $this->assertMyRequestUri(function (Uri $uri){
            $this->assertStringEndsWith('add_sign', $uri->getPath());
        });
        $this->assertMyRequestJson(function ($json) {
            $this->assertSame('foo', $json['text']);
            $this->assertSame('bar', $json['remark']);
        });
    }

    public function testEdit()
    {
        $this->signature->mod(233, 'foo', 'bar');
        $this->assertMyRequestUri(function (Uri $uri){
            $this->assertStringEndsWith('mod_sign', $uri->getPath());
        });
        $this->assertMyRequestJson(function ($json) {
            $this->assertSame(233, $json['sign_id']);
            $this->assertSame('foo', $json['text']);
            $this->assertSame('bar', $json['remark']);
        });
    }

    public function testDelete()
    {
        $this->signature->delete([22, 33]);
        $this->assertMyRequestUri(function (Uri $uri){
            $this->assertStringEndsWith('del_sign', $uri->getPath());
        });
        $this->assertMyRequestJson(function ($json) {
            $this->assertSame([22, 33], $json['sign_id']);
        });
    }

    public function testGet()
    {
        $this->signature->get(486);
        $this->assertMyRequestUri(function (Uri $uri){
            $this->assertStringEndsWith('get_sign', $uri->getPath());
        });
        $this->assertMyRequestJson(function ($json) {
            $this->assertSame(486, $json['sign_id']);
        });
    }

}
