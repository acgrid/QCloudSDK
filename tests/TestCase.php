<?php


namespace QCloudSDKTests;


use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use QCloudSDK\Core\CommonConfiguration;
use QCloudSDK\Core\Http;

class TestCase extends \PHPUnit\Framework\TestCase
{

    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var Http
     */
    protected $http;

    const EXAMPLE_CONFIG = [
        CommonConfiguration::CONFIG_SECRET_ID => 'foo',
        CommonConfiguration::CONFIG_SECRET_KEY => 'bar',
    ];

    protected function setUp(): void
    {
        $this->http = $this->getReflectedHttp();
        $this->logger = new Logger('Test', [new TestHandler()]);
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
    public function tearDown(): void
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