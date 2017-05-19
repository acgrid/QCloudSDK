<?php

namespace QCloudSDKTests\WSS;

use QCloudSDK\WSS\API;
use QCloudSDKTests\TestCase;

class WssTest extends TestCase
{
    /**
     * @var API;
     */
    protected $api;

    protected function setUp()
    {
        $this->http = $this->getReflectedHttpWithResponse(json_encode([API::RESPONSE_CODE => API::SUCCESS_CODE, API::RESPONSE_MESSAGE => 'OK', 'data' => ['id' => '9hyvgrkE']]));
        $this->api = new API($this->configForTest(), $this->http);
    }

    public function testUpload()
    {
        $this->api->uploadServerCert('foo', 'bar', 'cert');
        $this->assertMyRequestBody(function ($params) {
            $this->assertContains('cert=foo', $params);
            $this->assertContains('certType=SVR', $params);
            $this->assertContains('key=bar', $params);
            $this->assertContains('alias=cert', $params);
        });

        $response = $this->api->uploadClientCert('foo');
        $this->assertMyRequestBody(function ($params) {
            $this->assertContains('cert=foo', $params);
            $this->assertContains('certType=CA', $params);
            $this->assertNotContains('key=', $params);
            $this->assertNotContains('alias=', $params);
        });

        $this->assertSame('9hyvgrkE', $this->api->ensureUploaded($response)->get('id'));
    }
}
