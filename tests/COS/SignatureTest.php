<?php

namespace QCloudSDKTests\COS;


use QCloudSDK\COS\API;
use QCloudSDKTests\TestCase;

class SignatureTest extends TestCase
{
    /**
     * @var API
     */
    protected $api;

    protected $time = 1470736940;

    protected $rand = '490258943';

    protected function setUp()
    {
        parent::setUp();
        $this->api = new API($this->configForTest(), $this->http);
    }

    public function testOnceSignature()
    {
        $this->assertSame('CkZ0/gWkHy3f76ER7k6yXgzq7w1hPTIwMDAwMSZiPW5ld2J1Y2tldCZrPUFLSURVZkxVRVVpZ1FpWHFtN0NWU3NwS0pudWFpSUt0eHFBdiZlPTAmdD0xNDcwNzM2OTQwJnI9NDkwMjU4OTQzJmY9LzIwMDAwMS9uZXdidWNrZXQvdGVuY2VudF90ZXN0LmpwZw==', $this->api->signOnce('tencent_test.jpg', $this->time, $this->rand));
    }

    public function testMultipleSignature()
    {
        $this->assertSame('v6+um3VE3lxGz97PmnSg6+/V9PZhPTIwMDAwMSZiPW5ld2J1Y2tldCZrPUFLSURVZkxVRVVpZ1FpWHFtN0NWU3NwS0pudWFpSUt0eHFBdiZlPTE0NzA3MzcwMDAmdD0xNDcwNzM2OTQwJnI9NDkwMjU4OTQzJmY9', $this->api->signMultiEffect(60, $this->time, $this->rand));
    }

    public function testRandom()
    {
        $sign = base64_decode($this->api->signMultiEffect());
        $this->assertContains('&t=' . intdiv(time(), 100), $sign);
        $this->assertRegExp('/r=\d{5}/', $sign);
    }

}
