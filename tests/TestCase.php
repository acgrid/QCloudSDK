<?php


namespace QCloudSDKTests;


use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use QCloudSDK\Core\Http;
use QCloudSDK\Facade\Config;

class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Http
     */
    protected $http;

    protected function setUp()
    {
        $this->http = $this->getReflectedHttp();
    }

    protected function configForTest()
    {
        return new Config([
            Config::COMMON_SECRET_ID => 'foo',
            Config::COMMON_SECRET_KEY => 'bar',
            'cos' => [
                'AppId' => '200001',
                'bucket' => 'newbucket',
                Config::COMMON_SECRET_ID => 'AKIDUfLUEUigQiXqm7CVSspKJnuaiIKtxqAv',
                Config::COMMON_SECRET_KEY => 'bLcPnl88WU30VY57ipRhSePfPdOfSruK',
                Config::COMMON_REGION => 'gz',
            ],
            'tim' => [
                'AppId' => '100032221',
                'AppKey' => 'dffdfd6029698a5fdf4',
            ],
            'image' => [
                'AppId' => '1252821871',
                'bucket' => 'tencentyun',
                Config::COMMON_SECRET_ID => 'AKIDgaoOYh2kOmJfWVdH4lpfxScG2zPLPGoK',
                Config::COMMON_SECRET_KEY => 'nwOKDouy5JctNOlnere4gkVoOUz5EYAb',
                Config::COMMON_REGION => 'gz',
                'ApiScheme' => 'http',
                'ApiHost' => 'test.image.myqcloud.com',
                'private' => 60,
                'style-separator' => '!',
            ],
        ]);
    }

    protected function createParam($key, $action)
    {
        return [$key => $action];
    }

    /**
     * @return Http
     */
    public function getReflectedHttp()
    {
        $http = new Http();
        $http->setClient(new ReflectClient());
        return $http;
    }

    public function getReflectedHttpWithResponse($body = null, $status = 200, array $headers = ['X-Foo' => 'Bar'], string $protocol = '1.1')
    {
        $http = new Http();
        $http->setClient(new ReflectClient(function() use ($body, $status, $headers, $protocol) {
            return new Response($status, $headers, $body, $protocol);
        }));
        return $http;
    }

    protected function assertRequest(Http $http, \Closure $assertion)
    {
        $client = $http->getClient();
        if(method_exists($client, 'assertRequest')){
            $client->assertRequest($assertion);
        }else{
            throw new \InvalidArgumentException('Not a client has assertion.');
        }
    }

    protected function assertMyRequestMethod(string $method)
    {
        $this->assertRequest($this->http, function(Request $request) use ($method){
            $this->assertSame($method, $request->getMethod());
        });
    }

    protected function assertMyRequestBody(\Closure $assertion)
    {
        $this->assertRequest($this->http, function(Request $request) use ($assertion){
            $assertion(strval($request->getBody()));
        });
    }

    protected function assertMyRequestJson(\Closure $assertion)
    {
        $this->assertMyRequestBody(function ($data) use ($assertion) {
            $assertion(json_decode($data, true));
        });
    }

    protected function assertMyRequestUri(\Closure $assertion)
    {
        $this->assertRequest($this->http, function(Request $request) use ($assertion){
            $assertion($request->getUri());
        });
    }

    protected function assertMyRequestHeaders(\Closure $assertion)
    {
        $this->assertRequest($this->http, function(Request $request) use ($assertion){
            $assertion($request->getHeaders());
        });
    }

    protected function makeFormData(string $name, string $value, string $filename = null)
    {
        return sprintf("name=\"%s\"%s\r\nContent-Length: %u\r\n\r\n%s", $name, isset($filename) ? "; filename=\"$filename\"" : '', strlen($value), $value);
    }

    /**
     * Tear down the test case.
     */
    public function tearDown()
    {
        $this->finish();
        parent::tearDown();
        if ($container = \Mockery::getContainer()) {
            $this->addToAssertionCount($container->Mockery_getExpectationCount());
        }
        \Mockery::close();
    }

    /**
     * Run extra tear down code.
     */
    protected function finish()
    {
        // call more tear down methods
    }
}