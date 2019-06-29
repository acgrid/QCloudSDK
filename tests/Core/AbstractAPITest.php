<?php

namespace QCloudSDKTests\Core;

use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Psr\Http\Message\RequestInterface;
use QCloudSDK\Core\AbstractAPI;
use QCloudSDK\Core\Exceptions\ClientException;
use QCloudSDK\Core\Exceptions\HttpException;
use QCloudSDK\Core\Http;
use QCloudSDKTests\MockClient;
use QCloudSDKTests\TestCase;
use Tightenco\Collect\Support\Arr;

class TestAPI extends AbstractAPI
{
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

    public function getVersion()
    {
        return Arr::get($this->config, 'version', 1);
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
        return $this->parseJSON('get', 'http://example.org');
    }

    public function advRequest()
    {
        return $this->expectResult('data', $this->parseJSONSigned('post', 'www.example.org', $this->createParam('op', 'bar')));
    }

    protected function doSign($method, $url, $params)
    {
        $params['sign'] = "signed for $method to $url with param " . json_encode($params);
        return $params;
    }

}

class RetryTestAPI extends TestAPI
{
    protected $retryCodes = [1000];
}

class AbstractAPITest extends TestCase
{

    public function testAPI()
    {
        $http = new Http();
        $api = new TestAPI(['debug' => true, 'version' => 2], $http, $this->logger);
        $this->assertTrue($api->getFoo());
        $this->assertSame($http, $api->getHttp());
        $this->assertSame(2, $api->getVersion());
        $bareApi = new TestAPI([], $this->http, $this->logger);
        $bareApi->setHttp($http);
        $this->assertSame($http, $api->getHttp());
        $this->assertSame(1, $bareApi->getVersion());
    }

    public function testLogging()
    {
        new TestAPI([AbstractAPI::CONFIG_DEBUG => true, 'SecretId' => 'abcdefghijklmn', 'SystemKEY' => 'foo123456789', 'foo' => ['AppKey' => '87743144531']], $this->http, new Logger('Test', [$handler = new TestHandler()]));
        $handler->hasRecordThatPasses(function($record){
            $this->assertSame('Current config:', $record['message']);
            $this->assertSame([AbstractAPI::CONFIG_DEBUG => true, 'SecretId' => '***jklmn', 'SystemKEY' => '***56789', 'foo' => ['AppKey' => '***44531']], $record['context']);
        }, Logger::DEBUG);
    }

    public function testMiddleware()
    {
        $http = new Http();
        $api = new RetryTestAPI([Http::CONFIG_MAX_RETRIES => 3], $http, $this->logger);
        $mock = MockClient::mock(MockClient::repeatResponses(4, json_encode([AbstractAPI::RESPONSE_CODE => 1000, AbstractAPI::RESPONSE_MESSAGE => 'System busy'])));
        $http->setClient(MockClient::makeFromMock($mock));
        $this->assertCount(3, $api->getHttp()->getMiddlewares());
        try{
            $api->request();
            $this->fail('Should throw ClientException');
        }catch (ClientException $e){
            $this->assertSame(1000, $e->getCode());
            $this->assertSame('System busy', $e->getMessage());
        }
        $this->assertInstanceOf(RequestInterface::class, $api->getTapped());
        $this->assertCount(0, $mock);
    }

    public function testSkipRetry()
    {
        $mock = MockClient::mock([
            new Response(200, [], json_encode([AbstractAPI::RESPONSE_CODE => 1000, AbstractAPI::RESPONSE_MESSAGE => 'System busy'])),
            new Response(200, [], json_encode([AbstractAPI::RESPONSE_CODE => 9999, AbstractAPI::RESPONSE_MESSAGE => 'Service terminated'])),
        ]);
        $http = new Http();
        $http->setClient(MockClient::makeFromMock($mock));
        $api = new RetryTestAPI([Http::CONFIG_MAX_RETRIES => 3], $http, $this->logger);
        try{
            $api->request();
            $this->fail('Should throw ClientException');
        }catch (ClientException $e){
            $this->assertSame(9999, $e->getCode());
            $this->assertSame('Service terminated', $e->getMessage());
        }
        $this->assertCount(0, $mock);
    }

    public function testBreakRetry()
    {
        $mock = MockClient::mock(MockClient::repeatResponses(1, 'html'));
        $http = new Http();
        $http->setClient(MockClient::makeFromMock($mock));
        $api = new RetryTestAPI([Http::CONFIG_MAX_RETRIES => 3], $http, $this->logger);
        $api->setHttp($http);
        try{
            $api->request();
            $this->fail('Should throw ClientException');
        }catch (HttpException $e){ }
        $this->assertCount(0, $mock);
    }

    public function testSignature()
    {
        $api = new TestAPI([], $http = $this->getReflectedHttpWithResponse('foo'), $this->logger);
        $response = $api->requestSigned('get', 'www.example.org/', ['op' => 'foo']);
        $this->assertRequest($http, function(Request $request){
            $this->assertSame('https://www.example.org/?' . http_build_query(['op' => 'foo', 'sign' => 'signed for GET to www.example.org/ with param {"op":"foo"}'], null, '&', PHP_QUERY_RFC3986), strval($request->getUri()));
        });
        $this->assertSame('foo', strval($response->getBody()));
        $api->setHttp($http = $this->getReflectedHttpWithResponse(json_encode([TestAPI::RESPONSE_CODE => 2])));
        try{
            $api->parseJSON('get', 'www.example.org/');
        }catch (ClientException $exception){
            $this->assertSame('Unknown', $exception->getMessage());
        }

        $api->setHttp($http = $this->getReflectedHttpWithResponse(json_encode([TestAPI::RESPONSE_CODE => TestAPI::SUCCESS_CODE, 'data' => ['foo' => 'bar']])));
        $data = $api->advRequest();
        $this->assertSame('bar', $data->get('foo'));
        $this->assertRequest($http, function(Request $request){
            $this->assertSame(http_build_query(['op' => 'bar', 'sign' => 'signed for POST to www.example.org with param {"op":"bar"}']), $request->getBody()->__toString());
        });
    }

}
