<?php


namespace QCloudSDKTests\Core {

    use GuzzleHttp\Psr7\Request;
    use QCloudSDK\Core\Exceptions\HttpException;
    use QCloudSDK\Core\Http;
    use QCloudSDKTests\MockClient;
    use QCloudSDKTests\TestCase;
    use GuzzleHttp\Client;
    use GuzzleHttp\Middleware;
    use Psr\Http\Message\RequestInterface;

    class CoreHttpTest extends TestCase
    {
        public function testConstruct()
        {
            $http = new Http();

            $this->assertInstanceOf(Client::class, $http->getClient());
        }

        /**
         * Test request() with json response.
         */
        public function testRequestWithJsonResponse()
        {
            $http = new Http();
            $http->setClient(MockClient::make(json_encode(['errcode' => '0', 'errmsg' => 'ok'])));
            $this->assertEquals(['errcode' => '0', 'errmsg' => 'ok'], json_decode($http->request('http://example.org', 'GET')->getBody(), true));

            $http->setClient(MockClient::make(json_encode(['foo' => 'bar'])));

            $response = $http->request('http://example.org', 'GET');

            $this->assertEquals(json_encode(['foo' => 'bar']), $response->getBody());

            $http->setClient(MockClient::make('non-json content'));
            $response = $http->request('http://example.org', 'GET');

            $this->assertEquals('non-json content', $response->getBody());
        }

        /**
         * Test parseJSON().
         */
        public function testParseJSON()
        {
            $http = new Http();
            $http->setClient(MockClient::make('{foo: "bar"}'));
            try {
                $http->parseJSON($http->request('http://example.org', 'GET'));
                $this->fail('Invalid json body check fail.');
            } catch (HttpException $e) {}

            $http->setClient(MockClient::make('{"foo":"bar"}'));
            $this->assertEquals(['foo' => 'bar'], $http->parseJSON($http->request('http://example.org', 'GET')));

            $http->setClient(MockClient::make(''));
            $this->assertNull($http->parseJSON($http->request('http://example.org', 'GET')));

            $http->setClient(MockClient::make('null'));
            $this->assertNull($http->parseJSON($http->request('http://example.org', 'GET')));
        }

        /**
         * Test get().
         */
        public function testGet()
        {
            $http = $this->getReflectedHttp();
            $http->get('http://example.org', ['foo' => 'bar']);
            $this->assertRequest($http, function (RequestInterface $request) {
                $this->assertSame('GET', $request->getMethod());
                $this->assertSame('http://example.org?foo=bar', $request->getUri()->__toString());
            });
        }

        /**
         * Test post().
         */
        public function testPost()
        {
            $http = $this->getReflectedHttp();
            $params = ['foo' => 'bar'];

            // array
            $http->post('example.org', $params);
            $this->assertRequest($http, function(RequestInterface $request) use ($params){
                $this->assertSame('POST', $request->getMethod());
                $this->assertSame('https://example.org', $request->getUri()->__toString());
                $this->assertSame(http_build_query($params), $request->getBody()->__toString());
            });

            // string
            $http->post('http://example.org/', 'hello here.', $params);
            $this->assertRequest($http, function(RequestInterface $request){
                $this->assertSame('POST', $request->getMethod());
                $this->assertSame('http://example.org/?foo=bar', $request->getUri()->__toString());
                $this->assertSame('hello here.', $request->getBody()->__toString());
            });

        }

        /**
         * Test json().
         */
        public function testJson()
        {
            $http = $this->getReflectedHttp();
            $sample = ['foo' => 'b测a试r'];
            $query = ['op' => 'query'];

            $http->json('http://example.org', $sample, [], 0);
            $this->assertRequest($http, function(RequestInterface $request) use ($sample){
                $this->assertSame('POST', $request->getMethod());
                $this->assertSame('http://example.org', $request->getUri()->__toString());
                $this->assertSame(json_encode($sample), $request->getBody()->__toString());
                $this->assertSame('application/json', $request->getHeaderLine('Content-Type'));
            });

            $http->json('http://example.org', $sample, $query, JSON_UNESCAPED_UNICODE);
            $this->assertRequest($http, function(RequestInterface $request) use ($sample, $query){
                $this->assertSame('POST', $request->getMethod());
                $this->assertSame('http://example.org?' . http_build_query($query), $request->getUri()->__toString());
                $this->assertSame(json_encode($sample, JSON_UNESCAPED_UNICODE), $request->getBody()->__toString());
                $this->assertSame('application/json', $request->getHeaderLine('Content-Type'));
            });

        }

        /**
         * Test upload().
         */
        public function testUpload()
        {
            $http = $this->getReflectedHttp();
            $http->upload('http://example.org', ['var' => 'poi'], ['foo' => 'bar', 'hello' => 'world'], ['op' => 'upload']);

            $this->assertRequest($http, function(Request $request){
                $this->assertSame('POST', $request->getMethod());
                $this->assertSame('http://example.org?op=upload', $request->getUri()->__toString());
                $body = strval($request->getBody());
                $this->assertContains("Content-Disposition: form-data; name=\"foo\"\r\nContent-Length: 3\r\n\r\nbar", $body);
                $this->assertContains("Content-Disposition: form-data; name=\"hello\"\r\nContent-Length: 5\r\n\r\nworld", $body);
                $this->assertContains("Content-Disposition: form-data; name=\"var\"\r\nContent-Length: 3\r\n\r\npoi", $body);
            });

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

            $http = new Http();
            $http->setClient(MockClient::make());
            $http->request('example.org/domain/action', 'GET');
            $this->assertSame('https', $scheme);
            $this->assertSame(1, $statistics['/domain/action']);

            Http::setDefaultOptions($oldDefaultOptions);
        }
    }
}

namespace QCloudSDK\Core {
    function fopen($file, $mode = 'r')
    {
        unset($mode);
        return $file;
    }
}
