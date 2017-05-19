<?php


namespace QCloudSDKTests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class MockClient
{

    /**
     * @param null $body
     * @param int $status
     * @param array $headers
     * @param string $protocol
     * @return Client
     */
    public static function make($body = null, $status = 200, array $headers = ['X-Foo' => 'Bar'], string $protocol = '1.1')
    {
        return static::makeFromMock(static::mock(static::repeatResponses(1, $body, $status, $headers, $protocol)));
    }

    /**
     * @param null $json
     * @param int $status
     * @param array $headers
     * @param string $protocol
     * @return Client
     */
    public static function makeJson($json = null, $status = 200, array $headers = ['X-Foo' => 'Bar'], string $protocol = '1.1')
    {
        return static::make(json_encode($json), $status, $headers, $protocol);
    }

    /**
     * @param int $quantity
     * @param null $body
     * @param int $status
     * @param array $headers
     * @param string $protocol
     * @return array
     */
    public static function repeatResponses(int $quantity, $body = null, $status = 200, array $headers = ['X-Foo' => 'Bar'], string $protocol = '1.1')
    {
        return array_fill(0, $quantity, new Response($status, $headers, $body, $protocol));
    }

    /**
     * @param array $responses
     * @return MockHandler
     */
    public static function mock(array $responses)
    {
        return new MockHandler($responses);
    }

    /**
     * @param MockHandler $mockHandler
     * @return Client
     */
    public static function makeFromMock(MockHandler $mockHandler)
    {
        return new Client(['handler' => HandlerStack::create($mockHandler)]);
    }
}
