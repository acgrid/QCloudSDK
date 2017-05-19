<?php

namespace QCloudSDKTests\TIM;

use GuzzleHttp\Psr7\Uri;
use QCloudSDK\TIM\Voice;
use QCloudSDKTests\TestCase;

class VoiceTest extends TestCase
{

    /**
     * @var Voice
     */
    protected $voice;

    protected function setUp()
    {
        parent::setUp();
        $this->voice = new Voice($this->configForTest(), $this->http);
    }

    public function testVerifyCode()
    {
        $this->setUp();
        $this->voice->setExt('foo')->setPlayTimes(1)->sendVerifyCode('852', '98761234', '9999');
        $this->assertMyRequestUri(function (Uri $uri) {
            $this->assertStringEndsWith('sendvoice', $uri->getPath());
        });
        $this->assertMyRequestJson(function ($json) {
            $this->assertSame(['nationcode' => '852', 'mobile' => '98761234'], $json['tel']);
            $this->assertArrayNotHasKey('prompttype', $json);
            $this->assertArrayNotHasKey('promptfile', $json);
            $this->assertSame('9999', $json['msg']);
            $this->assertSame(1, $json['playtimes']);
            $this->assertSame('foo', $json['ext']);
        });
    }

    public function testPrompt()
    {
        $this->setUp();
        $this->voice->sendPrompt('852', '12345678', 'Hello World');
        $this->assertMyRequestUri(function (Uri $uri) {
            $this->assertStringEndsWith('sendvoiceprompt', $uri->getPath());
        });
        $this->assertMyRequestJson(function ($json) {
            $this->assertSame(['nationcode' => '852', 'mobile' => '12345678'], $json['tel']);
            $this->assertSame(2, $json['prompttype']);
            $this->assertSame('Hello World', $json['promptfile']);
            $this->assertArrayNotHasKey('msg', $json);
            $this->assertArrayNotHasKey('playtimes', $json);
            $this->assertSame('', $json['ext']);
        });
    }
}
