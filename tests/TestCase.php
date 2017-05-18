<?php


namespace QCloudSDKTests;


use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use QCloudSDK\Core\Http;

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

    protected function assertMyRequestBody(\Closure $assertion)
    {
        $this->assertRequest($this->http, function(Request $request) use ($assertion){
            $assertion(strval($request->getBody()));
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