<?php


namespace QCloudSDKTests\Core {

    use QCloudSDK\Core\Exceptions\HttpException;
    use QCloudSDK\Core\Http;
    use QCloudSDKTests\TestCase;
    use GuzzleHttp\Client;
    use GuzzleHttp\HandlerStack;
    use GuzzleHttp\Middleware;
    use GuzzleHttp\Psr7\Request;
    use GuzzleHttp\Psr7\Response;
    use Psr\Http\Message\RequestInterface;

    class CoreHttpTest extends TestCase
    {
        public function testConstruct()
        {
            $http = new Http();

            $this->assertInstanceOf(Client::class, $http->getClient());
        }

        /**
         * Get guzzle mock client.
         *
         * @param null $expected
         *
         * @return \GuzzleHttp\Client
         */
        public function getGuzzleWithResponse($expected = null)
        {
            $guzzle = \Mockery::mock(Client::class);

            $status = 200;
            $headers = ['X-Foo' => 'Bar'];
            $body = $expected;
            $protocol = '1.1';

            $guzzle->shouldReceive('request')->andReturn(new Response($status, $headers, $body, $protocol));

            return $guzzle;
        }

        /**
         * Test request() with json response.
         */
        public function testRequestWithJsonResponse()
        {
            $http = new Http();
            $http->setClient($this->getGuzzleWithResponse(json_encode(['errcode' => '0', 'errmsg' => 'ok'])));
            $this->assertEquals(['errcode' => '0', 'errmsg' => 'ok'], json_decode($http->request('http://example.org', 'GET')->getBody(), true));

            $http->setClient($this->getGuzzleWithResponse(json_encode(['foo' => 'bar'])));

            $response = $http->request('http://example.org', 'GET');

            $this->assertEquals(json_encode(['foo' => 'bar']), $response->getBody());

            $http->setClient($this->getGuzzleWithResponse('non-json content'));
            $response = $http->request('http://example.org', 'GET');

            $this->assertEquals('non-json content', $response->getBody());
        }

        /**
         * Test parseJSON().
         */
        public function testParseJSON()
        {
            $http = new Http();
            $http->setClient($this->getGuzzleWithResponse('{foo: "bar"}'));
            try {
                $http->parseJSON($http->request('http://example.org', 'GET'));
                $this->fail('Invalid json body check fail.');
            } catch (HttpException $e) {}

            $http->setClient($this->getGuzzleWithResponse('{"foo":"bar"}'));
            $this->assertEquals(['foo' => 'bar'], $http->parseJSON($http->request('http://example.org', 'GET')));

            $http->setClient($this->getGuzzleWithResponse(''));
            $this->assertNull($http->parseJSON($http->request('http://example.org', 'GET')));

            $http->setClient($this->getGuzzleWithResponse('null'));
            $this->assertNull($http->parseJSON($http->request('http://example.org', 'GET')));
        }

        /**
         * Test get().
         */
        public function testGet()
        {
            $guzzle = \Mockery::mock(Client::class);
            $http = \Mockery::mock(Http::class.'[request]');
            $http->setClient($guzzle);

            $http->shouldReceive('request')->andReturnUsing(function ($url, $method, $body) {
                return compact('url', 'method', 'body');
            });

            $response = $http->get('http://example.org', ['foo' => 'bar']);

            $this->assertEquals('http://example.org', $response['url']);
            $this->assertEquals('GET', $response['method']);
            $this->assertEquals(['query' => ['foo' => 'bar']], $response['body']);
        }

        /**
         * Test post().
         */
        public function testPost()
        {
            $guzzle = \Mockery::mock(Client::class);
            $http = \Mockery::mock(Http::class.'[request]');
            $http->setClient($guzzle);

            $http->shouldReceive('request')->andReturnUsing(function ($url, $method, $body) {
                return compact('url', 'method', 'body');
            });

            // array
            $response = $http->post('http://example.org', ['foo' => 'bar']);

            $this->assertEquals('http://example.org', $response['url']);
            $this->assertEquals('POST', $response['method']);
            $this->assertEquals(['form_params' => ['foo' => 'bar']], $response['body']);

            // string
            $response = $http->post('http://example.org', 'hello here.');

            $this->assertEquals('http://example.org', $response['url']);
            $this->assertEquals('POST', $response['method']);
            $this->assertEquals(['body' => 'hello here.'], $response['body']);
        }

        /**
         * Test json().
         */
        public function testJson()
        {
            $guzzle = \Mockery::mock(Client::class);
            $http = \Mockery::mock(Http::class.'[request]');
            $http->setClient($guzzle);

            $http->shouldReceive('request')->andReturnUsing(function ($url, $method, $body) {
                return compact('url', 'method', 'body');
            });

            $response = $http->json('http://example.org', ['foo' => 'bar']);

            $this->assertEquals('http://example.org', $response['url']);
            $this->assertEquals('POST', $response['method']);

            $this->assertEquals([], $response['body']['query']);
            $this->assertEquals(json_encode(['foo' => 'bar']), $response['body']['body']);
            $this->assertEquals(['content-type' => 'application/json'], $response['body']['headers']);

            $response = $http->json('http://example.org', ['foo' => 'bar'], [], JSON_UNESCAPED_UNICODE);

            $this->assertEquals('http://example.org', $response['url']);
            $this->assertEquals('POST', $response['method']);

            $this->assertEquals([], $response['body']['query']);
            $this->assertEquals(json_encode(['foo' => 'bar']), $response['body']['body']);
            $this->assertEquals(['content-type' => 'application/json'], $response['body']['headers']);
        }

        /**
         * Test upload().
         */
        public function testUpload()
        {
            $guzzle = \Mockery::mock(Client::class);
            $http = \Mockery::mock(Http::class.'[request]');
            $http->setClient($guzzle);

            $http->shouldReceive('request')->andReturnUsing(function ($url, $method, $body) {
                return compact('url', 'method', 'body');
            });
            $xs = '';
            $response = $http->upload('http://example.org', ['overtrue' => 'easywechat'], ['foo' => 'bar', 'hello' => 'world']);

            $this->assertEquals('http://example.org', $response['url']);
            $this->assertEquals('POST', $response['method']);
            $this->assertContains(['name' => 'overtrue', 'contents' => 'easywechat'], $response['body']['multipart']);
            $this->assertEquals('foo', $response['body']['multipart'][0]['name']);
            $this->assertEquals('bar', $response['body']['multipart'][0]['contents']);
            $this->assertEquals('hello', $response['body']['multipart'][1]['name']);
            $this->assertEquals('world', $response['body']['multipart'][1]['contents']);
        }

        public function testUserHandler()
        {
            $oldDefaultOptions = Http::getDefaultOptions();

            $scheme = 'http';
            $statistics = [];
            Http::setDefaultOptions([
                'timeout' => 3,
                'handler' => Middleware::tap(function (RequestInterface $request) use (&$statistics, &$scheme) {
                    $api = $request->getUri()->getPath();
                    if (!isset($statistics[$api])) {
                        $statistics[$api] = 0;
                    }
                    ++$statistics[$api];
                    $scheme = $request->getUri()->getScheme();
                }),
            ]);

            $httpClient = \Mockery::mock(Client::class);
            $httpClient->shouldReceive('request')->andReturnUsing(function ($method, $url, $options) {
                $request = new Request($method, $url);
                if (isset($options['handler']) && ($options['handler'] instanceof HandlerStack)) {
                    $options['handler']($request, $options);
                }

                return new Response();
            });

            $http = new Http();
            $http->setClient($httpClient);
            $http->request('example.org/domain/action', 'GET');
            $this->assertSame(1, $statistics['/domain/action']);
            $this->assertSame('https', $scheme);

            Http::setDefaultOptions($oldDefaultOptions);
        }
    }
}

namespace QCloudSDK\Core {
    function fopen($file, $mode = 'r')
    {
        return $file;
    }
}
