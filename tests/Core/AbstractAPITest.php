<?php

namespace QCloudSDKTests\Core;

use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use QCloudSDK\Core\AbstractAPI;
use QCloudSDK\Core\Exceptions\ClientException;
use QCloudSDK\Core\Http;
use QCloudSDK\Facade\Config;
use QCloudSDKTests\MockClient;
use QCloudSDKTests\TestCase;

class TestAPI extends AbstractAPI
{
    const CONFIG_SECTION = 'test';

    protected $foo;

    protected $tapped;

    protected function init()
    {
        parent::init();
        $this->foo = true;
    }

    protected function registerHttpMiddlewares()
    {
        parent::registerHttpMiddlewares();
        $this->http->addMiddleware(Middleware::tap(function (RequestInterface $request) {
            $this->tapped = $request;
        }));
    }

    /**
     * @return mixed
     */
    public function getFoo()
    {
        return $this->foo;
    }

    public function getGlobalVersion()
    {
        return $this->config->get('version', 1);
    }

    public function getLocalVersion()
    {
        return $this->getLocalConfig('version', 3);
    }

    /**
     * @return mixed
     */
    public function getTapped()
    {
        return $this->tapped;
    }

    public function request()
    {
        return $this->parseJSON('GET', 'http://example.org');
    }

    protected function doSign($method, $url, $params)
    {
        return "signed for " . json_encode(compact('method', 'url', 'params'));
    }

}

class RetryTestAPI extends TestAPI
{
    protected $retryCodes = [1000];

    protected function registerHttpMiddlewares()
    {
        $this->http->addMiddleware(Middleware::mapResponse(function(){
            return new Response(200, [], json_encode([AbstractAPI::RESPONSE_CODE => 1000, AbstractAPI::RESPONSE_MESSAGE => 'System busy']));
        }));
        parent::registerHttpMiddlewares();
    }

}

class AbstractAPITest extends TestCase
{

    public function testAPI()
    {
        $http = new Http();
        $api = new TestAPI(new Config(['debug' => true, 'version' => 2, TestAPI::CONFIG_SECTION => ['version' => 4]]), $http);
        $this->assertTrue($api->getFoo());
        $this->assertSame($http, $api->getHttp());
        $this->assertSame(2, $api->getGlobalVersion());
        $this->assertSame(4, $api->getLocalVersion());
        $bareApi = new TestAPI(new Config());
        $bareApi->setHttp($http);
        $this->assertSame($http, $api->getHttp());
        $this->assertSame(1, $bareApi->getGlobalVersion());
        $this->assertSame(3, $bareApi->getLocalVersion());
    }

    public function testMiddleware()
    {
        $http = new Http();
        $api = new RetryTestAPI(new Config());
        $api->setHttp($http);
        $http->setClient(MockClient::repeat(4, json_encode([AbstractAPI::RESPONSE_CODE => 1000, AbstractAPI::RESPONSE_MESSAGE => 'System busy'])));
        $this->assertCount(4, $api->getHttp()->getMiddlewares());
        try{
            $api->request();
        }catch (ClientException $e){
            $this->assertSame(1000, $e->getCode());
            $this->assertSame('System busy', $e->getMessage());
        }
        $this->assertInstanceOf(RequestInterface::class, $api->getTapped());
        //$this->assertSame(3, $client->getRequestCount());
    }

    public function testSignature()
    {

    }

}
