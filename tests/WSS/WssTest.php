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

    protected function setUp(): void
    {
        parent::setUp();
        $this->http = $this->getReflectedHttpWithResponse(json_encode([API::RESPONSE_CODE => API::SUCCESS_CODE, API::RESPONSE_MESSAGE => 'OK', 'data' => ['id' => '9hyvgrkE']]));
        $this->api = new API(static::EXAMPLE_CONFIG, $this->http, $this->logger);
    }

    public function testUpload()
    {
        $this->api->uploadServerCert('foo', 'bar', 'cert');
        $this->assertMyRequestBody(function($params){
            $this->assertStringContainsString('cert=foo', $params);
            $this->assertStringContainsString('certType=SVR', $params);
            $this->assertStringContainsString('key=bar', $params);
            $this->assertStringContainsString('alias=cert', $params);
        });

        $response = $this->api->uploadClientCert('foo');
        $this->assertMyRequestBody(function($params){
            $this->assertStringContainsString('cert=foo', $params);
            $this->assertStringContainsString('certType=CA', $params);
            $this->assertStringNotContainsString('key=', $params);
            $this->assertStringNotContainsString('alias=', $params);
        });

        $this->assertSame('9hyvgrkE', $this->api->ensureUploaded($response)->get('id'));

    }
}
